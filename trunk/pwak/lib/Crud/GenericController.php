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
 * @version   SVN: $Id: GenericController.php,v 1.10 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GenericController {
    // constantes {{{

    const FEATURE_GRID       = 'grid';
    const FEATURE_SEARCHFORM = 'searchform';
    const FEATURE_ADD        = 'add';
    const FEATURE_EDIT       = 'edit';
    const FEATURE_DELETE     = 'del';
    const FEATURE_VIEW       = 'view';

    const FAKE_INDEX = '##';

    // }}}
    // propriétés {{{

    /**
     * Nom de la classe de l'objet.
     *
     * @var string
     * @access protected
     */
    protected $clsname = '';

    /**
     * Nom alternatif, quand il y a plusieurs grid/addedit pour une même classe
     *
     * @var string
     * @access protected
     */
    protected $altname = '';

    /**
     * Action grid, add, edit ou delete
     *
     * @var string
     * @access protected
     */
    protected $action = GenericController::FEATURE_GRID;

    /**
     * Template à utiliser.
     *
     * @var string
     * @access protected
     */
    protected $htmlTemplate = BASE_TEMPLATE;

    /**
     * Profiles authorisés à accéder à la page
     *
     * @var array
     * @access protected
     */
    protected $profiles = array();

    /**
     * Instance de Auth
     *
     * @var object Auth
     * @access protected
     */
    protected $auth = false;

    /**
     * Instance de Session si useSession vaut true.
     *
     * @var object Session
     * @access protected
     */
    protected $session = false;

    /**
     * Titre du formulaire ou du grid
     *
     * @var string
     * @access protected
     */
    protected $title = '';

    /**
     * Instance de l'object en cours.
     *
     * @var Object
     * @access protected
     */
    protected $object = false;

    /**
     * Id de l'objet en cour d'édition
     *
     * @var integer
     * @access protected
     */
    protected $objID = false;

    /**
     * Détermine si oui ou non la classe utlise les sessions.
     *
     * @var boolean
     * @access protected
     */
    protected $useSession = false;

    /**
     * Détermine si oui ou non la classe utilise AJAX
     *
     * @var boolean
     * @access protected
     */
    protected $useAJAX = false;

    /**
     * Tableau des propriétés pour l'objet en cours.
     *
     * @var array
     * @access protected
     */
    protected $attrs = array();

    /**
     * Tableau des relations pour l'objet en cours
     *
     * @var array
     * @access protected
     */
    protected $links = array();

    /**
     * Tableau des fonctionalités pour l'objet en cours.
     *
     * @var array
     * @access protected
     */
    protected $features = array();

    /**
     * mapping de l'objet en cours.
     *
     * @var array
     * @access protected
     */
    protected $mapping = array();

    /**
     * URL de retour
     *
     * @var string
     * @access protected
     */
    protected $returnURL = false;

    /**
     * Fichiers js requis
     *
     * @var array
     * @access protected
     */
    protected $jsRequirements = array();

    /**
     * Fichiers css requis
     *
     * @var array
     * @access protected
     */
    protected $cssRequirements = array();

    // }}}
    // GenericController::__construct() {{{

    /**
     * Constructeur.
     *
     * Le constructeur prend en paramètres un tableau qui peut contenir les
     * valeurs suivantes:
     *
     * <b>Paramètres obligatoires:</b>
     * - clsname: nom de l'entité de base
     * - id: id de l'objet à éditer (obligatoire uniquement si action vaut edit
     * ou delete, si delete id peut être un tableau d'ids)
     *
     * <b>Paramètres facultatifs:</b>
     * - action: le type d'action (grid, add, edit, del)
     * - altname: nom alternatif si plusieurs grid ou addEdit pour un même objet
     * - profiles: un tableau de profils
     * - return_url: l'url de retour à utilise
     * - template: le template html à utiliser
     * - title: le titre du grid ou du formulaire
     * - use_ajax: true si la page utilise AJAX
     * - use_session: true si la page utilise les sessions
     *
     * @param array $params
     * @access public
     * @return void
     */
    public function __construct($params) {
        $this->clsname = $params['clsname'];
        $this->altname = isset($params['altname']) ?
            $params['altname'] : $this->clsname;
        if(isset($params['action'])) {
            $this->action = $params['action'];
        }
        if(isset($params['id'])) {
            $this->objID = $params['id'];
        }
        if(isset($params['template'])) {
            $this->htmlTemplate = $params['template'];
        }
        if(isset($params['profiles'])) {
            $this->profiles = $params['profiles'];
        }
        if(isset($params['use_session'])) {
            $this->useSession = $params['use_session'];
        }
        if(isset($params['use_ajax'])) {
            $this->useAJAX = $params['use_ajax'];
        }
        if(isset($params['return_url'])) {
            $this->returnURL = urldecode($params['return_url']);
        }

        try {
            $this->checkParams();
        } catch(Exception $exc) {
            throw new Exception($exc->getMessage());
            exit(-1);
        }

        require_once(MODELS_DIR . '/' . $this->clsname . '.php');
        $label = call_user_func(array($this->clsname, 'getObjectLabel'));
        $this->title = isset($params['title']) ? $params['title'] : $label;
        $this->features = $this->getFeatures();
        $this->mapping = $this->getMapping();
        $this->attrs = call_user_func(array($this->clsname, 'getProperties'));
        $this->links = call_user_func(array($this->clsname, 'getLinks'));
    }

    // }}}
    // GenericController::auth() {{{

    /**
     * auth
     *
     * @access protected
     * @return void
     */
    protected function auth() {
        $this->auth = Auth::singleton();
        if (count($this->profiles) == 0) {
            $this->profiles = $this->auth->getProfileArrayForPage(
                $this->altname.'List');
        }
        $this->auth->checkProfiles($this->profiles);
    }

    // }}}
    // GenericController::render() {{{

    /**
     * render
     *
     * Méthode à surchager dans les classes filles
     *
     * @access public
     * @return void
     */
    public function render() {
    }

    // }}}
    // GenericController::includeSessionRequirements() {{{

    /**
     * Inclu les fichiers nécéssaires pour la session en fonction des attributs
     * et liens de l'objet en cours.
     *
     * @access protected
     * @return void
     */
    protected function includeSessionRequirements() {
        foreach ($this->attrs as $name=>$type) {
            if (is_string($type)) {
                // inclu les fichiers de définition des foreignkeys
                require_once(MODELS_DIR . '/' . $type . '.php');
            }
        }
        foreach ($this->links as $name=>$params) {
            // inclu les fichiers de définition des classes liées
            require_once(MODELS_DIR . '/' . $params['linkClass'] . '.php');
        }
    }

    // }}}
    // GenericController::getFeatures() {{{

    /**
     * Retourne les 'features' de l'objet
     *
     * @access protected
     * @return array
     */
    protected function getFeatures() {
        return call_user_func(array($this->clsname, 'getFeatures'));
    }

    // }}}
    // GenericController::getMapping() {{{

    /**
     * Retourne le mapping de l'objet.
     *
     * @access protected
     * @return array
     */
    protected function getMapping() {
        return call_user_func(array($this->clsname, 'getMapping'));
    }

    // }}}
    // GenericController::getReturnURL() {{{

    /**
     * Retourne l'url de retour.
     *
     * @access protected
     * @return string
     */
    protected function getReturnURL() {
        return $this->returnURL;
    }

    // }}}
    // GenericController::addJSRequirements() {{{

    /**
     * Permet d'ajouter des dépendances de fichiers javaScripts à la page.
     *
     * <code>
     * $controller->addJSRequirements('fichier1.js', ..., 'fichierN.js');
     * </code>
     *
     * @access protected
     * @return void
     */
    protected function addJSRequirements() {
        $args = func_get_args();
        foreach ($args as $js) {
            $this->jsRequirements[] = $js;
        }
    }

    // }}}
    // GenericController::addCSSRequirements() {{{

    /**
     * Permet d'ajouter des dépendances de fichiers css.
     *
     * <code>
     * $controller->addCSSRequirements('fichier1.css', ..., 'fichierN.css');
     * </code>
     *
     * @access protected
     * @return void
     */
    protected function addCSSRequirements() {
        $args = func_get_args();
        foreach ($args as $css) {
            $this->cssRequirements[] = $css;
        }
    }

    // }}}
    // GenericController::checkParams() {{{

    /**
     * Méthode appelée lors de l'appel au constructeur pour vérifier les
     * paramètres.
     *
     * @access protected
     * @return void
     */
    protected function checkParams() {
        $file = MODELS_DIR . '/' . $this->clsname . '.php';
        if(!file_exists(PROJECT_ROOT . '/' . LIB_DIR . '/' . $file)) {
            throw new Exception(
                sprintf('le fichier %s correspondant à l\'objet %s n\'existe pas',
                $file, $this->clsname));
        }
        $idActions = array(self::FEATURE_EDIT, self::FEATURE_DELETE, self::FEATURE_VIEW);
        if(in_array($this->action, $idActions) && !$this->objID) {
            throw new Exception('Le paramètre id est obligatoire pour les actions edit, del et view');
        }

    }

    // }}}
    // GenericController::getElementType() {{{

    /**
     * Retourne le type de l'élément.
     *
     * @param string $name nom de l'élément
     * @access protected
     * @return int
     */
    protected function getElementType($name) {
        $type = isset($this->attrs[$name])?$this->attrs[$name]:false;
        if (is_string($type)) {
            return Object::TYPE_FKEY;
        }
        if(!$type) {
            $type = isset($this->links[$name])?$this->links[$name]:false;
            if($type && $type['multiplicity']=='manytomany') {
                return Object::TYPE_MANYTOMANY;
            }
        }
        return $type;

    }

    // }}}
    // GenericController::onBeforeDisplay() {{{

    /**
     * Méthode appelée avant l'affichage du résultat.
     *
     * @access protected
     * @return void
     */
    protected function onBeforeDisplay() {
    }

    // }}}
    // GenericController::preContent() {{{

    /**
     * Permet d'ajouter un contenu personalisé avant le formulaire.
     * Cette méthode doit être surchargée et retourner une chaine html.
     *
     * @access protected
     * @return string
     */
    protected function preContent() {
        return '';
    }

    // }}}
    // GenericController::additionalFormContent() {{{

    /**
     * Permet d'ajouter un contenu personalisé à la fin du addedit
     * Cette méthode doit être surchargée et retourner le contenu au format
     * html.
     *
     * @access protected
     * @return string
     */
    protected function additionalFormContent() {
        return '';
    }

    // }}}
    // GenericController::postContent() {{{

    /**
     * Permet d'ajouter un contenu personalisé après le formulaire.
     * Cette méthode doit être surchargée et retourner une chaine html.
     *
     * @access protected
     * @return string
     */
    protected function postContent() {
        return '';
    }

    // }}}
}

?>
