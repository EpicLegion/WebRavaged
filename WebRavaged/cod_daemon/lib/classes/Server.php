<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Server object
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

class Server
{
    /**
     * @var Server
     */
    public static $current = NULL;

    /**
     * @var string
     */
    public static $language = 'en';

    /**
     * @var array
     */
    protected $history = array();

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $ip = '';

    /**
     * @var string
     */
    public $logURL = '';

    /**
     * @var int
     */
    protected $messageLimit = 100;

    /**
     * @var string
     */
    protected $messagePrefix = '';

    /**
     * @var array
     */
    protected $messageWhisper = array();

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $password = '';

    /**
     * @var int
     */
    public $port = 3074;

    /**
     * @var resource
     */
    protected $socket = NULL;

    /**
     * @var int
     */
    protected $tempBanDuration = NULL;

    /**
     * Constructor
     *
     * @param int    $id
     * @param int    $port
     * @param string $ip
     * @param string $password
     * @param string $name
     * @param string $logURL
     */
    public function __construct($id, $port, $ip, $password, $name, $logURL)
    {
        // Set properties
        $this->id = $id;
        $this->port = $port;
        $this->ip = $ip;
        $this->password = $password;
        $this->name = $name;
        $this->logURL = $logURL;

        // Configuration
        $this->messageLimit  = Config::get('rcon.message_limit', 100, 'server-'.$id);
        $this->messagePrefix = Config::get('rcon.prefix'       , '' , 'server-'.$id);

        foreach (Config::get('rcon.whispers', array(), 'server-'.$id) as $k => $v)
        {
            $this->messageWhisper[$k] = (bool) $v;
        }

        $this->tempBanDuration = parse_time(Config::get('rcon.default_ban_duration', 'd', 'server-'.$id), FALSE);

        // Event
        Event::add('onConfigReload', array($this, 'config'));
    }

    /**
     * Ban specified player
     *
     * @param Player $player
     * @param string $reason
     */
    public function ban($player, $reason = NULL)
    {
        // Check history
        if (isset($this->history[$player->id])) return;

        // Send command
        $this->command('banclient '.$player->id);

        // ACL
        PlayerManager::setACL($player, PlayerManager::DENY, $reason, 0);

        // Add to history
        $this->history[$player->id] = TRUE;
    }

    /**
     * Send command
     *
     * @param  string    $command
     * @param  bool      $return_response
     * @throws Exception
     * @return mixed
     */
    public function command($command, $returnResponse = TRUE)
    {
        // No connection
        if(!$this->socket OR !is_resource($this->socket))
        {
            throw new Exception('You must connect to remote console before issuing commands');
        }

        // Send command
        fwrite($this->socket, "\xff\xff\xff\xff\x00".$this->password."\x20".$command."\x00");

        // Wait for response?
        if($returnResponse)
        {
            // Retrieve reponse
            $response = $this->getResponse();

            // Invalid password?
            if(trim($response) == 'Invalid password.')
            {
                throw new Exception('Invalid remote console password.');
            }

            return $response;
        }
    }

    /**
     * Reload config
     */
    public function config()
    {
        // Configuration
        $this->messageLimit   = Config::get('rcon.message_limit', 100);
        $this->messagePrefix  = Config::get('rcon.prefix'       , '');
        $this->messageWhisper = array();

        foreach (Config::get('rcon.whispers', array()) as $k => $v)
        {
            $this->messageWhisper[$k] = (bool) $v;
        }

        $this->tempBanDuration = parse_time(Config::get('rcon.default_ban_duration', 'd'), FALSE);
    }

    /**
     * Connect to rcon
     *
     * @throws Exception
     */
    public function connect()
    {
        // Errors
        $errno = 0;
        $errstr = '';

        // Open socket
        $this->socket = fsockopen('udp://'.$this->ip, $this->port, $errno, $errstr, 5);

        // Invalid?
        if (!$this->socket)
        {
            throw new Exception('Cannot connect to remote console');
        }
    }

    /**
     * Disconnect
     */
    public function disconnect()
    {
        if ($this->socket) fclose($this->socket);
    }

    /**
     * Get current server (or field)
     *
     * @param  string $field
     * @return Server
     */
    public static function get($field = NULL)
    {
        return ($field) ? Server::$current->$field : Server::$current;
    }

    /**
     * Get server cvar value
     *
     * @param string $name
     * @param bool   $numeric
     */
    public function getCvar($name, $numeric = FALSE)
    {
        // Command
        $response = $this->command($name);

        // Match here
        $match = '';

        // Stupid bugged crap
        if(empty($response))
        {
            // Wait a bit
            usleep(1000);

            // Command
            $response = $this->command($name);
        }

        // Get value
        if(preg_match('#"'.$name.'" is: "(.*?)" default.+#is', $response, $match))
        {
            // Return match
            return $numeric ? (int) str_replace('^7', '', $match[1]) : str_replace('^7', '', trim($match[1]));
        }
        else
        {
            // Cannot retrieve cvar
            return NULL;
        }
    }

