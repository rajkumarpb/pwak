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
 * @version   SVN: $Id: TimetableFormatDaily.php,v 1.5 2008-05-30 09:23:49 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

require_once 'Calendar/Day.php';

/**
 * TimetableFormatDaily
 *
 * @package Framework
 * @subpackage TimetableFormats
 */
class TimetableFormatDaily extends TimetableDecoratorTextualLang {
    // properties {{{
    public $format = 'Daily';
    
    /**
     * timetable 
     * 
     * @var mixed
     * @access protected
     */
    protected $timetable;

    // }}}
    // TimetableFormatDaily::__construct() {{{
    
    /**
     * __construct 
     * 
     * @param mixed $timetable 
     * @access public
     * @return void
     */
    public function __construct($timetable, $options=array()) {
        $day = new Calendar_Day($timetable->year, $timetable->month, $timetable->day);
        parent::__construct($day);
        $this->timetable = $timetable;
        
        require_once CALENDAR_ROOT . 'Hour.php';
        require_once CALENDAR_ROOT .  'Table/Helper.php';

        $this->cE = $this->getEngine();
        $this->year  = $this->thisYear();
        $this->month = $this->thisMonth();
        $this->day = $this->thisDay();
    }

    // }}}
    // TimetableFormatDaily::setSelection() {{{

    /**
     * setSelection 
     * 
     * @access public
     * @return void
     */
    public function setSelection() {
        $hoursInDay = $this->cE->getHoursInDay();
        for ($i=1 ; $i<=$hoursInDay ; $i++) {
            $hour = new Calendar_Hour(2000,1,1,1); // Create Day with dummy values
            $hour->setTimestamp($this->cE->dateToStamp(
                $this->year, $this->month, $this->day, $i)
            );
            $this->children[$i] = new TimetableEvent($hour);
            $stamp1 = $this->cE->dateToStamp(
                $this->year, $this->month, $this->day, $i);
            $stamp2 = $this->cE->dateToStamp(
                $this->year, $this->month, $this->day, $i + 1);
            foreach ($this->timetable->events as $event) {
                if (($stamp1 >= $event['start'] && $stamp1 < $event['end']) ||
                    ($stamp2 > $event['start'] && $stamp2 <= $event['end']) ||
                    ($stamp1 <= $event['start'] && $stamp2 >= $event['end'])
                ) {
                    $this->children[$i]->addEntry($event);
                    $this->children[$i]->setSelected();
                }
            }
        }
    }

    // }}}
    // TimetableFormatDaily::fetch() {{{

    /**
     * fetch 
     * 
     * @access public
     * @return void
     */
    public function fetch() {
        $child = each($this->children);
        if ($child) {
            return $child['value'];
        } else {
            reset($this->children);
            return false;
        }
    }

    // }}}
    // TimetableFormatDaily::render() {{{

    /**
     * render 
     * 
     * @access public
     * @return void
     */
    public function render() {
        $this->setSelection();
        $header = array(array(), array('label'=>I18N::formatDate(
            mktime(0,0,0, $this->month, $this->day, $this->year), 
            I18N::DATE_LONG_TEXTUAL))
        );
        $rows = array();
        while ($e = $this->fetch()) {
            $hour = $e->thisHour();
            $minute = $e->thisMinute();

            if ( $hour >= $this->timetable->firstHour && $hour <= $this->timetable->lastHour ) {
                $row = array('hour' => $hour.':'.$minute);
                if ($e->isSelected()) {
                    $cell = array(
                        'class' => 'timetable_calCell timetable_calCellBusy',
                        'busy'  => 1
                    );
                } elseif ($e->isEmpty()){
                    $cell = array(
                        'class' => 'timetable_calCell timetable_calCellEmpty',
                        'busy'  => 0
                    );
                }
                if (!$e->isEmpty()) {
                    $cell['popup'] = '';
                    while ($entry = $e->getEntry()) {
                        $cell['events'][] = $entry;
                    }
                }
                $row['cells'][] = $cell;
                $rows[] = $row;
            }
        }
        $smarty = new Template();
        $smarty->assign('header', $header);
        $smarty->assign('rows', $rows);
    }

    // }}}
}

?>
