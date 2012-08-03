<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Player log model
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
 * @subpackage    model
 * @license        http://www.opensource.org/licenses/bsd-license.php    New BSD License
 */
class Model_Player extends Model {

    /**
     * Count logs
     *
     * @param    int                $server
     * @param	 array				$conditions
     * @return   int
     */
    public static function count_logs($server, $conditions = array())
    {
        // Basic
        $query = DB::select(DB::expr('COUNT(*) AS mycount'))
                   ->from('players')
                   ->where('server_id', '=', (int) $server);

        // User
        if ($conditions['guid'])
        {
            $query->where('id', '=', (int) $conditions['guid']);
        }

        // Return
        return (int) $query->execute()->get('mycount');
    }

    /**
     * Get details for this player
     *
     * @param    int        $server
     * @param    int        $guid
     * @return    array
     */
    public static function get_details($server, $guid)
    {
        // Execute query
        $query = DB::select('names', 'ip_addresses')->where('server_id', '=', (int) $server)
                   ->where('id', '=', (int) $guid)->limit(1)->from('players')->execute();

        // Any rows
        if(count($query) <= 0)
        {
            return array();
        }

        // As array
        $query = $query->as_array();

        // Return first row
        return $query[0];
    }

    /**
     * Get logs
     *
     * @param     int             $server
     * @param     int             $offset
     * @param	  array           $conditions
     * @param     string          $order
     * @param	  string          $type
     * @return    Database_Result
     */
    public static function get_logs($server, $offset = 0, $conditions = array(), $order = 'last_update', $type = 'DESC')
    {
        // Basic
        $query = DB::select('*')->from('players')->where('server_id', '=', (int) $server)->order_by($order, $type)->limit(50)->offset($offset);

        // User
        if ($conditions['guid'])
        {
            $query->where('id', '=', (int) $conditions['guid']);
        }

        // Execute
        return $query->execute();
    }
}