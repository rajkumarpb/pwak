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

class TimetableAction {
    /**
     *  
     */
    const glyphRender = '<a href="{HREF}" title="{CAPTION}" onMouseOver="window.status=\'{CAPTION}\'; return true;"><img src="{GLYPH}" border="0" /></a>';

    /**
     *  
     */
    const defaultRender = '<input type="button" onClick="window.location.href=\'{HREF}\';" name="{CAPTION}" value="{CAPTION}" />';

    /**
     * url 
     * 
     * @var mixed
     * @access protected
     */
    protected $url;

    /**
     * caption 
     * 
     * @var mixed
     * @access protected
     */
    protected $caption;

    /**
     * glyph 
     * 
     * @var mixed
     * @access protected
     */
    protected $glyph = false;

    /**
     * __construct 
     * 
     * @param array $params 
     * @access public
     * @return void
     */
    public function __construct($params=array()) {
        $this->caption = $params['caption'];
        if(isset($params['glyph'])) {
            $this->glyph = $params['glyph'];
        }
    }

    /**
     * render 
     * 
     * @access public
     * @return void
     */
    public function render() {
        $html = '';
        if($this->glyph) {
            $html = str_replace(
                array('{HREF}', '{CAPTION}', '{GLYPH}'),
                array($this->url, $this->caption, $this->glyph),
                TimetableAction::glyphRender
            );
        } else {
            $html = str_replace(
                array('{HREF}', '{CAPTION}'),
                array($this->url, $this->caption),
                TimetableAction::defaultRender
            );
        }
        return $html;
    }
}
