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

if (!defined('ROOT_USERID')) {
    define('ROOT_USERID', 1);
}

/**
 * Dépendences
 */
require_once('adodb/adodb.inc.php');

/**
 * Auth
 * Classe de base pour la gestion de l'authentification.
 *
 * TODO:
 *  - beaucoup de méthodes sont redondantes et pas très pertinentes ici, il
 *    faudra voir si on peut les virer, ou en tout cas les mettre ailleurs,
 *  - la connection à la bado ne devrait pas se faitre dans cette classe, ça
 *    fait partie des choses à débordéliser au niveau du config.inc.php
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package Framework
 */
class Auth {
    // propriétés {{{

    /**
     * Instance singleton
     *
     * @var    object $instance
     * @access protected
     */
    protected static $instance = false;

    /**
     * Auth::hasAuth
     *
     * @var    object $instance
     * @access public
     */
    public static $hasAuth = true;

    // }}}
    // Constructeur {{{

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
    }

    // }}}
    // Auth::singleton() {{{

    /**
     * Retourne un singleton Auth
     *
     * @access public
     * @static
     */
    public static function Singleton() {
        if (!self::$instance) {
            self::$hasAuth = @include_once MODELS_DIR . '/UserAccount.php';
            Session::singleton();
            self::$instance = new Auth();
        }
        return self::$instance;
    }

    // }}}
    // Auth::login() {{{

    /**
     * Loggue un utilisateur avec un login et password
     *
     * @access public
     * @param string $login
     * @param string $password
     * @return mixed UserAccount ou Exception
     */
    public function login($login, $realm, $pwd) {
        if (!self::$hasAuth) {
            return false;
        }
        $user = Object::load('UserAccount',
            array('Login'=>$login, 'Password'=>sha1($pwd)));
        $dbID = defined('DATABASE_ID')?DATABASE_ID:false;
        if (Tools::isEmptyObject($user) ||
            ($dbID !== false && $user->dbID != null && $user->dbID != $dbID)) {
            unset($_SESSION[REALM_SESSION_NAME], $_SESSION[USER_SESSION_NAME]);
            return new Exception(E_AUTH_FAILED);
        }
        if (method_exists($user, 'getActive') && !$user->getActive()) {
            unset($_SESSION[REALM_SESSION_NAME], $_SESSION[USER_SESSION_NAME]);
            return new Exception(E_AUTH_FAILED);
        }
        $_SESSION[REALM_SESSION_NAME] = $realm;
        $_SESSION[USER_SESSION_NAME] = $user->getId();
        return $user;
    }

    // }}}
    // Auth::logout() {{{

    /**
     * Déconnecte l'utilisateur connecté.
     *
     * @access public
     * @return void
     */
    public function logout() {
        session_destroy();
        if (isset($_COOKIE[Navigation::NAVIGATION_VAR])) {
            self::setCookie(Navigation::NAVIGATION_VAR, '', -3600);
        }
    }

    // }}}
    // Auth::getRealm() {{{

    /**
     * Retourne le realm actuel
     *
     * @access public
     * @return string
     */
    public function getRealm() {
        if (isset($_SESSION[REALM_SESSION_NAME])) {
            return $_SESSION[REALM_SESSION_NAME];
        }
        return '';
    }

    // }}}
    // Auth::getUser() {{{

    /**
     * Retourne l'utilisateur (useraccount) connecté ou false.
     *
     * @access public
     * @return mixed object UserAccount ou false
     */
    public function getUser() {
        if (!self::$hasAuth) {
            return false;
        }
        if (isset($_SESSION[USER_SESSION_NAME])) {
            return Object::load('UserAccount', $_SESSION[USER_SESSION_NAME]);
        }
        return false;
    }

    // }}}
    // Auth::getUserId() {{{

    /**
     * Retourne l'id de l'utilisateur connecté.
     *
     * @access public
     * @return mixed integer ou false
     */
    public static function getUserId() {
        if (!self::$hasAuth) {
            return false;
        }
        if (isset($_SESSION[USER_SESSION_NAME])) {
            return $_SESSION[USER_SESSION_NAME];
        }
        return false;
    }

    // }}}
    // Auth::getActor() {{{

    /**
     * Methode proxy: retourne l'acteur associé à l'utilisateur connecté ou
     * false sinon.
     *
     * @access public
     * @return mixed object Actor ou false
     */
    public function getActor() {
        if (!self::$hasAuth) {
            return false;
        }
        return ($this->isUserConnected())?$this->getUser()->getActor():false;
    }

    // }}}
    // Auth::getActorId() {{{

    /**
     * Methode proxy: retourne l'id de l'acteur associé à l'utilisateur
     * connecté ou false.
     *
     * @access public
     * @return mixed integer ou false
     */
    public function getActorId() {
        if (!self::$hasAuth) {
            return false;
        }
        $actor = $this->getActor();
        return (method_exists($actor, 'getId'))?$actor->getId():false ;
    }

    // }}}
    // Auth::getIdentity() {{{

    /**
     * Methode proxy: retourne le nom (identity) de l'utilisateur connecté ou
     * la chaîne 'Aucun utilisateur'.
     *
     * @access public
     * @return string
     */
    public function getIdentity() {
        if (!self::$hasAuth) {
            return false;
        }
        return ($this->isUserConnected())?
             $this->getUser()->getIdentity():E_NO_USER;
    }

    // }}}
    // Auth::isUserConnected() {{{

    /**
     * Retourne true si un utilisateur est connecté et false sinon.
     *
     * @access public
     * @return boolean
     */
    public function isUserConnected() {
        return self::getUserId() > 0;
    }

    // }}}
    // Auth::isRootUserAccount() {{{

    /**
     * Retourne true si l'utilisateur connecté est l'utilisateur root.
     *
     * @access public
     * @return boolean
     */
    public function isRootUserAccount(){
        return $this->getUserId() == ROOT_USERID;
    }

    // }}}
    // Auth::isAdmin() {{{

    /**
     * Retourne true si le profil de l'utilisateur connecté est ADMIN.
     *
     * @access public
     * @return boolean
     */
    public function isAdmin() {
        $pf = $this->getUser()->getProfile();
        return (
            $pf == UserAccount::PROFILE_ADMIN || 
            $pf == UserAccount::PROFILE_ADMIN_WITHOUT_CASHFLOW ||
            $pf == UserAccount::PROFILE_ROOT
        );
    }

    // }}}
    // Auth::checkProfiles() {{{

    /**
     * Description du tableau $options:
     * url : l'url de redirection après check
     * showErrorDialog : si true affiche un dialogue d'erreur
     * extraparams : un tableau contenant des paramètres à passer dans l'url
     *
     * @access public
     * @param  array $profilesArray un tableau de profils (constantes)
     * @param  array $options un tableau associatif (voir ci dessus)
     * @return boolean
     */
    public function checkProfiles($profilesArray = array(),
        $options = array('url'=>false , 'showErrorDialog'=>true, 'redirect'=>true)) {
        if (!self::$hasAuth) {
            return true;
        }
        if ($this->isRootUserAccount()) {
            return true;
        }
        if ($this->isUserConnected()) {
            // si on ne passe pas de profiles, on va voir dans menu.ini
            if (count($profilesArray) == 0) {
                $profilesArray = $this->getProfileArrayForPage();
                $options['showErrorDialog'] = true;
            }
            $profile = $this->getUser()->getProfile();

            // l'utilisateur ne possède pas les droits
            if (false == in_array($profile, $profilesArray)) {
                if ($options['showErrorDialog']) {
                    $this->showMissingProfilesDialog();
                }
                return false;
            }
            return true;
        } else {
            if (isset($options['redirect']) && !$options['redirect']) {
                return false;
            }
            if (!isset($options['url']) || false == $options['url']) {
                $options['url'] = $_SERVER['PHP_SELF'];
            }

            if (isset($options['ExtraParams']) &&
                count($options['ExtraParams']) > 0) {
                if (!empty($options['url'])) {
                    $padding = '&';
                }
                $ExtraParams = '';
                foreach ($options['ExtraParams'] as $val) {
                    if (is_array($_REQUEST[$val])) {
                        foreach ($_REQUEST[$val] as $Tkey => $Tvalue) {
                            $ExtraParams .= $padding . $val .
                                '[' . $Tkey . ']=' . $Tvalue ;
                            $padding = '&';
                        }
                    } else {
                        $ExtraParams .= $padding . $val . '=' .
                            $_REQUEST[$val];
                        $padding = '&';
                    }
                }
            }
            $ExtraParams = isset($ExtraParams)?$ExtraParams:'';
            Tools::redirectTo('Login.php?redirect=' . $options['url'] .
                $ExtraParams . '&' . SID);
            exit;
        }
    }

    // }}}
    // Auth::showMissingProfilesDialog() {{{

    /**
     * Affiche le dialogue d'erreur pour profil insuffisant.
     *
     * @access public
     * @param  $MissingProfilesArray : the missing profile Ids
     * @param  $debug : boolean : TRUE pour le debug ou test
     * @return void
     */
    public function showMissingProfilesDialog() {
        $message = sprintf(E_USER_NOT_ALLOWED, $this->getUser()->getIdentity());
        $retURL = isset($_REQUEST['retURL'])?$_REQUEST['retURL']:'home.php';
        Template::errorDialog($message, $retURL);
        exit;
    }

    // }}}
    // Auth::getProfile() {{{

    /**
     * Retourne le Profil de l'utilisateur connecté ou false.
     *
     * @access public
     * @return mixed object Profile ou false
     */
    public function getProfile() {
        if (!self::$hasAuth) {
            return false;
        }
        return ($this->isUserConnected())?$this->getUser()->getProfile():false;
    }

    // }}}
    // Auth::getProfileArrayForPage() {{{

    /**
     * Retourne le tableau des profiles autorisés pour la page en cours.
     *
     * @access public
     * @param  string path optionnel, le nom de la page en cours sans l'extension
     * @return array of integer
     */
    public function getProfileArrayForPage($path = false) {
        if (!$path) {
            $path = UrlTools::getPageNameFromURL();
        }
        $nav = Navigation::singleton();
        $nav->authFunction = array(Auth::singleton(), 'checkProfiles');
        $nav->useCookie = true;
        $profiles = $nav->getProfileArrayForActivePage($path);
        return $profiles;
    }

    // }}}
    // Auth::getDataBaseOwner() {{{

    /**
     * Retourne l'Actor qui est DataBaseOwner, oubien une exception
     *
     * @access public
     * @static
     * @return object Actor
     */
    function getDataBaseOwner() {
        $actorMapper = Mapper::singleton('Actor');
        $dbo = $actorMapper->load(array('DataBaseOwner' => 1));
        return $dbo;
    }

    // }}}
    // Auth::setCookie() {{{

    /**
     * php set_cookie wrapper with better (and configurables) default values.
     *
     * @access public
     * @param string $name the cookie name
     * @param string $val the cookie value
     * @param int $t cookie expiration time (default: until browser is closed)
     * @param string $p cookie path (default: constant COOKIE_PATH)
     * @param string $d cookie domain (default: constant COOKIE_DOMAIN)
     * @static
     * @return boolean
     */
    public static function setCookie($name, $val, $t=0, $p=false, $d=false) {
        if ($p === false) $p = COOKIE_PATH;
        if ($d === false) $d = COOKIE_DOMAIN;
        return setcookie($name, $val, $t, $p, $d);
    }

    // }}}
}

?>
