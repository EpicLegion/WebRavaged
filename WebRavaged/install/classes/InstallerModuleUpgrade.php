<?php
class InstallerModuleUpgrade extends InstallerModule {

    /**
     * First step: Database setup
     */
    protected function databaseSetup()
    {
        // Retrieve config
        $config = require SYSTEM_PATH.'application/config/database.php';

        if(!is_array($config))
        {
            throw new Exception;
        }

        // Connect to database
        $this->installer->connectFromConfig($config['default']);

        // Execute
        $this->installer->executeScheme('update_'.$this->installer->db->getName().'.sql', $config['default']['table_prefix']);

        // Next step
        $_SESSION['step'] = 2;

        // Parse view
        $this->installer->parseView('database', array('file' => 'upgrade.php'));
    }

    /**
     * Fubak steo
     */
    protected function finalStep()
    {
        // Lock installer
        $this->installer->lockInstaller();

        // View
        $this->installer->parseView('finish_upgrade');
    }

    /**
     * Get step list
     *
     * @return array
     */
    public function getStepList()
    {
        return array(
            1 => 'Database',
            2 => 'Finish'
        );
    }

    /**
     * Handle current step
     */
    public function handle()
    {
        switch($this->currentStep)
        {
            case 2:
                $this->finalStep();
                break;

            default:
                $this->databaseSetup();
                break;
        }
    }
}