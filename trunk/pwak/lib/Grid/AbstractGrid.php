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
 * @version   SVN: $Id: AbstractGrid.php,v 1.5 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class AbstractGrid {
    // Constantes {{{
    /**
     * Constantes utilisées pour les pictos de navigation
     */
    const FIRST_PAGE_IMAGE = 'images/grid_first_page.png';
    const PREVIOUS_PAGE_IMAGE = 'images/grid_previous_page.png';
    const NEXT_PAGE_IMAGE = 'images/grid_next_page.png';
    const LAST_PAGE_IMAGE = 'images/grid_last_page.png';
    const SORT_ASC_IMAGE = 'images/grid_sort_asc.png';
    const SORT_DESC_IMAGE = 'images/grid_sort_desc.png';
    const CANCEL_FILTER_IMAGE = 'images/grid_cancel_filters.png';
    const CUSTOM_DISPLAY_IMAGE = 'images/grid_custom_display.png';
    const CUSTOM_DISPLAY_BAR_IMAGE = 'images/grid_custom_display_bar.png';
    // }}}
    /**
     * AbstractGrid::AbstractGrid()
     *
     * @return void
     */
    function __construct() {
    }

    /**
     *
     * @access protected
     */
    protected $columns = array();
    protected $gridContent = array();


    /**
     * Le nombre d'items à afficher par page
     *
     * @var integer
     * @access public
     */
    public $itemPerPage = 50;

    /**
     * Permet de mettre toutes les colonnes NON SORTABLE
     *
     * @var boolean
     * @access public
     **/
    public $withNoSortableColumn = false;

    /**
     * Determine si le grid peut être réordonné via drag and drop.
     *
     * @var boolean
     * @access public
     */
    public $dndSortable = false;

    /**
     * Attribut sur lequel est effectué le sortable drag'n'drop
     *
     * @var string
     * @access public
     */
    public $dndSortableField = null;

    /**
     * Sert à mettre en couleur les lignes respectant une condition
     * Tableau de la forme:
     * array('Macro' => '%Command.WishedStartDate|date%',
             'Operator' => '=',
             'Value' => date('Y-m-d',mktime(0,0,0, $month, $day, $year))));
     * @var mixed
     * @access public
     **/
    public $highlightCondition = array();

    /**
     *
     * @access private
     */
    private $_Mapper = false;

    /**
     * AbstractGrid::getMapper()
     *
     * @return object Mapper
     */
    public function getMapper() {
        if ($this->_Mapper instanceof Mapper) {
            return $this->_Mapper;
        }
        if (is_string($this->_Mapper)) {
            // On a un nom d'objet, on crée le mapper associé
            $return = Mapper::singleton($this->_Mapper);
        } else {
            $return = new Exception('Grid->_Mapper have to be defined.');
        }
        return $return;
    }

    /**
     * Grid::setMapper()
     *
     * @param  $value
     * @return void
     */
    public function setMapper($value) {
        $this->_Mapper = $value;
    }

    /**
     * AbstractGrid::NewColumn()
     *
     * @param  $columnType
     * @param  $title
     * @param array $params
     * @return object AbstractGridColumn
     */
    public function NewColumn($columnType, $title, $params = array()) {
        if ($this->withNoSortableColumn) {
            $params['Sortable'] = false;
        }
        $className = 'GridColumn' . $columnType;
        loadGridComponent('Column', $className);
        $column = new $className($title, $params);
        $column->index = count($this->columns);
        $column->groupCount = 0;
        $this->columns[$column->index] = $column;
        return $column;
    }

}

?>
