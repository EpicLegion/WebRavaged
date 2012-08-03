<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Blackops rcon connection
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
 * @author        EpicLegion, Maximusya
 * @package        rcon
 * @subpackage    lib
 * @license        http://www.opensource.org/licenses/bsd-license.php    New BSD License
 */
class Rcon {

    /**
     * Socket res
     *
     * @var    resource
     */
    protected $socket = NULL;

    /**
     * Password
     *
     * @var    string
     */
    protected $password = '';

    /**
     * Host (IP)
     *
     * @var    string
     */
    protected $host = '';

    /**
     * Serve port
     * @var    int
     */
    protected $port = 3074;

    /**
     * Constructor
     *
     * @param    string    $host
     * @param    int        $port
     * @param    string    $password
     */
    public function __construct($host = '', $port = 3074, $password = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
    }

    /**
     * Send command
     *
     * @param    string        $command
     * @param    bool        $return_response
     * @throws    Exception
     * @return    mixed
     */
    public function command($command, $return_response = TRUE)
    {
        // Wait for response?
        if($return_response)
        {
            return '';
        }
    }

    /**
     * Connect to rcon
     *
     * @throws    Exception
     */
    public function connect()
    {
    }

    /**
     * Disconnect
     */
    public function disconnect()
    {
        //fclose($this->socket);
    }

    /**
     * Read response
     *
     * @return    string
     */
    public function get_response()
    {
        // No response lol
        return '';
    }

    /**
     * Get socket resource
     *
     * @return    resource
     */
    public function get_socket()
    {
        return $this->socket;
    }

    /**
     * Set RCON password
     *
     * @param    string    $password
     */
    public function set_password($password = '')
    {
        $this->password = $password;
    }

    /**
     * Set server connection details
     *
     * @param    string    $host
     * @param    int        $port
     */
    public function set_server_info($host = '', $port = 3074)
    {
        $this->host = $host;
        $this->port = $port;
    }
}

class Rcon_Commands {

    /**
     * Rcon connection
     *
     * @var    Rcon
     */
    protected $console = NULL;

    /**
     * Constructor
     *
     * @param    Blackops_Rcon    $console
     */
    public function __construct(Rcon $console)
    {
        $this->console = $console;
    }

    /**
     * Read server cvar
     *
     * @param	string	$var
     * @return	string
     */
    public function get_cvar($var)
    {
        // Command
        $response = $this->console->command($var);

        if($var == 'sv_mapRotation')
        {
            return 'gametype koth map mp_array map mp_nuked map mp_hanoi';
        }
        elseif($var == 'playlist_excludeMap')
        {
            return 'mp_array';
        }

        return '';

        // Match here
        $match = '';

        // Get value
        if(preg_match('#"'.$var.'" is: "(.*?)" default.+#is', $response, $match))
        {
            // Return match
            return $match[1];
        }
        else
        {
            // Nothing
            return '';
        }
    }

    /**
     * Ban player
     *
     * @param    string|int    $data
     * @param    bool        $ban_by_name
     */
    public function ban($data, $ban_by_name = FALSE)
    {
        // Name?
        if($ban_by_name)
        {
            // Ban by name
            $this->console->command('banuser "'.htmlspecialchars($data, ENT_QUOTES, 'UTF-8').'"');
        }
        else
        {
            // Ban by ID
            $this->console->command('banclient '.(int) $data);
        }
    }

    /**
     * Set c/dvar
     *
     * @param    string    $name
     * @param    mixed    $value
     * @param    bool    $dvar
     */
    public function cvar($name, $value, $dvar = FALSE)
    {
        // Admin dvar or cvar
        if($dvar)
        {
            $this->console->command('setadmindvar '.$name.' '.(is_int($value) ? $value : '"'.$value.'"'));
        }
        else
        {
            $this->console->command($name.' '.(is_int($value) ? $value : '"'.$value.'"'));
        }
    }

    /**
     * Get current playlist
     *
     * @return    int|bool
     */
    public function get_playlist()
    {
        // Query server
        $response = $this->console->command('playlist');

        /*
         * playlist
            "playlist" is: "30" default: "1"
              Domain is any integer from 0 to 64
         */

        // Get playlist
        return '1';
    }

    /**
     * Get basic server info (map and player list)
     *
     * @return    array
     */
    public function get_server_info()
    {
        return array_merge($this->get_teamstatus(), $this->get_real_server_info());
    }

