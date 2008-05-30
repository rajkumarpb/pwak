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
 * @version   SVN: $Id: GridActionRendererImage.php,v 1.5 2008-05-30 09:23:48 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GridActionRendererImage extends GridActionRenderer {
    // properties {{{

    /**
     * Template html de l'action. 
     * 
     * @var string
     * @access public
     */
    public $actionTpl = '<a href="{HREF}" {ONCLICK} onmouseover="{ONMOUSEOVER}" title="{TITLE}">{LINK}</a>';
    
    /**
     * Template de l'image.
     * 
     * @var string
     * @access public
     */
    public $imgTpl = '<img src="{GLYPH}" alt="{TITLE}" />';
    
    /**
     * Template html de l'action à éxécuter.
     * 
     * @var string
     * @access public
     */
    public $onclickTpl = 'return fw.grid.triggerAction({FORM}, {INDEX});';

    // }}}
    // GridActionRendererImage::__construct() {{{

    /**
     * __construct 
     * 
     * @param string $form 
     * @access public
     * @return void
     */
    public function __construct($form) {
        parent::__construct($form);
        $this->type = 'Glyph';
    }

    // }}}
    // GridActionRendererImage::render() {{{

    /**
     * render 
     * 
     * @param array $actions les actions 
     * @access public
     * @return void
     */
    public function render($actions) {
        $return = array();
        foreach($actions as $anAction) {
            if ($anAction->enabled) {   
                $img = str_replace(
                    array('{GLYPH}', '{TITLE}'),
                    array($anAction->glyphEnabled, $anAction->title),
                    $this->imgTpl
                );
                $onclick = str_replace(
                    array('{FORM}', '{INDEX}'),
                    array($anAction->javascriptFormOwnerName, 
                          $anAction->index),
                    $this->onclickTpl
                );
                $result = str_replace(
                    array('{HREF}', '{ONCLICK}', '{ONMOUSEOVER}', 
                          '{TITLE}', '{LINK}'),
                    array('javascript::void(0);', $onclick, 
                          "window.status=''; return true", $anAction->caption, 
                          $img),
                    $this->actionTpl
                );
                $return[] = $result;
            } else {
                $return[] = str_replace(
                    array('{GLYPH}', '{TITLE}'),
                    array($anAction->glyphDisabled, $anAction->title),
                    $this->imgTpl
                );

            }
        }
        return $return;
    }

    // }}}
}

?>
