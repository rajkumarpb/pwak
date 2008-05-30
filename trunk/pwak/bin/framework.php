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
 * @version   SVN: $Id: framework.php,v 1.3 2008-05-15 11:18:49 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

#!/usr/bin/env php
<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * $Source: /home/cvs/framework/bin/framework.php,v $
 * Script de management des projets appelé en ligne de commande.
 *
 * @version   CVS: $Id: framework.php,v 1.3 2008-05-15 11:18:49 david Exp $
 * @author    David JL <david@ateor.com>
 * @copyright 2002-2006 ATEOR - All rights reserved
 */

define('PROJECT_ROOT', '.');
define('FRAMEWORK_ROOT', '.');
require_once 'framework.inc.php';
require_once 'lib/Frontend/Frontend.php';

$frontend = new Frontend();
$frontend->handleCommandLine();

?>
