<?php
/**
 * Message rotation plugin
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
class Logger_Plugin implements Plugin {

    /**
     * @var    Database
     */
    protected $db = NULL;

    /**
     * Load plugin
     *
     * @param    Database    $db
     * @param    array        $config
     */
    public function load(Database $db, array $config)
    {
        // Enabled?
        if($config['logger']['enabled'] == '1')
        {
            // Database
            $this->db = $db;

            // Event
            Event::register('onServerInfo', array($this, 'onServerInfo'));
        }
    }

    /**
     * On receive server info
     *
     * @param    array    $params
     */
    public function onServerInfo(array $params = array())
    {
        // No players?
        if(!count($params['status']['players']))
        {
            return;
        }

        // Commands
        $commands = new Rcon_Commands($params['rcon']);

        // Iterate players
        foreach($params['status']['players'] as $player)
        {
            // Remove democlient and connecting users
            if($player['name'] == 'democlient' OR $player['guid'] == 0 OR $player['ping'] == 0 OR $player['ping'] == 'CNCT' OR $player['ping'] == 999)
            {
                continue;
            }

            // Find by GUID
            $log = $this->db->getSingle("SELECT * FROM [prefix]players WHERE id = :id AND server_id = :server", array(
                ':id' => $player['guid'],
                ':server' => $params['server']['id']
            ));

            // Found?
            if(is_array($log))
            {
                // Unserialize
                $log['ip_addresses'] = empty($log['ip_addresses']) ? array() : unserialize($log['ip_addresses']);
                $log['names'] = empty($log['names']) ? array() : unserialize($log['names']);

                // Need to add name?
                if(!in_array($player['name'], $log['names']))
                {
                    $log['names'][] = strip_tags($player['name']);
                }

                // Need to add ip?
                if(!in_array($player['address'], $log['ip_addresses']))
                {
                    $log['ip_addresses'][] = strip_tags($player['address']);
                }

                // Update
                $this->db->exec("UPDATE [prefix]players SET last_update = :time, names = :names, ip_addresses = :ip_addresses
                                 WHERE id = :id AND server_id = :server", array(
                    ':time' => time(),
                    ':names' => serialize($log['names']),
                    ':ip_addresses' => serialize($log['ip_addresses']),
                    ':id' => (int) $log['id'],
                    ':server' => (int) $log['server_id']
                ));
            }
            else
            {
                // Insert
                $this->db->exec("INSERT INTO [prefix]players (id, ip_addresses, names, server_id, last_update)
                                 VALUES (:id, :ip_addresses, :names, :server_id, :last_update)", array(
                    ':id' => $player['guid'],
                    ':ip_addresses' => serialize(array($player['address'])),
                    ':names' => serialize(array($player['name'])),
                    ':server_id' => $params['server']['id'],
                    ':last_update' => time()
                ));
            }
        }
    }
}