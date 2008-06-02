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

class DateTimeTools {
    // constants {{{
    
    const ONE_DAY   = 86400;
    const ONE_WEEK  = 604800;
    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;
    const SUNDAY    = 7;

    // }}}
    // DateTimeTools::mySQLDateToTimeStamp() {{{

    /**
     * Convertit une date MySQL au format yyyy-mm-dd hh:mm:ss, ou hh:mm:ss ou
     * hh:mm en Timestamp.
     *
     * @static
     * @param string $date date MySQL au format
     * @return integer
     */
    static function mySQLDateToTimeStamp($date) {
        if(false!==($t = DateTimeTools::DateExploder($date))) {
            return mktime($t['hour'], $t['mn'], $t['sec'], $t['month'], $t['day'], $t['year']);
        }
        // on a peut-être un 'time' ?
        return DateTimeTools::timeToTimeStamp($date);
    }

    // }}}
    // DateTimeTools::timeStampToMySQLDate() {{{

    /**
     * Converti un timestamp unix en date au format mysql.
     *
     * @static
     * @access public
     * @param  int $timestamp timestamp unix
     * @param  string $format format de la date à retourner
     * @return string
     */
    static function timeStampToMySQLDate($timestamp, $format='Y-m-d H:i:s') {
        if ($timestamp < DateTimeTools::ONE_DAY) {
            // il faut enlever 1 heure quant il s'agit d'un time...
            $timestamp = $timestamp - 3600;
        }
        return date($format, $timestamp);
    }

    // }}}
    // DateTimeTools::timeToTimeStamp() {{{

    /**
     * Converti une heure sous la forme HH:MM ou HH:MM:SS en nombre de secondes.
     *
     * @static
     * @param string $time Durée sous la forme HH:MM ou HH:MM:SS
     * @return integer Le nombre de seconde correspondant à la durée ou FALSE
     * si le paramètre ne correspond pas à la syntaxe attendue
     */
    static function timeToTimeStamp($time) {
        $t = explode(':', $time);
        $c = count($t);
        if ($c > 1) {
            return $t[0]*3600 + $t[1]*60 + ($c==3?$t[2]:0);
        }
        return false;
    }

    // }}}
    // DateTimeTools::getHundredthsOfHour() {{{

    /**
     * Convertit un time en un nbre (à 2 décimales) de centiemes d'heure.
     *
     * @static
     * @param string $time temps au format hh:mm ou hh:mm:ss
     * @return float
     * @todo à enlever du framework et mettre dans onlogistics/lib-functions
     */
    static function getHundredthsOfHour($time) {
        $componentArray = explode(":", $time);
        // sec = nb de secondes si on ne tient pas compte de la composante heure
        // 1/100e d'heure vaut 36 sec
        if (count($componentArray) == 2) {  // pas de secondes (xx:yy)
            $sec = $componentArray[1] * 60;
        }
        else {
            $sec = $componentArray[2] + $componentArray[1] * 60;
        }
        $result = ($componentArray[0] * 100) + ($sec / 36);
        $result = I18N::formatNumber($result);
        return $result;
    }

    // }}}
    // DateTimeTools::hundredthsOfHourToTime() {{{

