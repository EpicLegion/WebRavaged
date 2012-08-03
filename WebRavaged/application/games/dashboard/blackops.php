<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Dashboard implementation for Call of Duty: Black Ops
 *
 * Copyright (c) 2010, EpicLegion
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA,
 * OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author        EpicLegion, Maximusya
 * @package        rcon
 * @subpackage    controller
 * @license        http://www.opensource.org/licenses/bsd-license.php    New BSD License
 */
class Controller_Dashboard extends Controller_Dashboard_Core {

    /**
     * Current action
     *
     * @var    string
     */
    protected $action = '';

    /**
     * Ajax refresh for player list
     */
    public function action_ajaxindex()
    {
        // Ajax?
        if(!Request::$is_ajax)
        {
            exit;
        }

        // Only logged in users
        $this->do_force_login();

        // Catch them all
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Init vars
            $first_team = array();
            $second_team = array();
            $spectators = array();

            // Get server info
            $server_info = $commands->get_server_info();
            $server_info += array('ip' => $this->current_server['ip'], 'port' => $this->current_server['port']);
            $server_info['colored_hostname'] = Blackops_Rcon_Constants::colorize($server_info['sv_hostname']);
            $server_info['playlist_name'] = Blackops_Rcon_Constants::$playlists[$server_info['playlist']];
            $server_info['map_name'] = Blackops_Rcon_Constants::$maps[$server_info['map']];
            $server_info['gametype_name'] = Blackops_Rcon_Constants::$gametypes[$server_info['g_gametype']];
            $server_info['gametype_abbrev'] = Blackops_Rcon_Constants::$gametypes_abbrev[$server_info['g_gametype']];

            // Cache
            $this->session->set('last_server_info', $server_info);

            // Iterate players
            foreach($server_info['players'] as $player)
            {
                // Get correct IP
                if($pos = strpos($player['address'], ':'))
                {
                    $player['ip'] = substr($player['address'], 0, $pos);
                }
                else
                {
                    $player['ip'] = $player['address'];
                }

                // Democlient?
                if($player['name'] == 'democlient' AND Config::get('general.hide_democlient', TRUE))
                {
                    continue;
                }

                // Add to correct team
                if($player['team'] == 1)
                {
                    $first_team[] = $player;
                }
                elseif($player['team'] == 2)
                {
                    $second_team[] = $player;
                }
                else
                {
                    $spectators[] = $player;
                }
            }

            // Players count
            $server_info['count_players'] = count($server_info['players']);

            // Democlient is not a player
            if(isset($server_info['players'][0]) && $server_info['players'][0]['team'] == 3 && $server_info['players'][0]['name'] == 'democlient')
            {
                $server_info['count_players']--;
            }
        }
        catch(Exception $e)
        {
            // Transmit error
            echo json_encode(array('error' => __($e->getMessage()), 'content' => ''));
            exit;
        }

        // View
        $view = new View('blackops/ajax');

        // Server info
        $view->server_info = $server_info;
        $view->permissions = $this->current_server['permissions'];
        $view->first_team = $first_team;
        $view->second_team = $second_team;
        $view->spectators = $spectators;

