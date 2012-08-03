<?php
interface InstallerDatabase {
    
    public function connect($host, $user, $password, $database);
    
    public function connectFromConfig($config);
    
    public function escape($string);
    
    public function fetch($query);
    
    public function getConfig($host, $user, $password, $database, $prefix);
    
    public function getName();
    
    public function prepareDatabase($database);
    
    public function query($sql);
}