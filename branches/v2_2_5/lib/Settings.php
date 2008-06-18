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

class Settings {
    // parseConfigFile() {{{

    /** 
     * Parse a unix-like config file and define constants for each key/value found
     *
     * @param  string $fname path to the config file
     * @return boolean true if file was parsed correctly
     * @throws Exception
     */
    public static function parseConfigFile($fname) {
        if (!file_exists($fname) || !is_readable($fname)) {
            throw new Exception("$fname must exists and must be readable.");
        }
        if (!($fp = fopen($fname, 'r'))) {
            throw new Exception("$fname cannot be opened.");
        }
        $i = 1;
        while($line = fgets($fp)){
            $line = trim($line);
            if(!empty($line) && !preg_match('/^[;\#]/', $line)){
                @list($const, $val) = explode('=', $line);
                if(isset($const) && isset($val)){
                    $val = explode('#', $val);
                    $val = trim($val[0]);
                    if (strtolower($val) == 'true') {
                        $val = true;
                    } else if(strtolower($val) == 'false') {
                        $val = false;
                    }
                    if(!defined($const)){
                        define("$const", $val);
                    }
                    if (strpos($const,'DSN_') === 0 || $const == 'DB_DSN') {
                        $GLOBALS['DSNS'][] = $const;
                    }
                } else {
                    throw new Exception("Syntax error in $fname on line $i.");
                }
            }
            $i++;
        }
        return true;
    }

    // }}}
}

?>
