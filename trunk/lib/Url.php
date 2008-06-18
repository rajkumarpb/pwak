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

class UrlTools {

    // UrlTools::buildHiddenFieldsFromURL() {{{

    /**
     * Permet de construire une serie de champs caches a partir de vars passees
     * ds l'url.
     *
     * @static
     * @param mixed $varName string or array of strings: nom de la var passee ds l'url
     * @access public
     * @return string
     */
    static function buildHiddenFieldsFromURL($varName) {
        $varName = (is_array($varName))?$varName:array($varName);
        $HiddenChainFields = '';
        for($i = 0; $i < count($varName); $i++){
            if (!isset($_REQUEST[$varName[$i]])) {
                continue;
            }
            if (is_array($_REQUEST[$varName[$i]])) {
                foreach($_REQUEST[$varName[$i]] as $id) {
                    $HiddenChainFields .= '<input type="hidden" name="'
                        . $varName[$i] . '[]" value="' . $id . '" />';
                }
            } else {
                $HiddenChainFields .= '<input type="hidden" name="'
                    . $varName[$i].'" value="' . $_REQUEST[$varName[$i]]
                    . '" />';
            }
        }
        return $HiddenChainFields;
    }

    // }}}
    // UrlTools::buildURLFromRequest() {{{

    /**
     * Permet de construire une url a partir d'une serie de champs $_REQUEST
     * XXX rendre compliant (cf UrlTools::compliantURL())
     *
     * @static
     * @param mixed $varName string or array of string nom de la var a passer ds l'url,
     * si false: utilise $_REQUEST
     * @param boolean $mochikitFormatted: pas de crochets si valeur multiple
     * @access public
     * @return string
     */
    static function buildURLFromRequest($varName=false, $mochikitFormatted=false){
        if ($varName == false) {
            $varName = array_keys($_REQUEST);
        }
        $varName = (is_array($varName))?$varName:array($varName);
        $url = '';
        for($i = 0; $i < count($varName); $i++){
            if (!isset($_REQUEST[$varName[$i]])) {
                continue;
            }
            if (is_array($_REQUEST[$varName[$i]])) {
                foreach($_REQUEST[$varName[$i]] as $key => $id) {
                    $key = (is_string($key))?$key:'';
                    $url .= '&' . $varName[$i];
                    $url .= $mochikitFormatted?'=':'[' . $key . ']=';
                    $url .= urlencode($id);
                }
            }
            else {
                $url .= '&' . $varName[$i] . '=' . urlencode($_REQUEST[$varName[$i]]);
            }
        }
        return $url;
    }

    // }}}
    // UrlTools::checkObjectFromUrlQuery() {{{

    /**
     * Verifie des Id passes ds une url: renvoit un tableau avec les Id valides
     * ou false si aucun id n'est valide
     *
     * @static
     * @access public
     * @param string $Entity nom de l'entite
     * @param string $varName nom de la var passee ds l'url
     * @return array of integer or false
     **/
    static function checkObjectFromUrlQuery($Entity, $varName='Id'){
        $validIdArray = array();
        if (!isset($_REQUEST[$varName])) {
            return false;
        }
        if (!is_array($_REQUEST[$varName])) {
            $Object = Object::load($Entity, $_REQUEST[$varName]);
            if (Tools::isEmptyObject($Object)) {
                return false;
            }
            else return $_REQUEST[$varName];
        }
        else {
            for ($i=0;$i<count($_REQUEST[$varName]);$i++) {
                $Object = Object::load($Entity, $_REQUEST[$varName][$i]);
                if (Tools::isEmptyObject($Object)) {
                    unset($Object);
                    continue;
                }
                $validIdArray[] = $_REQUEST[$varName][$i];
                unset($Object);
            }
        }
        if (empty($validIdArray)) {
            return false;
        }
        return $validIdArray;
    }

    // }}}
    // UrlTools::compliantURL() {{{

