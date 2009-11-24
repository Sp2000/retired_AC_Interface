<?php
/**
 * Annual Checklist Interface
 *
 * index.php
 * Initial page, launches the bootstrappers
 *
 * @category    ACI
 * @package     public
 */
// Define path to application directory
defined('APPLICATION_PATH')
    || define(
        'APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application')
    );

// Define application environment
defined('APPLICATION_ENV')
    || define(
        'APPLICATION_ENV', (getenv('APPLICATION_ENV') ?
        getenv('APPLICATION_ENV') : 'production')
    );

// Ensure library/ is on include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(realpath(APPLICATION_PATH . '/../library'), get_include_path())
    )
);

// Incude classes required for the initialitzation of the app
require_once 'Zend/Application.php';
require_once 'Zend/Config/Xml.php';
require_once 'Zend/Config/Ini.php';

// Load configuration
$config = new Zend_Config_Xml(
    APPLICATION_PATH . '/configs/application.xml',
    APPLICATION_ENV,
    true
);
$config->merge(
    new Zend_Config_Ini(
        APPLICATION_PATH . '/configs/config.ini',
        APPLICATION_ENV
    )
);
// Init application
$application = new Zend_Application(APPLICATION_ENV, $config);
// Store config
Zend_Registry::set('config', $config);
// Run bootstrap
$application->bootstrap()->run();