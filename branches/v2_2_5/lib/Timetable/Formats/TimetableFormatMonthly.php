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

require_once 'Calendar/Month/Weekdays.php';

/**
 * TimetableFormatMonthly
 *
 * @package Framework
 * @subpackage TimetableFormats
 */
class TimetableFormatMonthly extends TimetableDecoratorTextualLang {
    // properties {{{
    public $format = 'Monthly';

    /**
     * timetable 
     * 
     * @var Timetable
     * @access protected
     */
    protected $timetable;

    protected $clickOnDayUrl = false;

    protected $hoursInEvents = false;
    
    // }}}
    // TimetableFormatMonthly::__construct() {{{

    /**
     * __construct 
     *
     * options:
     * - clickondayurl: url de l'action onclick sur click sur un jour.
     * - hoursinevents: true pour afficher l'heure de l'événement devant ca 
     * description.
     *
     * @param Timetable $timetable un objet Timetable
     * @param array $options
     * @access public
     * @return void
     */
    public function __construct($timetable, $options=array()) {
        $month = new Calendar_Month_Weekdays($timetable->year, 
            $timetable->month, $timetable->firstDay);
        parent::__construct($month);

        $this->timetable = $timetable;
        require_once CALENDAR_ROOT . 'Day.php';
        require_once CALENDAR_ROOT .  'Table/Helper.php';

        $this->tableHelper = new Calendar_Table_Helper($this, $timetable->firstDay);
        $this->cE = $this->getEngine();
        $this->year = $timetable->year;
        $this->month = $timetable->month;
        $this->day = 1;
        
        if(isset($options['clickondayurl'])) {
            $this->clickOnDayUrl = $options['clickondayurl'];
        }
        if(isset($options['hoursinevents'])) {
            $this->hoursInEvents = $options['hoursinevents'];
        }
    }

    // }}}
    // TimetableFormatMonthly::setSelection() {{{

    /**
     * setSelection 
     * 
     * @access public
     * @return void
     */
    public function setSelection() {
        $daysInMonth = $this->cE->getDaysInMonth(
            $this->year, $this->month);
        for ($i=1 ; $i<=$daysInMonth ; $i++) {
            $day = new Calendar_Day(2000,1,1); // Create Day with dummy values
            $day->setTimeStamp($this->cE->dateToStamp(
                $this->year, $this->month, $i));
            $this->children[$i] = new TimetableEvent($day);
            
            $stamp1 = $this->cE->dateToStamp(
                $this->year, $this->month, $i);
            $stamp2 = $this->cE->dateToStamp(
                $this->year, $this->month, $i+1);
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
        Calendar_Month_Weekdays::buildEmptyDaysBefore();
        Calendar_Month_Weekdays::shiftDays();
        Calendar_Month_Weekdays::buildEmptyDaysAfter();
        Calendar_Month_Weekdays::setWeekMarkers();
    }

    // }}}
    // TimetableFormatMonthly::fetch() {{{

    /**
     * fetch 
     * 
     * @access public
     * @return TimetableEvent
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
    // TimetableFormatMonthly::render() {{{

    /**
     * render 
     * 
     * @access public
     * @return void
     */
    public function render() {
        $this->setSelection();

        $weekDayNames = $this->weekdayNames();
        $header = array();
        foreach ($this->tableHelper->getDaysOfWeek() as $key=>$value){
            $header[$key] = array(
                'label' => $weekDayNames[$value]
            );
        }

        $rows = array();
        while ($day = $this->fetch()) {
            if ($day->isFirst()) {
                $row = array();                
            }
            $cell = array();
            if ($day->isSelected()) {
                $cell['busy'] = 1;
            }
            $h=array();
            $h['label'] = $day->thisDay();
            if($this->clickOnDayUrl) {
                $h['onclick'] = sprintf('window.location=\'' . $this->clickOnDayUrl . '\';', 
                    $day->thisYear(), $day->thisMonth(), $day->thisDay());
            }
            $cell['header'] = $h;
            if($day->isSelected()) {
                $cell['events'] = array();
                while ($entry = $day->getEntry()) {
                    if($this->hoursInEvents) {
                        // Affichage de l'heure
                        $date = date('Y-m-d', mktime(0, 0, 0, $day->thisMonth(), $day->thisDay(), $day->thisYear()));
                        $dateEventStart = substr($entry['start'], 0,10);
                        $dateEventEnd = substr($entry['end'], 0,10);

                        $startHour = ($dateEventStart==$date)?substr($entry['start'], 11,5):'';
                        $endHour = ($dateEventEnd==$date)?substr($entry['end'], 11,5):'';

                        $theHour = $startHour.'-'.$endHour.' : ';
                        $entry['desc'] = $theHour . $entry['desc'];
                    }
                    $cell['events'][] = $entry;
                }
            }
            $row['cells'][] = $cell;
            if ($day->isLast()) {
                $rows[] = $row;
            }
        }

        $title = $this->thisMonthName().' '.$this->thisyear();
        $smarty = new Template();
        $smarty->assign('title', $title);
        $smarty->assign('header', $header);
        $smarty->assign('rows', $rows);
    }

    // }}}
}

?>