    /**
     * Convertit des centièmes d'heures en time mysql: hh:mm:ss
     *
     * @static
     * @access public
     * @param int $hoh les centièmes d'heures
     * @return string le time
     * @todo à enlever du framework et mettre dans onlogistics/lib-functions
     */
    static function hundredthsOfHourToTime($hoh) {
        // Ce test au cas ou appele via register_function() pour smarty:
        // Dans ce cas, le param est dans un array
        if (is_array($hoh)) {
            extract($hoh);
        }
        $tstamp = $hoh*36;
        $hours = floor($tstamp/3600);
        $minutes = ceil(($tstamp - ($hours*3600))/60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    // }}}
    // DateTimeTools::hourTohundredthsOfHour() {{{

    /**
     * Convertit des heures en centièmes d'heures.
     *
     * @static
     * @access public
     * @param float $hours les heures
     * @return string le time
     * @todo à enlever du framework et mettre dans onlogistics/lib-functions
     */
    static function hourTohundredthsOfHour($hours) {
        $hundredthsOfHour = (float)$hours * 100.00;
        return sprintf("%01.2f", I18N::formatNumber($hundredthsOfHour));
    }

    // }}}
    // DateTimeTools::quickFormDateToMySQL() {{{

    /**
     * Transforme une date issue d'un Widget de date de QuickForm de la forme:
     * 'dMY H:i'
     *
     * @static
     * @param $fieldName string nom du champs
     * @access public
     * @return string
     */
    static function quickFormDateToMySQL($fieldName) {
        if (SearchTools::requestOrSessionExist($fieldName) == false) {
            return false;
        }
        $date = SearchTools::requestOrSessionExist($fieldName);
        $month = isset($date['m'])?$date['m']:$date['M'];
        $return = $date['Y'].'-' . sprintf('%02d', $month) . '-' .
                  sprintf('%02d', $date['d']);
        if (isset($date['H'])) {
            $return .= ' ' . sprintf('%02d', $date['H']) .
                       ':' . sprintf('%02d', $date['i']).':'.'00';
        }
        return $return;
    }

    // }}}
    // DateTimeTools::mySQLToQuickFormDate() {{{

    /**
     * Transforme une date MySQL en un array du type:
     * array('d'=>date('d'), 'm'=>date('m'), 'Y'=>date('Y'),
     *       'H'=>date('H'), 'i'=>date('i'));
     * Utile pour remplir un widget de date avec le contenu d'un champs en base
     *
     * @static
     * @param $date string au format date MySQL
     * @access public
     * @return array
     */
    static function mySQLToQuickFormDate($date) {
        $detail = DateTimeTools::DateExploder($date);
        return array('d'=>$detail['day'], 'm'=>$detail['month'], 'Y'=>$detail['year'],
                     'H'=>$detail['hour'], 'i'=>$detail['mn']);
    }

    // }}}
    // DateTimeTools::quickFormDateToFrenchDate() {{{

    /**
     * Transforme une date issue d'un Widget de date de QuickForm de la forme:
     * 'dMY H:i' en date au format french.
     *
     * @static
     * @param $fieldName string nom du champs
     * @access public
     * @return string
     */
    static function quickFormDateToFrenchDate($fieldName, $hour = true) {
        $date = DateTimeTools::QuickFormDateToMySQL($fieldName);
        return I18N::formatDate($date, $hour?I18N::DATETIME_LONG:I18N::DATE_LONG);
    }

    // }}}
    // DateTimeTools::mySQLDateAdd() {{{

    /**
     * DateTimeTools::mySQLDateSubstract()
     * Ajoute 2 dates mysql et renvoie le résultat au format 'time' ou en
     * timestamp si $asTimeStamp est à true.
     *
     * @static
     * @access public
     * @param string $date1 la date au format 'hh:ss'
     * @param string $date2 la date au format 'hh:ss'
     * @param boolean $asTimeStamp true si doit retourner un timestamp
     * @return mixed string ou integer
     */
    static function mySQLDateAdd($date1, $date2, $asTimeStamp=false) {
        $res = DateTimeTools::MysqlDateToTimeStamp($date1) +
               DateTimeTools::MysqlDateToTimeStamp($date2);
        return $asTimeStamp?$res:DateTimeTools::timeStampToMySQLDate($res);
    }

    // }}}
    // DateTimeTools::mySQLDateSubstract() {{{

    /**
     * DateTimeTools::mySQLDateSubstract()
     * Soustrait 2 dates mysql et renvoie le résultat au format 'time' ou en
     * timestamp si $asTimeStamp est à true.
     *
     * @static
     * @access public
     * @param string $date1 la date au format 'hh:ss'
     * @param string $date2 la date au format 'hh:ss'
     * @param boolean $asTimeStamp true si doit retourner un timestamp
     * @return mixed string ou integer
     */
    static function mySQLDateSubstract($date1, $date2, $asTimeStamp=false) {
        $res = DateTimeTools::MysqlDateToTimeStamp($date1) -
               DateTimeTools::MysqlDateToTimeStamp($date2);
        return $asTimeStamp?$res:DateTimeTools::timeStampToMySQLDate($res);
    }

    // }}}
    // DateTimeTools::dateExploder() {{{

    /**
     * Transforme une date mysql en tableau de la forme:
     * array(
     *        'year'  => 2006,
     *        'month' => 01,
     *        'day'   => 01,
     *        'hour'  => 00,
     *        'mn'    => 00,
     *        'sec'   => 00
     * )
     *
     * @static
     * @param string $adate : datetime formatée Mysql yyyy-mm-dd hh:mn:ss
     * @access public
     * @return array
     */
    static function dateExploder($adate){
        $rx = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})\s?(([0-9]{2}):([0-9]{2})(:([0-9]{2}))?)?$/';
        if (preg_match($rx, $adate , $datetoken)){
            $date = array();
            $date['year']  = $datetoken[1];
            $date['month'] = $datetoken[2];
            $date['day']   = $datetoken[3];
            $date['hour']  = isset($datetoken[5])?$datetoken[5]:'00';
            $date['mn']    = isset($datetoken[6])?$datetoken[6]:'00';
            $date['sec']   = isset($datetoken[8])?$datetoken[8]:'00';
            return $date;
        }
        return false;
    }

