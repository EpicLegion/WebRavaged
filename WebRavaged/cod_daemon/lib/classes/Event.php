<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Event system
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

final class Event
{
    /**
     * @var array
     */
    static protected $events = array();
    
    /**
     * Add event handler
     * 
     * @param string $name
     * @param mixed $callback 
     */
    public static function add($name, $callback)
    {
        // New event
        if (!isset(self::$events[$name])) self::$events[$name] = array();
        
        // Register
        self::$events[$name][] = $callback;
    }
    
    /**
     * Clear events
     */
    public static function clear()
    {
        self::$events = array();
    }
    
    /**
     * Fire event
     * 
     * @param string $name
     * @param array  $params
     */
    public static function fireEvent($name, array $params = array())
    {
        // No handlers?
        if (!isset(self::$events[$name])) return;
        
        // Iterate
        foreach (self::$events[$name] as $callback)
        {
            call_user_func_array($callback, $params);
        }
    }
    
    /**
     * Remove event handler
     * 
     * @param string $name
     * @param mixed $callback 
     */
    public static function remove($name, $callback)
    {
        // No handlers?
        if (!isset(self::$events[$name])) return;
        
        // Iterate
        foreach (self::$events[$name] as $k => $v)
        {
            // Match
            if ($callback == $v)
            {
                unset(self::$events[$name][$k]);
            }
        }
    }
}