<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Representation of weapon in Black Ops
 *
 * Weapons and attachments copied from post by TracerNZ
 * http://forums.gameservers.com/viewtopic.php?f=11&t=43178&start=159
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

class Weapon
{
    /**
     * @var int
     */
    public $attachments = 0;

    /**
     * @var int
     */
    public $killedBy = 0;

    /**
     * @var int
     */
    public $weapon = 0;

    // [consts] Killed by
    const KILLED_BY_UNKNOWN = 0;
    const KILLED_BY_WEAPON = 1;
    const KILLED_BY_FLAMETHROWER = 2;
    const KILLED_BY_NOOBTUBE = 3;
    const KILLED_BY_MASTERKEY = 4;

    // [consts] Attachments
    const ATTACHMENT_UNKNOWN = 0;
    const ATTACHMENT_ACOG = 1;
    const ATTACHMENT_REFLEX = 2;
    const ATTACHMENT_DRUM_MAG = 4;
    const ATTACHMENT_DUAL_MAG = 8;
    const ATTACHMENT_RED_DOT = 16;
    const ATTACHMENT_EXT_MAG = 32;
    const ATTACHMENT_FLAMETHROWER = 64;
    const ATTACHMENT_NOOBTUBE = 128;
    const ATTACHMENT_GRIP = 256;
    const ATTACHMENT_INFRARED = 512;
    const ATTACHMENT_UPGRADED_IRON = 1024;
    const ATTACHMENT_LOW_POWER_SCOPE = 2048;
    const ATTACHMENT_MASTERKEY = 4096;
    const ATTACHMENT_SPEED_RELOADING = 8192;
    const ATTACHMENT_RAPID_FIRE = 16384;
    const ATTACHMENT_SILENCER = 32768;
    const ATTACHMENT_SNUB_NOSE = 65536;
    const ATTACHMENT_VARIABLE_ZOOM = 131072;
    const ATTACHMENT_DUAL_WIELD = 262144;
    const ATTACHMENT_FULL_AUTO = 524288;

    // [consts] Weapons
    const WEAPON_UNKNOWN = 0;
    const WEAPON_AK47 = 1;
    const WEAPON_AK47U = 2;
    const WEAPON_ASP = 3;
    const WEAPON_AUG = 4;
    const WEAPON_BALLISTIC_KNIFE = 5;
    const WEAPON_CHINA_LAKE = 6;
    const WEAPON_M1911 = 7;
    const WEAPON_COMMANDO = 8;
    const WEAPON_CROSSBOW_EXPLOSIVE = 9;
    const WEAPON_CZ75 = 10;
    const WEAPON_DRAGUNOV = 11;
    const WEAPON_ENFIELD = 12;
    const WEAPON_FAMAS = 13;
    const WEAPON_G11 = 14;
    const WEAPON_FNFAL = 15;
    const WEAPON_GALIL = 16;
    const WEAPON_HK21 = 17;
    const WEAPON_HS10 = 18;
    const WEAPON_STAKEOUT = 19;
    const WEAPON_KIPARIS = 20;
    const WEAPON_KNIFE = 21;
    const WEAPON_L96A1 = 22;
    const WEAPON_M14 = 23;
    const WEAPON_M16 = 24;
    const WEAPON_M60 = 25;
    const WEAPON_M72_LAW = 26;
    const WEAPON_MAC11 = 27;
    const WEAPON_MAKAROV = 28;
    const WEAPON_MP5K = 29;
    const WEAPON_MPL = 30;
    const WEAPON_PM63 = 31;
    const WEAPON_PSG1 = 32;
    const WEAPON_PYTHON = 33;
    const WEAPON_OLYMPIA = 34;
    const WEAPON_RPG = 35;
    const WEAPON_RPK = 36;
    const WEAPON_SCORPION = 37;
    const WEAPON_SPAS12 = 38;
    const WEAPON_SPECTRE = 39;
    const WEAPON_STONER63 = 40;
    const WEAPON_STRELA = 41;
    const WEAPON_UZI = 42;
    const WEAPON_WA2000 = 43;
    const WEAPON_CONCUSSION_GRENADE = 44;
    const WEAPON_FLASH_GRENADE = 45;
    const WEAPON_FRAG_GRENADE = 46;
    const WEAPON_SEMTEX = 47;
    const WEAPON_NOVA_GAS = 48;
    const WEAPON_WILLY_PETE = 49;
    const WEAPON_ROLLING_THUNDER = 50;
    const WEAPON_SENTRY_GUN = 51;
    const WEAPON_ATTACK_HELI = 52;
    const WEAPON_DOG_BITE = 53;
    const WEAPON_GUNSHIP_MINIGUN = 54;
    const WEAPON_CHOPPER_GUNNER = 55;
    const WEAPON_VALKYRIE_ROCKETS = 56;
    const WEAPON_MORTAR = 57;
    const WEAPON_NAPALM = 58;
    const WEAPON_RCXD = 59;
    const WEAPON_CLAYMORE = 60;
    const WEAPON_C4 = 61;
    const WEAPON_TOMAHAWK = 62;
    const WEAPON_EXPLOSIVE_BOLT = 63;
    const WEAPON_EXPLODABLE_BARREL = 64;
    const WEAPON_GUNSHIP_ROCKETS = 65;
    const WEAPON_DEATH_MACHINE = 66;
    const WEAPON_GRIM_REAPER = 67;
    const WEAPON_CAR_EXPLOSION = 68;
    const WEAPON_DECOY = 69;

