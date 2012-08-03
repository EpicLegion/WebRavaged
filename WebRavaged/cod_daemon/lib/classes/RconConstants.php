<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Rcon constants
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
 * @author     Maximusya
 * @package    cod_daemon
 * @subpackage game
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

final class RconConstants
{
    public static $playlists = array(
                        1 => 'Team Deathmatch',
                        2 => 'Free For All',
                        3 => 'Capture The Flag',
                        4 => 'Search And Destroy',
                        5 => 'Headquarters',
                        6 => 'Domination',
                        7 => 'Sabotage',
                        8 => 'Demolition',
                        9 => 'Hardcore Team Deathmatch',
                        10 => 'Hardcore Free For All',
                        11 => 'Hardcore Capture The Flag',
                        12 => 'Hardcore Search And Destroy',
                        13 => 'Hardcore Headquarters',
                        14 => 'Hardcore Domination',
                        15 => 'Hardcore Sabotage',
                        16 => 'Hardcore Demolition',
                        17 => 'Barebones Team Deathmatch',
                        18 => 'Barebones Free For All',
                        19 => 'Barebones Capture The Flag',
                        20 => 'Barebones Search And Destroy',
                        21 => 'Barebones Headquarters',
                        22 => 'Barebones Domination',
                        23 => 'Barebones Sabotage',
                        24 => 'Barebones Demolition',
                        25 => 'Team Tactical',
                        26 => 'One In The Chamber',
                        27 => 'Sticks And Stones',
                        28 => 'Gun Game',
                        29 => 'Sharpshooter',
                        30 => 'Hardcore Team Tactical',
                        31 => 'Barebones Team Tactical',
                        32 => 'Team Deathmatch 12 players',
                        33 => 'Free For All 12 players',
                        34 => 'Capture The Flag 12 players',
                        35 => 'Search And Destroy 12 players',
                        36 => 'Headquarters 12 players',
                        37 => 'Domination 12 players',
                        38 => 'Sabotage 12 players',
                        39 => 'Demolition 12 players',
                        40 => 'Team Tactical 12 players',
                        41 => 'Hardcore Team Deathmatch 12 players',
                        42 => 'Hardcore Free For All 12 players',
                        43 => 'Hardcore Capture The Flag 12 players',
                        44 => 'Hardcore Search And Destroy 12 players',
                        45 => 'Hardcore Headquarters 12 players',
                        46 => 'Hardcore Domination 12 players',
                        47 => 'Hardcore Sabotage 12 players',
                        48 => 'Hardcore Demolition 12 players',
                        49 => 'Hardcore Team Tactical 12 players',
                        50 => 'Barebones Team Deathmatch 12 players',
                        51 => 'Barebones Free For All 12 players',
                        52 => 'Barebones Capture The Flag 12 players',
                        53 => 'Barebones Search And Destroy 12 players',
                        54 => 'Barebones Headquarters 12 players',
                        55 => 'Barebones Domination 12 players',
                        56 => 'Barebones Sabotage 12 players',
                        57 => 'Barebones Team Tactical 12 players',
    ); // end of playlists

