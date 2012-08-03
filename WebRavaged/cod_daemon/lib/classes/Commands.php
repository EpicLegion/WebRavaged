<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Commands system
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

final class Commands
{
    // Consts
    const GLOBAL_SAY = 0;
    const TEAM_SAY   = 1;
    const BOTH_SAY   = 2;

    /**
     * @var array
     */
    protected static $aliases = array();

    /**
     * @var array
     */
    protected static $badwords = array();

    /**
     * @var array
     */
    protected static $commandCharacter = array();

    /**
     * @var array
     */
    protected static $commands = array();

    /**
     * @var array
     */
    protected static $custom = array();

    /**
     * @var array
     */
    protected static $disabled = array();

    /**
     * Register command
     *
     * @param string $name
     * @param string $callback
     * @param string $flag
     * @param int    $type
     */
    public static function add($name, $callback, $flag = NULL, $type = 2)
    {
        // Add command
        self::$commands[$name] = array('name' => $name, 'callback' => $callback, 'type' => $type, 'flag' => $flag);
    }

    /**
     * Language filter
     *
     * @param Player $player
     * @param string $text
     */
    private static function checkBadwords(Player $player, $text)
    {
        // Ignore
        if ($player->hasFlag('immunity'))
        {
            return;
        }

        // ID
        $id = Server::get('id');

        // Join
        if (is_array($text)) $text = implode(' ', $text);

        // Badword?
        foreach (array_keys(self::$badwords[$id]) as $bad)
        {
            // Check
            if (stripos($text, $bad) !== FALSE)
            {
                Warning::add($player, 'language');
            }
        }
    }

    /**
     * Clear commands list (and optionally - config)
     *
     * @param bool $config
     */
    public static function clear($config = TRUE)
    {
        // Commands
        self::$commands = array();

        // Config
        if ($config)
        {
            self::$commandCharacter = array();
            self::$aliases = array();
            self::$disabled = array();
        }
    }

    /**
     * Init commands system
     */
    public static function init()
    {
        // Events
        Event::add('onChat'        , array('Commands', 'onChat'));
        Event::add('onTeamChat'    , array('Commands', 'onTeamChat'));
        Event::add('onConfigReload', array('Commands', 'reloadConfig'));
    }

    /**
     * [Event handler] On global chat
     *
     * @param Player $player
     * @param string $text
     */
    public static function onChat(Player $player, $text)
    {
        // Ignore democlient / world chat (that's impossible anyway)
        if ($player instanceof DemoclientPlayer OR $player instanceof WorldPlayer)
        {
            return;
        }

        // Server ID
        $id = Server::get('id');

        // Load config
        if (!isset(self::$commandCharacter[$id]))
        {
            // Character
            self::$commandCharacter[$id] = Config::get('commands.character', '!');

            // Aliases
            self::$aliases[$id] = array();

            foreach (Config::get('commands.aliases', array()) as $k => $v)
            {
                self::$aliases[$id][$k] = $v;
            }

            // Disabled
            self::$disabled[$id] = array();

            foreach (Config::get('commands.disabled', array()) as $v)
            {
                self::$disabled[$id][$v] = TRUE;
            }

            // Badwords
            self::$badwords[$id] = array();

            foreach (Config::get('commands.badwords', array()) as $v)
            {
                self::$badwords[$id][$v] = TRUE;
            }

            // Custom commands
            self::$custom[$id] = array();

            foreach (Config::get('commands.custom', array()) as $k => $v)
            {
                self::$custom[$id][$k] = $v;
            }
        }

        // Command
        if (substr($text, 0, 1) == self::$commandCharacter[$id])
        {
            // Tokenize
            $text = self::tokenize(substr($text, 1));

            // Invalid
            if (empty($text))
            {
                return;
            }

            // Lower
            $text[0] = strtolower($text[0]);

            // Alias
            if (isset(self::$aliases[$id][$text[0]]))
            {
                // Recreate token list
                $newText = array();
                $t = '';

                foreach (explode(' ', self::$aliases[$id][$text[0]]) as $t)
                {
                    $newText[] = $t;
                }

                unset($text[0]);

                foreach ($text as $t)
                {
                    $newText[] = $t;
                }

                // Assign new token list
                $text = $newText;

                // Clean up
                unset($newText, $t);
            }

            // Custom
            if (isset(self::$custom[$id][$text[0]]))
            {
                Server::get()->message(self::$custom[$id][$text[0]], $player, 'custom');
                return;
            }

            // Commands list
            if ($text[0] == 'commands')
            {
                // Create list
                $cmdList = '';

                foreach (self::$commands as $info)
                {
                    // Check access
                    if (!isset(self::$disabled[$id][$info['name']]) AND (!$info['flag'] OR $player->hasFlag($info['flag'])))
                    {
                        $cmdList .= '!'.$info['name'].', ';
                    }
                }

                foreach (array_keys(self::$custom[$id]) as $v)
                {
                    $cmdList .= '!'.$v.', ';
                }

                // Reload
                if ($player->hasFlag('root'))
                {
                    $cmdList .= '!reload, ';
                }

                // Send message
                Server::get()->message(__('Available commands: :cmd', array(':cmd' => $cmdList.'!commands')), $player->id);

                // We're done here
                return;
            }

            // Reload
            if ($text[0] == 'reload')
            {
                // Permissions
                if (!$player->hasFlag('root'))
                {
                    return;
                }

                // Reload config
                Config::reload('server-'.$id);

                // Done
                return;
            }

            // Invalid command
            if (!isset(self::$commands[$text[0]]))
            {
                self::checkBadwords($player, $text);
                VoteManager::onVote($player, array('vote', $text[0]), TRUE);
                return;
            }

            // Disabled
            if (isset(self::$disabled[$id][$text[0]]))
            {
                return;
            }

            // Correct chat type?
            if (self::$commands[$text[0]]['type'] == self::TEAM_SAY)
            {
                return;
            }

            // Call home
            call_user_func(self::$commands[$text[0]]['callback'], $player, $text);
        }
        else
        {
            self::checkBadwords($player, $text);
        }
    }

