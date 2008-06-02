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

class FilterRule {
    // constantes operateurs {{{

    const OPERATOR_EQUALS                 = ' = ';
    const OPERATOR_NOT_EQUALS             = ' != ';
    const OPERATOR_GREATER_THAN           = ' > ';
    const OPERATOR_GREATER_THAN_OR_EQUALS = ' >= ';
    const OPERATOR_LOWER_THAN             = ' < ';
    const OPERATOR_LOWER_THAN_OR_EQUALS   = ' <= ';
    const OPERATOR_LIKE                   = ' LIKE ';
    const OPERATOR_NOT_LIKE               = ' NOT LIKE ';
    const OPERATOR_IN                     = ' IN ';
    const OPERATOR_NOT_IN                 = ' NOT IN ';
    const OPERATOR_BETWEEN                = ' BETWEEN ';
    const OPERATOR_NOT_BETWEEN            = ' NOT BETWEEN ';
    const OPERATOR_IS_NULL                = ' IS NULL';
    const OPERATOR_IS_NOT_NULL            = ' IS NOT NULL';

    // }}}
    // propriétes {{{

    /**
     *
     * @access private
     * @var    string _leftMember
     */
    private $_leftMember = false;

    /**
     *
     * @access private
     * @var    string _rightMember
     */
    private $_rightMember = false;

    /**
     * L'operateur du filtre
     *
     * @access public
     * @var    const operator
     */
    private $operator = false;

    // }}}
    // Constructeur {{{

    /**
     * FilterRule::__construct()
     *
     * @access public
     * @return void
     **/
    public function __construct($leftMember=null, $operator=null, $rightMember=null) {
        $this->_leftMember = $leftMember;
        $this->operator = $operator;
        $this->_rightMember = $rightMember;
    }

    // }}}
    // FilterRule::toSQL() {{{

    /**
     * FilterRule::toSQL()
     *
     * @return string
     */
    public function toSQL($fsm = false) {
        return '(' 
            . $this->_formatLeftMember($this->_leftMember, $fsm)
            . $this->operator
            . $this->_formatRightMember($this->_rightMember)
            . ')';
    }

    // }}}
    // FilterRule::collectMacros() {{{

    /**
     * FilterRule::collectMacros()
     *
     * @access public
     * @return array
     */
    public function collectMacros() {
        if ($this->_leftMember[0] == '!')
            return array();
        return array($this->_leftMember);
    }

    // }}}
    // FilterRule::_formatLeftMember() {{{

    /**
     * Retourne la valeur formatée.
     *
     * @access private
     * @return mixed
     */
    private function _formatLeftMember($data, $fsm = false) {
        if (isset($data[0]) && $data[0] == '!')
            return substr($data, 1);
        return $fsm->getPropertyName($data);
    }

    // }}}
    // FilterRule::_formatRightMember() {{{

    /**
     * Retourne la valeur formatée.
     *
     * @access private
     * @return mixed
     */
    private function _formatRightMember($data){
        if ($this->operator == FilterRule::OPERATOR_IS_NULL
         || $this->operator == FilterRule::OPERATOR_IS_NOT_NULL) {
            return '';
        }
        $type = gettype($data);
        switch ($type) {
            case 'NULL':
                return 'NULL';
            case 'boolean':
            case 'integer':
                return intval($data);
            case 'double':
                return doubleval($data);
            case 'float':
                return floatval($data);
            case 'string':
                if (isset($data[0]) && $data[0] == '!')
                    return substr($data, 1);
                return "'" . addslashes($data) . "'";
            case 'object':
                if (method_exists($data, 'getId')) {
                    return intval($data->getId());
                }
                return 'NULL';
            case 'array':
                if ($this->operator == self::OPERATOR_BETWEEN ||
                    $this->operator == self::OPERATOR_NOT_BETWEEN) {
                    if (count($data) != 2) break;
                    return sprintf(
                        '%s AND %s',
                        $this->_formatRightMember($data[0]),
                        $this->_formatRightMember($data[1])
                    );
                }
                if (empty($data)) {
                    return ' (NULL)';
                }
                $dataArray = $data;
                if ((isset($dataArray[0]) && is_string($dataArray[0]))
                || (isset($dataArray[1]) && is_string($dataArray[1]))) {
                    foreach($dataArray as $i => $item){
                        $dataArray[$i] = "'" . addslashes($item) . "'";
                    }
                }
                return "(" . implode(",", $dataArray) . ")";
            default:
                break;
        }
        trigger_error('FilterRule: invalid datatype: ' . $type, E_USER_ERROR);
    }

    // }}}
}

?>
