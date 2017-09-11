<?php
/**
 * A core configuration file.
 * @internal Users must not edit this file. Use My.Config.php to define application level settings.
 * @author Ritesh Gite <huestack@yahoo.com>
 */

// Root path of application.
define('APP_PATH', ($_SERVER['CONTEXT_DOCUMENT_ROOT'] ? $_SERVER['CONTEXT_DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'])  . '/');

// An extension of view, model, or controller file.
define('EXTN', '.php');

// Name of config file created by user.
define('CONFIG', APP_PATH . 'My.Config.php');

// Path of Global file.
define('GLOBAL_FILE', APP_PATH . 'Global.php');

// Path of common classes.
define('CORE_PATH', APP_PATH . 'Amvisie/Core/');

// Physical path of current file.
define('CURRENT_PATH', dirname(__FILE__) . '/');

// Directory where views are located.
define('VIEW_DIR', 'Views/');

// Name of alias configured in Apache.
define('ALIAS', array_key_exists('CONTEXT_PREFIX', $_SERVER) && strlen($_SERVER['CONTEXT_PREFIX']) > 0 ? $_SERVER['CONTEXT_PREFIX'] . '/' : '/');
