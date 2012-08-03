<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Plugin manager
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

final class PluginManager
{
    /**
     * @var array
     */
    static protected $plugins = array();

    /**
     * Get plugin list
     *
     * @return array
     */
    public static function get()
    {
        return self::$plugins;
    }

    /**
     * Load all plugins
     */
    public static function init()
    {
        foreach (scandir(ROOT_PATH.'plugins') as $file)
        {
            // Accept only .php files
            if ($file == '.' OR $file == '..' OR !is_file(ROOT_PATH.'plugins/'.$file) OR substr($file, -4, 4) !== '.php')
            {
                continue;
            }

            // File without .php
            $file = basename($file, '.php');

            // Load
            require_once ROOT_PATH.'plugins/'.$file.'.php';

            // Class name
            $class = $file.'Plugin';

            // Valid
            if (!class_exists($class))
            {
                continue;
            }

            // Load
            $class = new $class;

            // Valid once again
            if (!($class instanceof Plugin))
            {
                continue;
            }

            // Add
            self::$plugins[$file] = $class;

            // Try to load
            $result = self::$plugins[$file]->load();

            // Nope
            if (!$result)
            {
                // Debug
                cli_print('- [FAILED] Plugin failed to load: '.$file);

                // Plugin shutdown
                self::$plugins[$file]->unload();

                // Remove from list
                unset(self::$plugins[$file]);
            }
            else
            {
                // Debug
                cli_print('+ [DONE] Plugin loaded: '.$file);
            }
        }
    }

    /**
     * Reload
     */
    public static function reload()
    {
        // Clear data
        Config::clear();
        Event::clear();
        Commands::clear();
        Commands::init();
        VoteManager::clear();
        VoteManager::init();
        PlayerManager::init();
        
        // Loaded plugins
        $loaded = array();

        // Iterate
        foreach (scandir(ROOT_PATH.'plugins') as $file)
        {
            // Accept only .php files
            if ($file == '.' OR $file == '..' OR !is_file(ROOT_PATH.'plugins/'.$file) OR substr($file, -4, 4) !== '.php')
            {
                continue;
            }

            // File without .php
            $file = basename($file, '.php');

            // Loaded
            $loaded[$file] = TRUE;

            // Reload
            if (isset(self::$plugins[$file]))
            {
                self::$plugins[$file]->reload();
                continue;
            }

            // Load
            require_once ROOT_PATH.'plugins/'.$file.'.php';

            // Class name
            $class = $file.'Plugin';

            // Valid
            if (!class_exists($class))
            {
                continue;
            }

            // Load
            $class = new $class;

            // Valid once again
            if (!($class instanceof Plugin))
            {
                continue;
            }

            // Add
            self::$plugins[$file] = $class;

            // Try to load
            $result = self::$plugins[$file]->load();

            // Nope
            if (!$result)
            {
                // Debug
                cli_print('- [FAILED] Plugin failed to load: '.$file);

                // Plugin shutdown
                self::$plugins[$file]->unload();

                // Remove from list
                unset(self::$plugins[$file]);
            }
            else
            {
                // Debug
                cli_print('+ [DONE] Plugin loaded: '.$file);
            }
        }

        // Remove old plugins
        foreach (self::$plugins as $k => $v)
        {
            // Remove?
            if (!isset($loaded[$k]))
            {
                // Unload
                $v->unload();

                // Remove from list
                unset(self::$plugins[$k]);
            }
        }
    }

    /**
     * Resume plugins
     * 
     * @param string $plugins Serialized plugins
     */
    public static function resume($plugins)
    {
        // Empty
        if (empty($plugins))
        {
            // Load all
            self::init();
            return;
        }
        
        // Load plugin files
        foreach (scandir(ROOT_PATH.'plugins') as $file)
        {
            // Accept only .php files
            if ($file == '.' OR $file == '..' OR !is_file(ROOT_PATH.'plugins/'.$file) OR substr($file, -4, 4) !== '.php')
            {
                continue;
            }

            // File without .php
            $file = basename($file, '.php');

            // Load
            require_once ROOT_PATH.'plugins/'.$file.'.php';
        }
        
        // Preload few classes
        __autoload('Damage');
        __autoload('Weapon');
        
        // We can unserialize now
        $plugins = @unserialize($plugins);
        
        // No plugins?
        if ($plugins === FALSE OR !is_array($plugins) OR empty($plugins))
        {
            // Load all
            self::init();
            return;
        }
        
        // Set
        self::$plugins = $plugins;
        
        // Reinitalize
        foreach (self::$plugins as $k => $v)
        {
            // Reload
            self::$plugins[$k]->reload();
        }
    }
    
    /**
     * Unload plugins
     */
    public static function unload()
    {
        // Iterate
        foreach (self::$plugins as $p)
        {
            // Call unload
            $p->unload();
        }

        // Empty
        self::$plugins = array();
    }
}
