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
class Message_Plugin implements Plugin {

    /**
     * @var    Database
     */
    protected $db = NULL;

    protected $messages = array();

    protected $random = FALSE;

    /**
     * Load plugin
     *
     * @param    Database    $db
     * @param    array        $config
     */
    public function load(Database $db, array $config)
    {
        // Enabled?
        if($config['message']['enabled'] == '1')
        {
            // Database
            $this->db = $db;

            // Random message?
            $this->random = ($config['message']['random'] == '1');

            // Find message
            foreach($db->getAll("SELECT * FROM [prefix]messages ORDER BY id ASC") as $msg)
            {
                // Isset?
                if(!isset($this->messages[(int) $msg['server_id']]))
                {
                    $this->messages[(int) $msg['server_id']] = array();
                }

                // Append
                $this->messages[(int) $msg['server_id']][] = $msg;

                // Current?
                if($msg['current'] == '1')
                {
                    $this->messages[(int) $msg['server_id']]['current'] = $msg;
                }
            }

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
        // No messages to send?
        if(!isset($this->messages[$params['server']['id']]) OR !isset($this->messages[$params['server']['id']][0]))
        {
            return;
        }

        // Commands
        $commands = new Rcon_Commands($params['rcon']);

        // Random?
        if($this->random)
        {
            // Messages max index
            $maxIndex = count($this->messages[$params['server']['id']]) - 1;

            // Decrease if current key found
            if(isset($this->messages[$params['server']['id']]['current']))
            {
                $maxIndex--;
            }

            // Validate index
            if($maxIndex < 0)
            {
                return;
            }

            // Get random message
            $message = $this->messages[$params['server']['id']][rand(0, $maxIndex)];

            // Send
            $commands->message($message['message']);
        }
        else
        {
            // Invalid rotation?
            if(!isset($this->messages[$params['server']['id']]['current']))
            {
                // Update
                $this->db->exec("UPDATE [prefix]messages SET current = '1' WHERE id = :id", array(':id' => (int) $this->messages[$params['server']['id']][0]['id']));

                // Send first message
                $commands->message($this->messages[$params['server']['id']][0]['message']);
            }
            else
            {
                // Send current message
                $commands->message($this->messages[$params['server']['id']]['current']['message']);

                // Find next message
                $next = $this->db->getSingle("SELECT * FROM [prefix]messages WHERE id > :id AND server_id = :server LIMIT 1", array(
                    ':id' => (int) $this->messages[$params['server']['id']]['current']['id'],
                    ':server' => $params['server']['id']
                ));

                // Clean up
                $this->db->exec("UPDATE [prefix]messages SET current = '0' WHERE current = '1' AND server_id = :server", array(
                    ':server' => $params['server']['id']
                ));

                // Found?
                if(is_array($next))
                {
                    // Update rotation
                    $this->db->exec("UPDATE [prefix]messages SET current = '1' WHERE id = :id", array(
                        ':id' => (int) $next['id']
                    ));
                }
                else
                {
                    // Update
                    $this->db->exec("UPDATE [prefix]messages SET current = '1' WHERE id = :id", array(':id' => (int) $this->messages[$params['server']['id']][0]['id']));
                }
            }
        }
    }
}