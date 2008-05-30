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
 * @version   SVN: $Id: GridActionRedirect.php,v 1.7 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GridActionRedirect extends AbstractGridAction {
    /**
     * GridActionRedirect::__construct()
     *
     * @access public
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {
        // Par defaut ici
        $this->allowEmptySelection = false;
        parent::__construct($params);
        if (isset($params['URL'])) {
            $this->_URL = $params['URL'];
        }
        if (isset($params['TransmitedArrayName'])) {
            $this->transmitedArrayName = $params['TransmitedArrayName'];
        }
    }

    protected $_transmitedArrayName = false;

    /**
     * @access private
     */
    public $_URL = '';

    /**
     * GridActionRedirect::execute()
     *
     * @access public
     * @param object $objects
     * @param boolean $doRedirect
     * @return mixed
     */
    public function execute($objects, $itemIds=array(), $doRedirect=true) {
        // Retourne exception si pas d'item selectionne
        $result = parent::execute($objects, $itemIds);
        if (Tools::isException($result)) {
            return $result;
        }
        if (false !== strpos($this->_URL, '%d')) {
            if (count($objects) == 1) {
                $target = sprintf($this->_URL, $objects[0]->getId());
            } else {
                if (!$this->allowEmptySelection) {
                    return new Exception(I_NEED_SINGLE_ITEM);
                }
                $target = $this->_URL;
            }
        } else if (empty($objects)) {
            if ($this->transmitedArrayName && !$this->allowEmptySelection) {
                return new Exception(I_NEED_SELECT_ITEM);
            }
            $target = $this->_URL;
        } else {
            $padding = (strpos($this->_URL, '?') === false)?'?':'&';
            $queryString = '';
            foreach($objects as $key => $object) {
                $object = $objects[$key];
                if (method_exists($object, 'getId')) {
                    $queryString .= $padding . $this->transmitedArrayName .
                        "[]=" . $object->getId();
                    $padding = '&';
                }
            }
            $target = $this->_URL . $queryString;
        }
        $target = UrlTools::compliantURL($target, false);
        if ($doRedirect) {
            Tools::redirectTo($target);
            exit;
        } else {
            return $target;
        }
    }
}

?>