    const pls_Team_Deathmatch = 1;
    const pls_Free_For_All = 2;
    const pls_Capture_The_Flag = 3;
    const pls_Search_And_Destroy = 4;
    const pls_Headquarters = 5;
    const pls_Domination = 6;
    const pls_Sabotage = 7;
    const pls_Demolition = 8;
    const pls_Hardcore_Team_Deathmatch = 9;
    const pls_Hardcore_Free_For_All = 10;
    const pls_Hardcore_Capture_The_Flag = 11;
    const pls_Hardcore_Search_And_Destroy = 12;
    const pls_Hardcore_Headquarters = 13;
    const pls_Hardcore_Domination = 14;
    const pls_Hardcore_Sabotage = 15;
    const pls_Hardcore_Demolition = 16;
    const pls_Barebones_Team_Deathmatch = 17;
    const pls_Barebones_Free_For_All = 18;
    const pls_Barebones_Capture_The_Flag = 19;
    const pls_Barebones_Search_And_Destroy = 20;
    const pls_Barebones_Headquarters = 21;
    const pls_Barebones_Domination = 22;
    const pls_Barebones_Sabotage = 23;
    const pls_Barebones_Demolition = 24;
    const pls_Team_Tactical = 25;
    const pls_One_In_The_Chamber = 26;
    const pls_Sticks_And_Stones = 27;
    const pls_Gun_Game = 28;
    const pls_Sharpshooter = 29;
    const pls_Hardcore_Team_Tactical = 30;
    const pls_Barebones_Team_Tactical = 31;
    const pls_Team_Deathmatch_12_players = 32;
    const pls_Free_For_All_12_players = 33;
    const pls_Capture_The_Flag_12_players = 34;
    const pls_Search_And_Destroy_12_players = 35;
    const pls_Headquarters_12_players = 36;
    const pls_Domination_12_players = 37;
    const pls_Sabotage_12_players = 38;
    const pls_Demolition_12_players = 39;
    const pls_Team_Tactical_12_players = 40;
    const pls_Hardcore_Team_Deathmatch_12_players = 41;
    const pls_Hardcore_Free_For_All_12_players = 42;
    const pls_Hardcore_Capture_The_Flag_12_players = 43;
    const pls_Hardcore_Search_And_Destroy_12_players = 44;
    const pls_Hardcore_Headquarters_12_players = 45;
    const pls_Hardcore_Domination_12_players = 46;
    const pls_Hardcore_Sabotage_12_players = 47;
    const pls_Hardcore_Demolition_12_players = 48;
    const pls_Hardcore_Team_Tactical_12_players = 49;
    const pls_Barebones_Team_Deathmatch_12_players = 50;
    const pls_Barebones_Free_For_All_12_players = 51;
    const pls_Barebones_Capture_The_Flag_12_players = 52;
    const pls_Barebones_Search_And_Destroy_12_players = 53;
    const pls_Barebones_Headquarters_12_players = 54;
    const pls_Barebones_Domination_12_players = 55;
    const pls_Barebones_Sabotage_12_players = 56;
    const pls_Barebones_Team_Tactical_12_players = 57;

    public static function getHardcorePlaylists($return_small = false)
    {
        return self::getPlaylistsByMode(self::gamemode_Hardcore, $return_small);
    }

    public static function getNormalPlaylists($return_small = false)
    {
        return self::getPlaylistsByMode(self::gamemode_Normal, $return_small);
    }

    public static function getWagerPlaylists($return_small = false)
    {
        return self::getPlaylistsByMode(self::gamemode_Wager, $return_small);
    }

    public static function getBarebonesPlaylists($return_small = false)
    {
        return self::getPlaylistsByMode(self::gamemode_Barebones, $return_small);
    }

    /**
     * Return playlists filtered by mode and size
     * @param [optional] string $mode one of (all, wager, normal, hardcore, barebones )
     * @param [optional] bool $small pass TRUE to return 12 players playlists
     * @return array
     */
    public static function getPlaylistsByMode($mode = 'all', $small = false)
    {
        // lets juggle with arrays a bit :D

        if ( $mode === 'all' ) return self::$playlists;
        if ( $mode === 'wager' ) return array_intersect_key(self::$playlists, array_flip(array(26,27,28,29)));
        if ( $mode === 'normal')
        {
            if ( $small ) return array_intersect_key(self::$playlists, array_flip(array(32,33,34,35,36,37,38,39,40)));
            else return array_intersect_key(self::$playlists, array_flip(array(1,2,3,4,5,6,7,8,25)));
        }
        if ( $mode === 'hardcore')
        {
            if ( $small ) return array_intersect_key(self::$playlists, array_flip(array(41,42,43,44,45,46,47,48,49)));
            else return array_intersect_key(self::$playlists, array_flip(array(9,10,11,12,13,14,15,16,30)));
        }
        if ( $mode === 'barebones')
        {
            if ( $small ) return array_intersect_key(self::$playlists, array_flip(array(50,51,52,53,54,55,56,57)));
            else return array_intersect_key(self::$playlists, array_flip(array(17,18,19,20,21,22,23,24,31)));
        }

        //else
        return array();
    }

