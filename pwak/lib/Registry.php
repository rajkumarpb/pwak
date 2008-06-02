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

class Registry
{
    // Memory cache {{{

    /**
     * A simple memory cache to put in cache the results of 
     * Registry::getPropertyClassname().
     *
     * @var array cache
     * @access protected
     */
    protected static $cache = array();

    // }}}
    // Constructeur {{{

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct(){
    }

    // }}}
    // Registry::getPropertyClassname() {{{

    /**
     * Retourne le nom de la classe associee a la propriete $p de l'entite $e
     *
     * @access public
     * @static
     * @param  string $e le nom de l'entité
     * @param  string $p le nom de la propriété
     * @return string
     */
    public static function getPropertyClassname($e, $p) {
        // if result is in cache simply return it
        if (isset(self::$cache[$e][$p])) {
            return self::$cache[$e][$p];
        }
        if($e==Object::TYPE_I18N_STRING || $e==Object::TYPE_I18N_TEXT
          || $e==Object::TYPE_I18N_HTML) 
        {
            $e = 'I18nString';
        }
        require_once(MODELS_DIR . '/' . $e.'.php');
        $properties = call_user_func(array($e, 'getProperties'));
        $ret = isset($properties[$p])?$properties[$p]:0;  // $p
        if($ret==Object::TYPE_I18N_STRING || $ret==Object::TYPE_I18N_TEXT
          || $e==Object::TYPE_I18N_HTML) 
        {
            $ret = 'I18nString';
        }
        // put the result in the cache array
        self::$cache[$e][$p] = $ret;
        return $ret;
    }

    // }}}
    // Registry::getPropertyTableName() {{{

    /**
     * Retourne la table associee a la propriete $p de l'entite $e
     *
     * @access public
     * @static
     * @param  string $e le nom de l'entité
     * @param  string $p le nom de la propriété
     * @return string
     **/
    public static function getPropertyTableName($e, $p)
    {
        $p = Registry::getPropertyClassname($e, $p);
        require_once(MODELS_DIR . '/' . $p.'.php');
        return call_user_func(array($p, 'getTableName'));
    }

    // }}}
}

?>
