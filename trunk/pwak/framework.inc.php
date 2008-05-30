<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PWAK (PHP Web Application Kit) framework.
 *
 * PWAK is a php framework initially developed for the
 * {@link http://onlogistics.googlecode.com Onlogistics} ERP/Supply Chain
 * management web application.
 * It provides components and tools for developers to build complex web
 * applications faster and in a more reliable way.
 *
 * PHP version 5.1.0+
 * 
 * LICENSE: This source file is subject to the MIT license that is available
 * through the world-wide-web at the following URI:
 * http://opensource.org/licenses/mit-license.php
 *
 * @package   PWAK
 * @author    ATEOR dev team <dev@ateor.com>
 * @copyright 2003-2008 ATEOR <contact@ateor.com> 
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   SVN: $Id: framework.inc.php,v 1.27 2008-05-30 09:23:46 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

if (get_magic_quotes_gpc()) {
    echo '"magic_quotes_gpc" php.ini directive must be set to "Off". '
       . 'Please adjust your php.ini settings.';
    exit(1);
}

/**
 * Les constantes PROJECT_ROOT et FRAMEWORK_ROOT doivent être définies dans le 
 * fichier de conf de l'application.
 */
if (!defined('PROJECT_ROOT')/* || !defined('FRAMEWORK_ROOT')*/) {
    echo 'You must define your project root directory (PROJECT_ROOT constant)'
        . ' and the framework root directory (FRAMEWORK_ROOT constant).';
    exit(1);
}

/**
 *   
 */
if(!defined('FRAMEWORK_ROOT')) {
    define('FRAMEWORK_ROOT', dirname(__FILE__));
}

/**
 * Framework name used to create pear package and find pear install directory  
 */
define('FRAMEWORK_NAME', 'framework');

/**
 * Booléen qui détermine si l'application est en developpement ou en production
 */
if (!defined('DEV_VERSION')) {
    define('DEV_VERSION', true);
}

// Initialisation du timer si version dev
if (DEV_VERSION == true) {
    require_once FRAMEWORK_ROOT . '/lib/Timer.php';
    Timer::start('Framework initialization');
}


/**
 * Répertoire  relatif au PROJECT_ROOT contenant les fichiers "web accessibles"
 */
if (!defined('WWW_DIR')) {
    define('WWW_DIR', 'www');
}

/**
 * Répertoire relatif au PROJECT_ROOT contenant les classes "utilisateur"
 */
if (!defined('LIB_DIR')) {
    define('LIB_DIR', 'lib');
}

/**
 * Répertoire relatif au LIB_DIR contenant les customisations grid
 */
if (!defined('CUSTOM_GRID_DIR')) {
    define('CUSTOM_GRID_DIR', 'custom/Grid');
}

/**
 * Répertoire relatif au LIB_DIR contenant les customisations controller
 */
if (!defined('CUSTOM_CONTROLLER_DIR')) {
    define('CUSTOM_CONTROLLER_DIR', 'custom/Controller');
}

/**
 * Répertoire relatif au LIB_DIR contenant les customisations planning
 */
if (!defined('CUSTOM_TIMETABLE_DIR')) {
    define('CUSTOM_TIMETABLE_DIR', 'custom/Timetable');
}

/**
 * Répertoire relatif au LIB_DIR contenant les classes générées
 */
if (!defined('MODELS_DIR')) {
    define('MODELS_DIR', 'models');
}

/**
 * Répertoire relatif au LIB_DIR contenant les classes "utilisateur"
 */
if (!defined('CLASSES_DIR')) {
    define('CLASSES_DIR', 'classes');
}

/**
 * Répertoire relatif au LIB_DIR contenant les fonctions "utilisateur"
 */
if (!defined('FUNCTION_DIR')) {
    define('FUNCTION_DIR', 'functions');
}

/**
 * Répertoire relatif au LIB_DIR contenant les templates
 */
if (!defined('SMARTY_TEMPLATE_DIR')) {
    define('SMARTY_TEMPLATE_DIR', 'templates');
}

/**
 * Répertoire relatif au PROJECT_ROOT contenant les scripts cron
 */
if (!defined('CRON_DIR')) {
    define('CRON_DIR', 'crons');
}

/**
 * Répertoire relatif au WWW_DIR contenant les feuilles de style (css)
 */
if (!defined('CSS_DIR')) {
    define('CSS_DIR', 'css');
}

/**
 * Répertoire relatif au WWW_DIR contenant les images
 */
if (!defined('IMG_DIR')) {
    define('IMG_DIR', 'images');
}

/**
 * Répertoire relatif au WWW_DIR contenant le code javascript
 */
if (!defined('JS_DIR')) {
    define('JS_DIR', 'js');
}

/**
 * Répertoire ou smarty peut écrire
 */
if (!defined('SMARTY_COMPILE_DIR')) {
    require_once 'System.php';
    $tmpdir = System::tmpDir();
    define('SMARTY_COMPILE_DIR', $tmpdir);
}

/** 
 * constantes relatives aux cookies
 */
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 0);
}
if (!defined('USER_SESSION_NAME')) {
    define('USER_SESSION_NAME', 'auth_user');
}
if (!defined('REALM_SESSION_NAME')) {
    define('REALM_SESSION_NAME', 'auth_realm');
}
if (!defined('COOKIE_PATH')) {
    define('COOKIE_PATH',  '/');
}
if (!defined('COOKIE_DOMAIN')) {
    define('COOKIE_DOMAIN', '');
}

