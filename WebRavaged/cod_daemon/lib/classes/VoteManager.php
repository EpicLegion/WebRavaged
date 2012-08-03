<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Vote system
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

final class VoteManager
{
    /**
     * @var array
     */
    protected static $playerVotes = array();

    /**
     * @var array
     */
    protected static $types = array();

    /**
     * @var array
     */
    protected static $typesCallbacks = array();

    /**
     * @var array
     */
    protected static $voteData = array();

    /**
     * @var array
     */
    protected static $voteDisabled = array();

    /**
     * @var array
     */
    protected static $voteMinimum = array();

    /**
     * @var array
     */
    protected static $voteTimeout = array();

    /**
     * Add new vote type
     *
     * @param string $name
     * @param mixed  $onStart
     * @param mixed  $onEnd
     */
    public static function add($name, $onStart, $onEnd)
    {
        self::$types[$name] = array('name' => $name, 'onStart' => $onStart, 'onEnd' => $onEnd);
        self::$typesCallbacks[$name] = $onEnd;
    }

    /**
     * Add only callback
     *
     * @param string   $name
     * @param callback $onEnd
     */
    public static function addCallback($name, $onEnd)
    {
        self::$typesCallbacks[$name] = $onEnd;
    }

    /**
     * Manual call vote
     *
     * @param string $name
     * @param array  $options
     * @param mixed  $params
     * @param string $message
     */
    public static function callVote($name, $options, $params, $message = '')
    {
        // Server ID
        $id = Server::get('id');

        // End old vote
        if (!empty(self::$voteData[$id]))
        {
            self::doFinishVote();
        }

        // Override vote
        self::$playerVotes[$id] = array();
        self::$voteData[$id] = array(
            'name' => $name,
            'options' => $options,
            'params' => $params,
            'time' => 0,
            'current_players' => 0,
            'max_players' => count(PlayerManager::getPlayers())
        );

        // Command character
        $char = Config::get('commands.character', '!');

        // Ugly hack
        $optionsMSG = $char.str_replace('mp_', '', implode(__(' or ').$char, array_keys($options)));

        // Notify users about this event
        Server::get()->message(__('Server has called the vote: :message To vote say: :opt', array(
            ':message' => $message,
            ':opt' => $optionsMSG
        )));
    }

    /**
     * Clear data
     *
     * @param bool $config
     */
    public static function clear($config = TRUE)
    {
        self::$types = array();
        self::$playerVotes = array();
        self::$voteData = array();

        if ($config)
        {
            self::$voteDisabled = array();
            self::$voteTimeout = array();
            self::$voteMinimum = array();
        }
    }

    /**
     * Finish voting
     */
    private static function doFinishVote()
    {
        // Server ID
        $id = Server::get('id');

        // Callback
        $callback = self::$typesCallbacks[self::$voteData[$id]['name']];

        // Minimum vote count
        if (self::$voteMinimum[$id] > ((self::$voteData[$id]['current_players'] / self::$voteData[$id]['max_players']) * 100))
        {
            // End
            Server::get()->message(__('Voting finished. Minimum voters count not reached.'));
            call_user_func($callback, 'fail', '', self::$voteData[$id]['params']);

            // Cleanup
            self::$playerVotes[$id] = array();
            self::$voteData[$id] = array();

            // Done
            return;
        }

        // Best option
        $best = array('votes' => 0, 'opt' => '', 'draw' => 0);

        // Iterate options
        foreach (self::$voteData[$id]['options'] as $opt => $votes)
        {
            if ($best['votes'] < $votes)
            {
                $best = array('opt' => array(0 => $opt), 'votes' => $votes, 'draw' => 1);
            }
            elseif ($best['votes'] == $votes)
            {
                $best['draw']++;
                $best['opt'][] = $opt;
            }
        }

        // No votes?
        if (!$best OR !$best['votes'] OR !self::$voteData[$id]['current_players'])
        {
            Server::get()->message(__('Voting finished. No one has voted.'));
            call_user_func($callback, 'fail', '', self::$voteData[$id]['params']);
        }
        else
        {
            // Draw?
            if ($best['draw'] > 1)
            {
                Server::get()->message(__('Voting finished. Draw.'));
                call_user_func($callback, 'draw', $best['opt'], self::$voteData[$id]['params']);
            }
            else
            {
                Server::get()->message(__('Voting finished. Winning option: :opt.', array(':opt' => $best['opt'][0])));
                call_user_func($callback, 'win', $best['opt'][0], self::$voteData[$id]['params']);
            }
        }

        // Cleanup
        self::$playerVotes[$id] = array();
        self::$voteData[$id] = array();
    }

