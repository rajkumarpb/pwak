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
 * @version   SVN: $Id: GridActionPrint.php,v 1.4 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GridActionPrint extends GridActionJS {
    /**
     * GridActionPrint::__construct()
     *
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {
        if (false == isset($params['Caption'])) {
            $params['Caption'] = A_PRINT;
        }
        if (false == isset($params['Title'])) {
            $params['Title'] = _('Print list');
        }
        if (false == isset($params['GlyphEnabled'])) {
            $params['GlyphEnabled'] = 'images/imprimer.gif';
        }
        if (false == isset($params['GlyphDisabled'])) {
            $params['GlyphDisabled'] = 'images/imprimer_no.gif';
        }
        $params['jsActionArray'] = array('window.print()');
        parent::__construct($params);
    }

}

?>