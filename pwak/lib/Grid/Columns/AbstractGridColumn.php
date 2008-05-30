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
 * @version   SVN: $Id: AbstractGridColumn.php,v 1.5 2008-05-30 09:23:48 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class AbstractGridColumn {
    /**
     * AbstractGridColumn::__construct()
     *
     * @param string $title
     * @param array $params
     * @return void
     */
    function __construct($title = '', $params = array()) {
        $this->title = $title;

        if (isset($params['SortField'])) {
            $this->sortField = $params['SortField'];
        }
        if (isset($params['Sortable'])) {
            $this->sortable = $params['Sortable'];
        }
        if (isset($params['Enabled'])) {
            $this->enabled = $params['Enabled'];
        }
        // Renseigner par 'numeric' si text-align="right" voulu
        if (isset($params['DataType'])) {
            $this->datatype = $params['DataType'];
        }
    }
    /**
     *
     * @access public
     **/
    public $title = '';
    public $sortField = '';
    public $sortable = true;
    public $enabled = true;
    public $index = 0;
    // text-align="left" par defaut
    public $datatype = 'alphanumeric';

    /**
     * AbstractGridColumn::render()
     *
     * @param  $object
     * @return string
     */
    public function render($object) {
        if (method_exists($object, 'toString')) {
            return $object->toString();
        }
        return '[RENDER METHOD NOT OVERWRITTEN IN CLASS ' . get_class($this) . ']';
    }

    /**
     * AbstractGridColumn::getSortLink()
     *
     * @param  $sortOrder
     * @param  $tab_ordre
     * @return string
     */
    public function getSortLink($tab_ordre) {
        if (!$this->sortable) {
            return '';
        }
        $i = 0;
        while (($i < count($tab_ordre)) &&
            (!in_array($this->index, array_keys($tab_ordre[$i])))) {
            $i++;
        }
        return sprintf(
            'javascript:fw.grid.sortLinkExecute(%d, %d, %d); return false;',
            $i, $this->index, $this->getSortOrder($tab_ordre)
        );
    }

    /**
     * AbstractGridColumn::getSortOrder()
     * Renvoie le tri actuel SORT_ASC ou SORT_DESC
     *
     * @param  $direction
     * @param  $tab_ordre
     * @return mixed
     */
    public function getSortOrder($tab_ordre) {
        if (is_array($tab_ordre)) {
            foreach($tab_ordre as $sort_data) {
                foreach($sort_data as $column => $sort) {
                    if ($column == $this->index) {
                        return $sort == false || $sort == SORT_ASC?
                            SORT_DESC:SORT_ASC;
                    }
                }
            }
        }
        return false;
    }

}

?>
