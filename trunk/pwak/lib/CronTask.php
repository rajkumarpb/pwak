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

define('MAPPER_CACHE_DISABLED', true);

/**
 * CronTaskManager.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License 
 * @package Framework
 */
class CronTaskManager {

    /**
     * Execute les taches crons sur tous les dsn paramétrés
     * XXX: Les noms de variables sont volontairement exotiques, pour éviter 
     * qu'ils soient redéfinis dans les scripts inclus.
     *
     * @static
     * @access public
     * @return void
     */
    public static function run()
    {
        // ajouter ce check aussi dans les scripts à appeller (protection)
        if (php_sapi_name() != 'cli' || !isset($GLOBALS['DSNS'])) {
            exit(1);
        }
        // for compatibility issues
        if (!isset($argv)) {
            $argv = $_SERVER['argv'];
        }
        $_force_ = isset($argv[1]) && ($argv[1]=='-f' || $argv[1]=='--force');
        // error handler custom
        $_mday_ = date('d');
        $_wday_ = date('w');
        $_hour_ = date('H');
        $_logger_ = Tools::loggerFactory();
        // pour intercepter les fatal errors
        set_error_handler(array('CronTaskManager', 'errorHandler'));
        ob_start(array('CronTaskManager', 'fatalErrorHandler'));
        foreach ($GLOBALS['DSNS'] as $_dsn_) {
            $_dsn_ = constant($_dsn_);
            if (substr_count($_dsn_, '/') == 4) {
                // XXX compte qui n'a pas de base propre: les crons ne sont pas 
                // executées
                continue;
            }
            Database::connection($_dsn_);
            // IMPORTANT: le parmètre nocache doit être à true !
            $_col_ = Object::loadCollection('CronTask', array(), array(), array(),
                0, 1, false, true);
            $_count_ = $_col_->getCount();
            for ($_i_ = 0; $_i_ < $_count_; $_i_++) {
                $_cron_ = $_col_->getItem($_i_);
                if (!$_cron_->getActive() || (!$_force_ && $_cron_->getHourOfDay() != $_hour_)) {
                    continue;
                }
                if ($_force_ || // on a forcé l'execution de la cron
                    ($_cron_->getDayOfMonth() == $_mday_) || // montlhy
                    ($_cron_->getDayOfWeek() == $_wday_)  || // weekly
                    ($_cron_->getDayOfWeek() == -1 
                     && $_cron_->getDayOfMonth() == 0)) // daily
                {
                    // le script trouvera la cnx à la bado déjà ouverte
                    $_cron_script_ = CRON_DIR . '/' . $_cron_->getScriptName();
                    if (include $_cron_script_) {
                        $vars = $file_vars = $self_vars = array();
                        $self_vars = array_keys(get_defined_vars());
                        $file_vars = array_keys(get_defined_vars());
                        // loggue l'execution du script
                        $_msg_ = sprintf(
                            'Cron script "%s" executed on database "%s"', 
                            $_cron_script_, Database::connection()->database
                        );
                        $_logger_->log($_msg_, PEAR_LOG_NOTICE);
                        // nettoie les locales du fichier inclu
                        $vars = array_diff($file_vars, $self_vars);
                        foreach ($vars as $v) {
                            unset($$v);
                        }
                    }
                } 
            } 
        } 
        ob_end_flush();
        restore_error_handler();
    }

    /**
     * Error handler personalisé.
     * Intercepte les erreurs et envoie un mail à dev@.
     *
     * @static
     * @access public
     * @param const $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return boolean
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!error_reporting()) {
            // ne rien faire quand error_reporting() vaut 0, c'est le cas 
            // notamment pour les appels avec l'opérateur @
            return true;
        }
        switch($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                $errtype = 'FATAL ERROR';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $errtype = 'WARNING';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $errtype = 'NOTICE';
                break;
            case E_STRICT:
                return true;
            default:
                $errtype = 'UNKNOWN ERROR';
        }
        $db = Database::connection()->database;
        $subj = sprintf(_('[Scheduled task] %s on database "%s"'), $errtype, $db);
        $body = sprintf(
            _("A PHP error occured on database \"%s\":\n[%s] %s in %s on line %s."),
            $db, $errtype, $errstr, $errfile, $errline
        );
        if ($errtype == 'FATAL ERROR') {
            $body .= _("\nScheduled tasks execution aborted.");
        }
        $logger = Tools::loggerFactory();
        $logger->log($body, PEAR_LOG_ALERT);
        MailTools::send(array(MAIL_DEV), $subj, $body);
        return true;
    }

    /**
     * Handler pour les erreurs fatales qui ne peuvent être interceptées par
     * notre errorHandler personalisé.
     *
     * @static
     * @access public
     * @param string $buffer contenu du message d'erreur
     * @return string
     */
    public static function fatalErrorHandler($buffer) 
    {
        $rx = '/error:\s+(.+)\s+in\s+(.+)\s+on\s+line\s+(\d+)/';
        if (preg_match($rx, $buffer, $m)) {
            CronTaskManager::errorHandler(E_ERROR, $m[0], $m[1], $m[2]);
        }
        return $buffer;
    }
}

?>