    /**
     * @var array
     */
    public static $gameToWeapon = array(
        'ak47' => self::WEAPON_AK47,
        'ak47u' => self::WEAPON_AK47U,
        'asp' => self::WEAPON_ASP,
        'aug' => self::WEAPON_AUG,
        'knife_ballistic' => self::WEAPON_BALLISTIC_KNIFE,
        'china_lake' => self::WEAPON_CHINA_LAKE,
        'm1911' => self::WEAPON_M1911,
        'commando' => self::WEAPON_COMMANDO,
        'crossbow_explosive' => self::WEAPON_CROSSBOW_EXPLOSIVE,
        'cz75' => self::WEAPON_CZ75,
        'dragunov' => self::WEAPON_DRAGUNOV,
        'enfield' => self::WEAPON_ENFIELD,
        'famas' => self::WEAPON_FAMAS,
        'gg11' => self::WEAPON_G11,
        'fnfal' => self::WEAPON_FNFAL,
        'galil' => self::WEAPON_GALIL,
        'hk21' => self::WEAPON_HK21,
        'hs10' => self::WEAPON_HS10,
        'ithaca' => self::WEAPON_STAKEOUT,
        'kiparis' => self::WEAPON_KIPARIS,
        'knife' => self::WEAPON_KNIFE,
        'l96a1' => self::WEAPON_L96A1,
        'm14' => self::WEAPON_M14,
        'm16' => self::WEAPON_M16,
        'm60' => self::WEAPON_M60,
        'm72_law' => self::WEAPON_M72_LAW,
        'mac11' => self::WEAPON_MAC11,
        'makarov' => self::WEAPON_MAKAROV,
        'mp5k' => self::WEAPON_MP5K,
        'mpl' => self::WEAPON_MPL,
        'pm63' => self::WEAPON_PM63,
        'psg1' => self::WEAPON_PSG1,
        'python' => self::WEAPON_PYTHON,
        'rottweil72' => self::WEAPON_OLYMPIA,
        'rpg' => self::WEAPON_RPG,
        'rpk' => self::WEAPON_RPK,
        'skorpion' => self::WEAPON_SCORPION,
        'spas' => self::WEAPON_SPAS12,
        'spectre' => self::WEAPON_SPECTRE,
        'stoner63' => self::WEAPON_STONER63,
        'strela' => self::WEAPON_STRELA,
        'uzi' => self::WEAPON_UZI,
        'wa2000' => self::WEAPON_WA2000,
        'concussion_grenade' => self::WEAPON_CONCUSSION_GRENADE,
        'flash_grenade' => self::WEAPON_FLASH_GRENADE,
        'frag_grenade' => self::WEAPON_FRAG_GRENADE,
        'sticky_grenade' => self::WEAPON_SEMTEX,
        'tabun_gas' => self::WEAPON_NOVA_GAS,
        'willy_pete' => self::WEAPON_WILLY_PETE,
        'airstrike' => self::WEAPON_ROLLING_THUNDER,
        'auto_gun_turret' => self::WEAPON_SENTRY_GUN,
        'cobra_20mm_comlink' => self::WEAPON_ATTACK_HELI,
        'dog_bite' => self::WEAPON_DOG_BITE,
        'hind_minigun_pilot_firstperson' => self::WEAPON_GUNSHIP_MINIGUN,
        'huey_minigun_gunner' => self::WEAPON_CHOPPER_GUNNER,
        'm220_tow' => self::WEAPON_VALKYRIE_ROCKETS,
        'mortar' => self::WEAPON_MORTAR,
        'napalm' => self::WEAPON_NAPALM,
        'rcbomb' => self::WEAPON_RCXD,
        'claymore' => self::WEAPON_CLAYMORE,
        'satchel_charge' => self::WEAPON_C4,
        'hatchet' => self::WEAPON_TOMAHAWK,
        'explosive_bolt' => self::WEAPON_EXPLOSIVE_BOLT,
        'explodable_barrel' => self::WEAPON_EXPLODABLE_BARREL,
        'hind_rockets_firstperson' => self::WEAPON_GUNSHIP_ROCKETS,
        'minigun_mp' => self::WEAPON_DEATH_MACHINE,
        'm202_flash' => self::WEAPON_GRIM_REAPER,
        'destructible_car' => self::WEAPON_CAR_EXPLOSION,
        'nightingale' => self::WEAPON_DECOY
    );

