<?php
class InstallerDatabasePgsql implements InstallerDatabase {
    
    public $link = NULL;
    
    public function connect($host, $user, $password, $database)
    {
        if ($host != 'local socket')
        {
            // Port
            if (strstr($host, ':'))
            {
                $host = explode(':', $host, 2);
                
                $string = "host='".$host[0]."' port='".$host[1]."' ";
            }
            else
            {
                $string = "host='".$host."' ";
            }
        }
        else
        {
            $string = '';
        }
        
        //$string .= "dbname='".$database."' user='".$user."' password='".$password."'";
        $string = "dbname='".$database."' user='".$user."' password='".$password."'";
        
        $this->link = pg_connect($string);
        
        if (!$this->link)
        {
            throw new Exception('Cannot connect to database: '.  pg_last_error());
        }
        
        $this->query("SET NAMES 'utf8'");
    }
    
    public function connectFromConfig($config)
    {
        $this->connect($config['connection']['hostname'], $config['connection']['username'], $config['connection']['password'], $config['connection']['database']);
    }
    
    public function escape($string)
    {
        return pg_escape_string($this->link, $string);
    }
    
    public function fetch($query)
    {
        return pg_fetch_assoc($query);
    }
    
    public function getConfig($host, $user, $password, $database, $prefix)
    {
        if ($host == 'local socket') $host = NULL;
        
        return array
        (
            'default' => array(
                'type'       => 'postgresql',
                'connection' => array
                (
                    'hostname'   => $host,
                    'database'   => $database,
                    'username'   => $user,
                    'password'   => $password,
                    'persistent' => FALSE,
                ),
                'schema'       => '',
                'primary_key'  => '',
                'table_prefix' => $prefix,
                'charset'      => 'utf8',
                'caching'      => FALSE,
                'profiling'    => FALSE,
            )
        );
    }
    
    public function getName()
    {
        return 'pgsql';
    }
    
    public function prepareDatabase($database)
    {
    }
    
    public function query($sql)
    {
        return pg_query($this->link, $sql);
    }
}