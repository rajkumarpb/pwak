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
 * @version   SVN: $Id: NavigationRendererDefault.php,v 1.5 2008-05-30 09:23:48 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class NavigationRendererDefault
{
    // properties {{{

    /**
     * The initial indent.
     * 
     * @var    string $indent
     * @access public
     */
    public $initialIndent = '    ';

    /**
     * The indent string (default is 4 spaces).
     * 
     * @var    string $indent
     * @access public
     */
    public $indent = '    ';

    /**
     * The opening html code of a navigation level.
     * 
     * @var    string
     * @access public
     */
    public $openTpl = '<ul class="nav" id="nav_{level}">';

    /**
     * The closing html code of a navigation level.
     * 
     * @var    string
     * @access public
     */
    public $closeTpl = '</ul>';

    /**
     * The template of an active element.
     * 
     * @var    string
     * @access public
     */
    public $activeTpl = '{indent}<li class="nav_active{first}"><a href="{link}" title="{desc}"{akey}{onclick}><span>{title}</span></a></li>';

    /**
     * The template of an inactive element.
     * 
     * @var    string
     * @access public
     */
    public $inactiveTpl = '{indent}<li{first}><a href="{link}" title="{desc}"{akey}{onclick}><span>{title}</span></a></li>';

    // }}}
    // NavigationRendererDefault::render() {{{

    /**
     * Render a navigation element.
     * 
     * @access public
     * @param  integer $level the navigation level
     * @param  integer $index the element index
     * @param  array $data the element data
     * @param  boolean $useCookie
     * @return string
     */
    public function render($level, $index, $data)
    {
        if ($index === 0) {
            $first = $data['active']?' nav_first':' class="nav_first"';
        } else {
            $first = '';
        }
        $onclick = $data['onclick']?' onclick="'.$data['onclick'].'"':'';
        return $this->initialIndent . str_replace(
            array(
                '{indent}',
                '{first}',
                '{link}',
                '{desc}',
                '{akey}',
                '{title}',
                '{onclick}'
            ),
            array(
                $this->indent,
                $first,
                $data['link'],
                $data['description'],
                $data['accesskey']==''?'':' accesskey="'.$data['accesskey'],
                $data['title'],
                $onclick
            ),
            $data['active']?$this->activeTpl:$this->inactiveTpl
        );
    }

    // }}}
    // NavigationRendererDefault::open() {{{

    /**
     * Render the opening block of the element
     * 
     * @access public
     * @param  integer $level the navigation level
     * @return string
     */
    public function open($level)
    {
        return $this->initialIndent . str_replace(
            array('{indent}', '{level}'),
            array($this->indent, $level),
            $this->openTpl
        );
    }

    // }}}
    // NavigationRendererDefault::close() {{{

    /**
     * Render the closing block of the element
     * 
     * @access public
     * @param  integer $level the navigation level
     * @return string
     */
    public function close($level)
    {
        return $this->initialIndent . str_replace(
            '{indent}',
            $this->indent,
            $this->closeTpl
        );
    }

    // }}}
} 

?>
