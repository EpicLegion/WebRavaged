<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Configuration system
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

final class Config
{
    /**
     * @var array
     */
    protected static $config = array();
    
    /**
     * @var array
     */
    protected static $coreConfig = array();
    
    /**
     * @var string
     */
    public static $defaultFile = 'default';
    
    /**
     * Clear config
     */
    public static function clear()
    {
        self::$config = array();
    }
    
    /**
     * Get cvar
     * 
     * @param  string $key
     * @param  mixed  $default
     * @return mixed 
     */
    public static function get($key, $default = NULL, $file = NULL)
    {
        // Default file
        if (!$file) $file = self::$defaultFile;
        
        // Read
        if (strstr($key, '.'))
        {
            // Split
            $key = explode('.', $key, 2);
            
            // Exists
            if (!isset(self::$config[$file]))
            {
                // Preload
                if (!self::readConfig($file))
                {
                    // Default config
                    if (isset(self::$config['default'][$key[0]][$key[1]]))
                    {
                        $key = self::$config['default'][$key[0]][$key[1]];
                    }
                    else
                    {
                        return $default;
                    }
                }
                else
                {
                    // Server config
                    if (isset(self::$config[$file][$key[0]][$key[1]]))
                    {
                        $key = self::$config[$file][$key[0]][$key[1]];
                    }
                    else
                    {
                        // Default config
                        if (isset(self::$config['default'][$key[0]][$key[1]]))
                        {
                            $key = self::$config['default'][$key[0]][$key[1]];
                        }
                        else
                        {
                            return $default;
                        }
                    }
                }
            }
            else
            {
                // Valid item
                if (isset(self::$config[$file][$key[0]][$key[1]]))
                {
                    $key = self::$config[$file][$key[0]][$key[1]];
                }
                else
                {
                    // Default config
                    if (isset(self::$config['default'][$key[0]][$key[1]]))
                    {
                        $key = self::$config['default'][$key[0]][$key[1]];
                    }
                    else
                    {
                        return $default;
                    }
                }
            }
        }
        else
        {
            // Exists
            if (!isset(self::$config[$file]))
            {
                // Preload
                if (!self::readConfig($file))
                {
                    // Default config
                    if (isset(self::$config['default'][$key]))
                    {
                        $key = self::$config['default'][$key];
                    }
                    else
                    {
                        return $default;
                    }
                }
                else
                {
                    // Server config
                    if (isset(self::$config[$file][$key]))
                    {
                        $key = self::$config[$file][$key];
                    }
                    else
                    {
                        // Default config
                        if (isset(self::$config['default'][$key]))
                        {
                            $key = self::$config['default'][$key];
                        }
                        else
                        {
                            return $default;
                        }
                    }
                }
            }
            else
            {
                // Valid item
                if (isset(self::$config[$file][$key]))
                {
                    $key = self::$config[$file][$key];
                }
                else
                {
                    // Default config
                    if (isset(self::$config['default'][$key]))
                    {
                        $key = self::$config['default'][$key];
                    }
                    else
                    {
                        return $default;
                    }
                }
            }
        }
        
        // Return type check
        if ($default === NULL)
        {
            return $key;
        }
        elseif (is_bool($default))
        {
            return (bool) $key;
        }
        elseif (is_array($default))
        {
            return is_array($key) ? $key : $default;
        }
        elseif (is_int($default))
        {
            return (int) $key;
        }
        else
        {
            return (string) $key;
        }
    }
    
    /**
     * Get cvar
     * 
     * @param  string $key
     * @param  mixed  $default
     * @return mixed 
     */
    public static function getCoreConfig($key, $default = NULL)
    {
        // Read
        if (strstr($key, '.'))
        {
            // Split
            $key = explode('.', $key, 2);
            
            // Exists
            if (!isset(self::$coreConfig[$key[0]]))
            {
                // Preload
                self::readCoreConfig($key[0]);
                       
                // Default config
                if (isset(self::$coreConfig[$key[0]][$key[1]]))
                {
                    $key = self::$coreConfig[$key[0]][$key[1]];
                }
                else
                {
                    return $default;
                }
            }
            else
            {
                // Default config
                if (isset(self::$coreConfig[$key[0]][$key[1]]))
                {
                    $key = self::$coreConfig[$key[0]][$key[1]];
                }
                else
                {
                    return $default;
                }
            }
        }
        else
        {
            // Loaded
            if (!isset(self::$coreConfig[$key]))
            {
                self::readCoreConfig($key);
            }
            
            // Return
            return self::$coreConfig[$key];
        }
        
        // Return type check
        if ($default === NULL)
        {
            return $key;
        }
        elseif (is_bool($default))
        {
            return (bool) $key;
        }
        elseif (is_array($default))
        {
            return (array) $key;
        }
        elseif (is_int($default))
        {
            return (int) $key;
        }
        else
        {
            return (string) $key;
        }
    }
    
    /**
     * Read YAML config file
     * 
     * @param string $file
     */
    public static function readConfig($file)
    {
        // Load only once
        self::$config[$file] = array();
        
        // Exists
        if (is_file(ROOT_PATH.'config/'.$file.'.yml'))
        {
            self::$config[$file] = Spyc::YAMLLoad(ROOT_PATH.'config/'.$file.'.yml');
            
            return TRUE;
        }
        
        // Nope
        return FALSE;
    }
    
    /**
     * Read YAML config file
     * 
     * @param string $file
     */
    public static function readCoreConfig($file)
    {
        // Load only once
        self::$coreConfig[$file] = array();
        
        // Exists
        if (is_file(ROOT_PATH.'config/core/'.$file.'.yml'))
        {
            self::$coreConfig[$file] = Spyc::YAMLLoad(ROOT_PATH.'config/core/'.$file.'.yml');
        }
    }
    
    /**
     * Reload config for this server
     */
    public static function reload($id)
    {
        // Remove loaded
        if (isset(self::$config[$id])) unset(self::$config[$id]);
        
        // Fire
        Event::fireEvent('onConfigReload');
    }
    
    /**
     * Save configuration file
     * 
     * @param string $file 
     */
    public static function save($file = NULL)
    {
        // Default file
        if (!$file) $file = self::$defaultFile;
        
        // Save
        file_put_contents(ROOT_PATH.'config/'.$file.'.yml', Spyc::YAMLDump(self::$config[$file]));
    }
    
    /**
     * Set config file data
     * 
     * @param string $name
     * @param mixed  $value 
     * @param string $file
     */
    public static function set($name, $value, $file = NULL)
    {
        // Default file
        if (!$file) $file = self::$defaultFile;
        
        // Set
        self::$config[$file][$name] = $value;
    }
}