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
 * @version   SVN: $Id: autoload.inc.php,v 1.22 2008-05-30 09:23:46 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

global $autoload_map;
$autoload_map = array(
    'Object'           => 'lib/Object.php',
    'Collection'       => 'lib/Collection.php',
    'Auth'             => 'lib/Auth.php',
    'Database'         => 'lib/Database.php',
    'Mapper'           => 'lib/Mapper.php',
    'Registry'         => 'lib/Registry.php',
    'Session'          => 'lib/Session.php',
    'StateMachine'     => 'lib/Search/StateMachine/StateMachine.php',
    'StateMachineNode' => 'lib/Search/StateMachine/StateMachineNode.php',
    'CronTaskManager'  => 'lib/CronTask.php',
    'DateTimeTools'    => 'lib/DateTime.php',
    'Dispatcher'       => 'lib/Dispatcher.php',
    'FormTools'        => 'lib/Form.php',
    'GenericMessages'  => 'lib/GenericMessages.php',
    'I18N'             => 'lib/I18n.php',
    'ImageManager'     => 'lib/ImageManager.php',
    'JsTools'          => 'lib/Js.php',
    'MailTools'        => 'lib/Mail.php',
    'Preferences'      => 'lib/Preferences.php',
    'PreferencesByUser' => 'lib/Preferences.php',
    'SearchTools'      => 'lib/Search/Search.php',
    'TextTools'        => 'lib/Text.php',
    'Timer'            => 'lib/Timer.php',
    'Tools'            => 'lib/Tools.php',
    'Upload'           => 'lib/Upload.php',
    'UrlTools'         => 'lib/Url.php',
    'AjaxServer'       => 'lib/Ajax/Server.php',
    'AjaxClient'       => 'lib/Ajax/Client.php',
    'FilterComponent'  => 'lib/Search/Filter/FilterComponent.php',
    'FilterRule'       => 'lib/Search/Filter/FilterRule.php',
    'GenericController'=> 'lib/Crud/GenericController.php',
    'GenericAddEdit'   => 'lib/Crud/GenericAddEdit.php',
    'GenericGrid'      => 'lib/Crud/GenericGrid.php',
    'AbstractGrid'     => 'lib/Grid/AbstractGrid.php',
    'Grid'             => 'lib/Grid/Grid.php',
    'SubGrid'          => 'lib/Grid/SubGrid.php',
    'Navigation'       => 'lib/Navigation/Navigation.php',
    'NavigationRendererDefault' => 'lib/Navigation/Renderers/NavigationRendererDefault.php',
    'SearchForm'       => 'lib/Search/SearchForm.php',
    'Template'         => 'lib/Template/Template.php',
    'Timetable'        => 'lib/Timetable/Timetable.php',
    'TimetableEvent'   => 'lib/Timetable/TimetableEvent.php',
    'XmlRpcServer'     => 'lib/XmlRpc/Server.php',
    'XmlRpcClient'     => 'lib/XmlRpc/Client.php'
);


/**
 * Fonction appelée automatiquement si une classe n'est pas enlib définie au
 * moment de son utilisation.
 * cf. http://fr2.php.net/manual/fr/language.oop5.autoload.php
 *
 * @param  string $clsname
 * @return void
 */
function __autoload($clsname) {
    global $autoload_map;
    if (isset($autoload_map[$clsname])) {
        require $autoload_map[$clsname];
    } else if(strpos($clsname, 'GridColumn') !== false) { 
        require 'lib/Grid/Columns/' . $clsname . '.php';
    } else if(strpos($clsname, 'GridActionRenderer') !== false) { 
        require 'lib/Grid/Actions/Renderers/' . $clsname . '.php';
    } else if(strpos($clsname, 'GridAction') !== false) {
        require 'lib/Grid/Actions/' . $clsname . '.php';
    } else if(strpos($clsname, 'TimetableAction') !== false) {
        require 'lib/Timetable/Actions/' . $clsname . '.php';
    } else if(strpos($clsname, 'TimetableDecorator') !== false) {
        require 'lib/Timetable/Decorators/' . $clsname . '.php';
    } else if(strpos($clsname, 'TimetableFormat') !== false) {
        require 'lib/Timetable/Formats/' . $clsname . '.php';
    } else if(strpos($clsname, 'NavigationRenderer') !== false) {
        require 'lib/Navigation/Renderers/' . $clsname . '.php';
    } else {
        // si la classe n'est pas définie dans le mapping, on est peut-être
        // dans le cas d'une classe générée, on essaie d'inclure le fichier...
        @include MODELS_DIR . '/' . $clsname . '.php';
    }
}

/**
 * Charge une colonne, une action ou un filtre de grid.
 *
 * @access public
 * @param string $type le type de composant à charger, valeurs possibles: 
 * Column, Action ou Filter
 * @param string $name le nom de la colonne/action/filtre à charger
 * @return boolean true i le filtre a été trouvé et false sinon
 */
function loadGridComponent($type, $name) {
    $script = CUSTOM_GRID_DIR . '/' . $type . 's/' . $name . '.php';
    if (file_exists(PROJECT_ROOT . '/' . LIB_DIR . '/' . $script)) {
        include_once $script;
        return true;
    }
    $script = 'lib/Grid/' . $type . 's/' . $name . '.php';
    if (file_exists(FRAMEWORK_ROOT . '/' . $script)) {
        include_once $script;
        return true;
    }
    trigger_error('Unknown grid '. $type . ': ' . $name, E_USER_NOTICE);
    return false;
}

?>
