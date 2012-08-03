<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Tracker plugin
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
 * @subpackage plugin
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class TrackerPlugin implements Plugin
{
    // On death info
    protected $deadNotice = array();
    protected $deadNotice2 = array();
    protected $deadNoticeEnabled = array();

    // Dominations
    protected $dominations = array();
    protected $dominationsEnabled = array();

    // Restricted weapons
    protected $restrictedAction = array();
    protected $restrictedAttachments = array();
    protected $restrictedWeapons = array();

    /**
     * Check restrictions
     *
     * @param Weapon $weapon
     * @param Player $attacker
     */
    protected function checkRestrictions(Weapon $weapon, Player $attacker)
    {
        // ID
        $id = Server::get('id');

        // Preload config
        if (!isset($this->restrictedAction[$id])) $this->config();

        // World?
        if ($attacker instanceof WorldPlayer OR $attacker->hasFlag('immunity'))
        {
            return FALSE;
        }

        // Check weapon
        if (isset($this->restrictedWeapons[$id][$weapon->weapon]))
        {
            // Kick
            if ($this->restrictedAction[$id] == 'kick')
            {
                // Kick
                Server::get()->kick($attacker, __('Restricted weapon'));

                // Message
                Server::get()->message(__('Player :name has been kicked for using restricted weapon (:weapon)', array(
                    ':name' => $attacker->name,
                    ':weapon' => $weapon->getWeapon()
                )));
            }
            elseif ($this->restrictedAction[$id] == 'ban')
            {
                // Ban
                Server::get()->ban($attacker);

                // Message
                Server::get()->message(__('Player :name has been banned for using restricted weapon (:weapon)', array(
                    ':name' => $attacker->name,
                    ':weapon' => $weapon->getWeapon()
                )));
            }
            elseif ($this->restrictedAction[$id] == 'tempban')
            {
                // Ban
                Server::get()->tempBan($attacker);

                // Message
                Server::get()->message(__('Player :name has been temp-banned for using restricted weapon (:weapon)', array(
                    ':name' => $attacker->name,
                    ':weapon' => $weapon->getWeapon()
                )));
            }
            elseif($this->restrictedAction[$id] == 'warn')
            {
                Warning::add($attacker, 'weapon');
            }
            else
            {
                // Message
                Server::get()->message(__('Player :name is using restricted weapon (:weapon)', array(
                    ':name' => $attacker->name,
                    ':weapon' => $weapon->getWeapon()
                )));
            }

            // Done
            return TRUE;
        }

        // Check attachment
        foreach ($this->restrictedAttachments[$id] as $attach)
        {
            // GL/MK/FT
            if ($weapon->killedBy == Weapon::KILLED_BY_WEAPON AND ($attach == Weapon::ATTACHMENT_NOOBTUBE OR $attach == Weapon::ATTACHMENT_FLAMETHROWER OR $attach == Weapon::ATTACHMENT_MASTERKEY))
            {
                continue;
            }

            // Has?
            if ($weapon->hasAttachment($attach))
            {
                // Kick
                if ($this->restrictedAction[$id] == 'kick')
                {
                    // Kick
                    Server::get()->kick($attacker, __('Restricted attachment'));

                    // Message
                    Server::get()->message(__('Player :name has been kicked for using restricted attachment (:weapon)', array(
                        ':name' => $attacker->name,
                        ':weapon' => Weapon::$attachmentToName[$attach]
                    )));
                }
                elseif ($this->restrictedAction[$id] == 'ban')
                {
                    // Ban
                    Server::get()->ban($attacker);

                    // Message
                    Server::get()->message(__('Player :name has been banned for using restricted attachment (:weapon)', array(
                        ':name' => $attacker->name,
                        ':weapon' => Weapon::$attachmentToName[$attach]
                    )));
                }
                elseif ($this->restrictedAction[$id] == 'tempban')
                {
                    // Ban
                    Server::get()->tempBan($attacker);

                    // Message
                    Server::get()->message(__('Player :name has been temp-banned for using restricted attachment (:weapon)', array(
                        ':name' => $attacker->name,
                        ':weapon' => Weapon::$attachmentToName[$attach]
                    )));
                }
                elseif($this->restrictedAction[$id] == 'warn')
                {
                    Warning::add($attacker, 'attach');
                }
                else
                {
                    // Message
                    Server::get()->message(__('Player :name is using restricted attachment (:weapon)', array(
                        ':name' => $attacker->name,
                        ':weapon' => Weapon::$attachmentToName[$attach]
                    )));
                }

                // Done
                return TRUE;
            }
        }

        // Valid
        return FALSE;
    }

    /**
     * Death info
     *
     * @param Player $player
     * @param string $text
     */
    public function commandDeath(Player $player, $text)
    {
        if (!isset($this->deadNotice2[Server::get('id')])) return;
        
        if (!empty($this->deadNotice2[Server::get('id')][$player->guid]))
        {
            $s = &$this->deadNotice2[Server::get('id')][$player->guid];

            Server::get()->message(__('DMG dealt: :dealt, DMG received: :taken, HS: :hs, Kills: :kills, Most DMG dealt: :mostplayer^7 (:mostdmg)', array(
                ':dealt' => $s['dealt'],
                ':taken' => $s['taken'],
                ':hs' => $s['headshots'],
                ':kills' => $s['kills'],
                ':mostplayer' => $s['most_dealt']['player'],
                ':mostdmg' => $s['most_dealt']['dmg']
            )), $player);
        }
    }

    /**
     * Load config
     */
    public function config()
    {
        // ID
        $id = Server::get('id');

        // Dead notice
        $this->deadNotice[$id] = array();
        $this->deadNotice2[$id] = array();
        $this->deadNoticeEnabled[$id] = Config::get('tracker.show_dead_notice', TRUE);

        // Dominations
        $this->dominations[$id] = array();
        $this->dominationsEnabled[$id] = Config::get('tracker.show_dominations', TRUE);

        // Restrictions
        $this->restrictedAction[$id] = Config::get('tracker.penalty_type', 'kick');
        $this->restrictedAttachments[$id] = array();
        $this->restrictedWeapons[$id] = array();

        foreach (Config::get('tracker.restricted_attachments', array()) as $attach)
        {
            // Valid?
            if (($attach = Weapon::gameToAttachment($attach)))
            {
                $this->restrictedAttachments[$id][$attach] = $attach;
            }
        }

        foreach (Config::get('tracker.restricted_weapons', array()) as $weap)
        {
            // Valid?
            if (($weap = Weapon::gameToWeapon($weap)))
            {
                $this->restrictedWeapons[$id][$weap] = TRUE;
            }
        }
    }

    /**
     * Load plugin
     *
     * @return bool
     */
    public function load()
    {
        // Commands
        Commands::add('death', array($this, 'commandDeath'));

        // Events
        Event::add('onExitLevel'   , array($this, 'onExitLevel'));
        Event::add('onQuit'        , array($this, 'onQuit'));
        Event::add('onKill'        , array($this, 'onKill'));
        Event::add('onDamage'      , array($this, 'onDamage'));
        Event::add('onConfigReload', array($this, 'config'));

        // Done
        return TRUE;
    }

    /**
     * On damage
     *
     * @param Player $victim
     * @param Player $attacker
     * @param Weapon $weapon
     * @param Damage $damage
     */
    public function onDamage(Player $victim, Player $attacker, Weapon $weapon, Damage $damage)
    {
        // Check restrictions
        if ($this->checkRestrictions($weapon, $attacker))
        {
            return;
        }

        // ID
        $id = Server::get('id');

        // Dead notice
        if (!$this->deadNoticeEnabled[$id])
        {
            return;
        }

        // Victim
        if (!($victim instanceof WorldPlayer) AND (!$attacker->isSameTeam($victim) OR $attacker->guid == $victim->guid))
        {
            if (!isset($this->deadNotice[$id][$victim->guid]))
            {
                $this->deadNotice[$id][$victim->guid] = array(
                    'kills' => 0,
                    'headshots' => 0,
                    'dealt' => 0,
                    'taken' => 0,
                    'most_dealt' => array('dmg' => 0, 'player' => ''),
                    'most_taken' => array('dmg' => 0, 'player' => ''),
                );
            }

            $this->deadNotice[$id][$victim->guid]['taken'] += $damage->amount;

            // Most taken
            if ($this->deadNotice[$id][$victim->guid]['most_taken']['dmg'] < $damage->amount)
            {
                $this->deadNotice[$id][$victim->guid]['most_taken']['dmg'] = $damage->amount;
                $this->deadNotice[$id][$victim->guid]['most_taken']['player'] = $attacker->name;
            }
        }

        // Attacker
        if (!($attacker instanceof WorldPlayer) AND (!$attacker->isSameTeam($victim) OR $attacker->guid == $victim->guid))
        {
            if (!isset($this->deadNotice[$id][$attacker->guid]))
            {
                $this->deadNotice[$id][$attacker->guid] = array(
                    'kills' => 0,
                    'headshots' => 0,
                    'dealt' => 0,
                    'taken' => 0,
                    'most_dealt' => array('dmg' => 0, 'player' => ''),
                    'most_taken' => array('dmg' => 0, 'player' => ''),
                );
            }

            // Basic stats
            $this->deadNotice[$id][$attacker->guid]['dealt'] += $damage->amount;

            // Most dealt
            if ($this->deadNotice[$id][$attacker->guid]['most_dealt']['dmg'] < $damage->amount)
            {
                $this->deadNotice[$id][$attacker->guid]['most_dealt']['dmg'] = $damage->amount;
                $this->deadNotice[$id][$attacker->guid]['most_dealt']['player'] = $victim->name;
            }
        }
    }

    /**
     * Cleanup things
     */
    public function onExitLevel()
    {
        // Reset data
        $this->deadNotice[Server::get('id')] = array();
        $this->deadNotice2[Server::get('id')] = array();
        $this->dominations[Server::get('id')] = array();
    }

    /**
     * On kill
     *
     * @param Player $victim
     * @param Player $attacker
     * @param Weapon $weapon
     * @param Damage $damage
     */
    public function onKill(Player $victim, Player $attacker, Weapon $weapon, Damage $damage)
    {
        // Check restrictions
        if ($this->checkRestrictions($weapon, $attacker))
        {
            return;
        }

        // ID
        $id = Server::get('id');

        // Dominations
        if ($this->dominationsEnabled[$id] AND !($attacker instanceof WorldPlayer) AND !($victim instanceof WorldPlayer) AND $victim->guid != $attacker->guid)
        {
            if (!isset($this->dominations[$id][$attacker->guid]))
            {
                $this->dominations[$id][$attacker->guid] = array();
            }

            if (!isset($this->dominations[$id][$victim->guid]))
            {
                $this->dominations[$id][$victim->guid] = array();
            }

            if (isset($this->dominations[$id][$victim->guid][$attacker->guid]))
            {
                if ($this->dominations[$id][$victim->guid][$attacker->guid] >= 4)
                {
                    Server::get()->message(__('Player :name got revenge on :victim', array(':name' => $attacker->name, ':victim' => $victim->name)));
                }

                $this->dominations[$id][$victim->guid][$attacker->guid] = 0;
            }

            if (!isset($this->dominations[$id][$attacker->guid][$victim->guid]))
            {
                $this->dominations[$id][$attacker->guid][$victim->guid] = 0;
            }

            $this->dominations[$id][$attacker->guid][$victim->guid]++;

            if ($this->dominations[$id][$attacker->guid][$victim->guid] == 4)
            {
                Server::get()->message(__('Player :name is dominating :victim', array(':name' => $attacker->name, ':victim' => $victim->name)));
            }
        }

        // Dead notice
        if (!$this->deadNoticeEnabled[$id])
        {
            return;
        }

        // Dead notice
        if (!($victim instanceof WorldPlayer) AND (!$attacker->isSameTeam($victim) OR $attacker->guid == $victim->guid))
        {
            if (!isset($this->deadNotice[$id][$victim->guid]))
            {
                $this->deadNotice[$id][$victim->guid] = array(
                    'kills' => 0,
                    'headshots' => 0,
                    'dealt' => 0,
                    'taken' => 0,
                    'most_dealt' => array('dmg' => 0, 'player' => ''),
                    'most_taken' => array('dmg' => 0, 'player' => ''),
                );
            }

            $this->deadNotice[$id][$victim->guid]['taken'] += $damage->amount;

            // Most taken
            if ($this->deadNotice[$id][$victim->guid]['most_taken']['dmg'] < $damage->amount)
            {
                $this->deadNotice[$id][$victim->guid]['most_taken']['dmg'] = $damage->amount;
                $this->deadNotice[$id][$victim->guid]['most_taken']['player'] = $attacker->name;
            }

            // Shortcut
            $s = &$this->deadNotice[$id][$victim->guid];

            // Store
            $this->deadNotice2[$id][$victim->guid] = $this->deadNotice[$id][$victim->guid];

            // Reset
            $this->deadNotice[$id][$victim->guid] = array(
                'kills' => 0,
                'headshots' => 0,
                'dealt' => 0,
                'taken' => 0,
                'most_dealt' => array('dmg' => 0, 'player' => ''),
                'most_taken' => array('dmg' => 0, 'player' => ''),
            );
        }

        // Stats
        if (!($attacker instanceof WorldPlayer) AND (!$attacker->isSameTeam($victim) OR $attacker->guid == $victim->guid))
        {
            if (!isset($this->deadNotice[$id][$attacker->guid]))
            {
                $this->deadNotice[$id][$attacker->guid] = array(
                    'kills' => 0,
                    'headshots' => 0,
                    'dealt' => 0,
                    'taken' => 0,
                    'most_dealt' => array('dmg' => 0, 'player' => ''),
                    'most_taken' => array('dmg' => 0, 'player' => ''),
                );
            }

            // Basic stats
            $this->deadNotice[$id][$attacker->guid]['kills']++;
            $this->deadNotice[$id][$attacker->guid]['dealt'] += $damage->amount;

            // Most dealt
            if ($this->deadNotice[$id][$attacker->guid]['most_dealt']['dmg'] < $damage->amount)
            {
                $this->deadNotice[$id][$attacker->guid]['most_dealt']['dmg'] = $damage->amount;
                $this->deadNotice[$id][$attacker->guid]['most_dealt']['player'] = $victim->name;
            }

            // HS
            if ($damage->location == Damage::HIT_HEAD)
            {
                $this->deadNotice[$id][$attacker->guid]['headshots']++;
            }
        }
    }

    /**
     * Cleanup user data
     *
     * @param Player $player
     */
    public function onQuit(Player $player)
    {
        // ID
        $id = Server::get('id');

        // Dead notice
        if (isset($this->deadNotice[$id]) AND isset($this->deadNotice[$id][$player->guid]))
        {
            unset($this->deadNotice[$id][$player->guid]);
        }

        if (isset($this->deadNotice2[$id]) AND isset($this->deadNotice2[$id][$player->guid]))
        {
            unset($this->deadNotice2[$id][$player->guid]);
        }

        // Players dominated by him
        if (isset($this->dominations[$id]) AND isset($this->dominations[$id][$player->guid]))
        {
            unset($this->dominations[$id][$player->guid]);
        }
    }

    /**
     * Reload plugin
     */
    public function reload()
    {
        // Events
        Event::add('onExitLevel'   , array($this, 'onExitLevel'));
        Event::add('onQuit'        , array($this, 'onQuit'));
        Event::add('onKill'        , array($this, 'onKill'));
        Event::add('onDamage'      , array($this, 'onDamage'));
        Event::add('onConfigReload', array($this, 'config'));

        // Clear config
        $this->deadNoticeEnabled = array();
        $this->dominationsEnabled = array();
        $this->restrictedAction = array();
        $this->restrictedAttachments = array();
        $this->restrictedWeapons = array();
    }

    /**
     * Unload plugin
     */
    public function unload()
    {

    }
}
