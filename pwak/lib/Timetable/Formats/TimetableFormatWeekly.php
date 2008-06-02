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

require_once 'Calendar/Week.php';

/**
 * TimetableFormatWeekly
 *
 * @package Framework
 * @subpackage TimetableFormats
 */
class TimetableFormatWeekly extends TimetableDecoratorTextualLang {
    //properties {{{
    public $format = 'Weekly';

    /**
     * timetable 
     * 
     * @var mixed
     * @access protected
     */
    protected $timetable;
    
    /**
     * week 
     * 
     * @var mixed
     * @access protected
     */
    protected $week;

    protected $clickOnDayUrl = false;
    // }}}
    // TimetableFormatWeekly::__construct() {{{
    
    /**
     * __construct 
     * 
     * options:
     * - clickondayurl: url de l'action onclick sur click sur un jour.
     *
     * @param Timetable $timetable 
     * @param array $options
     * @access public
     * @return void
     */
    public function __construct($timetable, $options=array()) {
        $this->week = new Calendar_Week($timetable->year, $timetable->month, 
            $timetable->day, $timetable->firstDay);
        parent::__construct($this->week);
        $this->timetable = $timetable;
        $date = $this->week->tableHelper->getWeekStart($timetable->year, 
            $timetable->month, $timetable->day, $timetable->firstDay);
        require_once CALENDAR_ROOT . 'Hour.php';
        require_once CALENDAR_ROOT .  'Table/Helper.php';

        $this->tableHelper = new Calendar_Table_Helper($this, $timetable->firstDay);
        $this->cE = $this->getEngine();
        $this->year  = $this->thisYear();
        $this->month = $this->thisMonth();
        $this->day = $this->thisDay();

        $hoursInDay = $this->cE->getHoursInDay();

        if(isset($options['clickondayurl'])) {
            $this->clickOnDayUrl = $options['clickondayurl'];
        }
    }

    // }}}
    // TimetableFormatWeekly::setSelection() {{{

    /**
     * setSelection 
     * 
     * @access public
     * @return void
     */
    public function setSelection() {
        $hoursInDay = $this->cE->getHoursInDay();
        $firstDayOfweek = $this->week->toArray($this->week->thisWeek);
        for($j=0 ; $j<7 ; $j++) {
            for ($i=0; $i<$hoursInDay; $i++) {
                $hour = new Calendar_Hour(2000,1,1,1); // Create Day with dummy values
                $hour->setTimestamp(
                    $this->cE->dateToStamp(
                        $this->year,
                        $this->month,
                        $firstDayOfweek['day'] + $j,
                        $i
                    )
                );
                $this->children[$i][$j] = new TimetableEvent($hour);
                $stamp1 = $this->cE->dateToStamp(
                    $this->year,
                    $this->month,
                    $firstDayOfweek['day'] + $j,
                    $i
                );
                $stamp2 = $this->cE->dateToStamp(
                    $this->year,
                    $this->month,
                    $firstDayOfweek['day'] + $j,
                    $i + 1
                );
                foreach ($this->timetable->events as $event) {
                    if (($stamp1 >= $event['start'] && $stamp1 < $event['end']) ||
                        ($stamp2 > $event['start'] && $stamp2 <= $event['end']) ||
                        ($stamp1 <= $event['start'] && $stamp2 >= $event['end'])
                    ) {


                        $this->children[$i][$j]->addEntry($event);
                        $this->children[$i][$j]->setSelected();
                    }
                }

            }
        }
    }

    // }}}
    // TimetableFormatWeekly::fetch() {{{

    /**
     * fetch 
     * 
     * @param mixed $day 
     * @access public
     * @return void
     */
    public function fetch($day) {
        $child = each($this->children[$day]);
        if ($child) {
            return $child['value'];
        } else {
            reset($this->children);
            return false;
        }
    }

    // }}}
    // TimetableFormatWeekly::render() {{{

    /**
     * render 
     * 
     * @access public
     * @return void
     */
    public function render() {
        $this->setSelection();
        $header = array(0=>array());
        $firstDayOfweek = $this->week->toArray($this->week->thisWeek);
        for($i=0 ; $i<7 ; $i++) {
            $ts = mktime(0,0,0, $firstDayOfweek['month'], $firstDayOfweek['day']+$i, $firstDayOfweek['year']);
            $header[$i+1] = array('label' => I18N::formatDate(date('Y-m-d h:i:s', $ts), I18N::DATE_SHORT_TEXTUAL));
            if(isset($this->clickOnDayUrl)) {
                $header[$i+1]['onclick'] = sprintf('window.location=\''.$this->clickOnDayUrl.'\';',
                    date('Y', $ts), date('n', $ts), date('j', $ts)); 
            }
        }

        $rows = array();
        for($j=$this->timetable->firstHour ; $j<=$this->timetable->lastHour ; $j++){
            $row = array('hour' => $j.':00');
            $row['cells'] = array();
            while ($e = $this->fetch($j)) {
                $cell = array();
                if ($e->isSelected()) {
                    $cell['divAttr'] = '';
                    $cell['busy'] = 1;
                    $cell['events'] = array();
                    while ($entry = $e->getEntry()) {
                        $cell['events'][]=$entry;
                        unset($entry);
                    }
                }
                $row['cells'][] = $cell;
                unset($cell, $e);
            }
            $rows[] = $row;
            unset($row);
        }
        $title = sprintf(_('Week %s of %s'),
		    $this->thisWeek('n_in_year'),
			$this->thisYear());
        $smarty = new Template();
        $smarty->assign('title', $title);
        $smarty->assign('header', $header);
        $smarty->assign('rows', $rows);
    }

    // }}}
}
?>
