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

if (!defined('TIMER_FORMAT')) {
    define('TIMER_FORMAT', '%.4f sec.');
}

if (!defined('TIMER_MAIN_TEMPLATE')) {
    define('TIMER_MAIN_TEMPLATE', "<div id=\"timer\">\n    <ul>%s\n    </ul>\n</div>\n");
}

if (!defined('TIMER_MARKER_TEMPLATE')) {
    define('TIMER_MARKER_TEMPLATE', "\n        <li>%s exec time: %s</li>");
}

if (!defined('TIMER_TOTAL_MARKER_TEMPLATE')) {
    define('TIMER_TOTAL_MARKER_TEMPLATE', "\n        <li class=\"timer_total\">Total exec time: %s</li>");
}

if (!defined('TIMER_MAIN_TEMPLATE_TEXT')) {
    define('TIMER_MAIN_TEMPLATE_TEXT', "\nIndividual exec times:%s\n");
}

if (!defined('TIMER_MARKER_TEMPLATE_TEXT')) {
    define('TIMER_MARKER_TEMPLATE_TEXT', "\n\t* %s exec time: %s");
}

if (!defined('TIMER_TOTAL_MARKER_TEMPLATE_TEXT')) {
    define('TIMER_TOTAL_MARKER_TEMPLATE_TEXT', "\n\nTotal exec time: %s");
}
// }}}

/**
 * Timer
 * 
 * Classe basique pour "timer" l'execution des scripts.
 *
 * Example {{{
 * <code>
 * $classes = array('Actor', 'Product', 'ActivatedMovement', 'CommandItem');
 * 
 * foreach ($classes as $class) {
 *     Timer::start($class . ' Collection');
 *     $col = Object::loadCollection($class, array());
 *     $count = $col->getCount();
 *     for ($i=0; $i<$count; $i++) {
 *         $item = $col->getItem($i);
 *     }
 *     Timer::stop($class . ' Collection');
 * }
 *
 * echo Timer::render();
 *
 * </code>
 * }}}
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License 
 * @package Framework
 */
class Timer
{
    // Propriétés {{{

    /**
     * Tableau des marqueurs
     *
     * @static
     * @var    array markers
     */
    static $markers = array(); 

    // }}}
    // Timer::start() {{{

    /**
     * Méthode statique qui démarre le timer.
     *
     * @static
     * @param string $name nom du timer
     * @return void
     */
    static function start($name = 'Total')
    {
        if (!isset(self::$markers[$name])) {
            self::$markers[$name] = array(
                'name'  => $name,
                'start' => self::getMicrotime(),
                'time'  => 0
            );
        }
    }
    
    // }}} 
    // Timer::stop() {{{
    /**
     * Méthode statique qui arrête le marqueur $name et stocke le temps
     * d'execution de celui-ci.
     *
     * @static
     * @param string $name nom du timer à arrêter.
     * @return void
     */
    static function stop($name = 'Total')
    {
        if (!isset(self::$markers[$name])) {
            return '';
        }
        $time = self::getMicrotime() - self::$markers[$name]['start'];
        self::$markers[$name]['time'] = sprintf(TIMER_FORMAT, $time);
    }
    
    // }}} 
    // Timer::stopAll() {{{
    /**
     * Méthode statique qui arrête tous les marqueurs définis et retourne un 
     * tableau NomMarqueur=>temps.
     *
     * @static
     * @return void
     */
    static function stopAll()
    {
        foreach (self::$markers as $name=>$data) {
            self::stop($name);
        }
    }
    
    // }}} 
    // Timer::render() {{{
    /**
     * Méthode statique qui arrête tous les marqueurs définis et retourne un 
     * tableau NomMarqueur=>temps.
     *
     * @static
     * @param  array $markers permet de n'afficher le résultat que pour les 
     *               marqueurs spécifiés
     * @param  boolean $asHTML si false le rendu est effectué en plaintext
     * @return array
     */
    static function render($markers = array(), $asHTML = true)
    {
        if ($asHTML) {
            $tpl1 = TIMER_MARKER_TEMPLATE;
            $tpl2 = TIMER_TOTAL_MARKER_TEMPLATE;
            $tpl3 = TIMER_MAIN_TEMPLATE;
        } else {
            $tpl1 = TIMER_MARKER_TEMPLATE_TEXT;
            $tpl2 = TIMER_TOTAL_MARKER_TEMPLATE_TEXT;
            $tpl3 = TIMER_MAIN_TEMPLATE_TEXT;
        }
        $all = empty($markers);
        $items = '';
        foreach (self::$markers as $name=>&$data) {
            if ($name == 'Total' || (!$all && !in_array($name, $markers))) {
                continue;
            }
            if ($data['time'] == 0) {
                self::stop($name);
            }
            $items .= sprintf($tpl1, $name, $data['time']);
        }
        $totalTime = &self::$markers['Total']['time'];
        if ($totalTime == 0) {
            self::stop();
        }
        $items .= sprintf($tpl2, $totalTime);
        return sprintf($tpl3, $items);
    }
    
    // }}} 
    // Timer::getMicroTime() {{{

    static function getMicroTime() 
    {
        $time = explode(' ', microtime());
        return $time[1] + $time[0];
    }
    // }}}
}

// on démarre le timer principal, dès que ce script est inclus
Timer::start();

?>
