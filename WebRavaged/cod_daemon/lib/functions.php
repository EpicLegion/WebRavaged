<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Basic functions
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

// Autoload
function __autoload($class)
{
    // If exists
    if (is_file(ROOT_PATH.'lib/classes/'.$class.'.php'))
    {
        require_once ROOT_PATH.'lib/classes/'.$class.'.php';
    }
}

// UTF8 clean
function utf8_clean($var, $charset = 'UTF-8')
{
    if (is_array($var) OR is_object($var))
    {
        foreach ($var as $key => $val)
        {
            // Recursion!
            $var[self::clean($key)] = utf8_clean($val);
        }
    }
    elseif (is_string($var) AND $var !== '')
    {
        // Remove control characters
        $var = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $var);

        if (preg_match('/[^\x00-\x7F]/S', $var))
        {
            // Disable notices
            $ER = error_reporting(~E_NOTICE);

            // iconv is expensive, so it is only used when needed
            $var = iconv($charset, $charset.'//IGNORE', $var);

            // Turn notices back on
            error_reporting($ER);
        }
    }

    return $var;
}

// Shutdown handler
function daemon_shutdown_handler()
{
    // Stop
    if (defined('AVOID_CLEANUP')) return;

    // Custom lock support
    if (!defined('CUSTOM_LOCK')) define('CUSTOM_LOCK', ROOT_PATH.'tmp/run.lock');

    // Cleanup
    @unlink(ROOT_PATH.'tmp/run.data');
    @unlink(CUSTOM_LOCK);

    // Disconnect servers
    ServerManager::unload();

    // Unload plugins
    PluginManager::unload();

    // Done
    cli_print('Daemon shutdown');
}

// CLI
if (PHP_SAPI == 'cli')
{
    define('SCRIPT_CLI', TRUE);

    function cli_print($text)
    {
        if (defined('LOGFILE'))
        {
            file_put_contents(LOGFILE, '['.date('d.m.Y H:i:s').'] '.$text."\n", FILE_APPEND);
            return;
        }

        echo $text."\n";
    }

    if (function_exists('pcntl_signal'))
    {
        declare(ticks = 1);

        pcntl_signal(SIGTERM, 'daemon_shutdown_handler');
        pcntl_signal(SIGINT,  'daemon_shutdown_handler');
    }
}
else
{
    // Try to remove limits and disconnect browser
    @set_time_limit(0);
    ignore_user_abort(TRUE);
    header("Content-Length: 0");
    header("Connection: close");
    flush();

    define('SCRIPT_CLI', FALSE);

    function cli_print($text)
    {
        if (defined('LOGFILE'))
        {
            file_put_contents(LOGFILE, '['.date('d.m.Y H:i:s').'] '.$text."\n", FILE_APPEND);
            return;
        }

        return;
    }
}

// Translation (to be implemented)
function __($text, array $replacements = array())
{
    // Language strings store
    static $languages = array();

    // Preload language
    if (!isset($languages[Server::$language]))
    {
        if (is_file(ROOT_PATH.'languages/'.Server::$language.'.php'))
        {
            $languages[Server::$language] = require ROOT_PATH.'languages/'.Server::$language.'.php';
        }
        else
        {
            $languages[Server::$language] = array();
        }
    }

    // Translate
    if (isset($languages[Server::$language][$text]))
    {
        $text = $languages[Server::$language][$text];
    }

    return strtr($text, $replacements);
}

// Time parser
function parse_time($string, $time = TRUE)
{
    // Base time
    if ($time)
    {
        $time = time();
    }
    else
    {
        $time = 0;
    }

    // Empty?
    if (empty($string))
    {
        return $time;
    }

    // Split
    $string = explode(' ', $string);

    // Parse elements
    foreach ($string as $s)
    {
        // Length
        $len = strlen($s);

        // Single?
        if ($len == 1)
        {
            $quantity = 1;
        }
        elseif (ctype_digit(substr($s, 0, ($len - 1))))
        {
            $quantity = (int) substr($s, 0, ($len - 1));
        }
        else
        {
            // Invalid
            continue;
        }

        // Type
        switch (substr($s, -1))
        {
            // Seconds
            case 's':
                break;

            // Hours
            case 'h':
                $quantity *= 3600;
                break;

            // Days
            case 'd':
                $quantity *= 86400;
                break;

            // Weeks
            case 'w':
                $quantity *= 604800;
                break;

            // Months
            case 'm':
                $quantity *= 2592000;
                break;

            // Years
            case 'y':
                $quantity *= 31536000;
                break;

            // Minutes
            default:
                $quantity *= 60;
        }

        // Add
        $time += $quantity;
    }

    // Return
    return $time;
}