    /**
     * [Event handler] On team chat
     *
     * @param Player $player
     * @param string $text
     */
    public static function onTeamChat(Player $player, $text)
    {
        // Ignore democlient / world chat (that's impossible anyway)
        if ($player instanceof DemoclientPlayer OR $player instanceof WorldPlayer)
        {
            return;
        }

        // Server ID
        $id = Server::get('id');

        // Load config
        if (!isset(self::$commandCharacter[$id]))
        {
            // Character
            self::$commandCharacter[$id] = Config::get('commands.character', '!');

            // Aliases
            self::$aliases[$id] = array();

            foreach (Config::get('commands.aliases', array()) as $k => $v)
            {
                self::$aliases[$id][$k] = $v;
            }

            // Disabled
            self::$disabled[$id] = array();

            foreach (Config::get('commands.disabled', array()) as $v)
            {
                self::$disabled[$id][$v] = TRUE;
            }

            // Badwords
            self::$badwords[$id] = array();

            foreach (Config::get('commands.badwords', array()) as $v)
            {
                self::$badwords[$id][$v] = TRUE;
            }

            // Custom commands
            self::$custom[$id] = array();

            foreach (Config::get('commands.custom', array()) as $k => $v)
            {
                self::$custom[$id][$k] = $v;
            }
        }

        // Command
        if (substr($text, 0, 1) == self::$commandCharacter[$id])
        {
            // Tokenize
            $text = self::tokenize(substr($text, 1));

            // Invalid
            if (empty($text))
            {
                return;
            }

            // Lower
            $text[0] = strtolower($text[0]);

            // Alias
            if (isset(self::$aliases[$id][$text[0]]))
            {
                // Recreate token list
                $newText = array();
                $t = '';

                foreach (explode(' ', self::$aliases[$id][$text[0]]) as $t)
                {
                    $newText[] = $t;
                }

                unset($text[0]);

                foreach ($text as $t)
                {
                    $newText[] = $t;
                }

                // Assign new token list
                $text = $newText;

                // Clean up
                unset($newText, $t);
            }

            // Custom
            if (isset(self::$custom[$id][$text[0]]))
            {
                Server::get()->message(self::$custom[$id][$text[0]], $player, 'custom');
                return;
            }

            // Commands list
            if ($text[0] == 'commands')
            {
                // Create list
                $cmdList = '';

                foreach (self::$commands as $info)
                {
                    // Check access
                    if (!isset(self::$disabled[$id][$info['name']]) AND (!$info['flag'] OR $player->hasFlag($info['flag'])))
                    {
                        $cmdList .= '!'.$info['name'].', ';
                    }
                }

                foreach (array_keys(self::$custom[$id]) as $v)
                {
                    $cmdList .= '!'.$v.', ';
                }

                // Reload
                if ($player->hasFlag('root'))
                {
                    $cmdList .= '!reload, ';
                }

                // Send message
                Server::get()->message(__('Available commands: :cmd', array(':cmd' => $cmdList.'!commands')), $player->id);

                // We're done here
                return;
            }

            // Reload
            if ($text[0] == 'reload')
            {
                // Permissions
                if (!$player->hasFlag('root'))
                {
                    return;
                }

                // Reload config
                Config::reload('server-'.$id);

                // Done
                return;
            }

            // Invalid command
            if (!isset(self::$commands[$text[0]]))
            {
                self::checkBadwords($player, $text);
                VoteManager::onVote($player, array('vote', $text[0]), TRUE);
                return;
            }

            // Disabled
            if (isset(self::$disabled[$id][$text[0]]))
            {
                return;
            }

            // Correct chat type?
            if (self::$commands[$text[0]]['type'] == self::GLOBAL_SAY)
            {
                return;
            }

            // Call home
            call_user_func(self::$commands[$text[0]]['callback'], $player, $text);
        }
        else
        {
            self::checkBadwords($player, $text);
        }
    }

