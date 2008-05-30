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
 * @version   SVN: $Id: GridColumnGenericSubGrid.php,v 1.5 2008-05-30 09:23:48 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GridColumnGenericSubGrid extends SubGridColumn {
    /**
     * GridColumnFieldMapper::__construct()
     *
     * @param string $title
     * @param array $params
     * @return void
     */
    function __construct($title = array(), $params = array()) {
        parent::__construct($title, $params);
        $this->params = $params;        
    }

    /**
     * GridColumnFieldMapper::render()
     *
     * @param  $object
     * @return string
     */
    public function &render($object) {
        $grid = new SubGrid();
        $grid->NewColumn('FieldMapper', $this->title, $this->params);
        
        $getter = 'get' . $this->params['link'] . 'Collection';
        $result = $grid->render($object->$getter());
        return $result;
    }
}

?>
