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

define('AJAX_MODE_GET',  0);
define('AJAX_MODE_POST', 1);

/**
 * Serveur AJAX/JSON
 *
 * NOTE IMPORTANTE:
 * ===============
 * Le RFC4627 (http://www.ietf.org/rfc/rfc4627.txt) spécifie que l'encoding
 * du texte json doit être "unicode", il faut donc faire attention à encoder
 * les caractères non unicode avant de serializer les données au format json,
 * pour des raisons de performances le serveur ne le fait pas automatiquement,
 * il est du ressort du programmeur de le faire donc.
 *
 * Exemple de code pour démarrer le serveur:
 * ========================================
 *
 * <code>
 * $ajaxServer = new AjaxServer();
 * $ajaxServer->handleRequest();
 * </code>
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package Framework
 * @subpackage Ajax
 */
class AjaxServer
{
    // propriétés {{{

    /**
     * Le tableau des fonctions/methodes registerees.
     *
     * @var array
     * @access protected
     */
    protected $registeredMethods = array(
        'test',
        'getRegisteredMethods',
        'loadCollection',
        'load',
        'getCollectionForSelect',
        'callStaticMethod',
        'getSessionContent',
        'dndSortEntity',
        'getUploadedFiles'
    );

    /**
     * Mode d'envoi des donnees par le client: POST par defaut.
     * TODO: gerer GET: necessaire?
     *
     * @var string
     * @access public
     */
    public $mode = AJAX_MODE_POST;  // AJAX_MODE_GET;

    /**
     * Encoding de la reponse renvoyee; par defaut: ISO-8859-1 (latin1).
     *
     * @var string
     * @access public
     */
    public $encoding = 'utf-8';

    /**
     * Instance de PEAR::Log
     *
     * @var object Log
     * @access private
     */
    private $_logger = false;

    // }}}
    // Constructeur {{{

    /**
     * Constructor
     *
     * @access protected
     */
    public function __construct() {
        $this->_logger = Tools::loggerFactory();
    }

    // }}}
    // AjaxServer::handleRequest() {{{

    /**
     * Méthode qui gère les requêtes effectuées par les clients ajax.
     * Filtre pour ne permettre d'appeler que les methodes registrees.
     *
     * @access public
     * @return void
     */
    public function handleRequest() {
        if (session_id()) {
            // prolonge les données en session si une session est ouverte
            SearchTools::prolongDataInSession();
        }
        // Recupere la requete envoyee par le client
        if ($this->mode == AJAX_MODE_GET) {
            // XXX bug mochikit ??
            $argv = explode('&args=', $_SERVER['QUERY_STRING']);
            $method = array_shift($argv);  // on "nettoie" le tableau
            $method = $_GET['method'];
        } else {  //  AJAX_MODE_POST
            $argv = json_decode($_POST['args']);
            $method = $_POST['calledMethod'];
        }
        
        //$this->log('count($argv): ' . count($argv));
        // formattage et déserialization des arguments reçus
        for ($i=0; $i<count($argv); $i++) {
            $arg = json_decode(urldecode($argv[$i]), true);
            // json_decode retourne null si la chaine json n'est ni un
            // objet ni un tableau conformément au RFC
            $argv[$i] = $arg!==null?$arg:trim(urldecode($argv[$i]), '"\'');
            // gestion correcte des booléens
            if (strtolower($argv[$i]) == 'true') $argv[$i] = true;
            if (strtolower($argv[$i]) == 'false') $argv[$i] = false;
            // gestion correct des entiers et des flottants
            if (is_numeric($argv[$i]) && strpos($argv[$i], '.') === false)
                $argv[$i] = intval($argv[$i]);
            elseif (is_numeric($argv[$i]) && strpos($argv[$i], '.'))
                $argv[$i] = floatval($argv[$i]);
        }
        // vérifie si la méthode est dans le registre
        if (!in_array($method, $this->registeredMethods)) {
            $this->send($this->error('Error: call to an unregistered method.'));
            exit(1);
        }
        // Appelle la methode
        try {
            $this->send(call_user_func_array(array($this, $method), $argv));
        } catch (Exception $exc) {
            $this->send($this->error($exc->getMessage()));
            exit(1);
        }
    }