    /**
     * Reload config
     */
    public static function reloadConfig()
    {
        // ID
        $id = Server::get('id');

        // Character
        self::$commandCharacter[$id] = Config::get('commands.character', '!');

        // Aliases
        self::$aliases[$id] = array();

        foreach (Config::get('commands.aliases', array()) as $k => $v)
        {
            self::$aliases[$id][$k] = $v;
        }

        // Disabled
        self::$disabled[$id] = array();

        foreach (Config::get('commands.disabled', array()) as $v)
        {
            self::$disabled[$id][$v] = TRUE;
        }

        // Badwords
        self::$badwords[$id] = array();

        foreach (Config::get('commands.badwords', array()) as $v)
        {
            self::$badwords[$id][$v] = TRUE;
        }

        // Custom commands
        self::$custom[$id] = array();

        foreach (Config::get('commands.custom', array()) as $k => $v)
        {
            self::$custom[$id][$k] = $v;
        }
    }

    /**
     * Remove command
     *
     * @param string $name
     */
    public static function remove($name)
    {
        if (isset(self::$commands[$name]))
        {
            unset(self::$commands[$name]);
        }
    }

    /**
     * Tokenize
     *
     * @param	string	$string
     * @return	array
     */
    public static function tokenize($string)
    {
        // Trim
        $string = trim($string);

        // Variables
        $tokens   = array();
        $length   = strlen($string);
        $token    = '';
        $inString = FALSE;

        // Empty?
        if ($length < 1)
        {
            return array();
        }

        // Char by char
        for ($i = 0; $i < $length; $i++)
        {
            // String
            if ($inString)
            {
                // Add char
                $token .= $string[$i];

                // Close
                if ($string[$i] == "'")
                {
                    // Finish
                    self::tokenizeFinish($token, $tokens);

                    // Cleanup
                    $inString = FALSE;
                    $token = '';
                }

                // Next
                continue;
            }
            elseif ($string[$i] == "'")
            {
                // Add quote
                $token .= "'";

                // Start string
                $inString = TRUE;

                // Next
                continue;
            }

            // Space?
            if ($string[$i] == ' ')
            {
                // End token
                if (!empty($token))
                {
                    // Finish
                    self::tokenizeFinish($token, $tokens);

                    // Cleanup
                    $token = '';
                }

                // Next
                continue;
            }

            // Add char
            $token .= $string[$i];
        }

        // One more token?
        if (!empty($token))
        {
            self::tokenizeFinish($token, $tokens);
        }

        // Return
        return $tokens;
    }

    /**
     * Validate token and add to array
     *
     * @param	string	$tok
     * @param	array	$tokens
     * @return	mixed
     */
    private static function tokenizeFinish($tok, &$tokens)
    {
        // Trim
        $tok = trim($tok);

        // Literal
        if (isset($tok[0]) AND $tok[0] == "'")
        {
            // Length
            $length = strlen($tok);

            // At least two chars
            if ($length < 2)
            {
                return;
            }

            // Valid ending
            if ($tok[$length - 1] == "'")
            {
                // Empty string
                if ($length == 2)
                {
                    $tokens[] = '';
                }
                else
                {
                    $tokens[] = substr($tok, 1, ($length - 2));
                }
            }

            // Done
            return;
        }

        // Int
        if (ctype_digit($tok))
        {
            $tokens[] = (int) $tok;
            return;
        }

        // Float
        if (is_numeric($tok))
        {
            $tokens[] = (float) $tok;
            return;
        }

        // Symbol
        $tokens[] = $tok;
    }
}