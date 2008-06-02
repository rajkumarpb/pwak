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

class GridActionRendererButton extends GridActionRenderer {
    // properties {{{

    /**
     * Template html de l'action 
     * 
     * @var string
     * @access public
     */
    public $actionTpl = '<input type="button" value="{CAPTION}" title="{TITLE}" {ONCLICK} class="{CLASS}" name="{NAME}" />';

    /**
     * Template html de l'action à éxécuter.
     * 
     * @var string
     * @access public
     */
    public $onclickTpl = 'return fw.grid.triggerAction({FORM}, {INDEX});';

    // }}}
    // GridActionRendererButton::__construct() {{{

    /**
     * __construct 
     * 
     * @param string $form nom du formulaire
     * @access public
     * @return void
     */
    public function __construct($form) {
        parent::__construct($form);
        $this->type = 'Button';
    }

    // }}}
    // GridActionRendererButton::render() {{{

    /**
     * Créé les boutons d'action.
     *
     * Si une action à une méthode GridActionRendererButton, c'est cette méthode qui est 
     * appelé pour obtenir le code html du bouton. 
     * 
     * @param array $actions tableau eds actions
     * @access public
     * @return void
     */
    public function render($actions) {
        $actions = (!is_array($actions))?array($actions):$actions;
        $return = array();
        foreach($actions as $anAction) {
            if ($anAction->enabled) {
                /*$onclick = str_replace(
                    array('{FORM}', '{INDEX}'),
                    array($anAction->javascriptFormOwnerName, 
                          $anAction->index),
                    $this->onclickTpl
                );*/
                $onclick = $anAction->buildOnClick();
                $result = str_replace(
                    array('{CAPTION}', '{TITLE}', '{ONCLICK}', '{CLASS}', 
                          '{NAME}'),
                    array($anAction->caption, $anAction->title, 
                          $onclick, 'button', 'button_'.$anAction->index),
                    $this->actionTpl
                );
                $return[] = $result;
            }
        }
        return $return;
    }

    // }}}
}

?>
