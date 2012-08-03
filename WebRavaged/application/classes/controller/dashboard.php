<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Dashboard main controller
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
abstract class Controller_Dashboard_Core extends Controller_Main {

    /**
     * @var    array
     */
    protected $current_server = NULL;

    /**
     * @var    array
     */
    protected $owned = array();

    /**
     * Set server
     *
     * @param    string    $id
     */
    public function action_set_server($id = NULL)
    {
        // Invalid request
        if( (!isset($_POST['server']) OR !ctype_digit($_POST['server']))
            AND
            (!isset($_GET['server']) OR !ctype_digit($_GET['server']))
            AND
            !ctype_digit($id)
        )
        {
            throw new Kohana_Exception('Invalid request');
        }

        // Only logged in users
        $this->do_force_login();

        // ID
        $id = (int) (
                    isset($_POST['server']) ? $_POST['server'] :
                    ( isset($_GET['server']) ? $_GET['server'] :
                           $id )
                    );
        $found = FALSE;

        // Iterate
        foreach($this->owned as $o)
        {
            if($o['server_id'] == $id)
            {
                $found = TRUE;
                break;
            }
        }

        // Found?
        if(!$found)
        {
            throw new Kohana_Exception('Invalid server');
        }

        // Free memory
        unset($found);

        // Now try fetch it
        $id = ORM::factory('server', $id);

        // Found?
        if(!$id->loaded())
        {
            throw new Kohana_Exception('Invalid server');
        }

        // Set
        $this->session->set('current_server', $id->id);

        // Redirect
        $this->request->redirect('dashboard/index');
    }

    /**
     * Current server and owned servers
     *
     * @see blackops/application/classes/controller/Controller_Main::before()
     */
    public function before()
    {
        // Parent
        parent::before();

        // Get owned and current server
        $this->current_server = Gameloader::$current_server;
        $this->owned = Gameloader::$owned_servers;

        // Owned servers everywhere
        View::factory()->set_global('owned', $this->owned);
    }

    /**
     * Check permissions
     *
     * @param     int        $bit
     * @param    bool    $redirect
     */
    protected function check_permissions($bit, $redirect = TRUE)
    {
        // Check
        $bit = (bool) ($bit & $this->current_server['permissions']);

        // Redirect?
        if($redirect AND !$bit)
        {
            // Set
            $this->notice(__('No permissions'));

            // Redirect
            $this->request->redirect('dashboard/index');
        }

        // Return
        return $bit;
    }
}

// Load dashboard file
Gameloader::load_dashboard();