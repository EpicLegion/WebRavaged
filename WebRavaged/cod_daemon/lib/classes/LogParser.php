<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Log parser
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

class LogParser
{
    /**
     * @var array
     */
    protected $timestamps = array();

    /**
     * Constructor
     * 
     * @param array $timestamps
     */
    public function __construct(array $timestamps = array())
    {
        $this->timestamps = $timestamps;
    }
    
    /**
     * Get timestamps to cache
     *
     * @return array
     */
    public function getTimestamps()
    {
        return $this->timestamps;
    }
    
    /**
     * Parse line
     *
     * @param string $logLine
     */
    protected function parseLine(&$logLine)
    {
        // Remove spaces
        $logLine = trim($logLine);
		
        // Skip empty lines
        if (empty($logLine) OR $logLine == '------------------------------------------------------------' OR $logLine == 'ShutdownGame:')
        {
            return;
        }

        // Exit level
        if ($logLine == 'ExitLevel: executed')
        {
            Event::fireEvent('onExitLevel');
            return;
        }

        // Init game
        if (substr($logLine, 0, 9) == 'InitGame:')
        {
            // @todo Implement
            Event::fireEvent('onInitGame');
            return;
        }

        // Split
        $logLine = explode(';', $logLine);

        // Type
        switch ($logLine[0])
        {
            // Damage
            case 'D':
                // Valid
                if (count($logLine) != 13)
                {
                    break;
                }

                // Event ( Victim, Attacker, Weapon, Damage )
                Event::fireEvent('onDamage', array(
                    PlayerManager::readPlayer((int) $logLine[1], (int) $logLine[2], $logLine[4], $logLine[3]),
                    PlayerManager::readPlayer((int) $logLine[5], (int) $logLine[6], $logLine[8], $logLine[7]),
                    new Weapon($logLine[9]),
                    new Damage((int) $logLine[10], $logLine[11], $logLine[12])
                ));

                break;

            // Vehicle damage
            case 'VD':
                // Valid
                if (count($logLine) != 11)
                {
                    break;
                }

                // Event ( Victim, Attacker, Weapon, Damage )
                Event::fireEvent('onVehicleDamage', array(
                    PlayerManager::readPlayer(-1, (int) $logLine[1], $logLine[2]),
                    PlayerManager::readPlayer((int) $logLine[3], (int) $logLine[4], $logLine[6], $logLine[5]),
                    new Weapon($logLine[7]),
                    new Damage((int) $logLine[8], $logLine[9], $logLine[10])
                ));

                break;

            // Kill
            case 'K':
                // Valid
                if (count($logLine) != 13)
                {
                    break;
                }

                // Event ( Victim, Attacker, Weapon, Damage )
                Event::fireEvent('onKill', array(
                    PlayerManager::readPlayer((int) $logLine[1], (int) $logLine[2], $logLine[4], $logLine[3]),
                    PlayerManager::readPlayer((int) $logLine[5], (int) $logLine[6], $logLine[8], $logLine[7]),
                    new Weapon($logLine[9]),
                    new Damage((int) $logLine[10], $logLine[11], $logLine[12])
                ));

                break;

            // Weapon
            case 'Weapon':
                // Valid
                if (count($logLine) != 5)
                {
                    break;
                }

                // Event ( Player, Weapon )
                Event::fireEvent('onWeapon', array(
                    PlayerManager::readPlayer((int) $logLine[1], (int) $logLine[2], $logLine[3]),
                    new Weapon($logLine[4])
                ));

                break;

            // Join
            case 'J':
                // Valid
                if (count($logLine) != 4)
                {
                    break;
                }
                
                // Do ACL check
                if (!PlayerManager::aclCheck((int) $logLine[1], (int) $logLine[2]))
                {
                    break;
                }

                // Event ( Player )
                Event::fireEvent('onJoin', array(
                    PlayerManager::readPlayer((int) $logLine[1], (int) $logLine[2], $logLine[3]),
                ));

                break;

            // Quit
            case 'Q':
                // Valid
                if (count($logLine) != 4)
                {
                    break;
                }

                // Event ( Player )
                Event::fireEvent('onQuit', array(
                    PlayerManager::readPlayer((int) $logLine[1], (int) $logLine[2], $logLine[3]),
                ));

                break;

            // Say
            case 'say':
                // Valid
                if (count($logLine) != 5)
                {
                    break;
                }

                // Event
                Event::fireEvent('onChat', array(
                    PlayerManager::readPlayer((int) $logLine[1], (int) $logLine[2], $logLine[3]),
                    str_replace("\x15", '', $logLine[4])
                ));

                break;

            // Say team
            case 'sayteam':
                // Valid
                if (count($logLine) != 5)
                {
                    break;
                }

                // Event
                Event::fireEvent('onTeamChat', array(
                    PlayerManager::readPlayer((int) $logLine[1], (int) $logLine[2], $logLine[3]),
                    str_replace("\x15", '', $logLine[4])
                ));

                break;

            // Unknown
            default:
                cli_print('Unknown log: '.$logLine[0]);
        }
    }

