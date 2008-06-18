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

class GridColumnFieldMapperWithTranslation extends GridColumnFieldMapper {
    /**
     * Constructor
     *
     */
    function __construct($title = '', $params = array()) {
        $this->sortable = false;
        parent::__construct($title, $params);
        if (isset($params['TranslationMap'])) {
            $this->_translationMap = $params['TranslationMap'];
        }
        if (isset($params['DefaultValue'])) {
            $this->_defaultValue = $params['DefaultValue'];
        }
    }

    /**
     *
     * @access private
     */
    private $_translationMap = array();

    /**
     * permet de passer à grid une valeur
     * par defaut à afficher en cas de translation map
     *
     * @access private
     */
    private $_defaultValue = false;

    /**
     *
     * @access public
     * @return void
     */
    public function render($object) {
        $result = parent::render($object);
        if (isset($this->_translationMap[$result])) {
            return $this->_translationMap[$result];
        }
        if ($this->_defaultValue) {
            return $this->_defaultValue;
        }
        return $result;
    }
}

?>
