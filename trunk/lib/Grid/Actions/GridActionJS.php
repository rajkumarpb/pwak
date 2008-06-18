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
 * @version   SVN: $Id$
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GridActionJS extends AbstractGridAction {
    /**
     * GridActionJS::__construct()
     *
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {
        if (false == isset($params['Caption'])) {
            $params['Caption'] = A_VALIDATE;
        }
        if (false == isset($params['GlyphEnabled'])) {
            $params['GlyphEnabled'] = 'images/ok.gif';
        }
        if (false == isset($params['GlyphDisabled'])) {
            $params['GlyphDisabled'] = 'images/ok_no.gif';
        }
        // Les instructions js a executer
        if (!isset($params['jsActionArray'])) {
            $params['jsActionArray'] = 'alert(\'Error\')';
            trigger_error("jsActionArray has not been defined. "
                . "(GridActionJS)", E_USER_NOTICE);
        }
        $this->jsActionArray = $params['jsActionArray'];
        // Par defaut ici
        $this->withTriggerAction = false;
        parent::__construct($params);
    }

}

?>