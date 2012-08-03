<?php
class Installer {

    /**
     * @var InstallerDatabase 
     */
    public $db = NULL;
    
    /**
     * @var string 
     */
    protected $view = '';

    /**
     * Add user
     *
     * @param  string  $login
     * @param  string  $password
     * @param  string  $email
     * @param  boolean $admin [optional]
     */
    public function addUser($login, $password, $email, $admin = TRUE)
    {
        // DB initialised
        if (!$this->db)
        {
            throw new Exception('No database connection');
        }

        // Default prefix
        $prefix = isset($_SESSION['prefix']) ? $_SESSION['prefix'] : 'blackops_';

        // Prepare data
        $login = $this->db->escape(htmlspecialchars($login));
        $password = $this->db->escape(htmlspecialchars($password));
        $email = $this->db->escape(htmlspecialchars($email));
        $password = $this->db->escape($this->hashPassword($password));

        // Create user
        $this->db->query("INSERT INTO ".$prefix."users (username, email, password)
                          VALUES ('".$login."', '".$email."', '".$password."')");

        // Add permissions
        if ($admin)
        {
            $this->db->query("INSERT INTO ".$prefix."roles_users (user_id, role_id) VALUES (1, 1)");
            $this->db->query("INSERT INTO ".$prefix."roles_users (user_id, role_id) VALUES (1, 2)");
            $this->db->query("INSERT INTO ".$prefix."roles_users (user_id, role_id) VALUES (1, 3)");
            $this->db->query("INSERT INTO ".$prefix."roles_users (user_id, role_id) VALUES (1, 4)");
        }
    }

    /**
     * CHMOD check
     *
     * @param  array $list
     * @return array
     */
    public function checkWrite(array $list)
    {
        // Success?
        $success = TRUE;

        // New list
        $new = array();

        // Iterate
        foreach($list as $f)
        {
            if(!is_writeable(SYSTEM_PATH.$f)) $success = FALSE;

            $new[] = array('file' => $f, 'writeable' => is_writable(SYSTEM_PATH.$f));
        }

        // Return
        return array('success' => $success, 'files' => $new);
    }

    /**
     * Connect to database
     * 
     * @param  string    $driver
     * @param  string    $host
     * @param  string    $username
     * @param  string    $password
     * @param  string    $database
     * @throws Exception 
     */
    public function connect($driver, $host, $username, $password, $database)
    {
        // Load driver
        require_once INSTALLER_PATH.'classes/db/'.$driver.'.php';
        
        // Name
        $driver = 'InstallerDatabase'.ucfirst($driver);
        
        // Valid?
        if (!class_exists($driver))
        {
            throw new Exception('Invalid driver');
        }
        
        // Create instance
        $this->db = new $driver;
        
        // Connect
        $this->db->connect($host, $username, $password, $database);
    }
    
    /**
     * Connect to db using config file
     * 
     * @param array $config
     * @throws Exception 
     */
    public function connectFromConfig($config)
    {
        // Driver
        $driver = $config['type'];
        
        // Fix driver name
        if ($driver == 'postgresql')
        {
            $driver = 'pgsql';
        }
        
        // Load driver
        require_once INSTALLER_PATH.'classes/db/'.$driver.'.php';
        
        // Name
        $driver = 'InstallerDatabase'.ucfirst($driver);
        
        // Valid?
        if (!class_exists($driver))
        {
            throw new Exception('Invalid driver');
        }
        
        // Create instance
        $this->db = new $driver;
        
        // Connect
        $this->db->connectFromConfig($config);
    }
    
    /**
     * Execute scheme
     *
     * @param string $file
     * @param string $prefix
     */
    public function executeScheme($file, $prefix = 'blackops_')
    {
        // Exists?
        if(!is_readable(INSTALLER_PATH.$file))
        {
            throw new Exception('Scheme not found: '.$file);
        }

        // DB initialised
        if(!$this->db)
        {
            throw new Exception('No database connection');
        }

        // Some vars
        $queries = array();
        $inString = FALSE;
        $stringChar = '';
        $query = '';

        // Retrieve schema
        $file = file_get_contents(INSTALLER_PATH.$file);
        $count = strlen($file) - 1;

        // Prefix = {dbp}
        // Iterate
        for($i = 0; $i <= $count ; $i++)
        {
            // Prefix
            if($file[$i] == '{' AND $file[$i + 1] == 'd' AND $file[$i + 2] == 'b' AND $file[$i + 3] == 'p' AND $file[$i + 4] == '}')
            {
                // Add prefix
                $query .= $prefix;

                // Move cursor
                $i = ($i + 4);

                // Next iteration
                continue;
            }

            // String
            if($file[$i] == '"' OR $file[$i] == "'")
            {
                if(!$inString)
                {
                    $inString = TRUE;
                    $stringChar = $file[$i];
                }
                elseif($file[$i] == $stringChar)
                {
                    $inString = FALSE;
                }
            }

            // Seperator
            if($file[$i] == ';' AND $query AND !$inString)
            {
                $queries[] = $query;

                $query = '';
            }
            else
            {
                $query .= $file[$i];
            }
        }

        // Execute each query
        foreach($queries as $q)
        {
            $this->db->query($q);
        }
    }

    /**
     * Installation handler
     *
     * @param InstallerModule $mod
     */
    public function handleInstallation(InstallerModule $mod)
    {
        // Blocked?
        if(file_exists(SYSTEM_PATH.'application/cache/install.lock'))
        {
            echo $this->parseView('layout', array('content' => $this->parseView('blocked'), 'steps' => array(1 => 'Information'),
                                              'currentStep' => 1));

            return;
        }

        // Installer
        $mod->installer($this);

        // Fresh install
        if(empty($_SESSION['step']))
        {
            $_SESSION['step'] = 1;

            $mod->currentStep(1);
        }
        else
        {
            $mod->currentStep($_SESSION['step']);
        }

        // Handle module
        $mod->handle();

        // Output
        echo $this->parseView('layout', array('content' => $this->view, 'steps' => $mod->getStepList(),
                                              'currentStep' => $_SESSION['step']));
    }

    /**
     * Hash password
     * 
     * @param  string $password
     * @param  mixed  $salt
     * @return string
     */
    public function hashPassword($password, $salt = FALSE)
    {
        $pattern = preg_split('/,\s*/', '1, 3, 5, 9, 14, 15, 20, 21, 28, 30');

        if ($salt === FALSE)
        {
            // Create a salt seed, same length as the number of offsets in the pattern
            $salt = substr(sha1(uniqid(NULL, TRUE)), 0, count($pattern));
        }

        // Password hash that the salt will be inserted into
        $hash = sha1($salt.$password);

        // Change salt to an array
        $salt = str_split($salt, 1);

        // Returned password
        $password = '';

        // Used to calculate the length of splits
        $last_offset = 0;

        foreach ($pattern as $offset)
        {
            // Split a new part of the hash off
            $part = substr($hash, 0, $offset - $last_offset);

            // Cut the current part out of the hash
            $hash = substr($hash, $offset - $last_offset);

            // Add the part to the password, appending the salt character
            $password .= $part.array_shift($salt);

            // Set the last offset to the current offset
            $last_offset = $offset;
        }

        // Return the password, with the remaining hash appended
        return $password.$hash;
    }
    
    /**
     * Locks installer, preventing another instance execution
     */
    public function lockInstaller()
    {
        @touch(SYSTEM_PATH.'application/cache/install.lock');
    }

    /**
     * Modify WxSport config
     *
     * @param string $file
     * @param array  $values
     */
    public function modifyConfig($file, array $values)
    {
        // Exists?
        if(!is_writable(SYSTEM_PATH.'application/config/'.$file.'.php') AND substr(PHP_OS, 0, 3) != 'WIN')
        {
            throw new Exception('Config not found/writeable: '.$file);
        }

        // Load
        $config = require SYSTEM_PATH.'application/config/'.$file.'.php';

        // Merge
        $config = array_merge($config, $values);

        // Save
        file_put_contents(SYSTEM_PATH.'application/config/'.$file.'.php',
        "<?php defined('SYSPATH') or die('No direct access allowed.');"."\n".' return '.var_export($config, TRUE).'; '."\n".' ?>');
    }

    /**
     * Proceed
     */
    public function nextStep($file = 'index.php')
    {
        $_SESSION['step'] += 1;

        header('Location: '.$file);
        exit;
    }

    /**
     * Parse view
     *
     * @param  string $name
     * @param  array  $params [optional]
     * @return string
     */
    public function parseView($viewName, array $params = array())
    {
        // Exists?
        if(!is_readable(INSTALLER_PATH.'views/'.$viewName.'.php'))
        {
            throw new Exception('View not found: '.$viewName);
        }

        // Extract
        extract($params);

        // Handler
        ob_start();

        // Output
        include INSTALLER_PATH.'views/'.$viewName.'.php';

        // End
        $this->view = ob_get_clean();

        // Return
        return $this->view;
    }
}