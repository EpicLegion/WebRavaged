<?php
abstract class InstallerModule {

    protected $currentStep = 1;
    protected $installer = NULL;

    /**
     * Set current step
     *
     * @param integer $new
     */
    public function currentStep($new)
    {
        $this->currentStep = $new;
    }

    /**
     * Handle current step
     */
    abstract public function handle();

    /**
     * Set installer instance
     *
     * @param Installer $installer
     */
    public function installer(Installer $installer)
    {
        $this->installer = $installer;
    }
}
