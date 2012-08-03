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
class Blackops_Rcon {

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
        // No connection
        if(!$this->socket OR !is_resource($this->socket))
        {
            throw new Exception('You must connect to remote console before issuing commands');
        }

        // Send command
        fwrite($this->socket, "\xff\xff\xff\xff\x00".$this->password."\x20".$command."\x00");

        // Wait for response?
        if($return_response)
        {
            // Retrieve reponse
            $response = $this->get_response();

            // Invalid password?
            if(trim($response) == 'Invalid password.')
            {
                throw new Exception('Invalid remote console password.');
            }

            return $response;
        }
    }

    /**
     * Connect to rcon
     *
     * @throws    Exception
     */
    public function connect()
    {
        // Errors
        $errno = 0;
        $errstr = '';

        // Open socket
        $this->socket = fsockopen('udp://'.$this->host, $this->port, $errno, $errstr, 5);

        // Invalid?
        if(!$this->socket)
        {
            throw new Exception('Cannot connect to remote console');
        }
    }

    /**
     * Disconnect
     */
    public function disconnect()
    {
        fclose($this->socket);
    }

    /**
     * Read response
     *
     * @return    string
     */
    public function get_response()
    {
        // Var containing response
        $response = '';

        // Set socket timeout
        stream_set_timeout($this->socket, 0, 7e5);

        // Lol how rare
        do
        {
            // Read 8kb
            $stream_read = fread($this->socket, 8192);

            // End of response?
            if(strpos($stream_read, "\x00") !== FALSE)
            {
                // Cut
                $stream_read = substr($stream_read, 0, strpos($stream_read, "\x00"));
            }

            // Get socket info
            $stream_info = stream_get_meta_data($this->socket);

            // Append
            $response .= substr(trim($stream_read, "\x0a"), 11);
        }
        while(!$stream_info['timed_out']);

        // Return
        return $response;
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