    /**
     * Read response
     *
     * @return string
     */
    public function getResponse()
    {
        // Var containing response
        $response = '';

        // Set socket timeout
        stream_set_timeout($this->socket, 0, 7e5);

        // Do
        do
        {
            // Read 1/2kb
            $streamRead = fread($this->socket, 512);

            // End of response?
            if(strpos($streamRead, "\x00") !== FALSE)
            {
                // Cut
                $streamRead = substr($streamRead, 0, strpos($streamRead, "\x00"));
            }

            // Get socket info
            $streamInfo = stream_get_meta_data($this->socket);

            // Append
            $response .= substr(trim($streamRead, "\x0a"), 11);
        }
        while(!$streamInfo['timed_out']);

        // Return
        return $response;
    }

    /**
     * Kick specified player
     *
     * @param Player $player
     * @param string $reason
     */
    public function kick($player, $reason = NULL)
    {
        // Check history
        if (isset($this->history[$player->id])) return;

        // Send command
        if ($reason)
        {
            $this->command('clientkick '.$player->id.' "'.htmlspecialchars($reason, ENT_QUOTES, 'UTF-8').'"');
        }
        else
        {
            $this->command('clientkick '.$player->id);
        }

        // Add to history
        $this->history[$player->id] = TRUE;
    }

    /**
     * Send message
     *
     * @param string     $message
     * @param int|Player $player
     * @param string     $type
     */
    public function message($message, $player = NULL, $type = NULL)
    {
        // Add prefix
        $message = $this->messagePrefix.$message;

        // Message type
        if (!$player)
        {
            $type = 'global';
        }
        else
        {
            if ($player instanceof Player) $player = $player->id;

            if (!$type OR !isset($this->messageWhisper[$type]))
            {
                $type = 'whisper';
            }
            elseif (isset($this->messageWhisper[$type]))
            {
                $type = ($this->messageWhisper[$type]) ? 'whisper' : 'global';
            }
        }

        // Black flops doesn't support UTF (wordwrap too :D)
        $message = preg_replace('/[^\x01-\x7F]+/S', '', $message);

        // Split (anyone know less hacky/tricky solution?)
        $message = explode("\n", wordwrap($message, $this->messageLimit, "\n", TRUE));

        // Iterate
        foreach ($message as $msg)
        {
            // Type
            if ($type == 'global')
            {
                $this->command('say "'.strip_tags($msg).'"');
            }
            else
            {
                $this->command('tell '.$player.' "'.strip_tags($msg).'"');
            }
        }
    }

    /**
     * Set server config var
     *
     * @param string $cvar
     * @param mixed  $value
     * @param bool   $dvar
     */
    public function setCvar($cvar, $value, $dvar = FALSE)
    {
        // Admin dvar or cvar
        if($dvar)
        {
            $this->command('setadmindvar '.$cvar.' '.(is_int($value) ? $value : '"'.$value.'"'));
        }
        else
        {
            $this->command($cvar.' '.(is_int($value) ? $value : '"'.$value.'"'));
        }
    }

    /**
     * Get team status
     *
     * @param string $orderByPing
     */
    public function teamStatus($orderByPing = FALSE)
    {
        // Send command
        $rawResponse = $this->command('teamstatus');

        // Try again
        if (empty($rawResponse))
        {
            $rawResponse = $this->command('teamstatus');
        }

        // Break lines
        $response = explode("\x0a", $rawResponse);

        // Valid response?
        if (count($response) <= 1)
        {
            return array('map' => '', 'players' => array(), 'error' => 'Cannot download player list');
        }

        // Array containing teamstatus
        $teamstatus = array(
            'map' => substr($response[0], 5),
            'players' => array(

            ),
            'error' => ''
        );

        // Remove header
        unset($response[0], $response[1], $response[2]);

        // Reader object
        $parser = new StatusParser;

        // Iterate players
        foreach($response as $line)
        {
            // Not a player?
            if(empty($line) OR strlen($line) < 70)
            {
                break;
            }

            // Parse line
            $line = $parser->parse($line);

            // Invalid players
            if (!$line['guid'] OR $line['ping'] = '999')
            {
                continue;
            }

            // Fix ping
            if ($line['ping'] == 'CNCT' OR $line['ping'] == 'ZMBI')
            {
                $line['ping'] = '100';
            }

            // Set player info
            $teamstatus['players'][$line['id']] = $line;
        }

        // Sort
        if ($orderByPing) uasort($teamstatus['players'], create_function('$a,$b', 'return ($a == $b ? 0 : ($a < $b ? 1 : -1));'));

        // Return
        return $teamstatus;
    }

    /**
     * Temp ban specified player
     *
     * @param Player $player
     * @param string $reason
     * @param int    $expires
     */
    public function tempBan($player, $reason = NULL, $expires = NULL)
    {
        // Check history
        if (isset($this->history[$player->id])) return;

        // Send command
        $this->command('tempbanclient '.$player);

        // Default duration
        if ($expires === NULL)
        {
            $expires = (time() + $this->tempBanDuration);
        }

        // Deny access
        PlayerManager::setACL($player, PlayerManager::DENY, $reason, $expires);

        // Add to history
        $this->history[$player->id] = TRUE;
    }

    /**
     * Wipe kick/ban history
     */
    public function wipeHistory()
    {
        $this->history = array();
    }
}