        // Transmit content
        echo json_encode(array('error' => 'None', 'content' => $view->render()));
        exit;
    }

    /**
     * Ban player
     *
     * @param    string    $id
     */
    public function action_ban($id)
    {
        // ID is numeric
        if(!ctype_digit($id))
        {
            echo json_encode(array('error' => 'Invalid parameter', 'content' => ''));
            exit;
        }

        // Reason required?
        if (Config::get('general.kick_reason_required', FALSE) AND empty($_POST['reason']))
        {
            echo json_encode(array('error' => 'Reason required', 'content' => ''));
            exit;
        }

        // Cast
        $id = (int) $id;

        // Ajax?
        if(!Request::$is_ajax)
        {
            exit;
        }

        // Only logged in users
        $this->do_force_login();

        // Can ban
        if(!$this->check_permissions(SERVER_BAN, FALSE))
        {
            echo json_encode(array('error' => 'No permission', 'content' => ''));
            exit;
        }

        // Decode message
        if($_POST['reason'])
        {
            $reason = html_entity_decode($_POST['reason'], ENT_QUOTES, 'UTF-8');
        }
        else
        {
            $reason = '';
        }

        // Dropdown?
        if(count(Config::get('kick_reasons', array())) AND $reason)
        {
            // Check
            if(!in_array($reason, Config::get('kick_reasons', array())))
            {
                echo json_encode(array('error' => 'Invalid reason', 'content' => ''));
                exit;
            }
        }

        // Catch them all
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Ban
            $commands->ban($id);

            // Last server info
            $server_info = $this->session->get('last_server_info');

             // Player name not available?
            if(!$server_info OR !is_array($server_info) OR !isset($server_info['error']) OR $server_info['error'] != '')
            {
                // Log
                $this->log_action(__('Banned player with ID #:id (Reason: :reason)', array(':id' => $id, ':reason' => HTML::chars($reason))));
            }
            else
            {
                // Server info available, find player
                if(isset($server_info['players'][$id]))
                {
                    // Player found, log name
                    $this->log_action(__('Banned player :name (Reason: :reason)', array(
                    ':name' => $server_info['players'][$id]['name'],
                    ':reason' => HTML::chars($reason)
                    )));
                }
                else
                {
                    // Player not found, log ID
                    $this->log_action(__('Banned player with ID #:id (Reason: :reason)', array(
                    ':id' => (int) $id,
                    ':reason' => HTML::chars($reason)
                    )));
                }
            }

            // No error
            echo json_encode(array('error' => 'None', 'content' => ''));
            exit;
        }
        catch(Exception $e)
        {
            // Nasty error :<
            echo json_encode(array('error' => $e->getMessage(), 'content' => ''));
            exit;
        }
    }

    /**
     * Next map in rotation
     */
    public function action_next_map()
    {
        // Only logged in users
        $this->do_force_login();

        // Can fast restart?
        $this->check_permissions(SERVER_FAST_RESTART, TRUE);

        // Catch them all
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Command
            $rcon->command('map_rotate');

            // Done
            $this->notice(__('Changed to next map'));

            // Log
            $this->log_action(__('Used Change to next map command'));
        }
        catch(Exception $e)
        {
            // Error
            $this->notice($e->getMessage());
        }

        // Redirect
        $this->request->redirect('dashboard/index');
    }


    /**
     * Fast restart
     */
    public function action_fast_restart()
    {
        // Only logged in users
        $this->do_force_login();

        // Can fast restart?
        $this->check_permissions(SERVER_FAST_RESTART, TRUE);

        // Catch them all
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Command
            $rcon->command('fast_restart');

            // Done
            $this->notice(__('Fast restart done'));

            // Log
            $this->log_action(__('Used fast restart command'));
        }
        catch(Exception $e)
        {
            // Error
            $this->notice($e->getMessage());
        }

        // Redirect
        $this->request->redirect('dashboard/index');
    }

    /**
     * Index page
     */
    public function action_index()
    {
        // Action
        $this->action = 'index';

        // Only logged in users
        $this->do_force_login();

        // Title
        $this->title = __('Remote console');

        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Init vars
            $first_team = array();
            $second_team = array();
            $spectators = array();

            // Get server info
            $server_info = $commands->get_server_info();
            $server_info += array('ip' => $this->current_server['ip'], 'port' => $this->current_server['port']);
            $server_info['colored_hostname'] = Blackops_Rcon_Constants::colorize($server_info['sv_hostname']);
            $server_info['playlist_name'] = Blackops_Rcon_Constants::$playlists[$server_info['playlist']];
            $server_info['map_name'] = Blackops_Rcon_Constants::$maps[$server_info['map']];
            $server_info['gametype_name'] = Blackops_Rcon_Constants::$gametypes[$server_info['g_gametype']];
            $server_info['gametype_abbrev'] = Blackops_Rcon_Constants::$gametypes_abbrev[$server_info['g_gametype']];

            // Cache
            $this->session->set('last_server_info', $server_info);

            // Iterate players
            foreach($server_info['players'] as $player)
            {
                // Get correct IP
                if($pos = strpos($player['address'], ':'))
                {
                    $player['ip'] = substr($player['address'], 0, $pos);
                }
                else
                {
                    $player['ip'] = $player['address'];
                }

                // Democlient?
                if ($player['name'] == 'democlient' AND Config::get('general.hide_democlient', TRUE))
                {
                    continue;
                }

                // Add to correct team
                if($player['team'] == 1)
                {
                    $first_team[] = $player;
                }
                elseif($player['team'] == 2)
                {
                    $second_team[] = $player;
                }
                else
                {
                    $spectators[] = $player;
                }
            }

            // Players count
            $server_info['count_players'] = count($server_info['players']);

            // Democlient is not a player
            if(isset($server_info['players'][0]) && $server_info['players'][0]['team'] == 3 && $server_info['players'][0]['name'] == 'democlient')
            {
                $server_info['count_players']--;
            }
        }
        catch(Exception $e) {
           // Default
           $server_info = array('map' => '', 'players' => array(), 'error' => __($e->getMessage()));
        }

        // View
        $this->view = new View('blackops/index');

        // Server info
        $this->view->server_info = $server_info;
        $this->view->permissions = (int) $this->current_server['permissions'];
        $this->view->first_team = $first_team;
        $this->view->second_team = $second_team;
        $this->view->spectators = $spectators;
    }

    /**
     * Kick player
     *
     * @param    string    $id
     */
    public function action_kick($id)
    {
        // ID is numeric
        if(!ctype_digit($id))
        {
            echo json_encode(array('error' => 'Invalid parameter', 'content' => ''));
            exit;
        }

        // Reason required?
        if(Config::get('general.kick_reason_required', FALSE) AND empty($_POST['reason']))
        {
            echo json_encode(array('error' => 'Reason required', 'content' => ''));
            exit;
        }

        // Cast
        $id = (int) $id;

        // Ajax?
        if(!Request::$is_ajax)
        {
            exit;
        }

        // Only logged in users
        $this->do_force_login();

        // Can kick
        if(!$this->check_permissions(SERVER_KICK, FALSE))
        {
            echo json_encode(array('error' => 'No permission', 'content' => ''));
            exit;
        }

        // Decode message
        if($_POST['reason'])
        {
            $reason = html_entity_decode($_POST['reason'], ENT_QUOTES, 'UTF-8');
        }
        else
        {
            $reason = '';
        }

        // Dropdown?
        if (count(Config::get('kick_reasons', array())) AND $reason)
        {
            // Check
            if(!in_array($reason, Config::get('kick_reasons', array())))
            {
                echo json_encode(array('error' => 'Invalid reason', 'content' => ''));
                exit;
            }
        }

        // Catch them all
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Kick
            $commands->kick($id, FALSE, $reason);

            // Last server info
            $server_info = $this->session->get('last_server_info');

            // Player name not available?
            if(!$server_info OR !is_array($server_info) OR !isset($server_info['error']) OR $server_info['error'] != '')
            {
                // Log
                $this->log_action(__('Kicked player with ID #:id (Reason: :reason)', array(':id' => $id, ':reason' => HTML::chars($reason))));
            }
            else
            {
                // Server info available, find player
                if(isset($server_info['players'][$id]))
                {
                    // Player found, log name
                    $this->log_action(__('Kicked player :name (Reason: :reason)', array(
                    ':name' => $server_info['players'][$id]['name'],
                    ':reason' => HTML::chars($reason)
                    )));
                }
                else
                {
                    // Player not found, log ID
                    $this->log_action(__('Kicked player with ID #:id (Reason: :reason)', array(':id' => (int) $id, ':reason' => HTML::chars($reason))));
                }
            }

            // No error
            echo json_encode(array('error' => 'None', 'content' => ''));
            exit;
        }
        catch(Exception $e)
        {
            // Nasty error :<
            echo json_encode(array('error' => $e->getMessage(), 'content' => ''));
            exit;
        }
    }

    /**
     * View player logs
     *
     * @param    string    $guid
     */
    public function action_logs($guid = NULL)
    {
        // Action
        $this->action = 'logs';

        // Only logged in users
        $this->do_force_login();

        // Title
        $this->title = __('Player logs');

        // Check permissions
        $this->check_permissions(SERVER_USER_LOG);

        // Ajax request means player details
        if(Request::$is_ajax AND $guid !== NULL AND ctype_digit($guid))
        {
            // Find
            $log = Model_Player::get_details($this->current_server['id'], $guid);

            // Valid
            if(count($log))
            {
                // Unserialize names
                $log['names'] = empty($log['names']) ? array() : unserialize($log['names']);

                // Unserialize ip's
                $log['ip_addresses'] = empty($log['ip_addresses']) ? array() : unserialize($log['ip_addresses']);

                // Make sure it's array
                $log['names'] = !is_array($log['names']) ? array() : $log['names'];
                $log['ip_addresses'] = !is_array($log['ip_addresses']) ? array() : $log['ip_addresses'];

                // Clean up
                array_map('strip_tags', $log['names']);

                // Return
                echo json_encode(array('error' => 'None', 'ip' => '<li>'.implode('</li><li>', $log['ip_addresses']).'</li>',
                                       'names' => '<li>'.implode('</li><li>', $log['names']).'</li>'));
            }
            else
            {
                // Log not found
                echo json_encode(array('error' => 'Log not found'));
            }

            // Exit
            exit;
        }

        // Load conditions
        if($this->session->get('conditions_player_bo', FALSE))
        {
            // Load
            $conditions = $this->session->get('conditions_player_bo');
        }
        else
        {
            // Default
            $conditions = array('guid' => 0);
        }

        // Apply new conditions
        if(isset($_POST['guid']))
        {
            // User
            if(ctype_digit($_POST['guid']))
            {
                $conditions['guid'] = (int) $_POST['guid'];
            }
            else
            {
                $conditions['guid'] = 0;
            }

            // Save conditions
            $this->session->set('conditions_player_bo', $conditions);

            // Redirect
            $this->request->redirect('dashboard/logs');
        }

        // View
        $this->view = new View('blackops/logs');

        // Pagination
        $pagination = new Pagination(array(
            'current_page' => array('source' => 'route', 'key' => 'id'),
            'items_per_page' => 50,
            'auto_hide' => TRUE,
            'total_items' => Model_Player::count_logs($this->current_server['id'], $conditions)
        ));

        // Logs
        $this->view->logs = Model_Player::get_logs($this->current_server['id'], $pagination->offset, $conditions);
        $this->view->pagination = $pagination->render();
        $this->view->conditions = $conditions;
    }

    /**
     * Map management
     */
    public function action_maps()
    {
        // Action
        $this->action = 'maps';

        // Only logged in users
        $this->do_force_login();

        // Check permission
        $this->check_permissions(SERVER_MAPS);

        // Title
        $this->title = __('Map management');

        // Get presets
        $presets = Config::get_presets();

        // Catch errors
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Map rotation
            $cvar_rotation = $commands->get_cvar('sv_mapRotation');

            // Excludes
            $cvar_excludes = $commands->get_cvar('playlist_excludeMap');

            // Preset?
            if(!empty($_POST['preset']) AND isset($presets[$_POST['preset']]))
            {
                // Get preset
                $preset = $presets[$_POST['preset']];

                // Apply exclude
                if ($preset['excludes'] !== NULL)
                {
                    $commands->cvar('playlist_excludeMap', $preset['excludes'], TRUE);
                }

                // Gametype included?
                if(substr($preset['rotation'], 0, 8) != 'gametype')
                {
                    // Split using space as delimiter
                    $rotation2 = explode(' ', $cvar_rotation, 3);

                    // Set as new
                    $cvar_rotation = $preset;

                    // Add gametype
                    if($rotation2[0] == 'gametype' AND isset($rotation2[1]))
                    {
                        $cvar_rotation = 'gametype '.$rotation2[1].' '.$cvar_rotation;
                    }
                }
                else
                {
                    $cvar_rotation = $preset['rotation'];
                }

                // Send
                $commands->cvar('sv_mapRotation', $cvar_rotation, TRUE);

                // Success
                $this->notice(__('Preset successfully applied'));

                // Log
                $this->log_action(__('Applied map preset: :preset', array(':preset' => HTML::chars($_POST['preset']))));

                // Redirect
                $this->request->redirect('dashboard/maps');
            }

            // Current map
            $current_map = $commands->get_cvar('mapname');
        }
        catch(Exception $e)
        {
            // Error
            $this->notice(__('Cannot retrieve config vars'));

            // Redirect
            $this->request->redirect('dashboard/index');
        }

        // Maps
        $maps = Blackops_Rcon_Constants::$maps;

        // Excludes and rotation
        $excludes = $rotation = array();

        // Get excludes
        foreach(explode(' ', $cvar_excludes) as $e)
        {
            // Append if valid
            if(isset($maps[$e]))
            {
                $excludes[$e] = $e;
            }
        }

        // Get rotation
        foreach(explode(' ', $cvar_rotation) as $r)
        {
            // Mapname?
            if($r != 'map' AND isset($maps[$r]))
            {
                $rotation[] = $r;
            }
        }

        // Add remaining maps
        if(count($maps) != count($rotation))
        {
            foreach(array_keys($maps) as $k)
            {
                // Already added?
                if(!in_array($k, $rotation))
                {
                    $rotation[] = $k;
                }
            }
        }

        // View
        $this->view = new View('blackops/maps');

        // Vars
        $this->view->presets = $presets;
        $this->view->rotation = $rotation;
        $this->view->maps = $maps;
        $this->view->excludes = $excludes;
        $this->view->current_map = $current_map;
    }

    /**
     * Change map
     *
     * @param	string	$map
     */
    public function action_maps_change($map)
    {
        // Only logged in users
        $this->do_force_login();

        // Check permission
        $this->check_permissions(SERVER_MAPS);

        // Catch errors
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Send command
            $rcon->command('map '.strip_tags($map));

            // Notice
            $this->notice(__('Map successfully change'));

            // Log
            $this->log_action(__('Change map to: :map', array(':map' => HTML::chars($map))));
        }
        catch(Exception $e)
        {
            // Notice
            $this->notice(__($e->getMessage()));
        }

        // Redirect
        $this->request->redirect('dashboard/maps');
    }

    /**
     * Set next map
     *
     * @param	string	$map
     */
    public function action_maps_next($map)
    {
        // Only logged in users
        $this->do_force_login();

        // Check permission
        $this->check_permissions(SERVER_MAPS);

        // Maps
        $maps = Blackops_Rcon_Constants::$maps;

        // Valid map?
        if(!isset($maps[$map]))
        {
            throw new Exception('Invalid map');
        }

        // Remove from exclude list
        unset($maps[$map]);

        // Catch errors
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Send command
            $commands->cvar('playlist_excludeMap', implode(' ', array_keys($maps)), TRUE);

            // Notice
            $this->notice(__('Map successfully set as next'));

            // Log
            $this->log_action(__('Set next map: :map', array(':map' => HTML::chars($map))));
        }
        catch(Exception $e)
        {
            // Notice
            $this->notice($e->getMessage());
        }

        // Redirect
        $this->request->redirect('dashboard/maps');
    }

    /**
     * Update map rotation
     */
    public function action_maps_rotation()
    {
        // Only logged in users
        $this->do_force_login();

        // Check permission
        if(!$this->check_permissions(SERVER_MAPS, FALSE))
        {
            exit(__('No permissions'));
        }

        // Ajax?
        if(!Request::$is_ajax)
        {
            exit;
        }

        // Valid request?
        if(!isset($_POST['mp']) OR !is_array($_POST['mp']))
        {
            exit;
        }

        // Construct cvar
        $cvar = '';

        // Iterate maps
        foreach($_POST['mp'] as $v)
        {
            $cvar .= 'map mp_'.$v.' ';
        }

        // Trim spaces
        $cvar = trim($cvar);

        // Catch errors
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Get current
            $maps = explode(' ', $commands->get_cvar('sv_mapRotation'), 3);

            // Add gametype
            if($maps[0] == 'gametype' AND isset($maps[1]))
            {
                $cvar = 'gametype '.$maps[1].' '.$cvar;
            }

            // Send
            $commands->cvar('sv_mapRotation', $cvar, TRUE);
        }
        catch(Exception $e)
        {
            // Error
            exit($e->getMessage());
        }

        // Log
        $this->log_action(__('Changed map rotation'));

        // Done
        exit('Done');
    }

    /**
     * Exclude / include map
     *
     * @param	string	$map
     */
    public function action_maps_status($map)
    {
        // Only logged in users
        $this->do_force_login();

        // Check permission
        $this->check_permissions(SERVER_MAPS);

        // Maps
        $maps = Blackops_Rcon_Constants::$maps;

        // Valid map?
        if(!isset($maps[$map]))
        {
            throw new Exception('Invalid map');
        }

        // Catch errors
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Excludes
            $excludes = '';
            $excluded = FALSE;

            // Iterate excludes
            foreach(explode(' ', $commands->get_cvar('playlist_excludeMap')) as $e)
            {
                // Our map?
                if($e == $map)
                {
                    $excluded = TRUE;
                    continue;
                }

                // Append
                $excludes .= $e.' ';
            }

            // Exclude/include
            if(!$excluded)
            {
                $excludes .= $map;
            }

            // Send command
            $commands->cvar('playlist_excludeMap', trim($excludes), TRUE);

            // Notice
            $this->notice(__('Map successfully included/excluded'));

            // Log
            $this->log_action(__('Included/excluded map: :map', array(':map' => HTML::chars($map))));
        }
        catch(Exception $e)
        {
            // Notice
            $this->notice($e->getMessage());
        }

        // Redirect
        $this->request->redirect('dashboard/maps');
    }

    /**
     * Send global message or whisper
     */
    public function action_message()
    {
        // Check request vars
        if(!isset($_POST['message']) OR !isset($_POST['target']) OR (!ctype_digit($_POST['target']) AND $_POST['target'] != 'all'))
        {
            echo json_encode(array('error' => 'Invalid parameter', 'content' => ''));
            exit;
        }

        // Ajax?
        if(!Request::$is_ajax)
        {
            exit;
        }

        // Only logged in users
        $this->do_force_login();

        // Can message?
        $this->check_permissions(SERVER_MESSAGE);

        // Decode message
        $_POST['message'] = html_entity_decode($_POST['message'], ENT_QUOTES, 'UTF-8');

        // Dropdown?
        if (count(Config::get('messages', array())))
        {
            // Check
            if(!in_array($_POST['message'], Config::get('kick_reasons', array())))
            {
                echo json_encode(array('error' => 'Invalid message', 'content' => ''));
                exit;
            }
        }

        // Catch them all
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Log action
            if($_POST['target'] == 'all')
            {
                // Global message
                $this->log_action(__('Sent global message (:msg)', array(':msg' => HTML::chars($_POST['message']))));
            }
            else
            {
                // Last server info
                $server_info = $this->session->get('last_server_info');

                // Server info available?
                if(!$server_info OR !is_array($server_info) OR !isset($server_info['error']) OR $server_info['error'] != '')
                {
                    // Log
                    $this->log_action(__('Sent private message to #:id player (:msg)', array(
                    ':id' => (int) $_POST['target'],
                    ':msg' => HTML::chars($_POST['message'])
                    )));
                }
                else
                {
                    // Try finding player
                    if(isset($server_info['players'][(int) $_POST['target']]))
                    {
                        // Found, log name
                        $this->log_action(__('Sent private message to :name (:msg)', array(
                        ':name' => $server_info['players'][(int) $_POST['target']]['name'],
                        ':msg' => HTML::chars($_POST['message'])
                        )));
                    }
                    else
                    {
                        // Not found, ID
                        $this->log_action(__('Sent private message to #:id player (:msg)', array(
                        ':id' => (int) $_POST['target'],
                        ':msg' => HTML::chars($_POST['message'])
                        )));
                    }
                }
            }

            // Send message
            $commands->message($_POST['message'], $_POST['target']);

            // Success
            echo json_encode(array('error' => 'None', 'content' => ''));
            exit;
        }
        catch(Exception $e)
        {
            // Houston, We've Got a Problem
            echo json_encode(array('error' => $e->getMessage(), 'content' => ''));
            exit;
        }
    }

    /**
     * Message rotation
     *
     * @param    string    $id
     */
    public function action_msgrotation($id = NULL)
    {
        // Set action
        $this->action = 'msgrotation';

        // Only logged in users
        $this->do_force_login();

        // Title
        $this->title = __('Message rotation');

        // Check permissions
        $this->check_permissions(SERVER_MESSAGE_ROTATION);

        // Form submit
        if(isset($_POST['submit']) AND isset($_POST['message']))
        {
            // New object
            $message = new Model_Message;

            // Set user
            $message->user_id = $this->user->id;

            // Server
            $message->server_id = (int) $this->current_server['id'];

            // Message content
            $message->message = Security::xss_clean($_POST['message']);

            // Save
            $message->save();

            // Notice and log
            $this->notice(__('Message added'));
            $this->log_action(__('Added message to rotation'));

            // Redirect
            $this->request->redirect('dashboard/msgrotation');
        }

        // Remove
        if($id !== NULL AND ctype_digit($id))
        {
            // Find
            $message = new Model_Message((int) $id);

            // Found?
            if(!$message->loaded() OR $message->server_id != (int) $this->current_server['id'])
            {
                throw new Exception('Not found');
            }

            // Change rotation
            if($message->current == '1')
            {
                // Get random message
                $random = ORM::factory('message')->where('current', '=', '0')->find();

                // Found?
                if($random->loaded())
                {
                    // Set as current
                    $random->current = '1';

                    // Save
                    $random->save();
                }

                // Free memory
                unset($random);
            }

            // Delete
            $message->delete();

            // Notice and log
            $this->notice(__('Message removed'));
            $this->log_action(__('Removed rotation message'));

            // Redirect
            $this->request->redirect('dashboard/msgrotation');
        }

        // View
        $this->view = new View('blackops/messages');

        // Messages
        $this->view->messages = Model_Message::get_messages($this->current_server['id']);
    }

    /**
     * Delete playlist
     *
     * @param    string    $server_playlist_id
     */
    public function action_playlist_delete($server_playlist_id)
    {
        // Valid parameter?
        if(!ctype_digit($server_playlist_id))
        {
            throw new Kohana_Exception('Invalid request');
        }

        // Only logged in users
        $this->do_force_login();

        // Check permissions
        $this->check_permissions(SERVER_PLAYLIST);

        // Remove playlist
        DB::delete('servers_playlists')->where('server_playlist_id', '=', (int) $server_playlist_id)
                                       ->where('server_id', '=', (int) $this->current_server['id'])
                                       ->execute();

        // Log
        $this->log_action(__('Removed playlist with ID #:id', array(':id' => (int) $server_playlist_id)));

        // Notice and redirect
        $this->notice(__('Custom playlist deleted'));
        $this->request->redirect('dashboard/playlists');
    }

    /**
     * Edit playlist
     *
     * @param    string    $server_playlist_id
     */
    public function action_playlist_edit($server_playlist_id)
    {
        // Set action
        $this->action = 'playlists';

        // Validate parameter
        if(!ctype_digit($server_playlist_id))
        {
            throw new Kohana_Exception('Invalid request');
        }

        // Only logged in users
        $this->do_force_login();

        // Check permissions
        $this->check_permissions(SERVER_PLAYLIST);

        // Retrieve playlist
        $playlist_info = DB::select('server_playlist_name')->from('servers_playlists')
                                                           ->where('server_playlist_id', '=', (int) $server_playlist_id)
                                                           ->where('server_id', '=', (int) $this->current_server['id'])
                                                           ->execute();

        // Is this really a correct playlist?
        if(!count($playlist_info))
        {
            $this->notice(__('Invalid playlist'));
            $this->request->redirect('dashboard/playlists');
        }

        // First row
        $playlist_info = $playlist_info->as_array();
        $playlist_info = current($playlist_info);

        // Fill all playlists
        $all_playlists_res = DB::select()->from('playlists')
                                         ->where('gametype_codename', 'IN', array('tdm','dm','ctf','sd','koth','dom','sab','dem'))
                                         ->execute();

        // No rows
        if(count($all_playlists_res) <= 0)
        {
            throw new Kohana_Exception('List of playlists not found');
        }

        // Groups
        $grouped_playlists = array(
            'normal' => array(),
            'hardcore' => array(),
            'barebones' => array()
        );

        // Group playlists
        foreach($all_playlists_res as $value)
        {
            $grouped_playlists[$value['gamemode_codename']] += array($value['id'] => $value['name']);
        }

        // Process post
        if(isset($_POST['playlists_ids']) AND is_array($_POST['playlists_ids']) AND !empty($_POST['playlists_ids']))
        {
            // Playlist name
            $playlist_name = Security::xss_clean($_POST['playlist_name']);

            // Unique playlists
            $playlists_ids = array_unique($_POST['playlists_ids']);

            // Playlist ID's
            $all_playlists_ids = array_keys($all_playlists_res->as_array('id'));

            // Iterate playlists
            foreach($playlists_ids as $playlist_id)
            {
                // Invalid playlist
                if(!in_array($playlist_id, $all_playlists_ids))
                {
                    // Notice and redirect
                    $this->notice(__('Invalid default playlist specified'));
                    $this->request->redirect('dashboard/playlists');
                }
            }

            // Already exists?
            if(count(DB::select()->from('servers_playlists')->where('server_id', '=', (int) $this->current_server['id'])
                                                           ->where('server_playlist_name', '=', $playlist_name)
                                                           ->where('server_playlist_id', '<>', (int) $server_playlist_id)->execute()))
            {
                $this->notice(__('This playlist name already exists'));
                $this->request->redirect('dashboard/playlists');
            }

            // Update playlist name
            if($playlist_name <> $playlist_info['server_playlist_name'])
            {
                // Update
                DB::update('servers_playlists')->set(array('server_playlist_name' => $playlist_name))
                                               ->where('server_playlist_id', '=', (int) $server_playlist_id)
                                               ->where('server_id', '=', (int) $this->current_server['id'])
                                               ->execute();
            }

            // Delete old, insert new
            DB::delete('custom_playlists')->where('server_playlist_id', '=', (int) $server_playlist_id)
                                          ->where('server_id', '=', (int) $this->current_server['id'])
                                          ->execute();

            // Create query
            $insert = DB::insert('custom_playlists', array('server_playlist_id', 'server_id', 'playlist_id'));

            // Iterate playlists
            foreach($playlists_ids as $playlist_id)
            {
                // Append
                $insert->values(array($server_playlist_id, $this->current_server['id'], $playlist_id));
            }

            // Insert rows
            $insert->execute();

            // Log
            $this->log_action(__('Edited playlist: :name', array(':name' => HTML::chars($playlist_name))));

            // Notice and redirect
            $this->notice(__('Playlist saved'));
            $this->request->redirect('dashboard/playlists');
        }

        // Set page title
        $this->title = __('Playlist edit');

        // Umm... yeah
        $playlists_in_custom_playlist = DB::select('custom_playlists.playlist_id', 'playlists.name')
                                          ->from('custom_playlists')
                                          ->where('server_id', '=', (int) $this->current_server['id'])
                                          ->where('server_playlist_id', '=', (int) $server_playlist_id)
                                          ->join('playlists')->on('custom_playlists.playlist_id', '=', 'playlists.id')
                                          ->execute();

        // Setup playlist
        $playlist['id'] = $server_playlist_id;
        $playlist['name'] = $playlist_info['server_playlist_name'];
        $playlist['playlists'] = $playlists_in_custom_playlist->as_array();

        // View
        $this->view = new View('blackops/playlist_edit');

        // Assign
        $this->view->playlist = $playlist;
        $this->view->grouped_playlists = $grouped_playlists;
    }

    /**
     * Playlists
     *
     * @param    string                $playlist_id_to_switch
     * @param    string                $active
     * @throws    Kohana_Exception
     */
    public function action_playlists($playlist_id_to_switch = NULL, $active = NULL)
    {
        // Action
        $this->action = 'playlists';

        // Only logged in users
        $this->do_force_login();

        // Title
        $this->title = __('Playlists');

        // Check permissions
        $this->check_permissions(SERVER_PLAYLIST);

        // Fill all playlists
        $all_playlists_res = DB::select()->from('playlists')
                                         ->where('gametype_codename', 'IN', array('tdm','dm','ctf','sd','koth','dom','sab','dem'))
                                         ->execute();

        // Any playlist?
        if(count($all_playlists_res) <= 0)
        {
            throw new Kohana_Exception('List of playlists not found');
        }

        // ID's
        $all_playlists_ids = array_keys($all_playlists_res->as_array('id'));

        // Switch playlist activity
        if(ctype_digit($playlist_id_to_switch) AND ($active == 0 OR $active == 1))
        {
            // Valid playlist?
            if(!in_array((int) $playlist_id_to_switch, $all_playlists_ids))
            {
                // Redirect
                $this->notice(__('Invalid playlist specified'));
                $this->request->redirect('dashboard/playlists');
            }

            // Update
            DB::update('servers_playlists')->set(array('is_active' => (int) $active))
              ->where('server_id', '=', (int) $this->current_server['id'])
              ->where('server_playlist_id', '=', $playlist_id_to_switch)
              ->execute();

            // Deactivate different playlists
            if($active)
            {
                DB::update('servers_playlists')->set(array('is_active' => 0))
                  ->where('server_id', '=', (int) $this->current_server['id'])
                  ->where('server_playlist_id', '<>', $playlist_id_to_switch)->execute();
            }

            // Set notice
            if($active)
            {
                // Notice
                $this->notice(__('Custom playlist activated'));

                // Log
                $this->log_action(__('Activated playlist with ID #:id', array(':id' => (int) $playlist_id_to_switch)));
            }
            else
            {
                // Notice
                $this->notice(__('Custom playlist deactivated'));

                // Log
                $this->log_action(__('Deactivated playlist with ID #:id', array(':id' => (int) $playlist_id_to_switch)));
            }

            // Redirect
            $this->request->redirect('dashboard/playlists');
        }

        // Add new custom playlist
        if(isset($_POST['playlists_ids']) AND is_array($_POST['playlists_ids']) AND !empty($_POST['playlists_ids']))
        {
            // Vars
            $playlist_name = $_POST['playlist_name'];
            $make_active = isset($_POST['make_active']);

            // Unique playlist ID's
            $playlists_ids = array_unique($_POST['playlists_ids']);

            // Iterate playlists
            foreach($playlists_ids as $playlist_id)
            {
                // Invalid playlist?
                if(!in_array($playlist_id, $all_playlists_ids))
                {
                    // Notice and redirect
                    $this->notice(__('Invalid default playlist specified'));
                    $this->request->redirect('dashboard/playlists');
                }
            }

            // Already exists?
            if(count(DB::select()->from('servers_playlists')
                                 ->where('server_id', '=', (int) $this->current_server['id'])
                                 ->where('server_playlist_name', '=', Security::xss_clean($playlist_name))->execute()))
            {
                // Notice and redirect
                $this->notice(__('This playlist name already exists'));
                $this->request->redirect('dashboard/playlists');
            }

            // Add playlist
            $res = DB::insert('servers_playlists', array('server_id', 'server_playlist_name', 'is_active'))->values(array(
                (int) $this->current_server['id'], Security::xss_clean($playlist_name), $make_active
            ))->execute();

            // Last insert ID
            $server_playlist_id = $res[0];

            // Deactivate playlists
            if($make_active)
            {
                DB::update('servers_playlists')->set(array('is_active' => 0))
                  ->where('server_id', '=', (int) $this->current_server['id'])
                  ->where('server_playlist_id', '<>', $server_playlist_id)->execute();
            }

            // Create INSERT query
            $insert = DB::insert('custom_playlists', array('server_playlist_id', 'server_id', 'playlist_id'));

            // Add playlist
            foreach($playlists_ids as $playlist_id)
            {
                // Add VALUES
                $insert->values(array($server_playlist_id, $this->current_server['id'], $playlist_id));
            }

            // Execute INSERT query
            $insert->execute();

            // Notice
            $this->notice(__('Custom playlist added'));

            // Log
            $this->log_action(__('Created new playlist: :name', array(':name' => HTML::chars($playlist_name))));

            // Redirect
            $this->request->redirect('dashboard/playlists');
        }

        // Fill custom playlists
        $playlists = array();

        // Retrieve custom playlists
        $custom_playlists = DB::select('server_playlist_id', 'server_playlist_name', 'is_active')->from('servers_playlists')
                              ->where('server_id', '=', (int) $this->current_server['id'])
                              ->execute();

        // Iterate custom playlists
        foreach ($custom_playlists as $custom_playlist)
        {
            // Uhh... nevermind
            $playlists_in_custom_playlist = DB::select('custom_playlists.playlist_id', 'playlists.name', 'playlists.gametype_codename', 'playlists.gamemode_codename', 'playlists.size')
                                            ->from('custom_playlists')
                                            ->where('server_id', '=', (int) $this->current_server['id'])
                                            ->where('server_playlist_id', '=', $custom_playlist['server_playlist_id'])
                                            ->join('playlists')->on('custom_playlists.playlist_id', '=', 'playlists.id')
                                            ->execute();

            // Gametypes array
            $gametypes = array();

            // Iterate
            foreach($playlists_in_custom_playlist as $playlist)
            {
                // Append gametype
                $gametypes[] = array(
                    'name' => $playlist['name'],
                    'abbrev' => Blackops_Rcon_Constants::$gametypes_abbrev[$playlist['gametype_codename']],
                    'mode' => $playlist['gamemode_codename'],
                );
            }

            // Add playlist
            $playlists[] = array(
                'id' => $custom_playlist['server_playlist_id'],
                'name' => $custom_playlist['server_playlist_name'],
                'is_active' => $custom_playlist['is_active'],
                'gametypes' => $gametypes,
            );
        }

        // Init array
        $grouped_playlists = array(
            'normal'=>array(),
            'hardcore'=>array(),
            'barebones'=>array()
        );

        // Iterate playlists
        foreach($all_playlists_res as $playlist)
        {
            // Append to correct group
            $grouped_playlists[$playlist['gamemode_codename']] += array($playlist['id'] => $playlist['name']);
        }

        // View
        $this->view = new View('blackops/playlists');

        // Assign
        $this->view->playlists = $playlists;
        $this->view->grouped_playlists = $grouped_playlists;
    }

    /**
     * Temporary ban
     *
     * @param    string    $id
     */
    public function action_tempban($id)
    {
        // ID is numeric
        if(!ctype_digit($id))
        {
            echo json_encode(array('error' => 'Invalid parameter', 'content' => ''));
            exit;
        }

        // Reason required?
        if(Config::get('general.kick_reason_required', FALSE) AND empty($_POST['reason']))
        {
            echo json_encode(array('error' => 'Reason required', 'content' => ''));
            exit;
        }

        // Cast
        $id = (int) $id;

        // Ajax?
        if(!Request::$is_ajax)
        {
            exit;
        }

        // Only logged in users
        $this->do_force_login();

        // Can ban
        if(!$this->check_permissions(SERVER_TEMP_BAN, FALSE))
        {
            echo json_encode(array('error' => 'No permission', 'content' => ''));
            exit;
        }

        // Decode message
        if($_POST['reason'])
        {
            $reason = html_entity_decode($_POST['reason'], ENT_QUOTES, 'UTF-8');
        }
        else
        {
            $reason = '';
        }

        // Dropdown?
        if(count(Config::get('kick_reasons', array())) AND $reason)
        {
            // Check
            if(!in_array($reason, Config::get('kick_reasons', array())))
            {
                echo json_encode(array('error' => 'Invalid reason', 'content' => ''));
                exit;
            }
        }

        // Catch them all
        try {
            // Rcon connection
            $rcon = new Blackops_Rcon($this->current_server['ip'], $this->current_server['port'], $this->current_server['password']);
            $rcon->connect();

            // Commands
            $commands = new Blackops_Rcon_Commands($rcon);

            // Temporary ban
            $commands->temp_ban($id);

            // Last server info
            $server_info = $this->session->get('last_server_info');

            // Server info available?
            if(!$server_info OR !is_array($server_info) OR !isset($server_info['error']) OR $server_info['error'] != '')
            {
                // Log
                $this->log_action(__('Temp-banned player with ID #:id (Reason: :reason)', array(':id' => $id, ':reason' => HTML::chars($reason))));
            }
            else
            {
                // Try finding player
                if(isset($server_info['players'][$id]))
                {
                    // Found, log name
                    $this->log_action(__('Temp-banned player :name (Reason: :reason)', array(
                    ':name' => $server_info['players'][$id]['name'],
                    ':reason' => HTML::chars($reason)
                    )));
                }
                else
                {
                    // Not found, ID
                    $this->log_action(__('Temp-banned player with ID #:id (Reason: :reason)', array(
                    ':id' => (int) $id,
                    ':reason' => HTML::chars($reason)
                    )));
                }
            }

            // Success
            echo json_encode(array('error' => 'None', 'content' => ''));
            exit;
        }
        catch(Exception $e)
        {
            // Error
            echo json_encode(array('error' => $e->getMessage(), 'content' => ''));
            exit;
        }
    }

    /**
     * Navigation bar
     *
     * @see blackops/application/classes/controller/Controller_Main::after()
     */
    public function after()
    {
        // Assign navigation
        $this->view->navigation = new View('blackops/navigation');

        // Action
        $this->view->navigation->action = $this->action;

        // Owned servers
        $this->view->navigation->owned = $this->owned;

        // Current server ID
        $this->view->navigation->current_server_id = $this->current_server['id'];

        // Permissions
        $this->view->navigation->permissions = (int) $this->current_server['permissions'];

        // Execute parent
        parent::after();
    }

    /**
     * Log action
     *
     * @param    string    $action
     */
    protected function log_action($action)
    {
        // Add server name?
        if (Config::get('general.log_servername', TRUE) AND isset($this->current_server['name']))
        {
            $action = '('.$this->current_server['name'].') '.$action;
        }

        // Call parent
        parent::log_action($action);
    }
}