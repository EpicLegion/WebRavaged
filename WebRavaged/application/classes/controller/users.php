<?php defined('SYSPATH') or die('No direct script access.');

/**
 * User controller
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
class Controller_Users extends Controller_Main {

    /**
     * Delete user
     *
     * @param    string                $id
     * @throws     Kohana_Exception
     */
    public function action_delete($id)
    {
        // Validate ID
        if(!ctype_digit($id))
        {
            throw new Kohana_Exception('Invalid parameter');
        }

        // Get user
        $id = ORM::factory('user', (int) $id);

        // Validate
        if(!$id->loaded())
        {
            throw new Kohana_Exception('User not found');
        }

        // Selfkill?
        if($id->id == $this->user->id)
        {
            $this->notice(__('You cannot delete yourself'));
            $this->request->redirect('users');
        }

        // Log this action
        $this->log_action(__('Deleted user: :user', array(':user' => $id->username)));

        // Delete
        $id->delete();

        // Removed
        $this->notice(__('User has been successfully removed'));

        // Redirect
        $this->request->redirect('users');
    }

    /**
     * Edit user
     *
     * @param    string                $id
     * @throws    Kohana_Exception
     */
    public function action_edit($id)
    {
        // Validate ID
        if(!ctype_digit($id))
        {
            throw new Kohana_Exception('Invalid parameter');
        }

        // Get user
        $id = ORM::factory('user', (int) $id);

        // Validate
        if(!$id->loaded())
        {
            throw new Kohana_Exception('User not found');
        }

        // Get roles
        $log_role = ORM::factory('role', array('name' => 'logs'));
        $users_role = ORM::factory('role', array('name' => 'users'));
        $servers_role = ORM::factory('role', array('name' => 'servers'));

        // Form
        if(!empty($_POST))
        {
            // Change password?
            if(isset($_POST['password']) AND !empty($_POST['password']))
            {
                $id->password = $_POST['password'];
            }

            // Add/remove log management permission
            if(!$id->has('roles', $log_role) AND isset($_POST['can_log']) AND $_POST['can_log'] == '1')
            {
                $id->add('roles', $log_role);
            }
            elseif($id->has('roles', $log_role) AND (!isset($_POST['can_log']) OR $_POST['can_log'] != '1'))
            {
                $id->remove('roles', $log_role);
            }

            // User management
            if(!$id->has('roles', $users_role) AND isset($_POST['can_users']) AND $_POST['can_users'] == '1')
            {
                $id->add('roles', $users_role);
            }
            elseif($id->has('roles', $users_role) AND (!isset($_POST['can_users']) OR $_POST['can_users'] != '1'))
            {
                $id->remove('roles', $users_role);
            }

            // Server management
            if(!$id->has('roles', $servers_role) AND isset($_POST['can_servers']) AND $_POST['can_servers'] == '1')
            {
                $id->add('roles', $servers_role);
            }
            elseif($id->has('roles', $servers_role) AND (!isset($_POST['can_servers']) OR $_POST['can_servers'] != '1'))
            {
                $id->remove('roles', $servers_role);
            }

            // Log this action
            $this->log_action(__('Updated user account: :user', array(':user' => $id->username)));

            // Save
            $id->save();

            // Done
            $this->notice(__('User has been successfully updated'));

            // Redirect
            $this->request->redirect('users');
        }

        // Title
        $this->title = __('Edit user account');

        // View
        $this->view = new View('users/edit');
        $this->view->user = $id;

        // Retrieve current user permissions
        $this->view->can_log = $id->has('roles', $log_role);
        $this->view->can_users = $id->has('roles', $users_role);
        $this->view->can_servers = $id->has('roles', $servers_role);
    }

    /**
     * View users
     */
    public function action_index()
    {
        // Submitted form?
        if(isset($_POST['username']) AND !empty($_POST['username']))
        {
            // Check other required fields
            if(!isset($_POST['password']) OR empty($_POST['password']) OR !isset($_POST['password_confirm']) OR empty($_POST['password_confirm'])
               OR !isset($_POST['email']) OR empty($_POST['email']))
            {
                // Notice
                $this->notice(__('Password, password confirmation and email is required'));

                // Redirect
                $this->request->redirect('users/index');
            }

            // Validate username
            if(!preg_match('/^[-\pL\pN_.]++$/uD', $_POST['username']))
            {
                $error = 'Invalid username format';
            }

            // Username length
            if(UTF8::strlen($_POST['username']) > 32 OR UTF8::strlen($_POST['username']) < 4)
            {
                $error = 'Invalid username length (min. 4, max. 32)';
            }

            // Password
            if(UTF8::strlen($_POST['password']) > 42 OR UTF8::strlen($_POST['password']) < 5)
            {
                $error = 'Invalid password length (min. 5, max. 42)';
            }

            // Password confirmation
            if($_POST['password_confirm'] != $_POST['password'])
            {
                $error = 'Entered passwords does not match';
            }

            // Email
            if(UTF8::strlen($_POST['email']) > 127 OR UTF8::strlen($_POST['email']) < 4 OR !Validate::email($_POST['email']))
            {
                $error = 'Invalid email (format or length)';
            }

            // Already exists?
            if(ORM::factory('user', array('username' => $_POST['username']))->loaded())
            {
                $error = 'The username is already in use';
            }

            // Email?
            if(ORM::factory('user', array('email' => $_POST['email']))->loaded())
            {
                $error = 'Email is already in use';
            }

            // Any errors?
            if(isset($error))
            {
                // Notice
                $this->notice(__($error));

                // Redirect
                $this->request->redirect('users/index');
            }

            // New user object
            $user = new Model_User;

            // Validate once more
            if($user->values($_POST)->check())
            {
                // Save
                $user->save();

                // Add login role
                $user->add('roles', new Model_Role(array('name' => 'login')));

                // Logs management permission
                if(isset($_POST['can_log']) AND $_POST['can_log'] == '1')
                {
                    $user->add('roles', new Model_Role(array('name' => 'logs')));
                }

                // User management
                if(isset($_POST['can_users']) AND $_POST['can_users'] == '1')
                {
                    $user->add('roles', new Model_Role(array('name' => 'users')));
                }

                // Server management
                if(isset($_POST['can_servers']) AND $_POST['can_servers'] == '1')
                {
                    $user->add('roles', new Model_Role(array('name' => 'servers')));
                }

                // Log action
                $this->log_action(__('Added user: :user', array(':user' => $user->username)));

                // Notify user
                $this->notice(__('User has been added'));

                // Redirect
                $this->request->redirect('users');
            }
            else
            {
                // Unknown error
                $this->notice(__('Cannot create user account'));

                // Redirect
                $this->request->redirect('users');
            }
        }

        // Current title
        $this->title = __('User management');

        // View
        $this->view = new View('users/index');

        // Retrieve users
        $this->view->users = ORM::factory('user')->find_all();
    }

    /**
     * Actions log
     */
    public function action_logs()
    {
        // Set title
        $this->title = __('Log management');

        // View
        $this->view = new View('users/logs');

        // Load conditions
        if($this->session->get('conditions_log', FALSE))
        {
            // Load
            $conditions = $this->session->get('conditions_log');
        }
        else
        {
            // Default
            $conditions = array('user' => '', 'content' => '', 'date_from' => '', 'date_to' => time(), 'ip' => '');
        }

        // Apply new conditions
        if(isset($_POST['user']) AND isset($_POST['ip']) AND isset($_POST['content']) AND isset($_POST['date_from']) AND isset($_POST['date_to']))
        {
            // User
            $conditions['user'] = Security::xss_clean($_POST['user']);

            // Content
            $conditions['content'] = Security::xss_clean($_POST['content']);

            // Content
            $conditions['ip'] = Security::xss_clean($_POST['ip']);

            // Date (from)
            if($_POST['date_from'] AND strtotime($_POST['date_from']))
            {
                $conditions['date_from'] = strtotime($_POST['date_from']);
            }
            else
            {
                $conditions['date_from'] = '';
            }

            // Date (to)
            if($_POST['date_to'] AND strtotime($_POST['date_to']))
            {
                $conditions['date_to'] = strtotime($_POST['date_to']);
            }
            else
            {
                $conditions['date_to'] = time();
            }

            // Save conditions
            $this->session->set('conditions_log', $conditions);

            // Redirect
            $this->request->redirect('users/logs');
        }

        // Pagination
        $pagination = new Pagination(array(
            'current_page' => array('source' => 'route', 'key' => 'id'),
            'items_per_page' => 50,
            'auto_hide' => TRUE,
            'total_items' => Model_Log::count($conditions)
        ));

        // Get logs
        $this->view->logs = Model_Log::get('logs.id', 'DESC', $conditions, $pagination->offset, 50);

        // Set pagination
        $this->view->pagination = $pagination->render();

        // Conditions
        $this->view->conditions = $conditions;
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
        $this->tab = 'users';

        // Check permissions
        if($this->request->action == 'logs')
        {
            $this->do_force_login('logs');
        }
        else
        {
            $this->do_force_login('users');
        }
    }
}