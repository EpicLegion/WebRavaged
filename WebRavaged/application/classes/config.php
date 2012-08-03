<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Config
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
 * @subpackage    core
 * @license        http://www.opensource.org/licenses/bsd-license.php    New BSD License
 */
final class Config {

    // Config is stored here
    protected static $config = array();

    /**
     * Return config value (or default value)
     *
     * @param	string	$key
     * @param	mixed	$default
     * @return	mixed
     */
    public static function get($key, $default = NULL)
    {
        // Read
        if (strstr($key, '.'))
        {
            // Split
            $key = explode('.', $key, 2);

            // Valid?
            if (isset(self::$config[$key[0]]) AND isset(self::$config[$key[0]][$key[1]]))
            {
                $key = self::$config[$key[0]][$key[1]];
            }
            else
            {
                return $default;
            }
        }
        else
        {
            // Valid?
            if (isset(self::$config[$key]))
            {
                $key = self::$config[$key];
            }
            else
            {
                return $default;
            }
        }

        // Check types
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
     * It shouldn't be here, but well. Whatever.
     *
     * @return	array
     */
    public static function get_presets()
    {
        $presets = array();

        // Iterate
        foreach (self::get('map_presets', array()) as $mp)
        {
            // Invalid?
            if (!is_array($mp) OR !isset($mp['name']) OR !isset($mp['rotation']))
            {
                continue;
            }

            // Prepare name
            $mp['name'] = htmlspecialchars($mp['name'], ENT_COMPAT, 'UTF-8');

            // Excludes
            if (!isset($mp['excludes']))
            {
                $mp['excludes'] = NULL;
            }
            elseif (!is_string($mp['excludes']) OR $mp['excludes'] === NULL OR $mp['excludes'] == 'empty')
            {
                $mp['excludes'] = '';
            }
            else
            {
                $mp['excludes'] = (string) $mp['excludes'];
            }

            // Append
            $presets[$mp['name']] = array(
                'name'     => $mp['name'],
                'rotation' => $mp['rotation'],
                'excludes' => $mp['excludes']
                // ^ Exists & Is not null & Is string & Is not "empty"
            );
        }

        // Return
        return $presets;
    }

    /**
     * Loads configuration from YAML file
     *
     * @param	string	$file
     */
    public static function read_config($file)
    {
        // Load YAML file
        if (is_file(APPPATH.'config/'.$file.'.yml'))
        {
            self::$config = Spyc::YAMLLoad(APPPATH.'config/'.$file.'.yml');
        }
    }
}