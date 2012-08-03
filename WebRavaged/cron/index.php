<?php
/**
 * Cron execute
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
 * @subpackage    framework
 * @license        http://www.opensource.org/licenses/bsd-license.php    New BSD License
 */

// Pathes
define('ROOT_PATH', dirname(__FILE__).'/');
define('SYSPATH', ROOT_PATH.'../');

// Load files
require_once ROOT_PATH.'lib/Database.php';
require_once ROOT_PATH.'lib/Event.php';
require_once ROOT_PATH.'lib/Plugin.php';
//require_once ROOT_PATH.'lib/rcon/Fake.php';
require_once ROOT_PATH.'lib/Rcon.php';
require_once ROOT_PATH.'lib/rcon/Commands.php';
require_once ROOT_PATH.'lib/rcon/Statusparser.php';
require_once ROOT_PATH.'lib/rcon/Constants.php';

// Config
$config = parse_ini_file(ROOT_PATH.'ini/config.ini', TRUE);

// Database
$db = new Database(require SYSPATH.'application/config/database.php');

// Plugins
$plugins = array();

// Load plugins
foreach(scandir(ROOT_PATH.'plugins') as $file)
{
    // Accept only .php files
    if($file == '.' OR $file == '..' OR !is_file(ROOT_PATH.'plugins/'.$file) OR substr($file, -4, 4) !== '.php')
    {
        continue;
    }

    // File without .php
    $file = basename($file, '.php');

    // Load
    require_once ROOT_PATH.'plugins/'.$file.'.php';

    // Class name
    $class = ucfirst($file).'_Plugin';

    // Valid
    if(!class_exists($class))
    {
        continue;
    }

    // Load
    $class = new $class;

    // Valid once again
    if(!($class instanceof Plugin))
    {
        continue;
    }

    // Add
    $plugins[$file] = $class;

    // Load
    $plugins[$file]->load($db, $config);
}

// Iterate servers
foreach($db->getAll("SELECT * FROM [prefix]servers") as $server)
{
    // Black ops only ... for now
    if($server['game'] != 'blackops') continue;

    // Clean data
    $server['port'] = (int) $server['port'];
    $server['id'] = (int) $server['id'];

    // Rcon connection
    try {
        $rcon = new Rcon($server['ip'], $server['port'], $server['password']);
        $rcon->connect();
    }
    catch(Exception $e)
    {
        // Wait
        usleep(250);

        // Try again
        try {
            $rcon = new Rcon($server['ip'], $server['port'], $server['password']);
            $rcon->connect();
        }
        catch(Exception $e)
        {
            // Ignore server
            continue;
        }
    }

    // Commander
    $commands = new Rcon_Commands($rcon);

    // Fetch server info
    try {
        $status = $commands->get_server_info();
    }
    catch(Exception $e)
    {
        // Wait
        usleep(250);

        // Try again
        try {
            $status = $commands->get_server_info();
        }
        catch(Exception $e)
        {
            // Ignore this server...
            continue;
        }
    }

    // Run hooks
    Event::fireEvent('onServerInfo', array('status' => $status, 'server' => $server, 'rcon' => $rcon));
}