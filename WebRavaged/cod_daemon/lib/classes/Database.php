<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Database system
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
 * @subpackage core
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Database
{
    /**
     * Last instance
     *
     * @var Database
     */
    protected static $instance = NULL;

    /**
     * MySQL link
     *
     * @var resource
     */
    protected $link = NULL;

    /**
     * Database prefix
     *
     * @var string
     */
    protected $prefix = '';
    
    /**
     * Constructor
     *
     * @param  array     $config
     * @throws Exception
     */
    public function __construct(array $config = array())
    {
        // Empty config
        if (empty($config))
        {
            throw new Exception('Invalid database configuration. Please check your config/core/database.yml file.');
        }

        // Set prefix
        $this->prefix = $config['prefix'];

        // Define as const
        if(!defined('PREFIX')) define('PREFIX', $config['prefix']);

        // Connect
        $this->link = @mysql_connect($config['hostname'], $config['username'], $config['password'], TRUE);

        // Success?
        if(!$this->link)
        {
            throw new Exception('Cannot connect to database ['.mysql_errno().']: '.mysql_error());
        }

        // Select database
        if(!@mysql_select_db($config['database'], $this->link))
        {
            throw new Exception('Cannot select database ['.mysql_errno().']: '.mysql_error());
        }

        // UTF8
        $this->exec("SET NAMES 'utf8'");
    }

    /**
     * Close connection
     */
    public function close()
    {
        mysql_close($this->link);
        self::$instance = NULL;
    }
    
    /**
     * Execute query and return affected rows
     *
     * @param  string $statement
     * @param  array  $params
     * @return int
     */
    public function exec($statement, array $params = array())
    {
        // Prepare
        $statement = $this->prepareStatement($statement, $params);

        // Query
        $statement = $this->query($statement);

        // Return
        return mysql_affected_rows($this->link);
    }

    /**
     * Get last insert ID
     *
     * @return int
     */
    public function getLastInsertID()
    {
        return mysql_insert_id($this->link);
    }

    /**
     * Get all possible rows
     *
     * @param string $statement
     * @param array  $params
     * @param string $sortByField
     * @param array
     */
    public function getAll($statement, array $params = array(), $sortByField = NULL)
    {
        // Prepare
        $statement = $this->prepareStatement($statement, $params);

        // Query
        $statement = $this->query($statement);

        // Rows
        $rows = array();

        // Fetch all
        while($row = mysql_fetch_assoc($statement))
        {
            // Sort by field?
            if($sortByField AND isset($row[$sortByField]))
            {
                $rows[$row[$sortByField]] = $row;
            }
            else
            {
                $rows[] = $row;
            }
        }

        // Return
        return $rows;
    }

    /**
     * Get a single row
     *
     * @param string     $statement
     * @param array      $params
     * @param bool|array
     */
    public function getSingle($statement, array $params = array())
    {
        // Prepare
        $statement = $this->prepareStatement($statement, $params);

        // Query
        $statement = $this->query($statement);

        // Return
        return mysql_fetch_assoc($statement);
    }

    /**
     * Get/create db instance
     *
     * @param  array    $config
     * @return Database
     */
    public static function instance(array $config = array())
    {
        // Instance
        if (self::$instance === NULL)
        {
            self::$instance = new Database($config);
        }

        // Return
        return self::$instance;
    }
    
    /**
     * Prepare statement
     *
     * @param  string $statement
     * @param  array  $params
     * @return string
     */
    protected function prepareStatement($statement, array $params = array())
    {
        // Prefix
        $statement = str_replace('[prefix]', $this->prefix, $statement);

        // Parameters
        foreach($params as $k => $v)
        {
            // Prepare value
            if($v === NULL)
            {
                $v = 'NULL';
            }
            elseif(is_string($v))
            {
                $v = "'".mysql_real_escape_string($v, $this->link)."'";
            }
            elseif(is_bool($v))
            {
                $v = $v ? 1 : 0;
            }

            // Replace
            $statement = str_replace($k, $v, $statement);
        }

        // Return
        return $statement;
    }

    /**
     * Execute query
     *
     * @param  string    $statement
     * @throws Exception
     * @return mixed
     */
    public function query($statement)
    {
        // Send query
        $statement = @mysql_query($statement, $this->link);

        // Error?
        if(!$statement)
        {
            throw new Exception('MySQL query error ['.mysql_errno($this->link).']: '.mysql_error($this->link));
        }

        // Return result
        return $statement;
    }
}