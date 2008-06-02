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

class GridActionDelete extends AbstractGridAction {
    /**
     * GridActionDelete::__construct()
     *
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {
        $this->allowEmptySelection = false;
        $this->confirmMessage = I_DELETE_ITEMS;

        if (!isset($params['GlyphEnabled'])) {
            $params['GlyphEnabled'] = 'images/sup.gif';
        }
        if (!isset($params['GlyphDisabled'])) {
            $params['GlyphDisabled'] = 'images/sup_no.gif';
        }
        if (!isset($params['Caption'])) {
            $params['Caption'] = A_DELETE;
        }
        parent::__construct($params);
        if (isset($params['EntityType'])) {
            $this->_entityType = $params['EntityType'];
        }
        if (isset($params['Query'])) {
            $this->_query = $params['Query'];
        }
        if (isset($params['TransmitedArrayName'])) {
            $this->transmitedArrayName = $params['TransmitedArrayName'];
        } else {
            $this->transmitedArrayName = 'Id';
            trigger_error("TransmitedArrayName=>'Array name' has not been"
                . " defined. 'Id' was chosen by defect", E_USER_NOTICE);
        }
    }

    /**
     *
     * @access protected
     */
    protected $_entityType;
    protected $_query;

    /**
     * GridActionDelete::execute()
     *
     * @param  $objects
     * @return void
     */
    public function execute($objects, $itemIds=array()) {
        // Retourne exception si pas d'item selectionne
        $result = parent::execute($objects, $itemIds);
        if (Tools::isException($result)) {
            return $result;
        }
        $okLink = $this->_entityType . 'Delete.php?';
        $cancelLink = ('' != $this->returnURL)?
                $this->returnURL:$this->_entityType . 'List.php?' . $this->_query;

        $padding = '';
        foreach($objects as $object) {
            if (method_exists($object, 'getId')) {
                $okLink .= $padding . $this->transmitedArrayName . "[]="
                        . $object->getId();
            }
            $padding = '&';
        }

        if ($this->_query != "") {
            $okLink .= '&' . $this->_query;
        }
        if (false === $this->gridInPopup) {
            Template::confirmDialog($this->confirmMessage, $okLink, $cancelLink);
            exit;
        } else {
            Template::confirmDialog($this->confirmMessage, $okLink, $cancelLink,
                BASE_POPUP_TEMPLATE);
            exit;
        }
    }
}

?>
