<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Representation of damage in Call of Duty
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

class Damage
{
    /**
     * @var int
     */
    public $amount = 0;
    
    /**
     * @var int
     */
    public $location = 0;
    
    /**
     * @var int
     */
    public $type = 0;
    
    // [Consts] Damage types
    const MOD_UNKNOWN = 0;
    const MOD_RIFLE_BULLET = 1;
    const MOD_PISTOL_BULLET = 2;
    const MOD_HEAD_SHOT = 3;
    const MOD_GRENADE_SPLASH = 4;
    const MOD_EXPLOSIVE = 5;
    const MOD_SUICIDE = 6;
    const MOD_MELEE = 7;
    const MOD_BURNED = 8;
    const MOD_PROJECTILE = 9;
    const MOD_PROJECTILE_SPLASH = 10;
    const MOD_FALLING = 11;
    
    // [Consts] Hit locations
    const HIT_UNKNOWN = 0;
    const HIT_HEAD = 1;
    const HIT_NECK = 2;
    const HIT_LEFT_ARM_UPPER = 3;
    const HIT_RIGHT_ARM_UPPER = 4;
    const HIT_LEFT_ARM_LOWER = 5;
    const HIT_RIGHT_ARM_LOWER = 6;
    const HIT_LEFT_HAND = 7;
    const HIT_RIGHT_HAND = 8;
    const HIT_TORSO_UPPER = 9;
    const HIT_TORSO_LOWER = 10;
    const HIT_LEFT_LEG_LOWER = 11;
    const HIT_RIGHT_LEG_LOWER = 12;
    const HIT_LEFT_LEG_UPPER = 13;
    const HIT_RIGHT_LEG_UPPER = 14;
    const HIT_LEFT_FOOT = 15;
    const HIT_RIGHT_FOOT = 16;
    const HIT_NONE = 17; // Explosion?
    
    /**
     * @var array
     */
    static public $gameToLocation = array(
        'head' => self::HIT_HEAD,
        'neck' => self::HIT_NECK,
        'left_arm_upper' => self::HIT_LEFT_ARM_UPPER,
        'right_arm_upper' => self::HIT_RIGHT_ARM_UPPER,
        'left_arm_lower' => self::HIT_LEFT_ARM_LOWER,
        'right_arm_lower' => self::HIT_RIGHT_ARM_LOWER,
        'left_hand' => self::HIT_LEFT_HAND,
        'right_hand' => self::HIT_RIGHT_HAND,
        'torso_lower' => self::HIT_TORSO_LOWER,
        'torso_upper' => self::HIT_TORSO_UPPER,
        'left_leg_lower' => self::HIT_LEFT_LEG_LOWER,
        'right_leg_lower' => self::HIT_RIGHT_LEG_LOWER,
        'left_leg_upper' => self::HIT_LEFT_LEG_UPPER,
        'right_leg_upper' => self::HIT_RIGHT_LEG_UPPER,
        'left_foot' => self::HIT_LEFT_FOOT,
        'right_foot' => self::HIT_RIGHT_FOOT,
        'none' => self::HIT_NONE
    );
    
    /**
     * @var array
     */
    static public $locationToName = array(
        self::HIT_HEAD => 'Head',
        self::HIT_NECK => 'Neck',
        self::HIT_LEFT_ARM_UPPER => 'Upper left arm',
        self::HIT_RIGHT_ARM_UPPER => 'Upper right arm',
        self::HIT_LEFT_ARM_LOWER => 'Lower left arm',
        self::HIT_RIGHT_ARM_LOWER => 'Lower right arm',
        self::HIT_LEFT_HAND => 'Left hand',
        self::HIT_RIGHT_HAND => 'Right hand',
        self::HIT_TORSO_LOWER => 'Lower torso',
        self::HIT_TORSO_UPPER => 'Upper torso',
        self::HIT_LEFT_LEG_LOWER => 'Lower left leg',
        self::HIT_RIGHT_LEG_LOWER => 'Lower right leg',
        self::HIT_LEFT_LEG_UPPER => 'Upper left leg',
        self::HIT_RIGHT_LEG_UPPER => 'Upper right leg',
        self::HIT_LEFT_FOOT => 'Left foot',
        self::HIT_RIGHT_FOOT => 'Right foot',
        self::HIT_NONE => 'Explosion',
        self::HIT_UNKNOWN => 'Unknown'
    );
    
    /**
     * @var array
     */
    static public $gameToType = array(
        'MOD_RIFLE_BULLET' => self::MOD_RIFLE_BULLET,
        'MOD_PISTOL_BULLET' => self::MOD_PISTOL_BULLET,
        'MOD_HEAD_SHOT' => self::MOD_HEAD_SHOT,
        'MOD_GRENADE_SPLASH' => self::MOD_GRENADE_SPLASH,
        'MOD_EXPLOSIVE' => self::MOD_EXPLOSIVE,
        'MOD_SUICIDE' => self::MOD_SUICIDE,
        'MOD_MELEE' => self::MOD_MELEE,
        'MOD_BURNED' => self::MOD_BURNED,
        'MOD_PROJECTILE' => self::MOD_PROJECTILE,
        'MOD_PROJECTILE_SPLASH' => self::MOD_PROJECTILE_SPLASH,
        'MOD_FALLING' => self::MOD_FALLING
    );
    
    /**
     * @var array
     */
    static public $typeToName = array(
        self::MOD_RIFLE_BULLET => 'Rifle bullet',
        self::MOD_PISTOL_BULLET => 'Pistol bullet',
        self::MOD_HEAD_SHOT => 'Headshot',
        self::MOD_GRENADE_SPLASH => 'Grenade splash',
        self::MOD_EXPLOSIVE => 'Explosive',
        self::MOD_SUICIDE => 'Suicide',
        self::MOD_MELEE => 'Knife',
        self::MOD_BURNED => 'Burned',
        self::MOD_PROJECTILE => 'Projectile',
        self::MOD_PROJECTILE_SPLASH => 'Projectile splash',
        self::MOD_UNKNOWN => 'Unknown',
        self::MOD_FALLING => 'Fall'
    );
    
    /**
     * Create new damage object
     * 
     * @param int    $amount
     * @param string $type
     * @param string $location 
     */
    public function __construct($amount, $type, $location)
    {
        // Set amount
        $this->amount = $amount;
        
        // Type
        $this->type = self::gameToType($type);
        
        // Location
        $this->location = self::gameToLocation($location);
    }
    
    /**
     * Convert game string to location enum
     * 
     * @param string $location 
     */
    public static function gameToLocation($location)
    {
        return isset(self::$gameToLocation[$location]) ? self::$gameToLocation[$location] : self::HIT_UNKNOWN;
    }
    
    /**
     * Convert game string to type enum
     * 
     * @param string $location 
     */
    public static function gameToType($type)
    {
        return isset(self::$gameToType[$type]) ? self::$gameToType[$type] : self::MOD_UNKNOWN;
    }
    
    /**
     * Get location name
     * 
     * @return string
     */
    public function getLocation()
    {
        return __(self::$locationToName[$this->location]);
    }
    
    /**
     * Get type name
     * 
     * @return string
     */
    public function getType()
    {
        return __(self::$typeToName[$this->type]);
    }
}