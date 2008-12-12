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

class Tools {
    /**
     * Constantes utilisées pour les regexp sur les macros
     */
    const FIRST_PASS_MACRO_REGEX = '/%([a-zA-Z0-9()=[\].#]+)(\|(.+?))?%/';
    const SECOND_PASS_MACRO_REGEX =
        '([a-zA-Z0-9]+)(\((?:([a-zA-Z0-9]+)=([a-zA-Z0-9]+))?\))?(?:\[([0-9]+|#)\])?';

    /**
     * loggerFactory()
     * Retourne une instance de PEAR::Log
     *
     * @return object PEAR::Log instance
     */
    static function loggerFactory() {
        // un cache
        static $_logger = false;
        // si pas déjà instancié on instancie le logger
        if (false == $_logger) {
            require_once('Log.php');
            // l'ip de l'utilisateur
            $ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'NO_IP';
            // les infos sur l'user connecté
            $auth = Auth::singleton();
            $userstr = '';
            if ($auth->isUserConnected()) {
                $user = $auth->getIdentity() . '@' . $auth->getRealm();
                $userstr = ' user=' . $user;
            }
            // et puis l'environement d'utilisation
            $ev = defined('ENVIRONMENT') ? ENVIRONMENT :
                     (defined('DEV_VERSION') ? (DEV_VERSION ? 'dev' : 'prod') :
                         'unknown'));
            // le prefixe
            $logPrefix  = sprintf('[ONLOGISTICS ip=%s%s sc=%s env=%s]',
                $ip, $userstr, $_SERVER['SCRIPT_NAME'], $ev);
            $_logger = Log::Singleton('syslog', LOG_LOCAL0 , $logPrefix);
        }
        return $_logger;
    }

    /**
     * Tools::duplicateObject()
     *
     * Duplique un objet en copiant les attributs et les liens *..* de l'objet
     * source vers la copie.
     * XXX NOTE IMPORTANTE: les liens 1..* ne sont *pas* dupliqués, ils doivent
     * être gérés manuellement.
     *
     * @static
     * @param object $source objet à copier
     * @return object
     */
    static function duplicateObject($source) {
        $class = get_class($source);
        $new = new $class();
        $new->generateId();
        $attrs = $source->getProperties();
        foreach ($attrs as $attr=>$type) {
            $setter = 'set'.$attr;
            $getter = is_string($type)?'get'.$attr.'Id':'get'.$attr;
            if (method_exists($source, $getter) && method_exists($new, $setter)) {
                $new->$setter($source->$getter());
            }
        }
        $links = $new->getLinks();
        foreach ($links as $name=>$array) {
            if ($array['multiplicity'] == 'manytomany') {
                // liens *..*: les objets liés ne sont pas dupliqués
                $setter = 'set' . $name . 'CollectionIds';
                $getter = 'get' . $name . 'CollectionIds';
                $new->$setter($source->$getter());
            }
        }
        return $new;
    }

    /**
     * Tools::isEmptyObject()
     * Détermine si oui ou nom l'objet est vide
     *
     * @param object $object
     * @return boolean
     */
    static function isEmptyObject($object) {
        return (false == is_object($object))
            || (method_exists($object, 'getId') && $object->getId() == 0)
            || ($object instanceof Collection && $object->getCount() == 0)
            || (Tools::isException($object));
    }

    /**
     * Tools::isException()
     * Détermine la présence d'une exception.
     *
     * @access public
     * @param object $object
     * @return boolean
     */
    static function isException($object) {
        return $object instanceof Exception || $object instanceof pear_error;
    }

    /**
     * handleException()
     * Si l'objet ou le resultat de fonction passé en paramètre
     * est une exception alors la fonction redirige vers un message d'erreur.
     *
     * @access public
     * @param object $object
     * @param string $retURL url de retour
     * @return void
     **/
    static function handleException($object, $retURL=false){
        if(Tools::isException($object)) {
            if (!$retURL) {
                $retURL = isset($_SERVER['HTTP_REFERRER'])?$_SERVER['HTTP_REFERRER']:'home.php';
            }
            Template::errorDialog($object->getMessage(), $retURL);
            exit;
        }
        return true;
    }

    /**
     * singleton: Utilise par Mapper, NE PAS UTILISER TEL QUEL!!!
     * garde en mémoire les instances, pour éviter les pertes de références
     * XXX Voir si on peut optimizer cela
     *
     * @param string $clsname le nom de la classe à instancier
     * @param integer $id son id
     * @return object la nouvelle instance ou l'instance déjà chargée
     */
    static function singleton($clsname, $id) {
        static $instances = array();
        $id = (int)$id;
        if (!isset($instances[$clsname][$id]) ||
            !is_object($instances[$clsname][$id])) {
            $instances[$clsname][$id] = new $clsname();
            $instances[$clsname][$id]->setId($id);
        }
        return $instances[$clsname][$id];
    }

    /**
     * Tools::getToStringAttribute()
     * Retourne le nom de l'attribut représentant l'objet, pointé par toString()
     *
     *
     * @param string $clsname le nom de la classe a traiter
     * @access public
     * @static
     * @return string
     */
    static function getToStringAttribute($clsname) {
        require_once(MODELS_DIR . '/' . $clsname . '.php');
        $obj = new $clsname();
        return $obj->getToStringAttribute();
    }

    /**
     * Tools::redirectTo()
     * Redirige le browser vers la page $location
     *
     * @access public
     * @param string $location
     * @return void
     */
    static function redirectTo($location) {
        if (headers_sent()) {
            // Si les headers sont deja envoyes, on utilise la balise <META>.
            print("\n" . '<meta http-equiv="refresh" content="0;URL=' .
                $location . '">' . "\n");
        } else {
            header('Location: ' . $location);
            header('Content-Location: ' . $location);
        }
    }
    /**
     * Concatène une liste de componsantes de filtre.
     *
     * @param array $components Tableau contenant les composants à concaténer
     * @param const $operator
     * @return Filter
     */

    static function filterComponentsToFilter($components, $operator)
    {
        if (!is_array($components) || count($components) == 0) {
            return new Exception($components . ' doesn\'t contain any element '
                . 'or is not an array !');
        }
        $filter = new FilterComponent();
        foreach($components as $component){
            $filter->setItem($component);
        }
        $filter->operator = $operator;
        return $filter;
    }


    /**
     * Tools::getValueFromMacro()
     * Retourne une valeur à partir d'une une instance d'objet, et d'un chemin
     * (chaine de caractères séparée par des ".") vers la propriété désirée
     *
     * @param $object
     * @param string $pMacro
     * @return mixed
     */
    static function getValueFromMacro($object, $pMacro)
    {
        $pattern = array();
        /**
         * Extraction de toutes les macros (entre deux %)
         */
        if (preg_match_all(Tools::FIRST_PASS_MACRO_REGEX, $pMacro, $tokens)) {
            $foundPatterns = $tokens[1];

            /**
             * Traitement d'une macro
             */
            foreach($foundPatterns as $key => $macro) {
                $macroParts = explode('.', $macro);
                $valueNA = 'N/A';
                $currentItem = $object;
                foreach($macroParts as $part) {
                    // Syntaxe étendue: Word(Condition)[Index] avec condition de
                    // la forme Champ=Valeur; la condition est optionnelle.
                    if (preg_match('/^' . Tools::SECOND_PASS_MACRO_REGEX
                        . '$/', $part, $macroTokens)
                        && ((isset($macroTokens[2]) && ($macroTokens[2] == '()'))
                        || (isset($macroTokens[5]) && ($macroTokens[5] != '')))) {
                        $params = array();
                        if (!empty($macroTokens[3])) {
                            // Tableau contenant le filtre à transmettre à
                            // l'accesseur de collection
                            $params[$macroTokens[3]] = $macroTokens[4];
                        }
                        $accessorName = 'Get' . $macroTokens[1] . 'Collection';
                        $currentItem = $currentItem->$accessorName($params);

                        if (false == $currentItem || Tools::isException($currentItem)) {
                            return $valueNA;
                        }
                        if (isset($macroTokens[5])) {

                            if (method_exists($currentItem, 'getItem')) {
                                if ('#' == $macroTokens[5]) {
                                    // Si l'index est #, on prend le dernier
                                    $itemIndex = $currentItem->GetCount() - 1;
                                } else {
                                    // sinon on prend le numéro d'index
                                    $itemIndex = (int)$macroTokens[5];
                                }
                                $currentItem = $currentItem->getItem($itemIndex);
                                if (Tools::isException($currentItem)) {
                                    return $valueNA;
                                }
                            } else {
                                if (false == is_object($currentItem)) {
                                    return $valueNA;
                                }
                                return new Exception(__LINE__ .
                                    ': Method getItem doesn\'t exists in "' .
                                    get_class($currentItem) . '" objects.');
                            }
                        }
                    } else {
                        // Champ simple, appel de l'accesseur simplement
                        if ($currentItem instanceof Object) {
                            $accessorName = 'get' . $part;
                            $currentItem = $currentItem->$accessorName();
                            if (Tools::isException($currentItem)) {
                                return $valueNA;
                            }
                        }
                    }
                }
                /**
                 * Récupération du contenu de la cellule
                 */
                if (is_object($currentItem)) {
                    if (method_exists($currentItem, 'toString')) {
                        $currentItem = $currentItem->toString();
                    }
                }
                $filterPadding = '';

                /**
                 * Gestion du filtre
                 *
                 * Le filtre peut être simple, sans arguments ex: 'humandate'
                 * ou il peut comporter des arguments, chaque argument doit être
                 * précédé du symbole
                 * ex: editable@MonNomDeChamps@5
                 */
                if (!empty($tokens[3][$key])) {
                    // le filtre brut (nom filtre et arguments)
                    $rawfilter = $tokens[3][$key];
                    // on explose la chaine
                    $funcnameParams = explode('@', $rawfilter);
                    // on dépile le premier élément qui correspond au nom du filtre
                    $filter = strtolower(array_shift($funcnameParams));
                    // on empile au début du tableau la valeur de la cellule qui
                    // est systématiquement passée comme premier argument aux
                    // filtres
                    array_unshift($funcnameParams, $currentItem);
                    // inclu le fichier filtre
                    if (loadGridComponent('Filter', $filter)) {
                        $filterFunc = 'grid_filter_' . $filter;
                        if (function_exists($filterFunc)) {
                            $currentItem = call_user_func_array($filterFunc,
                                $funcnameParams);
                        } else if (function_exists($filter)) {
                            $currentItem = call_user_func_array($filter,
                                $funcnameParams);
                        } else {
                            trigger_error(
                                'Unknown grid filter function: ' . $filterFunc,
                                E_USER_NOTICE
                            );
                        }
                    }
                    $filterPadding = '|' . $rawfilter;
                }
                $pattern['%' . $macro . $filterPadding . '%'] = $currentItem;
            }
        }
        if (!empty($pattern)) {
            $return = str_replace(array_keys($pattern),
                array_values($pattern), $pMacro);

            return $return;
        }
        return $pMacro;
    }
}
?>
