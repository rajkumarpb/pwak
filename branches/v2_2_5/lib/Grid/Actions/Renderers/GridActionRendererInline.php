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

class GridActionRendererInline extends GridActionRenderer {
    public $objID;

    public $actionTpl = '<input type="button" value="{CAPTION}" title="{TITLE}" {ONCLICK} class="{CLASS}" name="{NAME}" />';

    public function __construct($form, $objID) {
        parent::__construct($form);
        $this->type = 'Inline';

        $this->objID = $objID;
    }

    public function render($actions) {
        $actions = (!is_array($actions))?array($actions):$actions;
        $return = '';
        foreach($actions as $anAction) {
            if($anAction->renderer != 'Inline') {
                continue;
            }
            $onclick = 'onclick="window.location=\''.str_replace('%d', $this->objID, $anAction->_URL).'\';"';
            $result = str_replace(
                array('{CAPTION}', '{TITLE}', '{ONCLICK}', '{CLASS}', 
                        '{NAME}'),
                array($anAction->caption, $anAction->title, 
                        $onclick, 'button', 'button_'.$anAction->index),
                $this->actionTpl
            );
            $return .= '&nbsp;'.$result;
        }
        return $return;
    }
}
?>
