<?php
/**
 * Custom rotation plugin
 * To enable it add the following text into your cron/ini/config.ini:
; ------------------------------
; Custom rotation
; ------------------------------
[customRotation]
enabled = 1;
 * @author Maximusya
 *
 */
class CustomRotation_Plugin implements Plugin {
    
    /**
     * @var    Database
     */
    protected $db = NULL;
    
    /**
     * @var    Rcon
     */
    protected $rcon = NULL;
    
    /**
     * @var Rcon_Commands
     */
    protected $commands = NULL;
    
    /**
     * @var array
     */
    protected $status = NULL;
    
    /**
     * @var array
     */
    protected $server = NULL;
    
    /**
     * @var array
     */
    protected $custom_playlist = NULL;
    
    /**
     * Load plugin
     *
     * @param    Database    $db
     * @param    array        $config
     */
    public function load(Database $db, array $config)
    {
        // Enabled?
        if($config['customRotation']['enabled'] == '1')
        {
             // Database
            $this->db = $db;           
                
            // Event
            Event::register('onServerInfo', array($this, 'onServerInfo'));
            
        }
    }
    
    /**
     * On receive server info
     *
     * @param    array    $params
     */
    public function onServerInfo(array $params = array())
    {
        /*
         * $params is data structure with server vars, player list etc..:
         * 
         */
        
        $server_playlist_res = $this->db->getSingle("SELECT server_playlist_id FROM [prefix]servers_playlists WHERE server_id = :server_id AND is_active = 1", 
                                                    array(':server_id' => $params['server']['id']));
        if ( !$server_playlist_res ) return;
        
        $this->rcon = $params['rcon'];
        $this->commands = new Rcon_Commands($this->rcon);
        $this->status = $params['status'];
        $this->server = $params['server'];
        
        
        $this->server = array_merge($this->server, $server_playlist_res);
        $this->custom_playlist = $this->getCustomPlaylist();
        
        $playlist = $this->status['playlist'];
            $playlist_name = Rcon_Constants::$playlists[$playlist];
        $gametype = $this->status['g_gametype'];
            $gametype_name = Rcon_Constants::$gametypes[$gametype];
        $map      = $this->status['mapname'];
            $map_name = Rcon_Constants::$maps[$map];
        $servername = $this->status['sv_hostname'];
        
        /*echo "Now playing on $servername:\n";
        echo "Playlist: $playlist_name ($playlist)\n";
        echo "Gametype: $gametype_name ($gametype)\n";
        echo "Map:      $map_name ($map)\n";
        echo "Players:  ", count($this->status['players']), "\n";*/
        
        $this->rotate();
    }
    
    private function rotate()
    {    
        $current_playlist_id = $this->status['playlist']; // may differ from currently running map because pls has been changed recently
        
        if ( !in_array($current_playlist_id, array_keys($this->custom_playlist)) )
        {
            // our custom rotation is to begin for the 1st time
            $force_rotate = true;
        }
        
        /*
         * We compare current pls with the one derived from gametype!
         * if current != derived - do nothing (i.e we have already set next pls)
         * if current = derived - change playlist
         * 
         */
        $running_playlist_id = $this->getRunningPlaylistId();
        
        
        if ( (int)$current_playlist_id === (int)$running_playlist_id || $force_rotate)
        {
            $total = count($this->custom_playlist);
            $window = $this->getWindowSize();
            /*
             * if window covers whole playlist, do nothing
             * if window is not filled, just select next not in a window and then put into the window
             * if window if filled, remove the oldest and then select random new - and put it into the window
             */
            if ( $window == $total && !$force_rotate ) return true;
            
            if ( $window == $this->getCountPlaylistsInWindow() )
            {
                $oldest_playlist_id = $this->getOldestPlaylistInWindow();
                $this->dewindowPlaylist($oldest_playlist_id);
            }
            
            $next_playlist_id = $this->getNextPlaylistId();
            $this->windowPlaylist($next_playlist_id);
            
            $this->commands->cvar('playlist', $next_playlist_id);
            $this->commands->message('^2Next gametype: ^1'.Rcon_Constants::$playlists[$next_playlist_id]);

            echo date('H:i:s'), ' Server ', $this->server['id'], ' NP: ', Rcon_Constants::$playlists[$running_playlist_id], ", ", Rcon_Constants::$maps[$this->status['mapname']],". ";
            echo 'Next: ', Rcon_Constants::$playlists[$next_playlist_id], "\n";
        }
        else 
        {
            $this->commands->message('^2Next gametype: ^1'.Rcon_Constants::$playlists[$current_playlist_id]);
            //echo '    Next: ', Rcon_Constants::$playlists[$current_playlist_id], "\n";
            // we may additionally check whether the running pls is the one we set previousely, but doing nothing would suffice for now.  
        }
    }
    