    /**
     * Retourne une url conforme aux specs du w3c.
     * Si $escape_amperscores vaut false, le séparateur & des arguments de la
     * query string ne sera *pas* remplacé par l'entité html &amp;
     *
     * @static
     * @access public
     * @param  string $url
     * @param  boolean $escape_amperscores
     * @return string
     */
    public static function compliantURL($url, $escape_amperscores=true)
    {
        $tokens = parse_url(urldecode($url));
        $curl = '';
        // scheme (http://, ftp:// etc...)
        if (isset($tokens['scheme'])) {
            $curl .= $tokens['scheme'] . '://';
        }
        // partie user@password
        if (isset($tokens['pass']) && isset($tokens['user'])) {
            $curl .= urlencode($tokens['user']) . ':'
                   . urlencode($tokens['pass']) . '@';
        } else if (isset($tokens['user'])) {
            $curl .= urlencode($tokens['user']) . '@';
        }
        // partie host:port
        if (isset($tokens['port']) && isset($tokens['host'])) {
            $curl .= $tokens['host'] . ':';
        } else if (isset($tokens['host'])) {
            $curl .= $tokens['host'];
        }
        // path
        if (isset($tokens['path'])) {
            $arr = preg_split("/([\/;=])/", $tokens['path'], -1,
                PREG_SPLIT_DELIM_CAPTURE);
            $path = '';
            foreach ($arr as $var){
                if ($var == '/' || $var == ';' || $var == '=') {
                    $path .= $var;
                } else {
                    $path .= rawurlencode($var);
                }
            }
            // pour les user directory unix
            $curl .= str_replace('/%7E', '/~', $path);
        }
        // query string (?foo=bar&boo=moo, etc...)
        if (isset($tokens['query'])) {
            $array = preg_split("/(&amp;|[&=])/", $tokens['query'], -1,
                PREG_SPLIT_DELIM_CAPTURE);
            $curl .= '?';
            foreach ($array as $var) {
                if ('&' == $var || '&amp;' == $var) {
                    $curl .= $escape_amperscores?'&amp;':'&';
                } else if ('=' == $var) {
                    $curl .= $var;
                } else {
                    $curl .= urlencode($var);
                }
            }
        } else if (strrpos($url, '?') !== false) {
            $curl .= '?';
        }
        // fragment (#node1)
        if (isset($tokens['fragment'])) {
            $curl .= '#' . urlencode($tokens['fragment']);
        }
        // tout est ok
        return $curl;
    }

    // }}}
    // UrlTools::getPageNameFromURL() {{{

    /**
     * Renvoie le nom de la page : EntityAddEdit pour les formulaires
     * d'ajout/édition, EntityList pour les grid en fonction de l'url.
     * Se base sur l'url courante si rien en paramètre.
     *
     * @param string $url URL
     * @return string
     */
    public static function getPageNameFromURL($url=false) {
        if(!$url) {
            $uri = explode('?', $_SERVER['REQUEST_URI']);
            $url = basename($_SERVER['PHP_SELF']) . 
                (isset($uri[1])?'?' . $uri[1]:'');
        }
        $tokens = explode('.', $url);
        $pageName = '';
        // XXX hack pour GenericAddEdit/Grid
        if($tokens[0]=='dispatcher') {
            $tokens = parse_url($url);
            if(isset($tokens['query'])) {
                $tokens = preg_split("/(&amp;|[&=])/", $tokens['query'], -1,
                    PREG_SPLIT_DELIM_CAPTURE);
                $pageName = $tokens[array_search('entity', $tokens) + 2 ] . 
                    (array_search('action', $tokens)?'AddEdit':'List');
            } else {
                $pageName = (isset($_REQUEST['entity'])?$_REQUEST['entity']:'') .
                    (isset($_REQUEST['action'])?'AddEdit':'List');
            }
        } else {
            $pageName = $tokens[0];
        }
        return $pageName;
    }
    
    // }}}
}

?>
