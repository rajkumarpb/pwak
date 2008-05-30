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
 * @version   SVN: $Id: FilterComponent.php,v 1.4 2008-05-30 09:23:48 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class FilterComponent {
    // constantes operateurs {{{

    const OPERATOR_OR  = ' OR ';
    const OPERATOR_AND = ' AND ';

    // }}}
    // propriétes {{{

    /**
     * Le tableau des éléments du filtre
     *
     * @access private
     */
    private $_items = array();

    /**
     * L'operateur du filtre
     *
     * @access public
     */
    public $operator = false;

    /**
     * Le tableau des tables pour les join
     *
     * @access public
     */
    public $tables = array();

    // }}}
    // Constructeur {{{

    /**
     * Constructeur.
     *
     * @access public
     */
     public function __construct(){
        $args = func_get_args();
        foreach($args as $currentArg) {
            if (is_string($currentArg)) { // Operator
                $this->operator = $currentArg;
                continue;
            }
            $this->setItem($currentArg);
        }
    }

    // }}}
    // FilterComponent::getItem() {{{

    /**
     * retourne l'élement à l'index $itemIndex.
     *
     * @access public
     * @param  integer $itemIndex
     * @return mixed object FilterRule ou false
     **/
    public function getItem($itemIndex){
        if (isset($this->_items[$itemIndex])){
            return $this->_items[$itemIndex];
        }
        return false;
    }

    // }}}
    // FilterComponent::setItem() {{{

    /**
     * Ajoute un élément;
     *
     * @access public
     * @param  object $item
     * @param  integer $itemIndex
     * @return integer
     */
    public function setItem($item){
        $this->_items[] = $item;
    }

    // }}}
    // FilterComponent::getCount() {{{

    /**
     * Retourne le nombre d'éléments
     *
     * @access public
     * @return integer
     */
    public function getCount(){
        return count($this->_items);
    }

    // }}}
    // FilterComponent::toSQL() {{{

    /**
     * Retourne la représentation SQL du composant de la clause where
     *
     * @access public
     * @return string
     */
    public function toSQL($stateMachine = false){
        if(0 == $this->getCount()){
            return '';
        }
        $result = /*$operatorString =*/ $padding = '';
        $count = count($this->_items);
        if ($count > 1){
            $operatorString = $this->operator;
            for($i = 0; $i < $count; $i++){
                $result .= $padding . $this->_items[$i]->toSQL($stateMachine);
                $padding = $operatorString;
            }
            return '(' . $result . ')';
        }
        return $this->_items[0]->toSQL($stateMachine);
    }

    // }}}
    // FilterComponent::collectMacros() {{{

    /**
     * FilterComponent::collectMacros()
     *
     * @return array
     **/
    public function collectMacros(){
        $result = array();
        $count = count($this->_items);
        for($i = 0; $i < $count; $i++){
            $rule = $this->_items[$i];
            $result = array_merge($result, $rule->collectMacros());
        }
        return $result;
    }

    // }}}
    // FilterComponent::isEmpty() {{{

    /**
     * Retourne true si le composant est vide et false sinon.
     *
     * @access public
     * @return integer
     */
    public function isEmpty(){
        return (0 == count($this->_items));
    }

    // }}}
}

?>
