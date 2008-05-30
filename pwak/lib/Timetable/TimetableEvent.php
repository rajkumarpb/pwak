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
 * @version   SVN: $Id: TimetableEvent.php,v 1.3 2008-05-30 09:23:48 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class TimetableEvent extends Calendar_Decorator {
    /**
    * Les entrées
    * @var array
    * @access private
    */
    public $entries;

    /**
    * constructeur
    * @param object $calendar un calendrier
    * @access public
    * @return void
    */
    public function __construct($calendar) {
        $this->entries = array();
        Calendar_Decorator::Calendar_Decorator($calendar);
    }

    /**
    * Ajoute une entrée
    * @param mixed $entry une entrée
    * @access public
    * @return void
    */
    function addEntry($entry) {
        $this->entries[] = $entry;
    }

    /**
    * retourne une entrée
    * @access public
    * @return mixed
    */
    function getEntry() {
        $entry = each($this->entries);
        if ($entry) {
            if(!isset($entry['value']['background'])) {
                $entry['value']['background'] = DEFAULT_EVENT_BACKGROUND;
            }
            return $entry['value'];
        } else {
            reset($this->entries);
            return false;
        }
    }
    
    /**
     * Méthode surchargée (original non documentée, 
     * elle retourne toujours true!) retourne true si
     * l'événement est vide.
     * @access public
     * @return boolean
     */
    function isEmpty() {
        if(!$this->getEntry()) {
            return true;
        }
        reset($this->entries);
        return false;
    }
}

?>
