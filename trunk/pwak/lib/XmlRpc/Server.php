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
 * @version   SVN: $Id: Server.php,v 1.6 2008-05-30 09:23:49 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class XmlRpcServer {
    // propriétés {{{

    /**
     * La ressource renvoyée par xmlrpc_server_create.
     *
     * @var ressource
     * @access private
     */
    protected $_server = false;

    /**
     * Le tableau des fonctions/methodes registerées.
     *
     * @var array
     * @access private
     */
    private $_methodMap = array();

    /**
     * Instance de PEAR::Log
     *
     * @var object Log
     * @access private
     */
    private $_logger = false;

    /**
     * Encoding de l'enveloppe xmlrpc, par defaut: ISO-8859-1 (latin1).
     *
     * @var string
     * @access public
     */
    public $encoding = 'iso-8859-1';

    /**
     * Instance de Auth (fichier du framework).
     *
     * @var object Auth
     * @access public
     */
    public $auth = false;

    // }}}
    // Constructeur {{{

    /**
     * Constructeur.
     *
     * @access protected
     */
    function __construct() {
        $this->_server = xmlrpc_server_create();
        $this->auth = false;
        $this->_logger = Tools::loggerFactory();
        $this->registerMethod('test');
        $this->registerMethod('auth.login');
        $this->registerMethod('i18n.setLocale');
        $this->registerMethod('i18n.getSupportedLocales');
        $this->registerMethod('session.register');
    }
    
    // }}}
    // XmlRpcServer::auth() {{{

    /**
     * Authentifie un utilisateur via l'auth onlogistics
     *
     * @access public
     * @param string $user le nom d'utilisateur
     * @param string $realm le nom du realm client (ex: wp)
     * @param string $passwd le mot de passe
     * @return boolean
     */
    public function auth($login=false, $realm=false, $passwd=false, $pf=false)
    {
        $this->auth = Auth::Singleton();
        if ($this->auth->isUserConnected()) {
            // déjà authentifié
            $user = $this->auth->getUser();
            $this->log('User "'.$user->getLogin().'" already authenticated.');
        } else {
            // l'user n'est pas encore authentifié
            $this->log('Authenticating user "' . $login . '".');
            if (!$login || !$realm || !$passwd) {
                return $this->showAuthError();
            }
            $db_dsn = 'DSN_' . strtoupper($realm);
            if (!in_array($db_dsn, $GLOBALS['DSNS'])) {
                return $this->showAuthError();
            }
            define('DB_DSN', constant($db_dsn));
            Database::connection();
            $user = $this->auth->login($login, $realm, $passwd);
        }
        if (!($user instanceof UserAccount)) {
            return $this->showAuthError();
        }
        if ($pf && !$this->auth->checkProfiles($pf, array('showErrorDialog' => false))) {
            return $this->showAuthError();
        }
        return true;
    }
    
    // }}}
    // XmlRpcServer::showAuthError() {{{
 
    /**
     * Renvoie le code d'erreur 401 au client, lui signifiant qu'il n'a pas pu 
     * être authentifié.
     *
     * @access public
     * @return void 
     */
    public function showAuthError() {
        header('WWW-Authenticate: Basic realm="Zone Privée"');
        header('HTTP/1.1 401 Authorization Required');
        exit;        
    }
    
    // }}}
    // XmlRpcServer::handleRequest() {{{

    /**
     * Méthoode qui gère les requêtes effectuées par les clients xmlrpc.
     *
     * @access public
     * @return void
     */
    public function handleRequest() {
        // récupère le xml envoyé par le client
        $params = isset($HTTP_RAW_POST_DATA)?
            $HTTP_RAW_POST_DATA:file_get_contents('php://input');
        // output_options
        // http://xmlrpc-epi.sourceforge.net/main.php?t=php_api#output_options
        $output_options = array(
            'encoding'  => $this->encoding,  // encoding du xml
            'verbosity' => 'no_white_space'  // économie de bande passante
        );
        // sette la locale
        if (isset($_SESSION['locale'])) {
            I18N::setLocale($_SESSION['locale']);
        }
        // appelle la méthode
        $response = xmlrpc_server_call_method($this->_server, $params, null,
            $output_options);
        header('Content-Type: text/xml');
        header('Content-Length: ' . strlen($response));
        echo $response;
        // nettoyage
        xmlrpc_server_destroy($this->_server);
    }
    
    // }}}
    // XmlRpcServer::registerMethod() {{{

    /**
     * Exporte la méthode $privateName ou, si non renseigné, $publicname
     * comme méthode publique rpc.
     *
     * @access public
     * @return void
     */
    public function registerMethod($publicName, $privateName=false){
        $this->_register($publicName, $privateName, true);
    }
    
    // }}}
    // XmlRpcServer::registerFunction() {{{

    /**
     * Exporte la fonction $privateName ou, si non renseigné, $publicname
     * comme méthode publique rpc.
     *
     * @access public
     * @return void
     */
    public function registerFunction($publicName, $privateName=false){
        $this->_register($publicName, $privateName);
    }
    
    // }}}
    // XmlRpcServer::_register() {{{

    /**
     * Exporte la fonction/méthode $privateName ou, si non renseigné,
     * $publicname comme méthode publique rpc.
     *
     * @access protected
     * @return void
     */
    private function _register($publicName, $privateName, $isMethod=false) {
        if (false == $privateName) {
            if (($dot = strrpos($publicName, '.')) > 0) {
                $privateName = substr($publicName, $dot+1);
            } else {
                $privateName = $publicName;
            }
        }
        if ($isMethod) {
            $key  = 'method';
            $func = array($this, $privateName);
            $exists = method_exists($this, $privateName);
        } else {
            $key  = 'function';
            $func = $privateName;
            $exists = function_exists($privateName);
        }
        if ($exists) {
            $this->methodMap[$publicName] = array($key=>$privateName);
            xmlrpc_server_register_method($this->_server, $publicName, $func);
        }
    }
    
    // }}}
    // XmlRpcServer::_formatArray() {{{

    /**
     * Formate les cles d'un array, pour contrer le probleme suivant:
     * http://bugs.php.net/bug.php?id=37746
     * Fonction recursive par defaut, mais recursivite non obligatoire
     *
     * @access protected
     * @param array $array tableau avec des cles du type '123'
     * @param boolean $recursion true/false selon s'il faut appliquer la
     * méthode de façon récursive.
     * @return array
     */
    protected function _formatArray($array, $recursion=true) {
        $return = array();
        foreach($array as $key => $val) {
            if ($key == 0 || (int)substr($key, 0, 1) > 0 || 
                ($key < 0 && (int)substr($key, 1, 1) > 0)) {
                $key .= chr(0x00);
            }
            $return[$key] = $recursion && is_array($val)?
                $this->_formatArray($val):$val;
        }
        return $return;
    }
    
    // }}}
    // XmlRpcServer::log() {{{

    /**
     * Loggue la chaine $data via PEAR::Log.
     *
     * @access public
     * @param mixed $data
     * @return void
     */
    public function log($data){
        $this->_logger->Log($data, PEAR_LOG_NOTICE);
    }

    // }}}
    // XmlRpcServer::test() {{{

    /**
     * Une fonction toute bête pour valider la connection des clients
     *
     * @access protected
     * @return boolean
     **/
    protected function test($method){
        $this->auth();
        $this->log('XmlRpcServer::test called');
        return true;
    }
    
    // }}}
    // XmlRpcServer::login() {{{

    /**
     * Authentifie un client xmlrpc.
     * Cette méthode doit être appelée avec 3 paramètres:
     *      - login: le nom d'utilisateur
     *      - realm: le nom du realm client
     *      - passwd: le mot de passe du user
     *      - locale: la langue de l'utilisateur
     * Elle retourne un id de session que le client devra repasser dans l'url
     * du serveur.
     * Exemple (client python):
     *
     * import xmlrpclib
     * s = ServerProxy('http://www.onlogistics.com/rpc/')
     * sid = s.login('toto', 'wp', 't0t4u')
     * s = ServerProxy('http://www.onlogistics.com/rpc/?sid=%s' % sid)
     * // à partir de là les méthodes appelées bénéficieront des sessions...
     *
     * @access protected
     * @return string
     */
    protected function login($method, $data) {
        $login  = $data[0];
        $realm  = $data[1];
        $passwd = $data[2];
        $sid = session_id();
        $this->auth($login, $realm, $passwd);
        // si on a passé une locale on la met en session
        if (isset($data[3]) && $data[3]) {
            I18N::setLocale($data[3]);
            $_SESSION['locale'] = $data[3];
        }
        return $sid;
    } 
    
    // }}}
    // XmlRpcServer::setLocale() {{{

    /**
     * Change ou défini la locale en session.
     *
     * @param array $array
     * @access public
     * @return string
     */
    protected function setLocale($method, $params){
        $this->auth();
        $this->log('XmlRpcServer::setLocale called');
        I18N::setLocale($params[0]);
        $_SESSION['locale'] = $params[0];
        return I18N::getLocaleCode();
    }
    
    // }}}
    // XmlRpcServer::getSupportedLocales() {{{

    /** Retourne les locales supportées
     *
     */
    protected function getSupportedLocales($method) {
        $this->auth();
        $this->log('XmlRpcServer::getSupportedLocales called');
        return I18N::getSupportedLocales();
    }

    // }}}
    // XmlRpcServer::register() {{{

    /**
     * Place en session un dictionnaire
     *
     * @param array $array
     * @access public
     * @return boolean
     */
    protected function register($method, $params){
        $this->auth();
        $this->log('XmlRpcServer::sessionRegister called');
        $array = $params[0];
        foreach($array as $key => $val){
            $_SESSION[$key] = $val;
        }
        return true;
    }
    
    // }}}
}

?>
