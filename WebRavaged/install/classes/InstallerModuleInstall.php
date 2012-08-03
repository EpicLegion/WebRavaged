<?php
class InstallerModuleInstall extends InstallerModule {

    /**
     * Fourth step: Add administrator
     */
    protected function addAdministrator()
    {
        // Connect to database
        $this->installer->connect($_SESSION['driver'], $_SESSION['host'], $_SESSION['user'], $_SESSION['password'], $_SESSION['database']);

        // Message
        $message = '';

        // Submitted?
        if(!empty($_POST['submit']) AND !empty($_POST['username']) AND !empty($_POST['password']) AND !empty($_POST['password2']) AND !empty($_POST['email']))
        {
            // Match?
            if($_POST['password'] == $_POST['password2'])
            {
                $this->installer->addUser($_POST['username'], $_POST['password'], $_POST['email']);
                $this->installer->nextStep();
            }
            else
            {
                $message = 'Password does not match';
            }
        }

        // View
        $this->installer->parseView('admin', array('message' => $message));
    }

    /**
     * Second step: Basic configuration (URL and database)
     */
    protected function basicConfig()
    {
        // Message
        $message = '';

        // Supported DB
        $supportedDB = $_SESSION['supportedDB'];
        
        
        
        // Submitted?
        if (!empty($_POST['driver']) AND !empty($_POST['submit']) AND !empty($_POST['path']) AND !empty($_POST['host'])
           AND !empty($_POST['user']) AND !empty($_POST['database']) AND isset($supportedDB[$_POST['driver']]))
        {
            // Check prefix
            if(empty($_POST['prefix']))
            {
                $dbPref = 'blackops_';
            }
            else
            {
                $dbPref = htmlspecialchars($_POST['prefix']);
            }

            // Handle exceptions
            try
            {
                // Connect to database
                $this->installer->connect($_POST['driver'], $_POST['host'], $_POST['user'], $_POST['password'], $_POST['database']);
                
                // Set configuration
                $this->installer->modifyConfig('app', array('cookie_path' => $_POST['path']));
                $this->installer->modifyConfig('database', $this->installer->db->getConfig($_POST['host'], $_POST['user'], $_POST['password'], $_POST['database'], $dbPref));
                
                // Set session data
                $_SESSION['driver'] = $_POST['driver'];
                $_SESSION['host'] = $_POST['host'];
                $_SESSION['user'] = $_POST['user'];
                $_SESSION['password'] = isset($_POST['password']) ? $_POST['password'] : '';
                $_SESSION['database'] = $_POST['database'];
                $_SESSION['prefix'] = $dbPref;

                // Next step
                $this->installer->nextStep();
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
            }
        }

        // Path
        $path = substr($_SERVER['PHP_SELF'], 0, strlen($_SERVER['PHP_SELF']) - 17);

        // Parse view
        $this->installer->parseView('config', array('path' => $path, 'message' => $message, 'databases' => $supportedDB));
    }

    /**
     * Third step: Database setup
     */
    protected function databaseSetup()
    {
        // Connect to database and prepare it
        $this->installer->connect($_SESSION['driver'], $_SESSION['host'], $_SESSION['user'], $_SESSION['password'], $_SESSION['database']);
        $this->installer->db->prepareDatabase($_SESSION['database']);

        // Execute
        $this->installer->executeScheme('scheme_'.$_SESSION['driver'].'.sql', $_SESSION['prefix']);

        // Next step
        $_SESSION['step'] = 4;

        // Parse view
        $this->installer->parseView('database', array('file' => 'index.php'));
    }

    /**
     * Finalization
     */
    protected function finalStep()
    {
        // Lock installer
        $this->installer->lockInstaller();

        // View
        $this->installer->parseView('finish');
    }

    /**
     * Get step list
     *
     * @return array
     */
    public function getStepList()
    {
        return array(
            1 => 'Checks',
            2 => 'Config',
            3 => 'Database',
            4 => 'Account setup',
            5 => 'Finish'
        );
    }