    private function getWindowSize()
    {
        $window = (int) ceil (count($this->custom_playlist) / 2);
        return $window;
    }
    
    private function getCountPlaylistsInWindow()
    {
        if ( empty($this->custom_playlist) ) return false;
        
        $count = 0;
        foreach ($this->custom_playlist as $playlist)
        {
            if ( $playlist['in_window'] == 1 )
                $count++;
        }
        return $count;
    }
    
    private function getOldestPlaylistInWindow()
    {
        if ( empty($this->custom_playlist) ) return false;
        
        foreach ($this->custom_playlist as $id => $playlist)
        {
            if ( $playlist['in_window'] == 1 && 
                 intval($playlist['last_set']) > 0 && 
                 ( !isset($min) || intval($playlist['last_set']) < intval($min['last_set']) ))
            {
                $min_id = $id;
                $min = $playlist;
            }
        }
        return $min_id;
    }
    
    private function getNextPlaylistId()
    {
        if ( empty($this->custom_playlist) ) return false;
        
        foreach ( $this->custom_playlist as $id => $playlist )
        {
            if ( !$playlist['in_window'] )
                $candidates[$id] = $id;
        }
        
        $next_id = array_rand($candidates);
        
        return $next_id;
    }
    
    private function dewindowPlaylist($playlist_id)
    {
        // Dewindow in DB
        $pls_dewindowed = $this->db->exec("UPDATE [prefix]custom_playlists SET in_window = 0 
                                        WHERE server_id = :server_id AND server_playlist_id = :server_playlist_id AND playlist_id = :playlist_id
                                        LIMIT 1",
                                        array(':server_id' => $this->server['id'],
                                              ':server_playlist_id' => $this->server['server_playlist_id'],
                                              ':playlist_id' => $playlist_id));
        // Dewindow in php object unless there is only 1 remaining in window so that next random playlist would not be the current
        if ( $this->getCountPlaylistsInWindow() > 1 )
        {
            $this->custom_playlist[$playlist_id]['in_window'] = 0;
        }
    }
    
    private function windowPlaylist($playlist_id)
    {
        $pls_windowed = $this->db->exec("UPDATE [prefix]custom_playlists SET in_window = 1, last_set = :time
                                        WHERE server_id = :server_id AND server_playlist_id = :server_playlist_id AND playlist_id = :playlist_id LIMIT 1",
                                        array(':time' => time(),
                                              ':server_id' => $this->server['id'],
                                              ':server_playlist_id' => $this->server['server_playlist_id'],
                                              ':playlist_id' => $playlist_id));
        $this->custom_playlist[$playlist_id]['in_window'] = 1;
    }
    
    private function getRunningPlaylistId()
    {
        $current_playlist_id = $this->status['playlist'];
        $gametype = $this->status['g_gametype'];
        
        $playlist_params = $this->db->getSingle("SELECT gametype_codename, gamemode_codename, size FROM [prefix]playlists WHERE id = :id", array(':id' => $current_playlist_id));
        
        if ( !$playlist_params ) return false;
        
        $suggested_playlist = $this->db->getSingle("SELECT id FROM [prefix]playlists WHERE gametype_codename = :gametype AND gamemode_codename = :gamemode AND size = :size", 
                                                     array(':gametype' => $gametype,
                                                            ':gamemode' => $playlist_params['gamemode_codename'],
                                                            ':size' => $playlist_params['size']));
        if (!$suggested_playlist) return false;
        
        return $suggested_playlist['id'];
    }
    
    private function getCustomPlaylist()
    {
        $custom_playlist = $this->db->getAll("SELECT playlist_id, in_window, last_set FROM [prefix]custom_playlists 
                                              WHERE server_id = :server_id AND server_playlist_id = :server_playlist_id", 
                                             array(':server_id' => $this->server['id'],
                                                   ':server_playlist_id' => $this->server['server_playlist_id']),
                                             'playlist_id');
        
        return $custom_playlist;
    }
    
