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
 * @version   SVN: $Id: Client.php,v 1.5 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

if (!defined('AJAX_SERVER_PATH')) {
    define('AJAX_SERVER_PATH', 'ajax/index.php');
}

/**
 * AjaxClient
 * Classe de base pour les clients AJAX
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License 
 * @package Framework
 * @subpackage Ajax 
 */
class AjaxClient
{
    // propriétés {{{

    /**
     * URI du serveur AJAX/JSON
     *
     * @var    serverURI
     * @access public
     */
    public $serverURI = '';

    // }}}
    // constructeur {{{

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($params = array()) {
        if (isset($params['serverURI'])) {
            $this->serverURI = $params['serverURI'];
        } else {
            $this->serverURI = AJAX_SERVER_PATH;
        }
    }

    // }}}
    // initialize {{{

    /**
     * Initialise le javascript nécessaire aux appels ajax/json
     * Si $onlyJS vaut true, seul le code javascript est retourné sans les tags
     * html nécessaires.
     *
     * @access public
     * @param  boolean $onlyJS
     * @return string
     */
    public function initialize($onlyJS = false) {
        $code = "var AJAX_SERVER_URL = '" . $this->serverURI . "';\n";
        if ($onlyJS) {
            return $code;
        }
        return "    <!-- Required for ajax/json -->\n"
             . "    <script type=\"text/javascript\">\n"
             . "    //<![CDATA[\n"
             . "    " . $code
             . "    //]]>\n"
             . "    </script>\n";
    }
    // }}}
}

?>
