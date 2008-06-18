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

class SubGrid extends AbstractGrid {
    /**
     * Constructor
     *
     * @access protected
     */
    function __construct()
    {
        parent::__construct();
        $this->itemPerPage = 1000000;
    }

    function getDataCollection($entityName, $ordre, $filtre = array())
    {
        $PageIndex = isset($_REQUEST['PageIndex'])?$_REQUEST['PageIndex']:0;
        if ($entityName instanceof Collection) {
            //On a deja une collection, on l'utilise tel qu'elle.
            return $entityName;
        }
        if ($entityName instanceof Mapper) {
            $aMapper = $entityName;
        }
        elseif (is_string($entityName)) {
            /**
             * On a un nom d'objet, on cree le mapper associé et on
             * l'utilise pour charger une collection d'objets
             */
            $aMapper = Mapper::singleton($entityName);
        }
        else {
            return new Exception('$aMapper is not OK');
        }
        $aCollection = $aMapper->loadCollection($filtre, $ordre);

        return $aCollection;
    }

    /**
     * Grid::render()
     *
     * @param mixed $aMapper - Collection: On utilise la collection pour le rendu
     *   - Mapper: On effectue un LoadObjectCollection pour récupérer une
     *              collection qui sera utilisée pour le rendu
     *   - string: On crée le mapper correspondant au nom de l'objet donné
     *              (Ex: ActivatedChainTask) qui est utilisé comme en 2.
     * @param boolean $pager
     * @param array $filtre
     * @param array $ordre
     * @param string $templateName
     * @return string
     */
    function render($aMapper, $filtre=array(), $ordre=array(), $templateName=SUBGRID_TEMPLATE)
    {
        $aCollection = $this->getDataCollection($aMapper, $ordre, $filtre);
        if (Tools::isException($aCollection)) {
            return new Exception('Grid::render called with bad first param.');
        }

        foreach($this->columns as $column) {
            $attributeName = isset($column->_Macro)?$column->_Macro:false;
            $attributeName = str_replace('%', '', strip_tags($attributeName));
            $toks = explode("|", $attributeName);
            if (false != $toks) {
                $attributeName = $toks[0];
            }
            if (substr_count($attributeName, ".") >= 1) {
                $attributeName = ucfirst($attributeName);
            }
        }

        $gridContent = array();
        $count = $aCollection->getCount();
        for($i = 0; $i < $count; $i++) {
            $objectInstance = $aCollection->getItem($i);
            $gridContent[$i] = array();
            foreach($this->columns as $column) {
                $cellContent = $column->render($objectInstance);
                $gridContent[$i][] = get_class($cellContent)=='exception'?'N/A':$cellContent;
            }
        }
        $smarty = new Template();
         // Pour permettre d'afficher un message si pas d''element a afficher!!!
        $smarty->assign('NbColumn', ($aCollection->getCount() > 0)?count($this->columns):0);
        // sert a mettre le bon colspan si pas d''element a afficher!!!
        $smarty->assign('NbColumnIfEmpty', count($this->columns));
        $smarty->assign('SubGridRow', $gridContent);

       return $smarty->fetch($templateName);
    }
}
?>
