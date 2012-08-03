<?php
// Session start
session_start();

// Pathes
define('INSTALLER_PATH', dirname(__FILE__).'/');
define('SYSTEM_PATH', substr(INSTALLER_PATH, 0, strlen(INSTALLER_PATH) - 8));
define('SYSPATH', 'yeah');

// Require
require_once INSTALLER_PATH.'classes/InstallerDatabase.php';
require_once INSTALLER_PATH.'classes/Installer.php';
require_once INSTALLER_PATH.'classes/InstallerModule.php';
require_once INSTALLER_PATH.'classes/InstallerModuleUpgrade.php';

// Installer
$installer = new Installer;

// Handle
$installer->handleInstallation(new InstallerModuleUpgrade);