    /**
     * @var array
     */
    public static $weaponToName = array(
        self::WEAPON_UNKNOWN => 'Unknown',
        self::WEAPON_AK47 => 'Ak47',
        self::WEAPON_AK47U => 'AK47u',
        self::WEAPON_ASP => 'ASP',
        self::WEAPON_AUG => 'AUG',
        self::WEAPON_BALLISTIC_KNIFE => 'Ballistic knife',
        self::WEAPON_CHINA_LAKE => 'China Lake',
        self::WEAPON_M1911 => 'M1911',
        self::WEAPON_COMMANDO => 'Commando',
        self::WEAPON_CROSSBOW_EXPLOSIVE => 'Crossbow explosive',
        self::WEAPON_CZ75 => 'CZ75',
        self::WEAPON_DRAGUNOV => 'Dragunov',
        self::WEAPON_ENFIELD => 'Enfield',
        self::WEAPON_FAMAS => 'Famas',
        self::WEAPON_G11 => 'G11',
        self::WEAPON_FNFAL => 'Fnfal',
        self::WEAPON_GALIL => 'Galil',
        self::WEAPON_HK21 => 'HK21',
        self::WEAPON_HS10 => 'HS10',
        self::WEAPON_STAKEOUT => 'Stakeout',
        self::WEAPON_KIPARIS => 'Kiparis',
        self::WEAPON_KNIFE => 'Knife',
        self::WEAPON_L96A1 => 'L96A1',
        self::WEAPON_M14 => 'M14',
        self::WEAPON_M16 => 'M16',
        self::WEAPON_M60 => 'M60',
        self::WEAPON_M72_LAW => 'M72 LAW',
        self::WEAPON_MAC11 => 'MAC11',
        self::WEAPON_MAKAROV => 'Makarov',
        self::WEAPON_MP5K => 'MP5K',
        self::WEAPON_MPL => 'MPL',
        self::WEAPON_PM63 => 'PM63',
        self::WEAPON_PSG1 => 'PSG1',
        self::WEAPON_PYTHON => 'Python',
        self::WEAPON_OLYMPIA => 'Olympia',
        self::WEAPON_RPG => 'RPG',
        self::WEAPON_RPK => 'RPK',
        self::WEAPON_SCORPION => 'Scorpion',
        self::WEAPON_SPAS12 => 'Spas-12',
        self::WEAPON_SPECTRE => 'Spectre',
        self::WEAPON_STONER63 => 'Stoner 63',
        self::WEAPON_STRELA => 'Strela',
        self::WEAPON_UZI => 'Uzi',
        self::WEAPON_WA2000 => 'WA2000',
        self::WEAPON_CONCUSSION_GRENADE => 'Concussion grenade',
        self::WEAPON_FLASH_GRENADE => 'Flash grenade',
        self::WEAPON_FRAG_GRENADE => 'Frag grenade',
        self::WEAPON_SEMTEX => 'Semtex',
        self::WEAPON_NOVA_GAS => 'Nova gas',
        self::WEAPON_WILLY_PETE => 'Willy pete',
        self::WEAPON_ROLLING_THUNDER => 'Rolling thunder',
        self::WEAPON_SENTRY_GUN => 'Sentry gun',
        self::WEAPON_ATTACK_HELI => 'Attack heli',
        self::WEAPON_DOG_BITE => 'Dog bite',
        self::WEAPON_GUNSHIP_MINIGUN => 'Gunship minigun',
        self::WEAPON_CHOPPER_GUNNER => 'Chopper gunner',
        self::WEAPON_VALKYRIE_ROCKETS => 'Valkyrie rockets',
        self::WEAPON_MORTAR => 'Mortar',
        self::WEAPON_NAPALM => 'Napalm',
        self::WEAPON_RCXD => 'RC-XD',
        self::WEAPON_CLAYMORE => 'Claymore',
        self::WEAPON_C4 => 'C4',
        self::WEAPON_TOMAHAWK => 'Tomahawk',
        self::WEAPON_EXPLOSIVE_BOLT => 'Explosive bolt',
        self::WEAPON_EXPLODABLE_BARREL => 'Explodable barrel',
        self::WEAPON_GUNSHIP_ROCKETS => 'Gunship rockets',
        self::WEAPON_DEATH_MACHINE => 'Death machine',
        self::WEAPON_GRIM_REAPER => 'Grim reaper',
        self::WEAPON_CAR_EXPLOSION => 'Car explosion',
        self::WEAPON_DECOY => 'Decoy'
    );