    /**
     * 
     * @deprecated
     */
    private function install()
    {
        $my_scripts_folder = ROOT_PATH.'plugins/customRotation';
        
        /* 1. Install gametypes */
        $this->db->executeScheme($my_scripts_folder . '/gametypes.sql');
        
        /* 2. Install gamemodes */
        $this->db->executeScheme($my_scripts_folder . '/gamemodes.sql');
        
        /* 3. Install default playlists */
        $this->db->executeScheme($my_scripts_folder . '/playlists.sql');
        
        /* 4. Install servers-playlists table */
        $this->db->executeScheme($my_scripts_folder . '/servers_playlists.sql');
        
        /* 5. Install custom playlist */
        $this->db->executeScheme($my_scripts_folder . '/custom_playlists.sql');
    }
    
    /**
     * 
     * @deprecated
     */
    private function addCustomPlaylist($server_id, $pls)
    {    
        $affected_rows = $this->db->exec("INSERT INTO [prefix]servers_playlists (server_id, server_playlist_name, is_active) VALUE (:server_id, :server_playlist_name, :is_active)",
                                        array(':server_id' => $server_id,
                                                ':server_playlist_name' => 'Test Playlist #1',
                                              ':is_active' => 1));
        if ( $affected_rows !== 1 ) throw new Exception("Failed to add playlist for server $server_id");
        
        $server_playlist_id = $this->db->getLastInsertID();
        
        if ( !$server_playlist_id ) throw new Exception("Failed to get newly added playlist id for server $server_id");
        
        foreach ($pls as $pls_id)
        {
            $values_arr[] = "($server_id,$server_playlist_id,$pls_id)";
        }
        $values_str = implode(',', $values_arr);
        
        $this->db->exec("INSERT INTO [prefix]custom_playlists (server_id, server_playlist_id, playlist_id) VALUES $values_str");
    }
    
    /**
     * 
     * @deprecated
     */
    private function install_from_php()
    {
        /* 1. Install default playlists */
        $this->db->exec("DROP TABLE IF EXISTS [prefix]playlists;");
        $this->db->exec("CREATE TABLE IF NOT EXISTS [prefix]playlists (
                            `id` INT UNSIGNED NOT NULL ,
                            `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                            `gametype_codename` ENUM( 'tdm','dm','sab','dem','ctf','sd','dom','koth','hlnd','gun','shrp','oic' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL ,
                            `gamemode_codename` ENUM( 'normal','hardcore','barebones','wager' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL ,
                            `size` ENUM( 'normal', 'small' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'normal' ,
                            PRIMARY KEY ( `id` ) 
                        ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        foreach ( Rcon_Constants::$playlists as $id => $name )
        {
            $this->db->exec("INSERT INTO [prefix]playlists (`id`, `name`, `gametype_codename`, `gamemode_codename`, `size`) VALUE ( :id, :name, :gametype_codename, :gamemode_codename, :size )",
                            array(':id' => (int) $id,
                                  ':name' => $name,
                                  ':gametype_codename' => $gametype_codename,
                                  ':gamemode_codename' => $gamemode_codename,
                                  ':size' => $is_small ? 'small' : 'normal'));
        }
        
        /* 2. Install gametypes */
        $this->db->exec("DROP TABLE IF EXISTS [prefix]gametypes;");
        $this->db->exec("CREATE TABLE IF NOT EXISTS [prefix]gametypes (
                            `codename` CHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                            `fullname` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                            PRIMARY KEY ( `codename` )
                        ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        foreach ( Rcon_Constants::$gametypes as $codename => $fullname )
        {
            $this->db->exec("INSERT INTO [prefix]gametypes (`codename`, `fullname`) VALUE ( :codename, :fullname )",
                            array(':codename' => $codename,
                                  ':fullname' => $fullname));
        }
        
        /* 3. Install gamemodes */
        $this->db->exec("DROP TABLE IF EXISTS [prefix]gamemodes;");
        $this->db->exec("CREATE TABLE IF NOT EXISTS [prefix]gamemodes (
                            `codename` CHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                            `fullname` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                            PRIMARY KEY ( `codename` )
                        ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        foreach ( Rcon_Constants::$gamemodes as $codename => $fullname )
        {
            $this->db->exec("INSERT INTO [prefix]gamemodes (`codename`, `fullname`) VALUE ( :codename, :fullname )",
                            array(':codename' => $codename,
                                  ':fullname' => $fullname));
        }
        
        /* 4. Install custom playlist */
        
    }
}