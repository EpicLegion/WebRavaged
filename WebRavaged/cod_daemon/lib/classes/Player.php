<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Player object
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
 * @subpackage game
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Player
{
    /**
     * @var array
     */
    public $flags = array();

    /**
     * @var int
     */
    public $guid = 0;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $team = '';

    /**
     * Constructor
     *
     * @param int    $guid
     * @param int    $id
     * @param string $name
     * @param string $team
     */
    public function __construct($guid, $id, $name, $team)
    {
        // Set properties
        $this->name = $name;
        $this->team = $team;
        $this->id = $id;
        $this->guid = $guid;

        // Get flags
        $this->flags = PlayerManager::getFlags($guid);
    }

    /**
     * Player has flag
     *
     * @param  string $flag
     * @param  bool   $acceptRoot
     * @return bool
     */
    public function hasFlag($flag, $acceptRoot = TRUE)
    {
        // Root
        if ($acceptRoot AND isset($this->flags['root']))
        {
            return TRUE;
        }

        // Has?
        return (bool) isset($this->flags[$flag]);
    }

    /**
     * Check teams
     *
     * @param  Player $otherPlayer
     * @return bool
     */
    public function isSameTeam(Player $otherPlayer)
    {
        if ($this->team == NULL OR $otherPlayer == NULL OR $otherPlayer->team == NULL OR $otherPlayer instanceof WorldPlayer)
        {
            return FALSE;
        }

        if ($this->team == $otherPlayer->team)
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Update user data
     *
     * @param int    $id
     * @param string $name
     * @param string $team
     */
    public function update($id, $name, $team)
    {
        // Set basic properties
        $this->name = $name;
        $this->id = $id;

        // Team
        if ($team == 'axis' OR $team == 'allies') $this->team = $team;
    }
}

// Some temp clients
class DemoclientPlayer extends Player
{
    /**
     * Constructor
     *
     * @param int    $guid
     * @param int    $id
     * @param string $name
     * @param string $team
     */
    public function __construct()
    {
        // Set properties
        $this->name = 'democlient';
        $this->team = 'none';
        $this->id = -1;
        $this->guid = 0;

        // Get flags
        $this->flags = array();
    }

    /**
     * Update user data
     *
     * @param int    $id
     * @param string $name
     * @param string $team
     */
    public function update($id, $name, $team)
    {
    }
}

class WorldPlayer extends Player
{
    /**
     * Constructor
     *
     * @param int    $guid
     * @param int    $id
     * @param string $name
     * @param string $team
     */
    public function __construct()
    {
        // Set properties
        $this->name = 'world';
        $this->team = 'none';
        $this->id = -1;
        $this->guid = 0;

        // Get flags
        $this->flags = array();
    }

    /**
     * Update user data
     *
     * @param int    $id
     * @param string $name
     * @param string $team
     */
    public function update($id, $name, $team)
    {
    }
}