    /**
     * Parse log file
     *
     * @param string $logContents
     */
    public function parseLogFile(&$logContents)
    {
        // Lines
        $logContents = explode("\n", $logContents);
        $linesCount  = count($logContents);
		
        // At least 2 lines
        if ($linesCount < 3)
        {
            return;
        }
        
        $linesCount--;
        
        // Validate first line
        if (!preg_match('/[0-9]{10} .+/i', ltrim($logContents[0])))
        {
            // Remove first
            unset($logContents[0]);
        }
        
        // Validate last line
        if (!preg_match('/[0-9]{10} .+/i', ltrim($logContents[$linesCount])))
        {
            // Remove last
            unset($logContents[$linesCount]);
            $linesCount--;
        }
        
        // Get next timestamp
        $timestamp = isset($this->timestamps[Server::get('id')]) ? $this->timestamps[Server::get('id')] : NULL;
        $pastCheck = ($timestamp === NULL);
        $pastLine = FALSE;
        
        // Get last timestamp in file
        $lastTimestamp = explode(' ', $logContents[$linesCount], 2);
        $lastTimestamp = (int) $lastTimestamp[0];
        $parsedLines = 0;
        $parsedWithLastTimeStamp = 0;
        
        // Skip first chunk
        if ($pastCheck)
        {
            // Well it's not supposed to be 0
            if ($lastTimestamp)
            {
                $this->timestamps[Server::get('id')] = array('timestamp' => $lastTimestamp, 'lines' => 0);
            }
            
            return;
        }
        
        // Fix skip count
        if ($timestamp['timestamp'] == $lastTimestamp)
        {
            $parsedWithLastTimeStamp = $timestamp['lines'];
        }
        
        // Iterate lines
        foreach ($logContents as $line)
        {
            // Explode
            $line = explode(' ', trim($line), 2);

            // To timestamp
            $line[0] = (int) $line[0];
            
            // Skip
            if (!$pastCheck)
            {
                if ($line[0] < $timestamp['timestamp'])
                {
                    continue;
                }
                elseif ($line[0] == $timestamp['timestamp'])
                {
                    $pastLine = TRUE;
                    
                    if ($timestamp['lines'] > 0)
                    {
                        $timestamp['lines']--;
                        continue;
                    }
                    else
                    {
                        $pastCheck = TRUE;
                    }
                }
                elseif($pastLine)
                {
                    $pastCheck = TRUE;
                }
                else
                {
                    continue;
                }
            }
            
            // Last timestamp
            if ($lastTimestamp == $line[0])
            {
                $parsedWithLastTimeStamp++;
            }

            // Parse line
            $this->parseLine($line[1]);

            // Parsed
            $parsedLines++;
        }

        // Update timestamp
        if ($parsedLines > 0)
        {
            cli_print('Parsed lines: '.$parsedLines);
            
            $this->timestamps[Server::get('id')] = array('timestamp' => $lastTimestamp, 'lines' => $parsedWithLastTimeStamp);
        }
    }
}