    /**
     * Teamstatus query
     *
     * @return    array
     */
    public function get_teamstatus()
    {
        // Send command
        $raw_response = $this->console->command('teamstatus');

        // Try again
        if(empty($raw_response))
        {
            $raw_response = $this->console->command('teamstatus');
        }

        // Break lines
        /*$response = explode("\x0a", $raw_response);

        // Valid response?
        if(count($response) <= 1)
        {
            throw new Exception('Cannot retrieve teamstatus');
        }*/

        // Array containing teamstatus
        $teamstatus = array(
            'map' => 'mp_array',
            'players' => array(
                1 => array(
                    'id' => 1,
                    'score' => 1000,
                    'ping' => 100,
                    'guid' => rand(100, 99999999),
                    'name' => 'Fake user #1',
                    'team' => 0,
                    'lastmsg' => 1,
                    'address' => '192.'.rand(1,999).'.1.'.rand(1,999).':3333',
                    'qport' => 3333,
                    'rate' => 1000
                ),
                2 => array(
                    'id' => 2,
                    'score' => 1000,
                    'ping' => 100,
                    'guid' => rand(100, 99999999),
                    'name' => 'Fake user #2',
                    'team' => 1,
                    'lastmsg' => 1,
                    'address' => '192.'.rand(1,999).'.1.'.rand(1,999).':3333',
                    'qport' => 3333,
                    'rate' => 1000
                ),
                3 => array(
                    'id' => 3,
                    'score' => 1000,
                    'ping' => 100,
                    'guid' => rand(100, 99999999),
                    'name' => 'Fake user #3',
                    'team' => 2,
                    'lastmsg' => 1,
                    'address' => '192.'.rand(1,999).'.1.'.rand(1,999).':3333',
                    'qport' => 3333,
                    'rate' => 1000
                ),
                4 => array(
                    'id' => 4,
                    'score' => 1000,
                    'ping' => 100,
                    'guid' => rand(100, 99999999),
                    'name' => 'Fake user #4',
                    'team' => 1,
                    'lastmsg' => 1,
                    'address' => '192.1.1.1:3333',
                    'qport' => 3333,
                    'rate' => 1000
                ),
            ),
            'error' => ''
        );

        // Remove header
        /*unset($response[0], $response[1], $response[2]);

        // Reader object
        $parser = new Blackops_Rcon_Statusparser;

        // Iterate players
        foreach($response as $line)
        {
            // Not a player?
            if(empty($line) OR strlen($line) < 80)
            {
                break;
            }

            // Parse line
            $line = $parser->parse($line);

            // Set player info
            $teamstatus['players'][$line['id']] = $line;
        }*/

        // Return
        return $teamstatus;
    }

    /**
     * Get real server info
     *
     * @return    array
     */
    public function get_real_server_info()
    {
        // Send command
        $raw_response = $this->console->command('serverinfo');

        // Try again
        if(empty($raw_response))
        {
            $raw_response = $this->console->command('serverinfo');
        }

        // Break lines
        $response = explode("\x0a", $raw_response);

        return array();

        // Valid response?
        if(count($response) <= 1)
        {
            throw new Exception('Cannot retrieve server info');
        }

        // Remove header
        unset($response[0]);

        // Vars
        $server_info = array();
        $var_name = '';
        $var_value = '';

        // Iterate info
        foreach($response as $line)
        {
            // Tokenize
            $var_name = strtok($line, ' ');

            // Value
            $var_value = trim(substr($line, strlen($var_name)));

            // Set var
            if($var_name !== FALSE)
            {
                $server_info[$var_name] = is_numeric($var_value) ? (int) $var_value : $var_value;
            }
        }

        // Return
        return $server_info;
    }

    /**
     * Kick player
     *
     * @param	string|int	$data
     * @param	bool		$kick_by_name
     * @param	string		$reason
     */
    public function kick($data, $kick_by_name = FALSE, $reason = '')
    {
        // Name?
        if($kick_by_name)
        {
            // Kick by name
            if(!$reason)
            {
                $this->console->command('kick "'.htmlspecialchars($data, ENT_QUOTES, 'UTF-8').'"');
            }
            else
            {
                $this->console->command('kick "'.htmlspecialchars($data, ENT_QUOTES, 'UTF-8').'" "'.htmlspecialchars($reason, ENT_QUOTES, 'UTF-8').'"');
            }
        }
        else
        {
            // Kick by ID
            if(!$reason)
            {
                $this->console->command('clientkick '.(int) $data);
            }
            else
            {
                $this->console->command('clientkick '.(int) $data.' "'.htmlspecialchars($reason, ENT_QUOTES, 'UTF-8').'"');
            }
        }
    }

    /**
     * Message (public or private)
     *
     * @param    string        $message
     * @param    string|int    $client
     */
    public function message($message, $client = 'all')
    {
        // Linebreak?
        if(stristr($message, '[linebreak]') !== FALSE)
        {
            $message = explode('[linebreak]', $message);
        }
        else
        {
            $message = array($message);
        }

        // Global say
        if($client == 'all')
        {
            foreach($message as $v)
            {
                $this->console->command('say "'.strip_tags($v).'"');
            }
        }
        else
        {
            // Cast
            $client = (int) $client;

            // Send tell command
            foreach($message as $v)
            {
                $this->console->command('tell '.$client.' "'.strip_tags($v).'"');
            }
        }
    }

    /**
     * Set rcon console
     *
     * @param    Blackops_Rcon    $console
     */
    public function set_rcon(Rcon $console)
    {
        $this->console = $console;
    }

    /**
     * Temporary ban player
     *
     * @param    string|int    $data
     * @param    bool        $ban_by_name
     */
    public function temp_ban($data, $ban_by_name = FALSE)
    {
        // Name?
        if($ban_by_name)
        {
            // Ban by name
            $this->console->command('tempbanuser "'.htmlspecialchars($data, ENT_QUOTES, 'UTF-8').'"');
        }
        else
        {
            // Ban by ID
            $this->console->command('tempbanclient '.(int) $data);
        }
    }
}