    // }}}
    // DateTimeTools::timeAnalyzer() {{{

    /**
     * Retourne une heure au format H:i:s à partir d'une heure au format H:i ou
     * H:i:s
     *
     * @static
     * @param string $time heure au format hh:mm
     * @access public
     * @return l'heure au format hh:mm:ss
     */
    static function timeAnalyzer($time){
        $ttime = explode (":", $time);
        if(count($ttime) == 3) {
            return $time;
        }
        return $time . ':00';
    }

    // }}}
    // DateTimeTools::getDayValue() {{{

    /**
     * Renvoie la valeur numérique du jour correspondant à la date aDate.
     *
     * @param integer $aDate Le timestamp concerné
     * @access public
     * @return integer la valeur correspondant au jour ou une exception
     * @todo à refactorer, pas I18N compliant en plus
     */
    static function getDayValue($aDate) {
        switch (date('D', $aDate)) {
        case 'Mon':
            return self::MONDAY;
        case 'Tue':
            return self::TUESDAY;
        case 'Wed':
            return self::WEDNESDAY;
        case 'Thu':
            return self::THURSDAY;
        case 'Fri':
            return self::FRIDAY;
        case 'Sat':
            return self::SATURDAY;
        case 'Sun':
            return self::SUNDAY;
        default:
            return new Exception('Unknown day for date: ' . $aDate);
        }
    }

    // }}}
    // DateTimeTools::dateModeler() {{{

    /**
     * Permet de recuperer le datetime formatée Mysql d'une date +/- un nbre
     * de secondes.
     *
     * @static
     * @param $adate : datetime formatée Mysql yyyy-mm-dd hh:mn:ss
     * @param $nbSec : nb de secondes (>0 ou <0)
     * @access public
     * @return string : datetime formatée Mysql yyyy-mm-dd hh:mn:ss
     */
    static function dateModeler($adate, $nbSec){
        if (preg_match(
            '/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/',
            $adate ))
        {
            $ExplodedDate = DateTimeTools::DateExploder($adate);
            $date = date(
                "Y-m-d H:i:s",
                mktime(
                    $ExplodedDate["hour"],
                    $ExplodedDate["mn"],
                    $ExplodedDate["sec"],
                    $ExplodedDate["month"],
                    $ExplodedDate["day"],
                    $ExplodedDate["year"]
                ) + $nbSec
            );
            return $date;
        }
        return false;
    }

    // }}}
    // DateTimeTools::lastDayInMonth() {{{

    /**
     * Fonction qui permet de donner le dernier jour du mois de la date rentrée
     * sous format php par ex:
     * "2004-02-16 11:52:56" retourne "2004-02-29 11:52:56"
     *
     * @static
     * @param sting $date date MySQL Y-m-d H:i:s
     * @return string
     */
    static function lastDayInMonth($date) {
        $date = explode(" ", $date);
        $dateArray = explode("-", $date[0]);

        $Month31 = Array("01", "03", "05", "07", "08", "10", "12");
        $Month30 = Array("04", "06", "09", "11");

        if (in_array($dateArray[1], $Month31) ) {
            return ($dateArray[0]."-".$dateArray[1]."-"."31"." ".$date[1]);
        }
        elseif(in_array($dateArray[1], $Month30) ) {
            return ($dateArray[0]."-".$dateArray[1]."-"."30"." ".$date[1]);
        } else { //le cas de fevrier
            if (checkdate($dateArray[1], 29, $dateArray[0])) {
                return ($dateArray[0]."-".$dateArray[1]."-"."29"." ".$date[1]);
            }
            return ($dateArray[0]."-".$dateArray[1]."-"."28"." ".$date[1]);
        }
    }

    // }}}
    // DateTimeTools::getNextMonthDate() {{{