    /**
     * Get data to save
     *
     * @return array
     */
    public static function get()
    {
        return array('players' => self::$playerVotes, 'votes' => self::$voteData);
    }

    /**
     * Init system
     */
    public static function init()
    {
        // Events
        Event::add('onServerLogFinish', array('VoteManager', 'onServerLogFinish'));
        Event::add('onExitLevel'      , array('VoteManager', 'onExitLevel'));
        Event::add('onConfigReload'   , array('VoteManager', 'reloadConfig'));

        // Commands
        Commands::add('callvote', array('VoteManager', 'onCallVote'), 'callvote');
        Commands::add('vote'    , array('VoteManager', 'onVote'));
    }

    /**
     * Call vote
     *
     * @param Player $player
     * @param string $text
     */
    public static function onCallVote(Player $player, $text)
    {
        // Permissions?
        if (!$player->hasFlag('callvote'))
        {
            Server::get()->message(__('Insufficient permissions'), $player);
            return;
        }

        // Option
        if (!isset($text[1]))
        {
            Server::get()->message(__('Usage: !callvote type params (Available types: :types)', array(':types' => implode(', ', array_keys(self::$types)))), $player);
            return;
        }

        // Remove spaces
        $text[1] = trim($text[1]);

        // Valid option?
        if (!isset(self::$types[$text[1]]))
        {
            Server::get()->message(__('Invalid vote type'), $player);
            return;
        }

        // Server ID
        $id = Server::get('id');

        // Any votes in progress?
        if (!empty(self::$voteData[$id]))
        {
            Server::get()->message(__('Vote is already in progress'), $player);
            return;
        }

        // Config loaded?
        if (!isset(self::$voteTimeout[$id]))
        {
            // Timeout
            self::$voteTimeout[$id] = Config::get('voting.timeout', 30);

            // Disabled
            self::$voteDisabled[$id] = array();

            foreach (Config::get('voting.disabled', array()) as $v)
            {
                self::$voteDisabled[$id][$v] = TRUE;
            }

            // Minimum vote count
            self::$voteMinimum[$id] = Config::get('voting.minimum', 50);
        }

        // Disabled?
        if (isset(self::$voteDisabled[$id][$text[1]]))
        {
            Server::get()->message(__('This vote type has been disabled by server admin'), $player);
            return;
        }

        // Make call
        $callback = call_user_func(self::$types[$text[1]]['onStart'], $player, $text);

        // Don't start it
        if (!$callback OR !is_array($callback))
        {
            return;
        }

        // Register vote
        self::$playerVotes[$id] = array();
        self::$voteData[$id] = array(
            'name' => $text[1],
            'options' => $callback['options'],
            'params' => $callback['params'],
            'time' => 0,
            'current_players' => 0,
            'max_players' => count(PlayerManager::getPlayers())
        );

        // Command character
        $char = Config::get('commands.character', '!');

        // Notify users about this event
        Server::get()->message(__('Player :name has called the vote: :message To vote say: :opt', array(
            ':name' => $player->name.'^7',
            ':message' => $callback['message'],
            ':opt' => $char.implode(__(' or ').$char, array_keys($callback['options']))
        )));
    }

    /**
     * Level exit (do cleanup)
     */
    public static function onExitLevel()
    {
        // Clear votes
        self::$playerVotes[Server::get('id')] = array();
        self::$voteData[Server::get('id')] = array();
    }

