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
 * @version   SVN: $Id: Database.php,v 1.7 2008-05-30 09:23:46 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

require_once('adodb/adodb.inc.php');

/**
 * Database
 * Classe de gestion de la connection, des transactions et des requêtes en base 
 * de données.
 *
 * Exemple:
 *
 * <code>
 * $conn = Database::connection('mysqlt://user:pass@host/db');
 * $result = $conn->execute('SHOW TABLES');
 * print_r($result);
 * </code>
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License 
 * @package    Framework
 */
class Database {
    // propriétés {{{

    /**
     * Array of active connections
     *
     * @static
     * @var    array
     * @access protected
     */
    protected static $connectionArray = array();

    /**
     * Active dsn
     *
     * @static
     * @var    object
     * @access protected
     */
    protected static $activeDSN = false;

    // }}}
    // Database::connection() {{{

    /**
     * Etabli la connection à la bases de données si ce n'est pas déjà fait et 
     * retourne l'objet connection.
     *
     * @static
     * @access public
     * @param  string $dsn (optionnel, sinon récupéré dans la constante DB_DSN)
     * @return ressource
     */
    public static function connection($dsn=false) {
        if (false === $dsn) {
            if (false !== self::$activeDSN) {
                $dsn = self::$activeDSN;
            } else if (defined('DB_DSN')) {
                $dsn = DB_DSN;
            } else {
                // exit with a FATAL ERROR
                trigger_error(
                    'No database DSN provided. You must pass a dsn string to '
                    . 'Database::connection() or define a DB_DSN constant.',
                    E_USER_ERROR
                );
            }
        }
        if (!isset(self::$connectionArray[$dsn])) {
            // register connection
            self::$connectionArray[$dsn] = NewADOConnection($dsn);
        }
        if (self::$activeDSN !== false && self::$activeDSN != $dsn) {
            // we must "re-connect" explicitely if another connection was 
            // opened before
            if (!self::$connectionArray[$dsn]->connect()) {
                trigger_error('Unable to connect to database.', E_USER_ERROR);
            }
        }
        self::$activeDSN = $dsn;
        return self::$connectionArray[$dsn];
    }

    // }}}
}

?>
