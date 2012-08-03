<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Runtime data
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

final class Runtime
{
    /**
     * @var array
     */
    protected static $data = array();
    
    /**
     * Get array key
     * 
     * @param string $key
     * @param  mixed $default
     * @return mixed
     */
    public static function get($key, $default = array())
    {
        return isset(self::$data[$key]) ? self::$data[$key] : $default;
    }
    
    /**
     * Load runtime data
     */
    public static function init()
    {
        // File exists?
        if (is_file(ROOT_PATH.'tmp/run.data'))
        {
            // Load file contents
            $file = file_get_contents(ROOT_PATH.'tmp/run.data');
            
            // Valid?
            if (!strstr($file, '|'))
            {
                return;
            }
            
            // Split
            $file = explode('|', $file, 2);
            
            // Check instance
            if ($file[0] != INSTANCE_ID)
            {
                return;
            }
            
            // Unserialize
            self::$data = @unserialize($file[1]);
            
            // Nope.avi
            if (self::$data === FALSE)
            {
                self::$data = array();
            }
        }
    }
    
    /**
     * Write to file
     */
    public static function save()
    {
        // Write
        file_put_contents(ROOT_PATH.'tmp/run.data', INSTANCE_ID.'|'.serialize(self::$data));
    }
    
    /**
     * 
     * @param type $key
     * @param type $value 
     */
    public static function set($key, $value = array())
    {
        self::$data[$key] = $value;
    }
}