    // }}}
    // AjaxServer::test() {{{

    /**
     * Méthode qui retourne les arguments passés en paramètre sous forme de
     * chaine json, permet de tester les appels ajax.
     *
     * @access public
     * @return string
     */
    public function test() {
        $args = func_get_args();
        throw new Exception('invalid callback');
        return json_encode(implode(', ', $args));
    }

    // }}}
    // AjaxServer::getRegisteredMethods() {{{

    /**
     * Méthode qui retourne la liste des méthodes appelables depuis les clients
     *
     * @access public
     * @return string json encoded array of strings
     */
    public function getRegisteredMethods() {
        return json_encode($this->registeredMethods);
    }

    // }}}
    // AjaxServer::loadCollection() {{{

    /**
     * Retourne un tableau de tableau correpondant aux propriétés de chaque
     * objet de la collection, les arguments sont les mêmes que ceux de
     * Object::loadCollection().
     * Si $fields est vide, seul les attributs 'Id' et 'toString' seront
     * retournés, sinon les attributs listés dans fields seront retournés en
     * plus de ces 2 derniers.
     *
     * @access public
     * @see    Mapper::loadCollection()
     * @param  string $entity
     * @param  array  $filter
     * @param  array  $order
     * @param  array  $fields
     * @param  int    $rows
     * @param  int    $page
     * @param  int    $limit
     * @param  bool   $noCache
     * @return string json encoded array
     */
    public function loadCollection($entity, $filter=array(), $order=array(),
        $fields=array(), $rows=0, $page=1, $limit=false, $noCache=false) {
        if (empty($fields)) {
            $toStringAttr = Tools::getToStringAttribute($entity);
            $fields = is_array($toStringAttr)?$toStringAttr:array($toStringAttr);
        }
        if (empty($order)) {
            $toStringAttr = isset($toStringAttr)?
                $toStringAttr:Tools::getToStringAttribute($entity);
            $order = is_array($toStringAttr)?
                array($toStringAttr[0] => SORT_ASC):array($toStringAttr => SORT_ASC);
        }
        $col = object::loadCollection($entity, $filter, $order, $fields, $rows,
            $limit, $noCache);
        return $col->toJSON($fields);
    }
    // }}}
    // AjaxServer::load() {{{

    /**
     * Retourne un tableau correpondant aux propriétés d'un objet, les
     * arguments sont les mêmes que ceux de Object::load().
     * Si $fields n'est pas un tableau vide, seul les attributs spécifiés
     * seront retournés.
     *
     * @access public
     * @see    Mapper::load()
     * @param  string $entity
     * @param  array  $filter
     * @param  array  $fields
     * @param  bool   $noCache
     * @return string json encoded array
     */
    public function load($entity, $filter, $fields=array(), $noCache=false) {
        $obj = object::load($entity, $filter, $fields, $noCache);
        if (Tools::isException($obj)) {
            return json_encode(array());
        }
        return $obj->toJSON($fields);
    }

    // }}}
    // AjaxServer::getCollectionForSelect() {{{

    /**
     * AjaxServer::getCollectionForSelect()
     *
     * @param  string $entity
     * @param  array  $filter
     * @access public
     * @return string json encoded array
     */
    public function getCollectionForSelect($entity, $filter=array()) {
        $return = array();
        $array = SearchTools::createArrayIDFromCollection($entity, $filter);
        foreach($array as $id => $toString){
            $return[] = array('id' => $id, 'toString' => utf8_encode($toString));
        }
        return json_encode($return);
    }

    // }}}
    // AjaxServer::callStaticMethod() {{{

