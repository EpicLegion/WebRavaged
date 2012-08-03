<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Server manager
 *
 * Copyright (c) 2011, EpicLegion
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
 * @author     EpicLegion
 * @package    cod_daemon
 * @subpackage core
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

final class ServerManager
{
    /**
     * @var array
     */
    static protected $servers = array();

    /**
     * Cleanup server (optionally leave one)
     */
    public static function clear($id, $connect = TRUE)
    {
        // Remove everything except this one
        foreach (self::$servers as $k => $v)
        {
            // Check
            if ($k != $id)
            {
                self::$servers[$k]->disconnect();
                unset(self::$servers[$k]);
            }
            else
            {
                try
                {
                    self::$servers[$k]->connect();
                }
                catch(Exception $e)
                {
                    // Wait
                    usleep(250);

                    // Try again
                    self::$servers[$k]->connect();
                }
            }
        }
    }
    
    /**
     * Get server list
     *
     * @return array
     */
    public static function get()
    {
        return self::$servers;
    }

    /**
     * Load all servers
     */
    public static function init($connect = TRUE)
    {
        foreach (Database::instance()->getAll("SELECT * FROM [prefix]servers") as $s)
        {
            // To num
            $s['id'] = (int) $s['id'];
            
            // New object
            $server = new Server($s['id'], (int) $s['port'], $s['ip'], $s['password'], $s['name'], $s['log_url']);

            // Rcon connection
            if ($connect)
            {
                try
                {
                    $server->connect();
                }
                catch(Exception $e)
                {
                    // Wait
                    usleep(250);

                    // Try again
                    try
                    {
                        $server->connect();
                    }
                    catch(Exception $e)
                    {
                        // Ignore server
                        continue;
                    }
                }
            }

            self::$servers[$s['id']] = $server;
        }
    }

    /**
     * Reload
     */
    public static function reload()
    {
        // Loaded plugins
        $loaded = array();

        // Iterate
        foreach (Database::instance()->getAll("SELECT * FROM [prefix]servers") as $s)
        {
            // ID as int
            $s['id'] = (int) $s['id'];

            // Loaded?
            $loaded[$s['id']] = TRUE;

            // Isset?
            if (isset(self::$servers[$s['id']]))
            {
                continue;
            }
            
            // New object
            $server = new Server($s['id'], (int) $s['port'], $s['ip'], $s['password'], $s['name'], $s['log_url']);

            // Rcon connection
            try
            {
                $server->connect();
            }
            catch(Exception $e)
            {
                // Wait
                usleep(250);

                // Try again
                try
                {
                    $server->connect();
                }
                catch(Exception $e)
                {
                    // Ignore server
                    continue;
                }
            }

            self::$servers[$server->id] = $server;
        }

        // Remove old servers
        foreach (self::$servers as $v)
        {
            // Remove?
            if (!isset($loaded[$v->id]))
            {
                // Disconnect
                $v->disconnect();

                // Remove from list
                unset(self::$servers[$v->id]);
            }
        }
    }
    
    /**
     * Resume servers
     */
    public static function resume(array $servers = array())
    {
        // Set
        self::$servers = $servers;
        
        // Reconnect
        foreach (self::$servers as $s)
        {
            // Rcon connection
            try
            {
                $s->connect();
            }
            catch(Exception $e)
            {
                // Wait
                usleep(250);

                // Try again
                try
                {
                    $s->connect();
                }
                catch(Exception $e)
                {
                    // Ignore server
                    unset(self::$servers[$s->id]);
                }
            }
        }
    }
    
    /**
     * Unload servers
     */
    public static function unload()
    {
        // Iterate
        foreach (self::$servers as $server)
        {
            // Disconnect
            $server->disconnect();
        }

        // Empty
        self::$servers = array();
    }   
}