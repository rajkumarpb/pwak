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

/**
 * Ensemble de méthodes pour le dispatching des urls.
 * Méthodes statiques uniquement.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package Framework
 */
class Dispatcher {
    // Dispatcher::dispatch() {{{

    /**
     * Dispatche la reqûete http en fonction de l'url vers les composants
     * génériques addedit/delete/grid.
     *
     * @static
     * @throws Exception
     * @return void
     */
    static function dispatch() {
        $retURL = isset($_REQUEST['retURL'])?$_REQUEST['retURL']:'home.php';
        if (!isset($_REQUEST['entity'])) {
            // pas assez de paramètres
            // No page corresponds to the request.
            throw new Exception(E_NO_PAGE_FOUND);
        }
        if (!isset($_REQUEST['altname'])) {
            $_REQUEST['altname'] = $_REQUEST['entity'];
        }
        if (isset($_REQUEST['action']) &&
            in_array($_REQUEST['action'], array('add', 'edit', 'del', 'view'))) {
            $customName = $_REQUEST['altname'] . 'AddEdit';
            $customFileName = CUSTOM_CONTROLLER_DIR.'/'.$customName.'.php';
            if (file_exists(PROJECT_ROOT . '/' . LIB_DIR . '/' . $customFileName)) {
                require_once($customFileName);
                $class = $customName;
            } else {
                $class = 'GenericAddEdit';
            }
            $objID = isset($_REQUEST['objID'])?$_REQUEST['objID']:0;
            $addedit = new $class(
                array(
                    'clsname' => $_REQUEST['entity'],
                    'altname' => $_REQUEST['altname'],
                    'action' => $_REQUEST['action'],
                    'id' => $objID,
                    //'profiles' => array(PROFILE_ADMIN, PROFILE_AERO_ADMIN),
                    'url' => sprintf(
                        $_SERVER['SCRIPT_NAME']
                        . '?action=%s&entity=%s&altname=%s&objID=%s',
                        $_REQUEST['action'], $_REQUEST['entity'],
                        $_REQUEST['altname'], $objID
                    )
                )
            );
            $addedit->render();
        } else {
            $customName = $_REQUEST['altname'] . 'Grid';
            $customFileName = CUSTOM_CONTROLLER_DIR.'/'.$customName.'.php';
            if (file_exists(PROJECT_ROOT . '/' . LIB_DIR . '/' . $customFileName)) {
                require_once($customFileName);
                $class = $customName;
            } else {
                $class = 'GenericGrid';
            }
            $params = array(
                'clsname'=>$_REQUEST['entity'],
                'altname'=>$_REQUEST['altname']
            );
            $grid = new $class($params);
            $grid->render();
        }
    }

    // }}}
    // Dispatcher::parseSmartURL() {{{

    /**
     * Parse une url du type 'http://example.com/index.php/foo/bar/baz/' et
     * retourne un tableau contenant les morceaux de l'url, ici:
     * array('foo', 'bar', 'baz')
     * XXX pas utilisé pour l'instant
     *
     * @static
     * @param string $url url à parser
     * @return array
     */
    static function parseSmartURL($url) {
        // découpe l'url à partir du fichier dispatcher '.php'
        $tokens = explode('.php/', $url);
        if (count($tokens) != 2) {
            // hum, pas de fichier php, on a peut-être utilisé mod_rewrite pour
            // avoir une url du style: http://example.com/foo/bar/baz
            // donc on se base sur tout ce qu'il y a après le ndd
            $url = preg_replace('/(https?:\/\/)?[\w\.]+\/(.+)/', '\\2', $url);
        } else {
            $url = $tokens[1];
        }
        // enlever le (ou les) begining/trailing slash s'il y en a
        $url = trim($url, '/');
        // retourne le tableau des paramètres
        return explode('/', $url);
    }

    // }}}
}
?>