    /**
     * Fonction qui permet de donner la date du mois suivant de la date rentrée
     * sous format php par ex :
     * "2004-02-16 11:52:56" retourne "2004-03-16 11:52-56"
     *
     * @static
     * @param string $date date au format Y-m-d H:i:s
     * @return string
     */
    static function getNextMonthDate($date) {
        $date = explode(" ", $date);
        $dateArray = explode("-", $date[0]);
        switch($dateArray[1]){
            case "01":
                return ($dateArray[0]."-"."02"."-".$dateArray[2]." ".$date[1]);
            case "02":
                 return ($dateArray[0]."-"."03"."-".$dateArray[2]." ".$date[1]);
            case "03":
                 return ($dateArray[0]."-"."04"."-".$dateArray[2]." ".$date[1]);
            case "04":
                 return ($dateArray[0]."-"."05"."-".$dateArray[2]." ".$date[1]);
            case "05":
                 return ($dateArray[0]."-"."06"."-".$dateArray[2]." ".$date[1]);
            case "06":
                 return ($dateArray[0]."-"."07"."-".$dateArray[2]." ".$date[1]);
            case "07":
                 return ($dateArray[0]."-"."08"."-".$dateArray[2]." ".$date[1]);
            case "08":
                 return ($dateArray[0]."-"."09"."-".$dateArray[2]." ".$date[1]);
            case "09":
                 return ($dateArray[0]."-"."10"."-".$dateArray[2]." ".$date[1]);
            case "10":
                 return ($dateArray[0]."-"."11"."-".$dateArray[2]." ".$date[1]);
            case "11":
                 return ($dateArray[0]."-"."12"."-".$dateArray[2]." ".$date[1]);
            case "12":
                $dateArray[0]++;//on passe a l'année suivante
                 return ($dateArray[0]."-"."01"."-".$dateArray[2]." ".$date[1]);
        } // switch
    }

    // }}}
    // DateTimeTools::getNextYearDate() {{{

    /**
     * Fonction qui permet de donner la date de l'annee suivante de la date rentrée
     * sous format php par ex :
     * "2004-02-16 11:52:56" retourne "2005-02-16 11:52:56"
     *
     * @param string $date date au format Y-m-d H:i:s
     * @static
     * @return string
     */
    static function getNextYearDate($date) {
        $date = explode(" ", $date);
        $dateArray = explode("-", $date[0]);
        $dateArray[0]++;
        return ($dateArray[0]."-".$dateArray[1]."-".$dateArray[2]." ".$date[1]);
    }

    // }}}
    // DateTimeTools::getTimeFromDate() {{{

    /**
     * Fonction qui renvoit l'heure pour un datetime ou un timestamp.
     *
     * L'heure est retournée sous forme de timestamp (nombre de secondes depuis
     * minuit).
     *
     * @static
     * @param  mixed string or interger $date: date au format Y-m-d H:i:s ou ts
     * @access public
     * @return integer
     */
    static function getTimeFromDate($date) {
        if (!is_numeric($date)) {
            // on a une date mysql
            $date = DateTimeTools::MysqlDateToTimeStamp($date);
        }
        $result  = (int)date('H', $date) * 60 * 60;
        $result += (int)date('i', $date) * 60;
        $result += (int)date('s', $date);
        return $result;
    }

    // }}}
    // DateTimeTools::getDateAtNoon() {{{

    /**
     * Fonction qui renvoit la date $date à minuit.
     *
     * La date est retournée sous forme de timestamp.
     *
     * @static
     * @param  mixed string or interger $date: date au format Y-m-d H:i:s ou ts
     * @access public
     * @return integer
     */
    static function getDateAtNoon($date) {
        if (!is_numeric($date)) {
            // on a une date mysql
            $date = DateTimeTools::MysqlDateToTimeStamp($date);
        }
        $date = date('Y-m-d 00:00:00', $date);
        return DateTimeTools::mySQLDateToTimeStamp($date);
    }

    // }}}
    // DateTimeTools::rangeDisplay() {{{

    /**
     * Fonction qui affiche un creneau d'heures ou une heure si debut=fin.
     *
     * @static
     * @param string $begin (time)
     * @param string $end (time)
     * @access public
     * @return string
     * @todo à enlever du framework et à mettre dans onlogistics/lib-functions
     */
    static function rangeDisplay($begin, $end) {
        if ($begin == $end) {
            return $begin;
        }
        return $begin . " - " . $end;
    }

    // }}}
}

?>
