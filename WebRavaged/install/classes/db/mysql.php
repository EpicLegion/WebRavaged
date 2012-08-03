<?php
class InstallerDatabaseMysql implements InstallerDatabase {

    public $link = NULL;

    public function connect($host, $user, $password, $database)
    {
        $this->link = @mysql_connect($host, $user, $password);

        if (!$this->link)
        {
            throw new Exception('MySQL error: '.mysql_error());
        }

        if (!@mysql_select_db($database, $this->link))
        {
            throw new Exception('MySQL error: '.mysql_error());
        }

        $this->query("SET NAMES 'utf8'");
    }

    public function connectFromConfig($config)
    {
        $this->connect($config['connection']['hostname'], $config['connection']['username'], $config['connection']['password'], $config['connection']['database']);
    }

    public function escape($string)
    {
        return mysql_real_escape_string($string, $this->link);
    }

    public function fetch($query)
    {
        return mysql_fetch_assoc($query);
    }

    public function getConfig($host, $user, $password, $database, $prefix)
    {
        return array
        (
            'default' => array(
                'type'       => 'mysql',
                'connection' => array
                (
                    'hostname'   => $host,
                    'database'   => $database,
                    'username'   => $user,
                    'password'   => $password,
                    'persistent' => FALSE,
                ),
                'table_prefix' => $prefix,
                'charset'      => 'utf8',
                'caching'      => FALSE,
                'profiling'    => FALSE,
            )
        );
    }

    public function getName()
    {
        return 'mysql';
    }

    public function prepareDatabase($database)
    {
        mysql_query('ALTER DATABASE `'.$database.'` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci', $this->link);
    }

    public function query($sql)
    {
        return mysql_query($sql, $this->link);
    }
}