    /**
     * @var array
     */
    public static $gameToAttachment = array(
        'acog' => self::ATTACHMENT_ACOG,
        'reflex' => self::ATTACHMENT_REFLEX,
        'drum' => self::ATTACHMENT_DRUM_MAG,
        'dualclip' => self::ATTACHMENT_DUAL_MAG,
        'elbit' => self::ATTACHMENT_RED_DOT,
        'extclip' => self::ATTACHMENT_EXT_MAG,
        'ft' => self::ATTACHMENT_FLAMETHROWER,
        'gl' => self::ATTACHMENT_NOOBTUBE,
        'grip' => self::ATTACHMENT_GRIP,
        'ir' => self::ATTACHMENT_INFRARED,
        'upgradesight' => self::ATTACHMENT_UPGRADED_IRON,
        'lps' => self::ATTACHMENT_LOW_POWER_SCOPE,
        'mk' => self::ATTACHMENT_MASTERKEY,
        'speed' => self::ATTACHMENT_SPEED_RELOADING,
        'rf' => self::ATTACHMENT_RAPID_FIRE,
        'silencer' => self::ATTACHMENT_SILENCER,
        'snub' => self::ATTACHMENT_SNUB_NOSE,
        'vzoom' => self::ATTACHMENT_VARIABLE_ZOOM,
        'dw' => self::ATTACHMENT_DUAL_WIELD,
        'auto' => self::ATTACHMENT_FULL_AUTO
    );

    /**
     * @var array
     */
    static $attachmentToName = array(
        self::ATTACHMENT_ACOG => 'Acog',
        self::ATTACHMENT_REFLEX => 'Reflex',
        self::ATTACHMENT_DRUM_MAG => 'Drum mag',
        self::ATTACHMENT_DUAL_MAG => 'Dual mag',
        self::ATTACHMENT_RED_DOT => 'Red dot',
        self::ATTACHMENT_EXT_MAG => 'Ext mag',
        self::ATTACHMENT_FLAMETHROWER => 'Flamethrower',
        self::ATTACHMENT_NOOBTUBE => 'Noobtube',
        self::ATTACHMENT_GRIP => 'Grip',
        self::ATTACHMENT_INFRARED => 'Infrared',
        self::ATTACHMENT_UPGRADED_IRON => 'Upgraded iron',
        self::ATTACHMENT_LOW_POWER_SCOPE => 'Low power scope',
        self::ATTACHMENT_MASTERKEY => 'Masterkey',
        self::ATTACHMENT_SPEED_RELOADING => 'Speed reloading',
        self::ATTACHMENT_RAPID_FIRE => 'Rapid fire',
        self::ATTACHMENT_SILENCER => 'Silencer',
        self::ATTACHMENT_SNUB_NOSE => 'Snub nose',
        self::ATTACHMENT_VARIABLE_ZOOM => 'Variable zoom',
        self::ATTACHMENT_DUAL_WIELD => 'Dual wield',
        self::ATTACHMENT_FULL_AUTO => 'Full auto',
        self::ATTACHMENT_UNKNOWN => 'Unknown'
    );

