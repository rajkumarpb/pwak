<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PWAK the PHP Web Application Kit.
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
 * @version   SVN: $Id: JS_AjaxTools.php,v 1.2 2008-05-15 11:18:49 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

require_once('config.inc.php');
$script = 'ajax/CustomAjaxCient.php';
if(file_exists(PROJECT_ROOT . '/' . WWW_DIR . '/' . $script)) {
    require_once($script);
    $class = 'CustomAjaxClient';
} else {
    require_once('lib/Ajax/Client.php');
    $class = 'AjaxClient';
}

$cli = new $class();
echo $cli->initialize(true);

?>