    /**
     * Time elapsed
     *
     * @param float $deltaTime
     */
    public static function onServerLogFinish($deltaTime)
    {
        // Server ID
        $id = Server::get('id');

        // Vote in progress?
        if (empty(self::$voteData[$id]))
        {
            return;
        }

        // Config loaded?
        if (!isset(self::$voteTimeout[$id]))
        {
            // Timeout
            self::$voteTimeout[$id] = Config::get('voting.timeout', 30);

            // Disabled
            self::$voteDisabled[$id] = array();

            foreach (Config::get('voting.disabled', array()) as $v)
            {
                self::$voteDisabled[$id][$v] = TRUE;
            }

            // Minimum vote count
            self::$voteMinimum[$id] = Config::get('voting.minimum', 50);
        }

        // Add timeout
        self::$voteData[$id]['time'] += $deltaTime;

        // Time to finish voting?
        if (self::$voteData[$id]['time'] >= self::$voteTimeout[$id])
        {
            self::doFinishVote();
        }
    }

    /**
     * Vote
     *
     * @param Player $player
     * @param string $text
     * @param bool   $silent
     */
    public static function onVote(Player $player, $text, $silent = FALSE)
    {
        // Server ID
        $id = Server::get('id');

        // Active vote?
        if (empty(self::$voteData[$id]))
        {
            if (!$silent) Server::get()->message(__('No vote in progress'), $player);
            return;
        }

        // Already voted?
        if (isset(self::$playerVotes[$id][$player->guid]))
        {
            if (!$silent) Server::get()->message(__('You cannot vote twice in the same voting'), $player);
            return;
        }

        // No option
        if (!isset($text[1]))
        {
            return;
        }

        // Trim
        $text[1] = trim($text[1]);

        // Valid option?
        if (!isset(self::$voteData[$id]['options'][$text[1]]))
        {
            // Found?
            $found = FALSE;

            // Find
            foreach (self::$voteData[$id]['options'] as $k => $v)
            {
                if (preg_match('/.*'.preg_quote($text[1]).'.*/i', $k))
                {
                    $text[1] = $k;
                    $found = TRUE;
                    break;
                }
            }

            // Message
            if (!$found)
            {
                if (!$silent) Server::get()->message(__('Invalid option'), $player);
                return;
            }
        }

        // Vote
        self::$playerVotes[$id][$player->guid] = TRUE;

        self::$voteData[$id]['options'][$text[1]]++;
        self::$voteData[$id]['current_players']++;

        // The end?
        if (self::$voteData[$id]['current_players'] >= self::$voteData[$id]['max_players'])
        {
            self::doFinishVote();
        }
        else
        {
            // Best option
            $best = 0;

            // Iterate options
            foreach (self::$voteData[$id]['options'] as $opt => $votes)
            {
                if ($best < $votes) $best = $votes;
            }

            // Double
            $best *= 2;

            // Majority?
            if ($best > self::$voteData[$id]['max_players'])
            {
                self::doFinishVote();
            }
        }
    }

    /**
     * Reload config
     */
    public static function reloadConfig()
    {
        // Server ID
        $id = Server::get('id');

        // Timeout
        self::$voteTimeout[$id] = Config::get('voting.timeout', 30);

        // Minimum vote count
        self::$voteMinimum[$id] = Config::get('voting.minimum', 50);

        // Disabled
        self::$voteDisabled[$id] = array();

        foreach (Config::get('voting.disabled', array()) as $v)
        {
            self::$voteDisabled[$id][$v] = TRUE;
        }
    }

    /**
     * Resume votes
     *
     * @param array $data
     */
    public static function resume(array $data = array())
    {
        // Init
        self::init();

        // Restore
        self::$playerVotes = $data['players'];
        self::$voteData    = $data['votes'];
    }

    /**
     * Remove vote type
     *
     * @param string $name
     */
    public static function remove($name)
    {
        if (isset(self::$types[$name])) unset(self::$types[$name]);
    }
}
