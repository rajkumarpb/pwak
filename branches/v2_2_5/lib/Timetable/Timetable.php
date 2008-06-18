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

define('CALENDAR_ENGINE', 'PearDate');

// doc {{{

/**
 * Timetable
 *
 * Utilisation:
 * <code>
 * $timetable = new Timetable(array(
 *     'format' => 'Daily',
 * ));
 * $timetable->addAction('Previous', array(
 *     'caption' => 'previous',
 *     'url'    => $_SERVER['PHP_SELF']
 * ));
 * $timetable->addAction('Previous', array(
 *     'caption' => 'next',
 *     'url'    => $_SERVER['PHP_SELF']
 * ));
 * $timetable->addEvent(array(
 *     'start'      => '2007-04-30 09:00:00',
 *     'end'        => '2007-04-30 12:00:00',
 *     'desc'       => 'description of the event',
 *     'background' => '#990000'
 * ));
 * $timetable->displayResult();
 * </code>
 *
 * @package Framework
 * @subpackage Timetable
 */ // }}}
class Timetable {
    // properties {{{
    
    /**
     * year 
     * 
     * @var mixed
     * @access public
     */
    public $year;
    
    /**
     * month 
     * 
     * @var mixed
     * @access public
     */
    public $month;
    
    /**
     * day 
     * 
     * @var mixed
     * @access public
     */
    public $day;
    
    /**
     * firstHour 
     * 
     * @var float
     * @access public
     */
    public $firstHour = 1;
    
    /**
     * lastHour 
     * 
     * @var float
     * @access public
     */
    public $lastHour = 24;
    
    /**
     * firstDay 
     * 
     * @var float
     * @access public
     */
    public $firstDay = 0;
    
    /**
     * actions 
     * 
     * @var array
     * @access public
     */
    public $actions = array();

    /**
     * events 
     * 
     * @var array
     * @access public
     */
    public $events = array();

    public $timetable;

    // }}}
    // Timetable::__construct() {{{

    /**
     * __construct
     *
     * paramètres:
     * - format
     * - year
     * - month
     * - day
     * - firsthour
     * - lasthour
     * - firstday
     * 
     * @param array $params 
     * @access public
     * @return void
     */
    public function __construct($params=array(), $formatOptions=array()) {
        $format = isset($params['format']) ? $params['format'] : 'Weekly';
        
        $this->year = isset($params['year']) ? $params['year'] : date('Y');
        $this->month = isset($params['month']) ? $params['month'] : date('n');
        $this->day = isset($params['day']) ? $params['day'] : date('d');
        
        if(isset($params['firsthour'])) {
            $this->firstHour = $params['firsthour'];
        }
        if(isset($params['lasthour'])) {
            $this->lastHour = $params['lasthour'];
        }
        if(isset($params['firstday'])) {
            $this->firstDay = $params['firstday'];
        }
        $this->initialize($format, $formatOptions);
    }

    // }}}
    // Timetable::initialize() {{{

    /**
     * initialize 
     * 
     * @access public
     * @return void
     */
    public function initialize($format='Weekly', $options=array()) {
        $clsname = 'TimetableFormat' . $format;
        if (!class_exists($clsname, true)) {
            $ok = @include_once CUSTOM_TIMETABLE_DIR . "/Formats/$clsname.php";
            if (!$ok) {
                throw new Exception(
                    sprintf('Unknow timetable format "%s", the class %s was not found. ',
                    $format, $clsname)
                );
            }
        }
        $this->timetable = new $clsname($this, $options);
    }

    // }}}
    // Timetable::addAction() {{{

    /**
     * addAction 
     * 
     * paramètres:
     * - name
     * - caption
     * - glyph
     * - page
     * - uri
     *
     * @param string $name nom de l'action
     * @param array $params paramètres de l'action
     * @access public
     * @return void
     */
    public function addAction($name, $params) {
        $clsname = 'TimetableAction' . $name;
        if (!class_exists($clsname, true)) {
            $ok = @include_once CUSTOM_TIMETABLE_DIR . "/Actions/$clsname.php";
            if (!$ok) {
                throw new Exception(
                    sprintf('Unknow timetable action "%s", the class %s was not found. ',
                    $name, $clsname)
                );
            }
        }
        $this->actions[] = new $clsname($this->timetable, $params);
    }

    // }}}
    // Timetable::addEvent() {{{

    /**
     * addEvent 
     * 
     * paramètres:
     * - start
     * - end
     * - purpose
     * - desc
     * - background
     *
     * @param array $params paramètres de l'événement 
     * @access public
     * @return void
     */
    public function addEvent($params) {
        $this->events[] = $params;
    }

    // }}}
    // Timetable::render() {{{

    /**
     * render 
     * 
     * @param string $template nom du template html à utiliser
     * @access public
     * @return string
     */
    public function render($title='', $template=TIMETABLE_TEMPLATE) {
        $smarty = new Template();
        
        $content = $this->timetable->render();
        $actions = array();
        foreach($this->actions as $action) {
            $actions[] = $action->render();
        }
        if(!empty($title)) {
            $smarty->assign('title', $title);
        }
        $smarty->assign('actions', $actions);
        return $smarty->fetch($template);
    }

    // }}}
}

?>
