<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Blackops teamstatus parser
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
 * @subpackage    lib
 * @license        http://www.opensource.org/licenses/bsd-license.php    New BSD License
 */
class Statusparser {

    /**
     * Max string pointer (length - 1)
     *
     * @var    int
     */
    protected $max = 0;

    /**
     * Status to parse
     *
     * @var    string
     */
    protected $string = '';

    /**
     * Current pointer
     *
     * @var    int
     */
    protected $pointer = 0;

    /**
     * Skip whitechars
     */
    protected function clear_whitechars()
    {
        // Iterate
        for(; $this->pointer <= $this->max; $this->pointer++)
        {
            // Whitechar?
            if($this->string[$this->pointer] != ' ' AND $this->string[$this->pointer] != "\x20")
            {
                // Spaces cleared
                break;
            }
        }
    }

    /**
     * Parse status
     *
     * @param    string    $string
     */
    public function parse($string)
    {
        // Reset pointer
        $this->pointer = 0;

        // Remove spaces (for some stupid reason, PHP thinks sometimes \x20 is diff than space)
        $string = trim(trim($string, "\x20"));

        // Set string
        $this->string = $string;

        // Max pointer
        $this->max = strlen($string) - 1;

        // Player data goes here
        $player = array();

        // Read ID
        $player['id'] = $this->read_int();

        // Clear whitechars
        $this->clear_whitechars();

        // Read score
        $player['score'] = $this->read_int();

        // Clear whitechars
        $this->clear_whitechars();

        // Read ping (yes string, sometimes it's CNCT)
        $player['ping'] = $this->read_string();

        // Clear whitechars
        $this->clear_whitechars();

        // Read GUID
        $player['guid'] = $this->read_int();

        // Clear whitechars
        $this->clear_whitechars();

        // Most issues is caused by this...
        $player['name'] = $this->read_name();

        // Clear whitechars
        $this->clear_whitechars();

        // Read team
        $player['team'] = $this->read_int();

        // Clear whitechars
        $this->clear_whitechars();

        // Read last message
        $player['lastmsg'] = $this->read_int();

        // Clear whitechars
        $this->clear_whitechars();

        // Read address (with port?)
        $player['address'] = $this->read_string();

        // Clear whitechars
        $this->clear_whitechars();

        // Read qport
        $player['qport'] = $this->read_int();

        // Clear whitechars
        $this->clear_whitechars();

        // Read rate
        $player['rate'] = $this->read_int();

        // Clear whitechars
        $this->clear_whitechars();

        // Done :)
        return $player;
    }

    /**
     * Read integer from status
     *
     * @return    int
     */
    protected function read_int()
    {
        // Init var
        $reader = '';

        // Iterate
        for(; $this->pointer <= $this->max; $this->pointer++)
        {
            // Whitechar?
            if($this->string[$this->pointer] == ' ' OR $this->string[$this->pointer] == "\x20")
            {
                // Space detected, stop iteration
                break;
            }

            // Valid character?
            if($this->string[$this->pointer] == '-' OR ctype_digit($this->string[$this->pointer]))
            {
                // Valid character, continue
                $reader .= $this->string[$this->pointer];
            }
        }

        // Return integer
        return (int) $reader;
    }

    /**
     * Read player name from status
     *
     * @return    string
     */
    protected function read_name()
    {
        // Init var
        $reader = '';

        // Iterate
        for(; $this->pointer <= $this->max; $this->pointer++)
        {
            // Delimiter?
            if($this->string[$this->pointer] == "\x5e")
            {
                // Hmm
                if(($this->pointer + 2) > $this->max)
                {
                    // This is not supposed to happen anyway
                    continue;
                }

                // Seven?
                if($this->string[$this->pointer + 1] == "\x37")
                {
                    // Fix pointer position
                    $this->pointer += 2;

                    // Name is ready
                    break;
                }
            }

            // Valid character, continue
            $reader .= $this->string[$this->pointer];
        }

        // Return string
        return $reader;
    }

    /**
     * Read standard string from status
     *
     * @return    string
     */
    protected function read_string()
    {
        // Init var
        $reader = '';

        // Iterate
        for(; $this->pointer <= $this->max; $this->pointer++)
        {
            // Whitechar?
            if($this->string[$this->pointer] == ' ' OR $this->string[$this->pointer] == "\x20")
            {
                // Space detected, stop iteration
                break;
            }

            // Valid character, continue
            $reader .= $this->string[$this->pointer];
        }

        // Return string
        return $reader;
    }
}