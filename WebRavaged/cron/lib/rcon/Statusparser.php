<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Teamstatus command parser
 *
 * @author        EpicLegion
 * @package        rcon
 * @subpackage    lib
 */
class Rcon_Statusparser {

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