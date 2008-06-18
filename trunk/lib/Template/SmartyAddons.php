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

function hour_minute_widget($params){
    extract($params);
    if (isset($type)) {
        if ($type == 1) {
            $value = DateTimeTools::timeStampToMySQLDate($value);
        } elseif ($type == 2) {
            $value = DateTimeTools::hundredthsOfHourToTime($value);
        }
    }
    $disabled = isset($disabled)?' disabled':'';
    $tokens = explode(':', $value);
    $hours  = '00';
    $mins   = '00';
    $secs   = false;
    
    if (count($tokens) >= 2) {
        $hours = $tokens[0];
        $mins  = $tokens[1];
        $secs  = isset($tokens[2])?$tokens[2]:false;
    }
    printf('<input type="text" id="%s" name="%s_Hours" value="%s" size="2"%s>', 
        $name, $name, $hours, $disabled);
    echo '&nbsp;:&nbsp;';
    printf('<input type="text" id="%s" name="%s_Minutes" value="%s" size="2"%s>', 
        $name, $name, $mins, $disabled);
    if ($secs) {
        echo '&nbsp;:&nbsp;';
        printf(
            '<input type="text" id="%s" name="%s_Seconds" value="%s" size="2"%s>', 
            $name, $name, $secs, $disabled);
    }
}

/**
 * smarty register function to {t} tag.
 * utilisation : $smarty->register_block("t", "gettextize");
 * 
 * @param $params
 * @param string $content
 * @param $smarty
 * @access public
 * @return string
 */
function gettextize($params, $content, $smarty)
{
    if ($content) {
        echo _($content);
    }
}
?>
