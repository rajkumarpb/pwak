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
 * @version   SVN: $Id: GridActionAddEdit.php,v 1.5 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GridActionAddEdit extends GridActionRedirect {
    /**
     * GridActionAddEdit::__construct()
     *
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {

        if (isset($params['EntityType'])) {
            $this->_entityType = $params['EntityType'];
        }
        if (isset($params['Action'])) {
            $this->_action = $params['Action'];
        }else {
            $this->_action = $params['Add'];
        }
        // Par defaut
        $this->caption = ($this->_action == 'Add')?A_ADD:A_UPDATE;

        if (!isset($params['GlyphEnabled'])) {
            $params['GlyphEnabled'] = ($this->_action == 'Add')?
                'images/ajouter.gif':'images/modifier.gif';
        }
        if (!isset($params['GlyphDisabled'])) {
            $params['GlyphDisabled'] = ($this->_action == 'Add')?
                'images/ajouter_no.gif':'images/modifier_no.gif';
        }
        if (($this->_action == 'Edit' || $this->_action == 'View') && 
            !isset($params['TransmitedArrayName'])) {
            $params['TransmitedArrayName'] = 'Id';
        }
        if (!isset($params['URL'])) {
            $url = $this->_entityType . 'AddEdit.php';
            $params['URL'] = ($this->_action == 'Add')?$url
                    :$url . '?' . $params['TransmitedArrayName'] . '=%d';
        }
        elseif ($this->_action == 'Edit' || $this->_action == 'View') {
            $params['URL'] .= '?' . $params['TransmitedArrayName'] . '=%d';
        }
        if (isset($params['Query'])) {
            $params['URL'] = (strpos($params['URL'], "?") === false)?
                    $params['URL'] . '?':$params['URL'] . '&';
            $params['URL'] .= $params['Query'];
        }
        parent::__construct($params);
        // Apres le constructeur du parent, sinon, ecrase par ce dernier
        if ($this->_action == 'Add') {
            $this->allowEmptySelection = true;
        }
    }

    /**
     * @access private
     */
    private $_entityType;
    private $_query;
}

?>
