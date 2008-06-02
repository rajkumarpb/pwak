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

require_once 'Calendar/Decorator/Textual.php';

/**
 * Decorateur pour avoir les mois en mode text et dans 
 * un langage choisi (FR par defaut)
 *
 * @package Framework
 * @subpackage TimetableDecorators
 */
class TimetableDecoratorTextualLang extends Calendar_Decorator_Textual {
    /**
     * Les mois en lettre et en entier
     * @var array
     * @access private
     */
    protected $longMonthArray;
    
    /**
     * Les mois en lettre et abrégés
     * @var array
     * @access private
     */
    protected $shortMonthArray;
    
    /**
     * Les jours de la semaine en lettre et en entier
     * @var array
     * @access private
     */
    protected $longWeekDayNames;
    
    /**
     * Les jours de la semaine en lettre et en abrégés
     * @var array
     * @access private
     */
    protected $shortWeekDayNames;
    
    /**
     * Constructs Calendar_Decorator_Textual_Lang
     * @param object subclass of Calendar
     * @access public
     * @return void
     */
    public function __construct($calendar) {
        parent::Calendar_Decorator_Textual($calendar);            
        $this->longMonthArray = I18N::getMonthesArray();
        $this->shortMonthArray = I18N::getMonthesArray(true);
        $this->longWeekDayNames = I18N::getDaysArray();
        $this->shortWeekDayNames = I18N::getDaysArray(true);
    }    
    
    /**
     * Retourne le jour du mois
     * @param string $format 'long' ou 'short'
     * @return string
     * @access public
     */
    public function thisMonthName($format='long') {
        $month = Calendar_Decorator_Textual::thisMonth('int');
        switch ($format) {
            case 'long':
                return $this->longMonthArray[$month];
                break;
            case 'short':
                return $this->shortMonthArray[$month];
        }
    }
    
    /**
     * Retourne le jour du mois
     * @param string $format 'long' ou 'short'
     * @return string
     * @access public
     */
    public function thisDayName($format='long') {
        $day = Calendar_Decorator_Textual::thisDayName();
        $dayArray = Calendar_Decorator_Textual::weekdayNames();
        $d = array_keys($dayArray, $day);
        switch ($format) {
            case 'long':
                return $this->longWeekDayNames[$d[0]];
                break;
            case 'short':
                return $this->shortWeekDayNames[$d[0]];
        }
    }
    
    /**
     * retourne les mois d'année en francais
     * @param string $format 'long' ou 'short'
     * @return string
     * @access public
     */
    public function monthNames($format='long') {
        switch ($format){
            case 'long':
                return $this->longMonthArray;
                break;
            case 'short':
                return $this->shortMonthArray;
            break;
        }
    }
    
    /**
     * Retourne les jours de la semaines en francais
     * @param string $format 'long' ou 'short'
     * @return string
     * @access public
     */
    public function weekdayNames($format='long') {                
        return $this->longWeekDayNames;
    }
    
}
?>

