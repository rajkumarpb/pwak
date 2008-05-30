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
 * @version   SVN: $Id: Client.php,v 1.5 2008-05-30 09:23:49 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

define('E_AUTHENTICATION'           , -1);
define('E_SOCKET'                   , -2);
define('E_URL'                      , -3);
define('E_HTTP'                     , -4);
define('E_NOT_WELL_FORMED_XML'      , -32700);
define('E_UNSUPPORTED_ENCODING'     , -32701);
define('E_INVALID_CHAR_ENCODING'    , -32702);
define('E_INVALID_XMLRPC'           , -32600);
define('E_METHOD_NOT_FOUND'         , -32601);
define('E_INVALID_METHOD_PARAMETERS', -32602);
define('E_INTERNAL_ERROR'           , -32603);
define('E_APPLICATION_ERROR'        , -32500);
define('E_SYSTEM_ERROR'             , -32400);
define('E_TRANSPORT_ERROR'          , -32300);
// }}}

/**
 * Classe de base pour les clients XMLRPC.
 *  
 * DOCUMENTATION {{{
 *
 * Exemple de base:
 * ===============
 *
 * <code>
 * try {
 *     $cli = new XmlRpcClient('http://ateor.com/unsecure/rpc/');
 *     // ici namespace__method correspond à une méthode rpc:
 *     // namespace.method()
 *     $cli->namespace__method();
 * } catch (Exception $e) {
 *     echo 'Erreur: ' . $e->getMessage();
 * }
 * </code>
 *
 *
 * Exemple avancé, avec auth HTTP, auth php et arguments:
 * =====================================================
 * <code>
 * $cli = new XmlRpcClient(
 *     'http://user:pass@ateor.com/secure/rpc/', 
 *     array(
 *         'user'=>'user@realm',
 *         'passwd'=>'secret',
 *         'verbose'=>true
 *     )
 * );
 * try {
 *     echo $cli->math__multiply(3, 4);
 *     // affiche 12
 * } catch (Exception $e) {
 *     echo "Erreur: " . $e->getMessage() . " (" . $e->getCode() . ")";
 * }
 * </code>
 *
 * }}}
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License 
 * @package Framework
 * @subpackage XmlRpc 
 */
class XmlRpcClient {
    // propriétés {{{

    /**
     * URL complète vers le serveur xmlrpc, elle peut comporter aussi les 
     * crédentials de l'auth HTTP (ex: http://user:pass@host.com/rpc/)
     *
     * @var string
     * @access public
     */
    public $url = '';

    /**
     * Port du serveur XMLRPC.
     *
     * @var integer
     * @access public
     */
    public $port = 80;

    /**
     * Nom d'utilisateur pour l'auth php (pas http)
     *
     * @var string
     * @access public
     */
    public $user = false;

    /**
     * Mot de passe pour l'auth php (pas http)
     *
     * @var string
     * @access public
     */
    public $passwd = false;

    /**
     * Encoding de l'enveloppe xmlrpc, par defaut: ISO-8859-1 (latin1).
     *
     * @var string
     * @access public
     */
    public $encoding = 'iso-8859-1';

    /**
     * Booléen verbose, si true les requêtes client/serveur sont dumpées.
     *
     * @var boolean
     * @access public
     */
    public $verbose = false;

    /**
     * Booléen secure, si la communication se fait via ssl/tls.
     *
     * @var boolean
     * @access public
     */
    public $secure = false;

    /**
     * Nom "User-Agent" du client XMLRPC
     *
     * @var string
     * @access public
     */
    protected $_userAgentName = 'Ateor funky PHP XMLRPC client';

    /**
     * Version du client XMLRPC
     *
     * @var string
     * @access public
     */
    protected $_userAgentVersion = '$Version$';

    /**
     * Identifiant de session
     *
     * @var string
     * @access protected
     */
    protected $_sid = false;

    // }}}
    // Constructeur {{{