    /**
     * Appelle la méthode statique $method de la classe $entity.
     *
     * @access public
     * @param  string $entity
     * @param  string $method
     * @return string json
     */
    public function callStaticMethod($entity, $method) {
        require_once MODELS_DIR . DIRECTORY_SEPARATOR . $entity . '.php';
        $result = call_user_func(array($entity, $method));
        if (strtolower(substr($method, -10)) == 'constarray') {
            // cas particulier pour constarray
            foreach ($result as $val=>$label) {
                $return[] = array('id'=>$val, 'toString'=>utf8_encode($label));
            }
        }
        return json_encode($return);
    }

    // }}}
    // AjaxServer::dndSortEntity() {{{

    /**
     * Appelle la méthode statique $method de la classe $entity.
     *
     * @access public
     * @param  string $entity
     * @param  string $dndSortableField
     * @param  array $ids
     * @return string json
     */
    public function dndSortEntity($entity, $dndSortableField, $ids, $page=1) {
        $setter = 'set' . $dndSortableField;
        if (!method_exists($entity, $setter) || empty($ids)) {
            return json_encode($entity . '::' . $setter . ' does not exists.');
        }
        $col = Object::loadCollection($entity, array('Id' => $ids));
        $index = (($page-1) * count($ids)) + 1;
        foreach ($ids as $id) {
            $item = $col->getItemById($id);
            if (!($item instanceof $entity)) {
                continue;
            }
            $item->$setter($index);
            $item->save();
            $index++;
        }
        return json_encode(true);
    }

    // }}}
    // AjaxServer::error() {{{

    /**
     * contruit une erreur json.
     * Ce format pour les erreurs permet de faire côté js:
     * <code>
     * if (data.isError) {
     *     alert('Erreur ' + data.errorCode + ': ' + data.errorMessage);
     * } else {
     *     // code ici
     * }
     * </code>
     *
     * @access public
     * @param  string $errorMessage
     * @param  int $errorCode
     * @return string
     */
    protected function error($errorMessage, $errorCode=0){
        return json_encode(
            array(
                'isError'      => true, // pour déterminer si erreur côté js
                'errorCode'    => $errorCode,
                'errorMessage' => utf8_encode($errorMessage)
            )
        );
    }
    // }}}
    // AjaxServer::log() {{{

    /**
     * Loggue la chaine $data via PEAR::Log
     *
     * @access protected
     * @param  mixed $data
     * @param  const $level
     * @return void
     */
    protected function log($data, $level=PEAR_LOG_NOTICE){
        $this->_logger->log($data, $level);
    }
    // }}}
    // AjaxServer::send() {{{

    /**
     * Envoie les données encodées au browser.
     *
     * @static
     * @access protected
     * @return void
     */
    protected function send($data) {
        // pour éviter le cache des données par le navigateur
    	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
    	header('Pragma: no-cache');                         // HTTP/1.0
        // approuvé: http://www.iana.org/assignments/media-types/application/
        header('Content-Type: application/json; charset=' . $this->encoding);
        // La ligne suivante est commentee car retourne une erreur si
        // la methode appelee retourne : []
        //header('Content-Length: ' . strlen($data));
        echo $data;
    }
    // }}}
    // AjaxServer::getSessionContent() {{{
    /**
     * Récupere en session les variables dont les noms sont passés en parametre.
     *
     * @static
     * @access public
     * @param  array of strings $varNames nom des champs à recuperer
     * @return string json
     */
    public function getSessionContent($varNames=array()) {
        $auth = Auth::Singleton();
        $return = array();

        if (!empty($varNames)) {
            foreach($_SESSION as $key => $val) {
                if (in_array($key, $varNames)) {
                    $return[$key] = $val;
                }
            }
        }
        //$this->log('count: ' . count($return));
        return json_encode($return);
    }
    // }}}
    // AjaxServer::getUploadedFiles() {{{

    /**
     * getUploadedFiles 
     * 
     * @param mixed $noimage null pout tout les fichier, true pour ne pas 
     * prendre les images et false pour ne prendre que les images 
     * @access public
     * @return void
     */
    public function getUploadedFiles($noimage=null) {
        $return = Upload::getUploadedFiles($noimage);
        return json_encode($return);
    }

    // }}}
}

?>