    /**
     * Handle current step
     */
    public function handle()
    {
        switch($this->currentStep)
        {
            case 1:
                $this->preinstallCheck();
                break;

            case 2:
                $this->basicConfig();
                break;

            case 3:
                $this->databaseSetup();
                break;

            case 4:
                $this->addAdministrator();
                break;

            case 5:
                $this->finalStep();
                break;
        }
    }

    /**
     * First step: preinstallation check(CHMOD and requirements)
     */
    protected function preinstallCheck()
    {
        // Everything OK?
        $success = TRUE;

        // Server config
        $server = array();
        
        // Databases
        $databases = array();

        // PHP version
        if(version_compare(PHP_VERSION, '5.2.3', '>='))
        {
            $server['php'] = '<span style="color: green">'.PHP_VERSION.'</span>';
        }
        else
        {
            $success = FALSE;
            $server['php'] = '<span style="color: red">'.PHP_VERSION.'</span>';
        }

        // UTF-8
        if(!preg_match('/^.$/u', 'ñ'))
        {
            $success = FALSE;
            $server['pcre'] = '<span style="color: red">No</span>';
        }
        else
        {
            $server['pcre'] = '<span style="color: green">Yes</span>';
        }

        // Iconv
        if(!extension_loaded('iconv'))
        {
            $success = FALSE;
            $server['iconv'] = '<span style="color: red">No</span>';
        }
        else
        {
            $server['iconv'] = '<span style="color: green">Yes</span>';
        }

        // MySQL
        if(!function_exists('mysql_connect'))
        {
            $server['mysql'] = '<span style="color: red">No</span>';
        }
        else
        {
            $server['mysql'] = '<span style="color: green">Yes</span>';
            $databases['mysql'] = 'MySQL';
        }
        
        // PgSQL
        if (!function_exists('pg_connect'))
        {
            $server['pgsql'] = '<span style="color: red">No</span>';
        }
        else
        {
            $server['pgsql'] = '<span style="color: green">Yes</span>';
            $databases['pgsql'] = 'PostgreSQL';
        }
        
        /*
        // SQLite2
        if (!function_exists('sqlite_open'))
        {
            $server['sqlite2'] = '<span style="color: red">No</span>';
        }
        else
        {
            $server['sqlite2'] = '<span style="color: green">Yes</span>';
            $databases['sqlite2'] = 'SQLite 2.x';
        }
        
        // SQLite3
        if (!class_exists('SQLite3'))
        {
            $server['sqlite'] = '<span style="color: red">No</span>';
        }
        else
        {
            $server['sqlite'] = '<span style="color: green">Yes</span>';
            $databases['sqlite'] = 'SQLite 3.x';
        }
        */

        // Register globals
        if(ini_get('register_globals'))
        {
            $server['globals'] = 'On';
        }
        else
        {
            $server['globals'] = '<span style="color: green">Off</span>';
        }

        // Magic quotes
        if(get_magic_quotes_gpc())
        {
            $server['magic_quotes'] = 'On';
        }
        else
        {
            $server['magic_quotes'] = '<span style="color: green">Off</span>';
        }

        // CHMOD check
        $chmod = $this->installer->checkWrite(array
        (
            'application/cache',
            'application/config/app.php',
            'application/config/database.php',
        ));

        // Success?
        if(!$chmod['success'] AND substr(PHP_OS, 0, 3) != 'WIN')
        {
            $success = FALSE;
        }

        // Next step?
        if(isset($_GET['nextstep']) AND $_GET['nextstep'] == '1' AND $success AND !empty($databases))
        {
            $_SESSION['supportedDB'] = $databases;
            $this->installer->nextStep();
        }

        // Render view
        $this->installer->parseView('check', array('success' => $success, 'server' => $server, 'files' => $chmod['files']));
    }
}
