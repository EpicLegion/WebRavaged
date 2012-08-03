<?php

/**
 * Server daemon - Single server enviroment
 *
 * Events:
 * - onTick [float $deltaTime]
 * - onServerTick [float $deltaTime]
 * - onServerLogFinish [float $deltaTime]
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

// Root path
define('ROOT_PATH', dirname(__FILE__).'/');

// Debugging?
define('DEBUG', TRUE);

// Functions
require_once ROOT_PATH.'lib/functions.php';

// Get ID
if (!SCRIPT_CLI)
{
    if (!isset($_GET['id']) OR !ctype_digit($_GET['id'])) exit('Invalid parameter');

    define('SERVER_ID', (int) $_GET['id']);
}
else
{
    if ($argc < 2 OR !ctype_digit($argv[1])) exit('Invalid parameter');

    define('SERVER_ID', (int) $argv[1]);
}

define('INSTANCE_ID'    , sha1(uniqid(mt_rand(), TRUE)));
define('INSTANCE_RESUME', FALSE);

define('CUSTOM_LOCK', ROOT_PATH.'tmp/run-'.SERVER_ID.'.lock');

if (is_file(CUSTOM_LOCK))
{
    cli_print('Script is locked. Please stop/resume previous instance OR remove tmp/run.lock file');
    exit;
}

file_put_contents(CUSTOM_LOCK, INSTANCE_ID.':0');

// No errors
if (!DEBUG) error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

// Connect to database
cli_print('Connecting to database...');

if (Config::getCoreConfig('database.type', 'mysql') == 'pgsql')
{
    require_once ROOT_PATH.'lib/classes/DatabasePgsql.php';
}

Database::instance(Config::getCoreConfig('database', array()));

// Important consts
define('SECONDS_PER_TICK', Config::getCoreConfig('main.seconds_per_tick', 3));
define('MAX_SECONDS'     , Config::getCoreConfig('main.max_seconds', 0));

// Shutdown handler
register_shutdown_function('daemon_shutdown_handler');

// Runtime and commands
Commands::init();
Runtime::init();
Warning::init();

// Initialize core managers
ServerManager::init(FALSE);
PluginManager::init();
PlayerManager::init();
VoteManager::init();

// Time
$deltaTime = array('tick' => microtime(TRUE), 'server' => microtime(TRUE), 'log' => microtime(TRUE));

// Parser
$logParser = new LogParser;

// Silent stop
define('AVOID_CLEANUP', TRUE);

// Create child processes
foreach (ServerManager::get() as $server)
{
    // Ignore
    if (empty($server->logURL) OR $server->id != SERVER_ID) continue;

    // Logfile
    define('LOGFILE', ROOT_PATH.'tmp/server-'.$server->id.'.log');

    // Cleanup server manager
    ServerManager::clear($server->id);

    // Set to current server
    Config::$defaultFile = 'server-'.$server->id;
    Server::$current = $server;
    Server::$language = Config::get('rcon.language', 'en');

    // Timer
    $timer = 0;

    // Start
    cli_print('Process started for server: '.$server->name);

    // Process loop
    while (TRUE)
    {
        // Clear cache
        clearstatcache();

        // Close?
        if (!is_file(CUSTOM_LOCK)) break;

        // Wait?
        if ($timer > microtime(TRUE))
        {
            usleep(($timer - microtime(TRUE)) * 1000000);
        }

        // Tick start
        Event::fireEvent('onTick', array(microtime(TRUE) - $deltaTime['tick']));
        $deltaTime['tick'] = microtime(TRUE);

        // Server tick
        Event::fireEvent('onServerTick', array(microtime(TRUE) - $deltaTime['server']));
        $deltaTime['server'] = microtime(TRUE);

        // cURL
        $curl = curl_init();

        // Settings
        curl_setopt($curl, CURLOPT_URL           , $server->logURL);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT , TRUE); // Not sure about that one
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT       , 3);
        curl_setopt($curl, CURLOPT_USERAGENT     , 'Web Rcon 1.x');
        curl_setopt($curl, CURLOPT_RANGE         , -20000); // Last 20kbytes. Thanks slydog

        // Execute
        $content = curl_exec($curl);

        // Set next execution time
        $timer = (microtime(TRUE) + SECONDS_PER_TICK);

        // Main logic there
        if (!empty($content))
        {
            // Wipe history
            Server::$current->wipeHistory();

            // Parse
            $logParser->parseLogFile($content);

            // Event
            Event::fireEvent('onServerLogFinish', array(microtime(TRUE) - $deltaTime['log']));
            $deltaTime['log'] = microtime(TRUE);
        }

        // Close
        curl_close($curl);
    }

    // Done
    exit;
}