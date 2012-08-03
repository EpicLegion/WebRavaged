<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Server model (ORM)
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
 * @subpackage    model
 * @license        http://www.opensource.org/licenses/bsd-license.php    New BSD License
 */
class Model_Server extends ORM {

    /**
     * Add permissions
     *
     * @param    int    $user
     * @param    int    $server
     * @param    int    $bitset
     * @param    int    $template
     */
    public static function add_permissions($user, $server, $bitset = 0, $template = 0)
    {
        // Insert
        DB::insert('servers_users', array('user_id', 'server_id', 'permissions', 'template_id'))
          ->values(array(
          'user_id' => (int) $user,
          'server_id' => (int) $server,
          'permissions' => (int) $bitset,
          'template_id' => (int) $template
          ))->execute();
    }

    /**
     * Delete permissions
     *
     * @param    int    $user
     * @param    int    $server
     */
    public static function delete_permissions($user, $server)
    {
        // Delete
        DB::delete('servers_users')->where('user_id', '=', (int) $user)->where('server_id', '=', (int) $server)->execute();
    }

    /**
     * Edit permissions
     *
     * @param    int    $user
     * @param    int    $server
     * @param    int    $bitset
     * @param    int    $template_id
     */
    public static function edit_permissions($user, $server, $bitset = 0, $template_id = 0)
    {
        // Update
        DB::update('servers_users')
          ->set(array('permissions' => (int) $bitset, 'template_id' => (int) $template_id))
          ->where('user_id', '=', (int) $user)
          ->where('server_id', '=', (int) $server)
          ->execute();
    }

    /**
     * Get server details
     *
     * @param     int        $server
     * @return    array
     */
    public static function get_details($server)
    {
        // Find server
        $server = DB::select('*')->limit(1)->from('servers')->where('id', '=', (int) $server)->execute();

        // Found?
        if(count($server) > 0)
        {
            // Cast port to int
            $server[0]['port'] = (int) $server[0]['port'];

            // Return
            return $server[0];
        }
        else
        {
            // Not found
            return array();
        }
    }

    /**
     * Get servers owned by user
     *
     * @param    int    $user
     */
    public static function get_owned($user)
    {
        // Owned
        $owned = DB::select('server_id', 'permissions')
                   ->from('servers_users')
                   ->where('user_id', '=', $user)
                   ->execute();

        // No servers?
        if(count($owned) <= 0)
        {
            return array();
        }

        // Get available servers
        $servers = array();

        // Iterate
        foreach($owned as $o)
        {
            // Server ID and permissions to int
            $o['server_id'] = (int) $o['server_id'];
            $o['permissions'] = (int) $o['permissions'];

            // Set
            $servers[$o['server_id']] = $o;
        }

        // Get server names
        foreach(DB::select('id', 'name')->from('servers')->where('id', 'IN', array_keys($servers))->execute() as $serv)
        {
            $servers[$serv['id']] += $serv;
        }

        // Return
        return $servers;
    }

    /**
     * Get user->server permissions
     *
     * @param    int                            $user
     * @param    int                            $server
     * @return    Database_Result|bool|int
     */
    public static function get_permissions($user = NULL, $server = NULL)
    {
        // All permissions?
        if($user == NULL OR $server == NULL)
        {
            return DB::select()->from('servers_users')->execute();
        }

        // Query
        $query = DB::select('permissions')->from('servers_users')
                   ->where('user_id', '=', (int) $user)
                   ->where('server_id', '=', (int) $server)
                   ->execute();

        // Owned?
        if(count($query) <= 0)
        {
            return FALSE;
        }

        // Array
        $query = $query->as_array();

        // Return bitset
        return (int) $query[0]['permissions'];
    }

    /**
     * Get user->server permission template
     *
     * @param    int                            $user
     * @param    int                            $server
     * @return    Database_Result|bool|int
     */
    public static function get_template($user = NULL, $server = NULL)
    {
        // All permissions?
        if($user == NULL OR $server == NULL)
        {
            return DB::select()->from('servers_users')->execute();
        }

        // Query
        $query = DB::select('template_id')->from('servers_users')
                   ->where('user_id', '=', (int) $user)
                   ->where('server_id', '=', (int) $server)
                   ->execute();

        // Owned?
        if(count($query) <= 0)
        {
            return FALSE;
        }

        // Array
        $query = $query->as_array();

        // Return bitset
        return (int) $query[0]['template_id'];
    }

    /**
     * Is specified server owned by user?
     *
     * @param    int    $user
     * @param    int    $server
     */
    public static function is_owned($user, $server)
    {
        // Query
        $query = DB::select('user_id')->from('servers_users')
                                      ->where('user_id', '=', (int) $user)
                                      ->where('server_id', '=', (int) $server)
                                      ->execute();

        // Is owned?
        return count($query) > 0 ? TRUE : FALSE;
    }
}