    /**
     * Construct weapon object
     *
     * @param string $weaponName
     */
    public function __construct($weaponName = '')
    {
        // Valid
        if (!$weaponName)
        {
            return;
        }
        
        // Quick weapon check
        if (( $weapon = self::gameToWeapon( substr($weaponName, 0, (strlen($weaponName) - 3)) ) )) // Removed last 3 characters (_mp)
        {
            $this->killedBy = self::KILLED_BY_WEAPON;
            $this->weapon = $weapon;
            return;
        }

        // Valid?
        if (!strstr($weaponName, '_'))
        {
            return;
        }

        // Split
        $weaponName = explode('_', $weaponName);

        // Iterate
        for ($i = 0, $count = count($weaponName); $i < $count; $i++)
        {
            // Ignore
            if ($weaponName[$i] == 'mp')
            {
                continue;
            }

            // First item
            if ($i == 0)
            {
                // Attachments
                if ($weaponName[$i] == 'mk')
                {
                    $this->killedBy = self::KILLED_BY_MASTERKEY;
                    $this->attachments |= self::ATTACHMENT_MASTERKEY;
                }
                elseif ($weaponName[$i] == 'ft')
                {
                    $this->killedBy = self::KILLED_BY_FLAMETHROWER;
                    $this->attachments |= self::ATTACHMENT_FLAMETHROWER;
                }
                elseif ($weaponName[$i] == 'gl')
                {
                    $this->killedBy = self::KILLED_BY_NOOBTUBE;
                    $this->attachments |= self::ATTACHMENT_NOOBTUBE;
                }
                elseif (($weapon = self::gameToWeapon($weaponName[$i])))
                {
                    $this->killedBy = self::KILLED_BY_WEAPON;
                    $this->weapon = $weapon;
                }
                else
                {
                    $this->killedBy = self::KILLED_BY_UNKNOWN;
                }

                // Next
                continue;
            }

            // Attachment
            if (($attachment = self::gameToAttachment($weaponName[$i])))
            {
                $this->attachments |= $attachment;
                continue;
            }

            // Weapon
            if (($weapon = self::gameToWeapon($weaponName[$i])))
            {
                $this->weapon = $weapon;
            }
        }
    }

    /**
     * Convert game string to attachment
     *
     * @param string $attach
     */
    public static function gameToAttachment($attach)
    {
        return isset(self::$gameToAttachment[$attach]) ? self::$gameToAttachment[$attach] : self::ATTACHMENT_UNKNOWN;
    }

    /**
     * Convert game string to weapon enum
     *
     * @param string $weapon
     */
    public static function gameToWeapon($weapon)
    {
        return isset(self::$gameToWeapon[$weapon]) ? self::$gameToWeapon[$weapon] : self::WEAPON_UNKNOWN;
    }

    /**
     * Get attachments
     *
     * @return string
     */
    public function getAttachments()
    {
        // Empty
        if (!$this->attachments)
        {
            return array();
        }

        // Attachments
        $attach = array();

        // Iterate
        foreach (self::$attachmentToName as $bit => $name)
        {
            if ($this->attachments & $bit)
            {
                $attach[$bit] = $name;
            }
        }

        // Return
        return $attach;
    }

    /**
     * Get weapon name
     *
     * @return string
     */
    public function getWeapon()
    {
        return __(self::$weaponToName[$this->weapon]);
    }

    /**
     * Weapon has attached xxx?
     *
     * @param  int  $attachment
     * @return bool
     */
    public function hasAttachment($attachment)
    {
        return ($this->attachments & $attachment);
    }
}