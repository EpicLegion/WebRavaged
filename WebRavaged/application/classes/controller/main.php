<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Main controller
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
 * @author        EpicLegion
 * @package        rcon
 * @subpackage    controller
 * @license        http://www.opensource.org/licenses/bsd-license.php    New BSD License
 */
abstract class Controller_Main extends Controller {

    /**
     * @var    Auth
     */
    protected $auth = NULL;

    /**
     * @var    Database
     */
    protected $db = NULL;

    /**
     * @var    string
     */
    protected $layout = 'layout/backend';

    /**
     * @var    Session
     */
    protected $session = NULL;

    /**
     * @var    string
     */
    protected $tab = 'rcon';

    /**
     * @var    string
     */
    protected $title = '';

    /**
     * @var    Model_User
     */
    protected $user = NULL;

    /**
     * @var    View
     */
    protected $view = NULL;

    /**
     * Display layout with content
     *
     * @see blackops/system/classes/kohana/Kohana_Controller::after()
     */
    public function after()
    {
        // No layout?
        if(!$this->layout)
        {
            return;
        }

        // Init layout
        $layout = new View($this->layout);

        // Set title, notice and current tab
        $layout->title = $this->title;
        $layout->notice = $this->session->get_once('rcon_notice');
        $layout->tab = $this->tab;

        // Set content
        $layout->content = ($this->view == NULL) ? '' : $this->view->render();

        // Render layout
        echo $layout->render();
    }

    /**
     * Set-up objects
     *
     * @see blackops/system/classes/kohana/Kohana_Controller::before()
     */
    public function before()
    {
        // Database
        $this->db = Database::instance();

        // Session
        $this->session = Session::instance();

        // Auth
        $this->auth = Auth::instance();

        // Get current user
        $this->user = $this->auth->get_user();
    }

    /**
     * Force login
     *
     * @param    string    $role
     */
    protected function do_force_login($role = 'login')
    {
        // Validate
        if(!$this->auth->logged_in($role))
        {
            // Guest of insufficient permissiosn?
            if(!$this->user)
            {
                // Redirect to login page
                $this->request->redirect('login/index');
            }
            else
            {
                // Set notice and redirect to dashboard
                $this->notice(__('No permissions'));
                $this->request->redirect('dashboard/index');
            }
        }
    }

    /**
     * Log action
     *
     * @param    string    $action
     */
    protected function log_action($action)
    {
        // Send to model
        Model_Log::add($this->user->id, $action, $this->request->client_ip);
    }

    /**
     * Shortcut function used for changing notice
     *
     * @param    string    $notice
     */
    protected function notice($notice)
    {
        // Change `rcon_notice` session variable
        $this->session->set('rcon_notice', $notice);
    }
}