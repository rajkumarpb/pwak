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

class StateMachine {
    // propriétés private/protected {{{

    /**
     * Etat de départ de la machine à état.
     *
     * @access private
     * @var    object StateMachineNode _startState
     */
    private $_startState = null;

    /**
     * Tableau des macros pour la clause select.
     *
     * @access private
     * @var    array _selectMacros
     */
    private $_selectMacros = array();

    /**
     * Tableau des macros pour la clause where.
     *
     * @access private
     * @var    array _filterMacros
     */
    private $_filterMacros = array();

    /**
     * Tableau des macros pour la clause orderby.
     *
     * @access private
     * @var    array _orderMacros
     */
    private $_orderMacros = array();

    // }}}
    // Constructeur {{{

    /**
     * Constructor
     *
     */
    public function __construct($entity, $selectMacros, $filterMacros=array(),
        $orderMacros=array()) {
        $this->_orderMacros = $orderMacros;
        $this->_filterMacros = $filterMacros;
        $this->_selectMacros = $selectMacros;
        $this->entity = $entity;
        if (is_array($orderMacros)) {
            $orderMacros = array_keys($orderMacros);
        }
        $this->_initializeFromMacros(
            array_merge($selectMacros, $filterMacros, $orderMacros)
        );
    }

    // }}}
    // StateMachine::toSQL() {{{

    /**
     * Retourne la requête sql construite.
     *
     * @param  object FilterComponent $filter
     * @return string
     **/
    public function toSQL($filter) {
        $where = '';
        $tables = array();
        if ($filter instanceof FilterComponent && !$filter->isEmpty()) {
            $tables = $filter->tables;
            $whereAddon = $filter->toSQL($this);
            $padding = empty($whereAddon)?'':' AND ';
            $where = $whereAddon . $this->_startState->getWhereClause($padding);
        } else {
            $where = $this->_startState->getWhereClause($padding);
        }
        if (!empty($where)) {
            $where = 'WHERE ' . $where;
        }
        if (defined('DATABASE_ID')) {
            if(!Object::isPublicEntity($this->entity)) {
                if (!empty($where)) {
                    $where .= ' AND (T0._DBId IS NULL OR T0._DBId=' . DATABASE_ID . ')';
                } else {
                    $where .= 'WHERE T0._DBId IS NULL OR T0._DBId=' . DATABASE_ID ;
                }
            }
        }
        // clause select
        $select = '';
        $padding = '';
        foreach($this->_selectMacros as $macro) {
            $select .= $padding . $this->getPropertyName($macro, true);
            $padding = ', ';
        }
        // clause from
        $tables = $this->_getFromClause($this->_startState, $tables);
        $from = '';
        $padding = '';
        foreach($tables as $alias=>$table) {
            $from .= $padding . $table . ' ' . $alias;
            $padding = ', ';
        }
        // clause order
        $order = '';
        $padding = '';
        foreach($this->_orderMacros as $macro=>$sortOrder) {
            $name = $this->getPropertyName($macro) ;
            if (!empty($name)) {
                $order .= $padding . $name . ' ' .
                    (($sortOrder == SORT_DESC)?'DESC':'ASC');
                $padding = ', ';
            }
        }
        if (!empty($order)) {
            $order = ' ORDER BY ' . $order;
        }
        // clause complète
        return 'SELECT ' . $select . ' FROM ' . $from . ' ' . $where . $order;
    }

    // }}}
    // StateMachine::getPropertyName() {{{

    /**
     * Retourne le nom d'un champs de table pour la requête.
     *
     * @access public
     * @param  string $macro
     * @param  bool $forSelectClause
     * @return string
     */
    public function getPropertyName($macro, $forSelectClause=false) {
        if ($macro == 'DISTINCT(Id)') {
            $macro = 'Id';
        }
        $tokens = explode('.', $macro);
        $token = array_pop($tokens);
        $curState = $this->_startState;
        foreach($tokens as $tok) {
            $curState = $curState->transitions[$tok];
        }
        if(!$forSelectClause && isset($curState->transitions[$token])) {
            $obj = $curState->transitions[$token];
            if($obj instanceof StateMachineNode && $obj->className == 'I18nString') {
                $curState = $obj;
                $token = 'StringValue_' . I18n::getLocaleCode();
            }
        }
        $token = ($token == '*')?'.*':'._' . $token;
        return 'T' . $curState->id . $token;
    }

    // }}}
    // StateMachine::_initializeFromMacros {{{

    /**
     * Construit un automate à état à partir d'une liste de macros
     *
     * @access private
     * @param  array $macros
     * @return void
     */
    private function _initializeFromMacros($macros) {
        $pathNodes = array();
        $this->_startState = new StateMachineNode($this->entity, true);
        foreach($macros as $macro) {
            $curState = $this->_startState;
            $curEntity = $this->entity;
            $macroTokens = explode('.', $macro);
            if (is_array($macroTokens) && (count($macroTokens) > 1)) {
                $curPath = '';
                foreach($macroTokens as $macroToken) {
                    $curPath .= '.' . $macroToken;
                    $curEntity = Registry::getPropertyClassname($curEntity, $macroToken);
                    if (isset($pathNodes[$curPath])) {
                        $nextState = $pathNodes[$curPath];
                    } else {
                        $nextState = new StateMachineNode($curEntity);
                        $pathNodes[$curPath] = $nextState;
                        $curState->transitions[$macroToken] = $nextState;
                    }
                    $curState = $nextState;
                }
            } else {
                $curPath = '.' . $macro;
                $curEntity = Registry::getPropertyClassname($curEntity, $macro);
                if (!isset($pathNodes[$curPath])) {
                    $nextState = new StateMachineNode($curEntity);
                    $pathNodes[$curPath] = $nextState;
                    $curState->transitions[$macro] = $nextState;
                }
            }
            if(is_string($curEntity) && $curEntity == 'I18nString') {
                $curState = $nextState;
                $macroToken = 'StringValue_' . I18n::getLocaleCode();
                $curPath .= '.' . $macroToken;
                $curEntity = Registry::getPropertyClassname($curEntity, $macroToken);
                if (isset($pathNodes[$curPath])) {
                    $nextState = $pathNodes[$curPath];
                } else {
                    $nextState = new StateMachineNode($curEntity);
                    $pathNodes[$curPath] = $nextState;
                    $curState->transitions[$macroToken] = $nextState;
                }
            }
        }
    }

    // }}}
    // StateMachine::_getFromClause() {{{

    /**
     * Ajoute à la liste des tables $table les transitions correspondant à
     * l'automate.
     *
     * @access private
     * @param StateMachineNode $StateMachineNode Le noeud à gérer
     * @param array $tables Tableau associatif dans lequel les nouveaux alias
     * sont ajoutés. Les clefs sont les alias de table et les valeurs les noms
     * de tables correspondantes.
     * @return array
     */
    private function _getFromClause($StateMachineNode, $tables=array()) {
        if (empty($StateMachineNode->transitions)) {
            return $tables;
        }
        require_once(MODELS_DIR . '/' . $StateMachineNode->className . '.php');
        $tables['T' . $StateMachineNode->id] =  call_user_func(
                array($StateMachineNode->className, 'getTableName'));
        $nodeTransitions = $StateMachineNode->transitions;
        foreach($nodeTransitions as $currentNode) {
            if (false != $currentNode) {
                $tables = $this->_getFromClause($currentNode, $tables);
            }
        }
        return $tables;
    }

    // }}}
}

?>