    public static function getInfo($id)
    {
        $info = array('type' => '', 'mode' => '');
        
        if (in_array($id, array(3,11,19,34,43,52)))
        {
            $info['type'] = self::gametype_CTF;
        }
        elseif (in_array($id, array(8,16,24,39,48)))
        {
            $info['type'] = self::gametype_DEM;
        }
        elseif (in_array($id, array(6,14,22,37,46,55)))
        {
            $info['type'] = self::gametype_DOM;
        }
        elseif (in_array($id, array(2,10,18,33,42,51)))
        {
            $info['type'] = self::gametype_FFA;
        }
        elseif (in_array($id, array(5,13,21,36,45,54)))
        {
            $info['type'] = self::gametype_HQ;
        }
        elseif (in_array($id, array(7,15,23,38,47,56)))
        {
            $info['type'] = self::gametype_SAB;
        }
        elseif (in_array($id, array(4,12,20,35,44,53)))
        {
            $info['type'] = self::gametype_SD;
        }
        elseif (in_array($id, array(1,9,17,32,41,50)))
        {
            $info['type'] = self::gametype_TDM;
        }
        else
        {
            $info['type'] = 'wager';
        }
        
        if (in_array($id, array(1,2,3,4,5,6,7,8,25,32,33,34,35,36,37,38,39,40)))
        {
            $info['mode'] = 'normal';
        }
        elseif (in_array($id, array(9,10,11,12,13,14,15,16,30,41,42,43,44,45,46,47,48,49)))
        {
            $info['mode'] = 'hardcore';
        }
        elseif (in_array($id, array(50,51,52,53,54,55,56,57,17,18,19,20,21,22,23,24,31)))
        {
            $info['mode'] = 'barebones';
        }
        else
        {
            $info['mode'] = 'wager';
        }
     
        return $info;
    }
    
    public static function getPlaylistsByType($gametype)
    {
        if ( !array_key_exists($gametype, self::$gametypes) )
            return false;

        switch ($gametype)
        {
            case self::gametype_CTF : return array_intersect_key(self::$playlists, array_flip(array(3,11,19,34,43,52)));
            case self::gametype_DEM : return array_intersect_key(self::$playlists, array_flip(array(8,16,24,39,48)));
            case self::gametype_DOM : return array_intersect_key(self::$playlists, array_flip(array(6,14,22,37,46,55)));
            case self::gametype_FFA : return array_intersect_key(self::$playlists, array_flip(array(2,10,18,33,42,51)));
            case self::gametype_HQ : return array_intersect_key(self::$playlists, array_flip(array(5,13,21,36,45,54)));
            case self::gametype_SAB : return array_intersect_key(self::$playlists, array_flip(array(7,15,23,38,47,56)));
            case self::gametype_SD : return array_intersect_key(self::$playlists, array_flip(array(4,12,20,35,44,53)));
            case self::gametype_TDM : return array_intersect_key(self::$playlists, array_flip(array(1,9,17,32,41,50)));
            case self::gametype_GUN : return array_intersect_key(self::$playlists, array_flip(array(28)));
            case self::gametype_OIC : return array_intersect_key(self::$playlists, array_flip(array(26)));
            case self::gametype_SAS : return array_intersect_key(self::$playlists, array_flip(array(27)));
            case self::gametype_SHRP : return array_intersect_key(self::$playlists, array_flip(array(29)));
            default: return false;
        }
    }

    public static $gamemodes = array(
        'wager'        => 'Wager Matches',
        'normal'    => 'Normal Matches',
        'hardcore'    => 'Hardcore Matches',
        'barebones'    => 'Barebones Matches'
    );

    const gamemode_Wager  = 'wager';
    const gamemode_Normal = 'normal';
    const gamemode_Hardcore = 'hardcore';
    const gamemode_Barebones = 'barebones';

    /**
     * Derive playlist from $gametype using $mode hint
     * @param $gametype @see Rcon_Constants::$gametypes for possible values
     * @param $mode one of (normal, hardcore, barebones)
     */
    public static function getPlaylistByGametype($gametype, $mode, $small = false)
    {
        if ( !array_key_exists($gametype, self::$gametypes) ) return false;

        $gtgms = array_keys();

        switch ($gametype)
        {
            case 'hlnd': return self::pls_Sticks_And_Stones;
            case 'gun' : return self::pls_Gun_Game;
            case 'shrp': return self::pls_Sharpshooter;
            case 'oic' : return self::pls_One_In_The_Chamber;

            case 'tdm' :
                {

                }
        }
    }

