<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Login/out controller
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
class Controller_Login extends Controller_Main {

    /**
     * Login form
     */
    public function action_index()
    {
        // Logged already?
        if($this->auth->logged_in())
        {
            $this->request->redirect('dashboard/index');
        }

        // Login form
        if(isset($_POST['username']) AND isset($_POST['password']))
        {
            // Try to login user
            $result = $this->auth->login(Security::xss_clean($_POST['username']), Security::xss_clean($_POST['password']), TRUE);

            // Success?
            if($result)
            {
                $this->notice(__('Login successful'));
                $this->request->redirect('dashboard/index');
            }
            else
            {
                $this->notice(__('Invalid username or password'));
            }
        }

        // Page title and view
        $this->title = __('Login to panel');
        $this->view = new View('login');

        // Frontend layout
        $this->layout = 'layout/frontend';
    }

    /**
     * Logout
     */
    public function action_out()
    {
        // Guests can't logout
        $this->do_force_login();

        // Logout
        $this->auth->logout(TRUE, TRUE);

        // Redirect
        $this->request->redirect('login/index');
    }
}