/**
 * Format du fichier contenant les données du menu
 * xml|php
 */
if(!defined('MENU_METADATA_FORMAT')) {
    define('MENU_METADATA_FORMAT', 'xml');
}

/**
 * Fichier contenant les données du menu
 */
if (!defined('MENU_METADATA')) {
    define('MENU_METADATA', 'config/xml/menu.xml');
}

/**
 * Constantes des templates des divers composants.
 *
 * Si la constante n'est pas définie, c'est le template par défaut du framework
 * qui est pris en compte.
 */
if (!defined('BASE_TEMPLATE')) {
    define('BASE_TEMPLATE', 'default/Base.html');
}
if (!defined('BASE_POPUP_TEMPLATE')) {
    define('BASE_POPUP_TEMPLATE', 'default/Base.html');
}
if (!defined('DIALOG_TEMPLATE')) {
    define('DIALOG_TEMPLATE', 'default/Dialog.html');
}
if (!defined('GRID_TEMPLATE')) {
    define('GRID_TEMPLATE', 'default/Grid.html');
}
if (!defined('SUBGRID_TEMPLATE')) {
    define('SUBGRID_TEMPLATE', 'default/SubGrid.html');
}
if (!defined('SEARCHFORM_TEMPLATE')) {
    define('SEARCHFORM_TEMPLATE', 'default/SearchForm.html');
}
if (!defined('GENERIC_ADDEDIT_TEMPLATE')) {
    define('GENERIC_ADDEDIT_TEMPLATE', 'default/GenericAddEdit.html');
}
if (!defined('GENERIC_ADDEDIT_2COLS_TEMPLATE')) {
    define('GENERIC_ADDEDIT_2COLS_TEMPLATE', 'default/GenericAddEditOnly2Cols.html');
}
if (!defined('TIMETABLE_TEMPLATE')) {
    define('TIMETABLE_TEMPLATE', 'default/Timetable.html');
}

/**
 * Booléen qui détermine si oui ou on l'application utilise l'i18n
 */
if (!defined('I18N_ENABLED')) {
    define('I18N_ENABLED', true);
}

/**
 * feuille de style par defaut du widget jscalendar.
 */
if (!defined('JSCALENDAR_DEFAULT_CSS')) {
    define('JSCALENDAR_DEFAULT_CSS', 'css/calendar.css');
}

/**
 * picto par defaut du widget jscalendar.
 */
if (!defined('JSCALENDAR_DEFAULT_PICTO')) {
    define('JSCALENDAR_DEFAULT_PICTO', 'images/calendar.gif');
}

/**
 * mode d'upload des fichiers et images
 * [db|path/to/writable/dir]
 */
if (!defined('UPLOAD_STORAGE')) {
    define('UPLOAD_STORAGE', 'db');
}
// on ajoute les chemins nécessaires au include_path de php
$pathes = ini_get('include_path')
        . PATH_SEPARATOR . PROJECT_ROOT . DIRECTORY_SEPARATOR . WWW_DIR 
        . PATH_SEPARATOR . PROJECT_ROOT . DIRECTORY_SEPARATOR . LIB_DIR 
        . PATH_SEPARATOR . FRAMEWORK_ROOT
        . PATH_SEPARATOR . FRAMEWORK_ROOT . DIRECTORY_SEPARATOR . 'vendor';
ini_set('include_path', $pathes);

// classe de base
require_once 'lib/Object.php';

// inclus le système de chargement auto des fichiers requis
require_once('autoload.inc.php');

// i18n
if (!function_exists('_')) {
    // gettext n'est pas disponible
    function _($msg) {
        return $msg;
    }
} else if (I18N_ENABLED) {
    I18N::setLocale();
}

// inclusion des messages d'erreur/info génériques
require_once('lib/GenericMessages.php');

// point d'arret marquant la fin de l'initialisation du framework pour le timer
if (DEV_VERSION) {
    Timer::stop('Framework initialization');
}

?>