    /**
     * Constructeur.
     *
     * @access protected
     * @throws Exception
     */
    public function __construct($url, $options=array()) { 
        $this->url = $url;
        if (isset($options['user'])) {
            $this->user = $options['user'];
        }
        if (isset($options['passwd'])) {
            $this->passwd = $options['passwd'];
        }
        if (isset($options['verbose'])) {
            $this->verbose = $options['verbose'];
        }
        if (isset($options['encoding'])) {
            $this->encoding = $options['encoding'];
        }
        if (isset($options['secure'])) {
            $this->secure = $options['secure'];
        }
        try {
            $this->_urlTokens = $this->_parseURL();
        } catch (Exception $e) {
            throw $e;
        }
        // détecte le mode secure
        if (!$this->secure && in_array($this->_urlTokens['scheme'],
            array('https://', 'tls://', 'ssl://'))) {
            $this->secure = true;
        }
    }
    
    // }}}
    // XmlRpcClient::__call() {{{

    /**
     * Intercepteur de methodes.
     * Cela nous permet d'intercepter n'importe quel appel de methode rpc.
     * Le namespace et le nom de la méthode doivent être séparés par __ (double 
     * underscore).
     * 
     * @param  string $method nom namespace/méthode
     * @param  array $args tableau d'arguments à passer à la méthode
     * @access protected 
     * @throws Exception
     * @return mixed
     */
    protected function __call($method, $args) {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        }
        // auth php
        if (!$this->_sid && $this->user != false) {
            $credentials = explode('@', $this->user);
            $credentials[] = $this->passwd;
            try {
                $sid = $this->_callMethod('auth.login', $credentials);
            } catch (Exception $e) {
                throw $e;
            }
            if (!$sid) {
                throw new Exception('Authentication error', E_AUTHENTICATION);
            }
            $this->_sid = $sid;
        }
        // gestion du namespace
        $method = str_replace('__', '.', $method);
        // appel à la vrai méthode
        try {
            $return = $this->_callMethod($method, $args);
        } catch (Exception $e) {
            throw $e;
        }
        return $return;
    }
    
    // }}}
    // XmlRpcClient::_callMethod() {{{
    
    /**
     * Appelle une méthode rpc $methodName avec les paramètres $params à 
     * l'aide de fsockopen et retourne la réponse xmlrpc décodée au format php.
     * 
     * @access protected
     * @param  string $methodName
     * @param  array $params
     * @throws Exception
     * @return string 
     */
    protected function _callMethod($methodName, $params) {
        // output_options
        // http://xmlrpc-epi.sourceforge.net/main.php?t=php_api#output_options
        $output_options = array(
            'encoding'  => $this->encoding,  // encoding du xml
            'verbosity' => 'no_white_space'  // économie de bande passante
        );
        $host = $this->_urlTokens['host'];
        $path = $this->_urlTokens['path'];
        if (!empty($this->_urlTokens['query'])) {
            $path .= '?'.$this->_urlTokens['query'];
        }
        // encode la requête
        $request = xmlrpc_encode_request($methodName, $params);
        // contruit la requête
        $query = sprintf(
            "POST %s HTTP/1.1\r\nUser-Agent: %s/Version: %s\r\nHost: %s\r\n",
            $path, $this->_userAgentName, $this->_userAgentVersion, $host 
        );
        // auth http
        if (!empty($this->_urlTokens['user'])) {
            $hash = base64_encode($this->_urlTokens['user'] . ':' 
                . $this->_urlTokens['pass']);
            $query .= "Authorization: BASIC " . $hash . "\r\n";
        }
        // auth php via cookie de session
        if ($this->_sid) {
            // on passe le cookie
            $query .= sprintf("Cookie: PHPSESSID=%s\r\n", $this->_sid);
        }
        // headers nécessaires
        $query .= sprintf(
            "Content-Type: text/xml\r\nContent-Length: %s\r\n" . 
            "Connection: Close\r\n",
            strlen($request)
        );
        // envoie la requête
        $query .= "\r\n" . $request . "\r\n";
        if ($this->secure) {
            if ($this->port == 80) {
                $this->port = 443;
            }
            $host = 'tls://' . $host;
        }
        if ($this->verbose) {
            echo '<h1>Client request</h1>';
            echo "<h3>Opening connection to: $host:$this->port</h3>";
            echo '<h3>Headers:</h3>' . nl2br($query);
            echo '<h3>Body:</h3><xmp>' . $request . '</xmp>';
        }
        $sock = fsockopen($host, $this->port, $errno, $errstr);
        if (!$sock) {
            throw new Exception(
                sprintf(
                    'Impossible de se connecter à %s:%s (%s)', 
                    $host, $this->port, $errstr
                ),
                E_SOCKET
            );
        }
        if (!fputs($sock, $query, strlen($query))) {
            throw new Exception('Unable to send xmlrpc query.', E_SOCKET);
        }
        $response = $this->_read($sock);
        if ($this->verbose) {
            echo '<h1>Server response</h1>';
            echo '<h3>Code: ' . $response['code'] . '</h3>';
            echo '<h3>Headers:</h3>';
            foreach($response['headers'] as $key=>$val) {
                echo "$key: $val<br/>";
            }
            echo '<h3>Body:</h3><xmp>' . $response['body'] . '</xmp>';
        }
        if ($response['code'] == 401 || !strlen($response['body'])) {
            throw new Exception('Authentication error', E_AUTHENTICATION);
        }
        if ($response['code'] != 200) {
            throw new Exception('HTTP error: '.$response['body'], E_HTTP);
        }
        $ret = xmlrpc_decode($response['body']);
        // gestion des erreurs
        if (is_array($ret) && xmlrpc_is_fault($ret)) {
            throw new Exception($ret['faultString'], $ret['faultCode']);
        }
        return $ret;
    }
    
    // }}}
    // XmlRpcClient::_parseURL() {{{
    
    /**
     * La fonction native parse_url de php ne semble pas marcher avec nos url 
     * du style http://user@realm:passwd@example.com.
     * Réimplémentée from scratch donc...
     * 
     * @access protected
     * @param  string $url
     * @throws Exception
     * @return string 
     */
    private function _parseURL() {
        $rx = '/^(?P<scheme>(?:http|https|ssl|tls):\/\/)?'
            . '((?P<user>[\w@]+):(?P<pass>[^@]+))?@?'
            . '(?P<host>[^\/:]*)?:?'
            . '(?P<port>\d+)?'
            . '(?P<path>\/?[^\?]*)?\??'
            . '(?P<query>[^#]*)?#?'
            . '(?P<fragment>.*)?$/';
        if (!preg_match($rx, $this->url, $tokens)) {
            throw new Exception('Invalid URL "' . $this->url . '"', E_URL);
        }
        return array(
            'scheme'=>$tokens['scheme'],
            'user'=>$tokens['user'],
            'pass'=>$tokens['pass'],
            'host'=>$tokens['host'],
            'port'=>$tokens['port'],
            'path'=>$tokens['path'],
            'args'=>$tokens['query'],
            'anchor'=>$tokens['fragment']
        );
    }
    
    // }}}
    // XmlRpcClient::_read() {{{

    /**
     * Lit les données du socket passé en paramètre et construit une réponse 
     * sous forme de tableau, ex: 
     * array('code'=>200, 'headers'=>array(...), 'body'=>'...')
     *
     * @access private
     * @param  resource $socket
     * @return array $response
     */
    private function _read($socket) {
        $bufsize  = 1024;
        $response = array();
        $response['code'] = null;
        $response['headers'] = array();
        $response['body'] = null;
        $hdr = null;
        // parse les headers
        while (strlen($header = rtrim(fgets($socket, $bufsize)))) {
            if (preg_match('|HTTP/\d.\d (\d+) (\w+)|', $header, $matches)) {
                $response['code'] = (int)$matches[1];
            } else if (preg_match('|^\s|', $header)) {
                if ($hdr !== null) {
                    $response['headers'][$hdr] .= ' ' . trim($header);
                }
            } else {
                $pieces = explode(': ', $header, 2);
                $response['headers'][$pieces[0]] = isset($pieces[1])?
                    $pieces[1]:null;
            }
        }
        // le xml
        while (!feof($socket)) {
            $response['body'] .= fgets($socket, $bufsize);
        }
        fclose($socket);
        return $response;
    }
    
    // }}}
}

?>
