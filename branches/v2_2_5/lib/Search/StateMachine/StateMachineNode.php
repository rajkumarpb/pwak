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

class StateMachineNode {
    // propriétés publiques {{{

    /**
     * L'id unique du noeud
     *
     * @access public
     * @var    integer id
     */
    public $id = 0;

    /**
     * Le nom de la classe associée
     *
     * @access public
     * @var    string className
     */
    public $className = '';

    /**
     * Le tableau des transitions du noeud
     *
     * @access public
     * @var    array transitions
     */
    public $transitions = array();

    // }}}
    // Constructeur {{{

    /**
     * Constructor
     *
     * @access public
     * @param string className
     * @param boolean resetIncrement
     */
    public function __construct($className, $resetIncrement=false) {
        if (is_int($className)) {
            return true;
        }
        static $nodeID = 0;
        if ($resetIncrement) {
            $nodeID = 0;
        }
        $this->id = $nodeID++;
        $this->className = $className;
    }

    // }}}
    // StateMachineNode::getWhereClause() {{{

    /**
     * Retourne la clause where du noeud.
     * Attention: le préfixe est modifié *par référence* donc il ne faut pas
     * enlever le & dans getWhereClause($prefix)
     *
     * @param $prefix
     * @access public
     * @return string
     */
    public function getWhereClause(&$prefix) {
        $result = '';
        $aliasName = 'T' . $this->id;
        foreach($this->transitions as $transition => $nextState) {
            $result .= $nextState->getWhereClause($prefix);
            if (!empty($nextState->transitions)) {
                $result .= sprintf('%s(%s._%s = %s._Id)', $prefix,
                        $aliasName, $transition, 'T' . $nextState->id);
                $prefix = ' AND ';
            }
        }
        return $result;
    }

    // }}}
}

?>