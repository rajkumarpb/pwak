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
 * @version   SVN: $Id: Js.php,v 1.5 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class JsTools {
    
    /**
     * retourne la chaine $str avec les quotes backslachés.
     * 
     * @static
     * @param string $str
     * @return string    
     */
    static function JSQuoteString($str){
        return "'" . str_replace("'", "\'", $str) . "'";
    }
    
    /**
     * Converti un tableau php en tableau js.
     *
     * @static
     * @param array $items
     * @return string
     */
    static function JSArray($items){
        $return = array();
        if (is_array($items)){
            foreach($items as $key => $value){
                switch (gettype($value)){
                    case 'integer':
                        $return[$key] = $value;
                        break;
                    case 'string':
                        $return[$key] = JsTools::JSQuoteString($value);
                        break;
                    default:
                        // @todo Implement other data type
                        $return[$key] = JsTools::JSQuoteString($value);
                } // switch
            }
        }
        return '[' . implode(', ', $return) . ']';
    }
    
    /**
     * Retourne une string: declaration du tableau js equivalent a $PHParray
     *
     * @static
     * @param mixed $phpArray array
     * @param string $JSarrayName nom qu'aura le tableau js
     * @return string
     */
    static function phpToJsArray($phpArray, $JSarrayName='options') {
        $return = $JSarrayName.' = new Array();';
        foreach($phpArray as $key => $val) {
            if(!is_array($val)) {
                $return .= $JSarrayName.'["' . $key . '"] = "' . $val . '";';
            }
            else{
                $return .= JsTools::phpToJsArray($val,
                    $JSarrayName.'["'.$key.'"]');
            }
        }
        return $return;
    }
}
?>
