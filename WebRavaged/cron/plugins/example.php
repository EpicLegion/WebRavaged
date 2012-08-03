<?php
/**
 * Example plugin
 * Does nothing. Use as a starting point for your plugin
 *
 * To enable it add the following text into your cron/ini/config.ini:
 *
; ------------------------------
; Example plugin config
; ------------------------------
[example]
enabled = 1;
say_hello = 1;
 *
 * @author Maximusya
 *
 */
class Example_Plugin implements Plugin {

     /**
     * @var    Database
     */
    protected $db = NULL;

    /**
     * Load plugin
     *
     * @param    Database    $db
     * @param    array        $config
     */
    public function load(Database $db, array $config)
    {
        // Enabled?
        if (isset($config['example']) AND $config['example']['enabled'] == '1')
        {
             // Database
            $this->db = $db;

            /*
             * Here goes your plugin Init code (getting config values, initial quering DB etc.)
             * See message plugin for example
             * DO NOT CALL RCON COMMANDS HERE
             */
            if ( $config['example']['say_hello'] == '1' )
            {
                echo 'Hello, world!', "\n";
            }

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

        var_dump($params);

        /*
         * You will get something like this:
         *
            array(3) {
              ["status"]=>
              array(27) {
                ["map"]=>
                string(13) "mp_cosmodrome"
                ["players"]=>
                array(0) {
                }
                ["error"]=>
                string(0) ""
                ["com_maxclients"]=>
                int(19)
                ["g_gametype"]=>
                string(3) "dem"
                ["mapname"]=>
                string(13) "mp_cosmodrome"
                ["playlist"]=>
                int(30)
                ["playlist_enabled"]=>
                int(1)
                ["playlist_entry"]=>
                int(30)
                ["protocol"]=>
                int(1046)
                ["scr_team_fftype"]=>
                int(1)
                ["shortversion"]=>
                int(7)
                ["sv_disableClientConsole0"]=>
                string(0) ""
                ["sv_floodprotect"]=>
                int(4)
                ["sv_hostname"]=>
                string(45) "^2My ^1Test ^7Server"
                ["sv_maxclients"]=>
                int(18)
                ["sv_maxPing"]=>
                int(0)
                ["sv_maxRate"]=>
                int(25000)
                ["sv_minPing"]=>
                int(0)
                ["sv_pure"]=>
                int(1)
                ["sv_ranked"]=>
                int(2)
                ["sv_security"]=>
                int(1)
                ["sv_voice"]=>
                int(1)
                ["xblive_basictraining0"]=>
                string(0) ""
                ["xblive_privatematch"]=>
                int(0)
                ["xblive_rankedmatch"]=>
                int(0)
                ["xblive_wagermatch"]=>
                int(0)
              }
              ["server"]=>
              array(5) {
                ["id"]=>
                int(1)
                ["name"]=>
                string(6) "testserver"
                ["ip"]=>
                string(13) "192.168.0.1"
                ["port"]=>
                int(3264)
                ["password"]=>
                string(8) "hackme"
              }
              ["rcon"]=>
              object(Rcon)#5 (4) {
                ["socket:protected"]=>
                resource(18) of type (stream)
                ["password:protected"]=>
                string(8) "hackme"
                ["host:protected"]=>
                string(13) "192.168.0.1"
                ["port:protected"]=>
                int(3264)
              }
            }
         */
    }
}