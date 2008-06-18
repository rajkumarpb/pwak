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

class GridActionSubmit extends GridActionJS {
    /**
     * GridActionSubmit::__construct()
     *
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {

        $jsActionArray = array();
        if (isset($params['FormAction'])) {
            $jsActionArray[] = $this->jsOwnerForm . '.action=\''
                    . $params['FormAction']. '\'';
        }
        // La ligne suivante n'etait que si pas ouverture de popup...
        $jsActionArray[] = $this->jsOwnerForm . '.method=\'post\'';
        // Si doit ouvrir un popup
        if (isset($params['TargetPopup'])) {
            $jsActionArray[] = $this->jsOwnerForm . '.target=\'popup\'';
        }
        if (isset($params['CheckForm']) && $params['CheckForm'] == true) {
            $jsActionArray[] = 'return checkForm(' . $this->jsOwnerForm
                    . ', requiredFields);';
        }
        else {
            $jsActionArray[] = $this->jsOwnerForm . '.submit()';
        }

        $params['jsActionArray'] = $jsActionArray;
        parent::__construct($params);
    }

    protected $checkForm = false;
}

?>