    public static $gametypes = array(
                        'tdm' => 'Team Deathmatch',
                        'dm' => 'Free For All',
                        'sab' => 'Sabotage',
                        'dem' => 'Demolition',
                        'ctf' => 'Capture The Flag',
                        'sd' => 'Search And Destroy',
                        'dom' => 'Domination',
                        'koth' => 'Headquarters',
                        'hlnd' => 'Sticks And Stones',
                        'gun' => 'Gun Game',
                        'shrp' => 'Sharpshooter',
                        'oic' => 'One In The Chamber'
    ); // end of gametypes

    const gametype_TDM = 'tdm';
    const gametype_FFA = 'dm';
    const gametype_SAB = 'sab';
    const gametype_DEM = 'dem';
    const gametype_CTF = 'ctf';
    const gametype_SD = 'sd';
    const gametype_DOM = 'dom';
    const gametype_HQ = 'koth';
    const gametype_SAS = 'hlnd';
    const gametype_GUN = 'gun';
    const gametype_SHRP = 'shrp';
    const gametype_OIC = 'oic';

    public static $gtgms = array(
                        'tdm' => array(
                            array(self::pls_Team_Deathmatch, self::pls_Team_Deathmatch_12_players),
                            array(self::pls_Hardcore_Team_Deathmatch, self::pls_Hardcore_Team_Deathmatch_12_players),
                            array(self::pls_Barebones_Team_Deathmatch, self::pls_Barebones_Team_Deathmatch_12_players)
                        ),
                        'dm' => array(
                            array(self::pls_Free_For_All, self::pls_Free_For_All_12_players),
                            array(self::pls_Hardcore_Free_For_All, self::pls_Hardcore_Free_For_All_12_players),
                            array(self::pls_Barebones_Free_For_All, self::pls_Barebones_Free_For_All_12_players)
                        ),
                        'sab' => array(

                        ),
                        'dem' => 'Demolition',
                        'ctf' => 'Capture The Flag',
                        'sd' => 'Search And Destroy',
                        'dom' => 'Domination',
                        'koth' => 'Headquarters',
                        'hlnd' => 'Sticks And Stones',
                        'gun' => 'Gun Game',
                        'shrp' => 'Sharpshooter',
                        'oic' => 'One In The Chamber'
    );

    public static $maps = array(
                        'mp_array' => 'Array',
                        'mp_cracked' => 'Cracked',
                        'mp_crisis' => 'Crisis',
                        'mp_firingrange' => 'Firing Range',
                        'mp_duga' => 'Grid',
                        'mp_hanoi' => 'Hanoi',
                        'mp_cairo' => 'Havana',
                        'mp_havoc' => 'Jungle',
                        'mp_cosmodrome' => 'Launch',
                        'mp_nuked' => 'Nuketown',
                        'mp_radiation' => 'Radiation',
                        'mp_mountain' => 'Summit',
                        'mp_villa' => 'Villa',
                        'mp_russianbase' => 'WMD',
                        'mp_berlinwall2' => 'Berlin Wall',
                        'mp_stadium' => 'Stadium',
                        'mp_discovery' => 'Discovery',
                        'mp_kowloon' => 'Kowloon',
                        'mp_zoo' => 'Zoo',
                        'mp_gridlock' => 'Gridlock',
                        'mp_hotel2' => 'Hotel2',
                        'mp_outskirts' => 'Outskirts',
                        'mp_golfcourse' => 'Hazard',
                        'mp_area51' => 'Hangar 18',
                        'mp_drivein' => 'Drive in',
                        'mp_silo' => 'Silo'
    ); // end of maps

    public static $colors = array(
                        0 => 'black',
                        1 => 'red',
                        2 => 'green',
                        3 => 'yellow',
                        4 => 'blue',
                        5 => 'cyan',
                        6 => 'magenta',
                        7 => 'white', // default
                        8 => 'grey',
                        9 => 'brown',
    ); // end of colors
}