<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Personal tab controller
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
class Controller_Personal extends Controller_Main {

    /**
     * Personal settings
     */
    public function action_index()
    {
        // Set page title
        $this->title = __('Personal settings');

        // Set page view
        $this->view = new View('personal/index');
    }

    /**
     * Change password
     */
    public function action_update_password()
    {
        // Validate fields
        if(!isset($_POST['current_password']) OR !isset($_POST['password']) OR !isset($_POST['confirm_password'])
           OR !$_POST['current_password'] OR !$_POST['password'] OR !$_POST['confirm_password'])
        {
            // Redirect
            $this->request->redirect('personal/index');
        }

        // Check password
        if($this->user->password != $this->auth->hash_password($_POST['current_password'], $this->auth->find_salt($this->user->password)))
        {
            // Error
            $this->notice(__('Entered password is invalid'));

            // Redirect
            $this->request->redirect('personal/index');
        }

        // Match passwords
        if($_POST['password'] != $_POST['confirm_password'])
        {
            // Error
            $this->notice(__('Entered passwords does not match'));

            // Redirect
            $this->request->redirect('personal/index');
        }

        // Set password
        $this->user->password = $_POST['password'];

        // Save
        $this->user->save();

        // Success
        $this->notice(__('Password has been sucessfully changed'));

        // Redirect
        $this->request->redirect('personal/index');
    }

    /**
     * Set current tab and check permissions
     *
     * @see application/classes/controller/Controller_Main::before()
     */
    public function before()
    {
        // Run parent constructor
        parent::before();

        // Set tab
        $this->tab = 'personal';

        // Check permissions
        $this->do_force_login();
    }
}