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
 * @version   SVN: $Id: Mapper.php,v 1.14 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class Mapper {
    // propriétés private/protected {{{

    /**
     * Le nom de l'entité cible
     *
     * @access private
     * @var    string _cls
     */
    private $_cls = false;

    /**
     * Le nom de table de l'entité cible
     *
     * @access private
     * @var    string _tbname
     */
    private $_tbname = false;

    /**
     * Les attributs de l'entité cible
     *
     * @access private
     * @var    string _attrs
     */
    private $_attrs = false;

    // }}}
    // Constructeur {{{

    /**
     * Constructor
     *
     * @param string $clsname le nom de la classe que le mapper doit gérer.
     * @access protected
     */
    public function __construct($clsname) {
        // on inclue la définition et l'objet collection de l'entité cible
        // et on extrait les infos de nom de table et attributs
        require_once(MODELS_DIR . '/' . $clsname . '.php');
        $this->_cls = $clsname;
        $this->_tbname = call_user_func(array($this->_cls, 'getTableName'));
        $this->_attrs = call_user_func(array($this->_cls, 'getProperties'));
        $this->_links = call_user_func(array($this->_cls, 'getLinks'));
    }

    // }}}
    // Mapper::singleton() {{{

    /**
     * Mapper::singleton()
     * un multiton, qui conserve les instances dans un tableau statique
     *
     * @access public
     * @static
     * @param string $clsname le nom de la classe que le mapper doit gérer
     * @return object Mapper
     */
    public static function singleton($clsname) {
        static $_instances = array();
        if (isset($_instances[$clsname])) {
            return $_instances[$clsname];
        }
        $instance = new Mapper($clsname);
        $_instances[$clsname] = $instance;
        return $instance;
    }

    // }}}
    // Mapper::load() {{{

    /**
     * Charge une instance en bdd.
     * Exemples:
     * <code>
     * // charge l'acteur avec le nom 'toto'
     * $mapper = Mapper::singleton('Actor');
     * $actor = $mapper->load(array('Name'=>'toto'));
     * // charge le produit avec l'id 1 en utilisant le lazy-loading, seuls le
     * // nom et le prix du produit sont chargés
     * $mapper = Mapper::singleton('Product');
     * $product = $mapper->load(array('Id'=>1), array('Name', 'Price'));
     * </code>
     *
     *
     * @access public
     * @param  mixed $attributeFilters un tableau ou un objet filtre
     * @param  array $fields un tableau des champs à récupérer (lazy loading)
     * @param  boolean $noCache si à false, on utilise pas le cache en mémoire
     * @return mixed un objet (Object ou Exception)
     */
    public function load($attributeFilters, $fields = array(), $noCache = false) {
        if (defined('MAPPER_CACHE_DISABLED') && MAPPER_CACHE_DISABLED) {
            $noCache = true;
        }
        // si l'objet est chargé en lazy loading, on le taggue readonly
        if (!empty($fields)) {
            $readonly = $noCache = true;
            $attrs = $fields;
        } else {
            $readonly = false;
            $attrs = array_keys($this->_attrs);
        }
        // on instancie l'entité à partir de l'id s'il est passé en paramètre
        // $instance = new $this->_cls();
        if (is_array($attributeFilters) && isset($attributeFilters['Id']) &&
            !$noCache) {
            $instance = Tools::singleton($this->_cls, $attributeFilters['Id']);
            if ($instance->hasBeenInitialized) {
                return $instance;
            }
        } else {
            $instance = new $this->_cls();
        }

        // On charge les propriétés de l'entité
        $sql = $this->_getSQLRequest($attributeFilters, array(), $fields);
        $result = Database::connection()->execute($sql);
        // si erreur sql
        if (false === $result) {
            if (DEV_VERSION) {
                echo $sql . '<br>';
            }
            trigger_error(Database::connection()->ErrorMsg(), E_USER_ERROR);
        }
        // si pas d'enregistrement en bdd
        if ($result->EOF) {
            $ret = new Exception(E_NO_RECORD_FOUND);
            return $ret;
        }
        // gestion de l'héritage, le class name est récupéré en bdd, il faut
        // donc instancier la bonne classe (cad celle retournée par adodb)
        if (isset($result->fields['_ClassName']) &&
            !empty($result->fields['_ClassName']) &&
            $result->fields['_ClassName'] != $this->_cls) {
            unset($instance);
            require_once(MODELS_DIR . '/' . $result->fields['_ClassName'] . '.php');
            if ($noCache) {
                $instance = new $result->fields['_ClassName']();
            } else {
                $instance = Tools::singleton($result->fields['_ClassName'],
                                       $result->fields['_Id']);
                if ($instance->hasBeenInitialized) {
                    return $instance;
                }
            }
            if (!$readonly) {
                $attrs = array_merge($attrs,
                    array_keys($instance->getProperties()));
            }
        }
        // on remplit l'instance
        $instance->setId($result->fields['_Id']);
        foreach($attrs as $attr) {
            // on ne traite que les attributs simples et les foreignkeys
            $setter = 'set' . $attr;
            $instance->$setter($result->fields['_' . $attr]);
        }
        if (defined('DATABASE_ID')) {
            $instance->dbID = $result->fields['_DBId'] === null?
                null:(int)$result->fields['_DBId'];
        }
        if (property_exists($instance, 'lastModified')) {
            $instance->lastModified = $result->fields['_LastModified'];
        }
        $result->close();
        // on la marque comme initialisée et on affecte readonly
        /*
         * Le fait de mettre hasBeenInitialized à true après l'initialisation de
         * l'instance peut poser problème en cas de surcharge d'un setter dans
         * lequel l'instance serait modifié, le hasBeenInitialized étant encore
         * à false la requette sql sera un insert au lieu d'un update et ili y
         * aura une fatal error duplicate ID, dans le cas de setter surchargé il
         * ne faut sauver les objets que si hasBeenInitialized vaut false
         */
        $instance->hasBeenInitialized = true;
        $instance->readonly = $readonly;
        return $instance;
    }

    // }}}
    // Mapper::loadCollection() {{{

    /**
     * Charge une collection d'objets
     *
     * Exemples:
     * <code>
     * // charge la collection des acteurs qui ont 18 ans
     * $mapper = Mapper::singleton('Actor');
     * $actor = $mapper->loadCollection(array('Age'=>18));
     * // charge la collection de produits actifs en utilisant le lazy-loading,
     * // seuls le nom et le prix des produits sont chargés
     * $mapper = Mapper::singleton('Product');
     * $product = $mapper->loadCollection(array('Active'=>1),
     *      array('Name', 'Price'));
     * </code>
     *
     * @access public
     * @param  mixed $attributeFilters un tableau ou un objet filtre
     * @param  array $sortOrder un tableau pour les tris
     * @param  array $fields un tableau de chaines pour les champs à charger
     * @param  integer $rows le nombre de lignes à charger (pagination)
     * @param  integer $page l'index de la page en cours (pagination)
     * @param  integer $limit le nombre d'enregistrements à charger (LIMIT)
     * @return object Collection
     */
    public function loadCollection($filter = array(),
        $sortOrder = array(), $fields = array(), $rows = 0, $page = 1,
        $limit = false, $noCache = false)
    {
        if (defined('MAPPER_CACHE_DISABLED') && MAPPER_CACHE_DISABLED) {
            $noCache = true;
        }
        // si l'objet est chargé en lazy loading, on la taggue readonly
        if (!empty($fields)) {
            $readonly = $noCache = true;
            $attrs = $fields;
        } else {
            $readonly = false;
            $attrs = array_keys($this->_attrs);
        }
        $sql = $this->_getSQLRequest($filter, $sortOrder, $fields);
        if ($rows == 0) {
            if ($limit) {
                $result = Database::connection()->SelectLimit($sql, $limit);
            } else {
                $result = Database::connection()->Execute($sql);
            }
        } else {
            $result = Database::connection()->PageExecute($sql, $rows, $page);
        }
        // si erreur sql
        if (false === $result) {
            if (DEV_VERSION) {
                echo $sql . '<br />';
            }
            trigger_error(Database::connection()->ErrorMsg(), E_USER_ERROR);
        }
        // on instancie une collection
        $collection = new Collection();
        // propriétés utilisées par la pagination
        $collection->currentPage = $result->_currentPage;
        $collection->lastPageNo = $result->_lastPageNo;
        $collection->entityName = $this->_cls;
        // on remplit l'objet
        while (!$result->EOF) {
            // gestion de l'héritage, le class name est récupéré en bdd, il faut
            // donc instancier la bonne classe (cad celle retournée par adodb)
            if (isset($result->fields['_ClassName']) &&
                !empty($result->fields['_ClassName']) &&
                $result->fields['_ClassName'] != $this->_cls) {
                unset($instance);
                require_once(MODELS_DIR . '/' . $result->fields['_ClassName']
                     . '.php');
                if ($noCache) {
                    $instance = new $result->fields['_ClassName']();
                } else {
                    $instance = Tools::singleton($result->fields['_ClassName'],
                        $result->fields['_Id']);
                }
                if (!$readonly) {
                    $new_attrs = array_merge($attrs,
                        array_keys($instance->getProperties()));
                } else {
                    $new_attrs = $attrs;
                }
            } else {
                if ($noCache) {
                    $instance = new $this->_cls();
                } else {
                    $instance = Tools::singleton($this->_cls, $result->fields['_Id']);
                }
                $new_attrs = $attrs;
            }
            // on rempli l'instance uniquement s'il est pas déjà en mémoire
            $instance->setId($result->fields['_Id']);
            if (defined('DATABASE_ID')) {
                $instance->dbID = $result->fields['_DBId'] === null?
                    null:(int)$result->fields['_DBId'];
            }
            if (!$instance->hasBeenInitialized) {
                foreach($new_attrs as $attr) {
                    // on ne traite que les attributs simples et les fkeys
                    $setter = 'set' . $attr;
                    $instance->$setter($result->fields['_' . $attr]);
                }
                // on la marque comme initialisée et on affecte readonly
                $instance->hasBeenInitialized = true;
                $instance->readonly = $readonly;
                if (property_exists($instance, 'lastModified')) {
                    $instance->lastModified = $result->fields['_LastModified'];
                }
            }
            // on l'ajoute à la collection
            $collection->setItem($instance);
            unset($instance);
            $result->moveNext();
        }
        // After setItem() calls, sinon, ecrase...
        $collection->totalCount = $result->MaxRecordCount();
        $result->close();
        return $collection;
    }

    // }}}
    // Mapper::delete() {{{

    /**
     * Supprime l'objet en base de données.
     *
     * @access public
     * @param mixed $id : un entier id ou un tableau d'ids
     * @return boolean
     */
    public function delete($id) {
        if (false == $id || empty($id)) {
            return false;
        }
        // on demarre une transaction
        Database::connection()->startTrans();
        // on supprime les eventuels liens *..*
        // et on applique le ondelete set NULL pour les liens 1..n
        foreach($this->_links as $name => $data) {
            $sql = "";
            if ($data['multiplicity'] == 'manytomany') {
                // liens *..*: sont toujours supprimés
                $sql = 'DELETE FROM ' . $data['linkTable'] . ' WHERE _' .
                        $data['field'] . '=';
            }
            else {
                require_once(MODELS_DIR . '/' . $data['linkClass'] . '.php');
                $table = call_user_func(array($data['linkClass'],
                    'getTableName'));
                // liens 1..n: sont supprimés / ou fkey mise à null suivant
                // config
                if (isset($data['ondelete'])
                && strtolower($data['ondelete']) == 'cascade') {
                    $ids = is_array($id)?$id:array($id);
                    $coll = $this->loadCollection(array('Id'=>$ids));
                    $count = $coll->getCount();

                    for($i = 0; $i < $count; $i++){
                        $item = $coll->getItem($i);
                        if ($data['multiplicity'] == 'onetoone') {
                            $getter = 'get' . $name;
                            $lItem = $item->$getter();
                            if (!$lItem instanceof Object) {
                                continue;
                            }
                            $lItem->delete();
                        }else { // oneToMany
                            // On s'affranchit de $getter = 'get'.$name.'Collection'
                            $linkedColl = $this->getOneToMany($item->getId(), $name);
                            $lCount = $linkedColl->getCount();
                            for($j = 0; $j < $lCount; $j++){
                                $lItem = $linkedColl->getItem($j);
                                $lItem->delete();
                            }
                        }
                    }
                } else {
                    $sql = 'UPDATE ' . $table . ' SET _' . $data['field'] .
                           '=NULL WHERE _' . $data['field'] . '=';
                }
            }
            if (!empty($sql)) {
                $sql .= is_array($id)?
                    implode(' OR _' . $data['field'] . '=', $id):$id;
                Database::connection()->execute($sql);
            }
        }
        // on supprime les eventuelles donnees dans la table I18nString
        $i18nAttrs = array_merge(
                array_keys($this->_attrs, Object::TYPE_I18N_STRING),
                array_keys($this->_attrs, Object::TYPE_I18N_TEXT),
                array_keys($this->_attrs, Object::TYPE_I18N_HTML));
        if (!empty($i18nAttrs)) {
            $ids = is_array($id)?$id:array($id);
            $coll = $this->loadCollection(array('Id'=>$ids));
            $count = $coll->getCount();
            $i18nItemIds = array();
            for($i = 0; $i < $count; $i++){
                $item = $coll->getItem($i);
                foreach($i18nAttrs as $name) {
                    $getter = 'get' . $name . 'Id';
                    $i18nItemIds[] = $item->$getter();
                }
            }
            if (!empty($i18nItemIds)) {
                $iMapper = Mapper::singleton('I18nString');
                $iMapper->delete($i18nItemIds);
            }
        }

        // construction de la requête
        $id = is_array($id)?implode(' OR _Id=', $id):$id;
        $sql = 'DELETE FROM ' . $this->_tbname . ' WHERE _Id=' . $id;
        // transaction
        Database::connection()->execute($sql);
        if (Database::connection()->hasFailedTrans()) {
            echo $sql . '<br>';
            trigger_error(Database::connection()->errorMsg(), E_USER_ERROR);
        }
        Database::connection()->completeTrans();
        return true;
    }

    // }}}
    // Mapper::generateId() {{{

    /**
     * Génére un ID pour l'objet en cours.
     *
     * @access public
     * @return integer
     */
    public function generateId() {
        // on vérifie l'existance d'un id dans la table de hash des ids
        // on utilise FOR UPDATE pour locker la table en cas d'accès
        // concurrents.
        // XXX il faudra voir à utiliser rowLock de ADODB
        // Cela ne semble pas marcher, surement à cause du select qui est fait
        // après.
        //Database::connection()->rowLock('IdHashTable', '_Table=\''
        //    . $this->_tbname . '\'');
        $sql = 'SELECT _Id FROM IdHashTable WHERE _Table=\'' . $this->_tbname
            . '\' FOR UPDATE';
        $exists = Database::connection()->execute($sql);
        if (false == $exists || $exists->EOF) {
            // on insère le champs dans la table de hash
            $sql = sprintf(
                'INSERT INTO IdHashTable (_Table, _Id) ' .
                'SELECT \'%s\', MAX(_Id) FROM %s',
                $this->_tbname, $this->_tbname
            );
            $result = Database::connection()->execute($sql);
            if (false == $result) {
                if (DEV_VERSION) {
                    echo $sql . '<br />';
                }
                trigger_error(Database::connection()->errorMsg(), E_USER_ERROR);
            }
            return $this->generateId();
        } else {
            // l'entrée existe on l'update après l'avoir incrémentée
            $id = (int)$exists->fields[0] + 1;
            $sql = 'UPDATE IdHashTable SET _Id=' . $id . ' WHERE _Table=\'' .
                $this->_tbname . '\'';
        }
        $result = Database::connection()->execute($sql);
        if (false == $result) {
            if (DEV_VERSION) {
                echo $sql . '<br />';
            }
            trigger_error(Database::connection()->errorMsg(), E_USER_ERROR);
        }
        return $id;
    }

    // }}}
    // Mapper::save() {{{

    /**
     * Sauve l'objet $o en bases de données.
     *
     * @access public
     * @param  object $o l'objet à sauver
     * @return boolean
     */
    public function save($o) {
        // si l'objet est readonly on affiche un warning et on retourne false
        if ($o->readonly) {
            trigger_error(
                'Object ' . get_class($o) .' can\'t be saved, because it\'s readonly.',
                E_USER_WARNING);
            return false;
        }

        // si l'objet n'a pas encore d'id on lui en génère un
        if ($o->getId() == 0) $o->setId($this->generateId());

        // XXX gérer ici le onupdate/ondelete pour les liens 1..* ?
        // TODO
        // faut-il utiliser insert ou update ?
        $sqlbuilder = $o->hasBeenInitialized?'_getUpdateSQL':'_getInsertSQL';
        // on execute la requête
        $sql = $this->$sqlbuilder($o);
        $result = Database::connection()->execute($sql);

        $errormsg = Database::connection()->errorMsg();
        if (!empty($errormsg)) {
            if (DEV_VERSION) {
                echo $sql . '<br>';
            }
            trigger_error($errormsg, E_USER_ERROR);
        } else {
            $o->hasBeenInitialized = true;
            if (property_exists($o, 'lastModified')) {
                $sql = 'SELECT _LastModified FROM ' . $this->_tbname
                     . ' WHERE _Id=' . $o->getId();
                $rsdate = Database::connection()->execute($sql);
                $o->lastModified = $rsdate->fields['_LastModified'];
            }
        }
        // On sauve les relations *..*, sans transaction (ToDo??)
        foreach($this->_links as $name => $data) {
            if ($data['multiplicity'] != 'manytomany') {  // si relation *..*
                continue;
            }
            // XXX pas très beau: test sur la variable privée _XXXCollection
            // nécessaire pour éviter de supprimer des liens, dans  le cas ou
            // la collection n'a pas été chargée (le load ne précharge
            // pas les collections).
            $fx = $name . 'CollectionIsLoaded';
            if (!$o->$fx()) {
                continue;
            }
            // on supprime les liens
            $sql = 'DELETE FROM ' . $data['linkTable'] . ' WHERE _' .
                   $data['field'] . ' = ' . $o->getId();
            Database::connection()->execute($sql);
            // on actualise les liens
            $collectionName = 'get' . $name . 'CollectionIds';
            $collectionIds = $o->$collectionName();
            if(!empty($collectionIds)) {
                foreach($collectionIds as $itemId){
                    if ($itemId == 0) {
                        continue;
                    }
                    Database::connection()->execute('INSERT INTO ' .
                        $data['linkTable'] . ' SET _' . $data['field'] .
                        ' = ' . $o->getId().', _' . $data['linkField'] .
                        ' = ' . $itemId);
                }
            }
        }
        return true;
    }

    // }}}
    // Mapper::alreadyExists() {{{

    /**
     * Retourne true s'il existe un enregistrement avec les paramètres passés
     * dans $params et false sinon. Si l'opérateur est AND (défaut)
     * l'enregistrement doit avoir tous les paramètres correspondant, si c'est
     * OR, il doit avoir au moins un des paramètres correspondant.
     *
     * Exemple:
     * <code>
     * $mapper = Mapper::singleton('Actor');
     * // renverra true si et seulement s'il existe en bdd un acteur ayant le
     * // champs Name à 'toto' *et* le champs Age à 10
     * $mapper->alreadyExists(array('Name'=>'toto', 'Age'=>'10'));
     * // renverra true s'il existe en bdd un acteur ayant le champs Name à
     * // 'toto' *ou* le champs Age à 10
     * $mapper->alreadyExists(array('Name'=>'toto', 'Age'=>'10'), 'OR');
     * </code>
     *
     * @access public
     * @param array $params le tableau des champs pour verification
     * @param string $operator1 l'operateur de la requête (AND, OR)
     * @param string $operator2 l'operateur de comparaison (=, >, LIKE etc...)
     * @return boolean
     */
    public function alreadyExists($params = array(), $operator1 = 'AND',
        $operator2 = '=')
    {
        if (is_array($params) || !empty($params)) {
            if(defined('DATABASE_ID') && !isset($params['DBId'])) {
                $params['DBId'] = DATABASE_ID;
            }
            $sql = 'SELECT COUNT(_Id) FROM ' . $this->_tbname . ' WHERE';
            $padding = '';
            foreach($params as $key=>$val){
                $sql .= $padding . ' _' . $key . ' ' . $operator2 . ' ' .
                    Database::connection()->qstr($val);
                $padding = ' ' . $operator1;
            }
            $result = Database::connection()->execute($sql);
            if (false != $result) {
                return $result->fields[0] > 0;
            }
        }
        return false;
    }

    // }}}
    // Mapper::getOneToMany() {{{

    /**
     * Retourne une collection d'objets pour les propriétés 1..*
     *
     * @access public
     * @param int $id l'id de l'objet en cours
     * @param string $attr le nom de la propriété
     * @param mixed $attributeFilters un tableau ou un objet filtre
     * @param array $sortOrder un tableau pour les tris
     * @param array $fields un tableau de chaines pour les champs à charger
     * @return object Collection une collection d'objets
     */
    public function getOneToMany($id, $attr, $filter = array(),
        $sortOrder = array(), $fields = array())
    {
        if ($id == 0) {
            // on ne doit pas retourner les objets qui ont une fkey à 0
            $ret = new Collection();
            return $ret;
        }
        $data = $this->_links[$attr];
        if ($filter instanceof FilterComponent) {
            $filter = SearchTools::buildFilterFromArray(
                array($data['field'] => $id), $filter);
        } else {
            $filter = array_merge(array($data['field'] => $id), $filter);
        }
        $mapper = Mapper::singleton($data['linkClass']);
        $ret = $mapper->loadCollection($filter, $sortOrder, $fields);
        return $ret;
    }

    // }}}
    // Mapper::getManyToMany() {{{

    /**
     * Retourne une collection d'objets pour les propriétés *..*
     *
     * @access public
     * @param int $id l'id de l'objet en cours
     * @param string $attr le nom de la propriété
     * @param mixed $attributeFilters un tableau ou un objet filtre
     * @param array $sortOrder un tableau pour les tris
     * @param array $fields un tableau de chaines pour les champs à charger
     * @return object Collection une collection d'objets
     */
    public function getManyToMany($id, $attr, $filter = array(),
        $sortOrder = array(), $fields = array())
    {
        if ($id == 0) {
            // on ne doit pas retourner les objets qui ont une fkey à 0
            return new Collection();
        }
        $data = $this->_links[$attr];
        $ids = array('Id' => $this->getManyToManyIds($id, $attr));

        if (empty($ids)) {
            return new Collection();
        }
        if ($filter instanceof FilterComponent) {
            $filter = SearchTools::buildFilterFromArray($ids, $filter);
        } else {
            $filter = array_merge($ids, $filter);
        }

        $mapper = Mapper::singleton($data['linkClass']);
        return $mapper->loadCollection($filter, $sortOrder, $fields);
    }

    /**
     * Retourne un tableau d'ids pour les propriétés *..*
     *
     * @access public
     * @param int $id l'id de l'objet en cours
     * @param string $attr le nom de la propriété
     * @return array of int
     */
    public function getManyToManyIds($id, $attr)
    {
        $data = $this->_links[$attr];

        $sql = 'SELECT _' . $data['linkField'] . ' FROM ' . $data['linkTable']
                . ' WHERE _' . $data['field'] . ' = ' . $id;

        // Si relation reflexive (de $this->_cls vers $this->_cls),
        // et si c'est bidirectionnel:
        if ($data['linkClass'] == $this->_cls
        && $data['bidirectional'] == 1) {
            $sql .= ' UNION SELECT _' . $data['field'] . ' FROM ' . $data['linkTable']
                . ' WHERE _' . $data['linkField'] . ' = ' . $id;
        }

        $result = Database::connection()->execute($sql);
        if (false == $result) {
            echo $sql . '<br>';
            trigger_error(Database::connection()->errorMsg(), E_USER_WARNING);
        }
        $ids = array();
        while (!$result->EOF) {
            $ids[] = (int)$result->fields['_' . $data['linkField']];
            $result->moveNext();
        }
        $result->close();
        return $ids;
    }

    // }}}
    // Mapper::_getInsertSQL() {{{

    /**
     * Construit une requête INSERT à partir de l'objet $o
     *
     * @access private
     * @param  object $o l'objet pour lequel on veut construire la requête
     * @return string la requête sql
     */
    private function _getInsertSQL($o) {
        $sql = 'INSERT INTO ' . $this->_tbname . ' ';
        $fields = '_Id';
        $values = $o->getId();
        if (defined('DATABASE_ID')) {
            $fields .= ', _DBId';
            $values .= ', ' . DATABASE_ID;
        }
        // on parcours les propriétés et on construit le tableau
        foreach($this->_attrs as $name => $type) {
            $getter = 'get' . $name;
            if (is_string($type) || $type==Object::TYPE_I18N_STRING ||
                $type==Object::TYPE_I18N_TEXT || $type==Object::TYPE_I18N_HTML)
            {
                $getter .= 'Id';
            }
            $fields .= ', _' . $name;
            $val = $o->$getter();
            if ($val === null) {
                $val = 'NULL';
            } else {
                if ($type == Object::TYPE_STRING) {
                    // Suppression des \r\n avant ADOConnection::qstr()
                    $val = trim($val);
                }
                if ($type == Object::TYPE_PASSWORD) {
                    $val = sha1($val);
                }
                $val = Database::connection()->qstr($val);
            }
            $values .= ', ' . $val;
        }
        $fields = ($o->useInheritance())?$fields . ', _ClassName':$fields;
        $values = ($o->useInheritance())?$values.', \''.get_class($o).'\'':$values;
        $sql .= '(' . $fields . ') VALUES (' . $values . ')';
        return $sql;
    }

    // }}}
    // Mapper::_getUpdateSQL() {{{

    /**
     * Construit une requête UPDATE à partir de l'objet $o
     *
     * @access private
     * @param  object $o l'objet pour lequel on veut construire la requête
     * @return string la requête sql
     */
    private function _getUpdateSQL($o) {
        $sql = 'UPDATE ' . $this->_tbname . ' SET ';
        $padding = '';
        // on parcours les propriétés et on construit la requête
        foreach($this->_attrs as $name => $type) {
            $getter = 'get' . $name;
            if (is_string($type) || $type==Object::TYPE_I18N_STRING ||
                $type==Object::TYPE_I18N_TEXT || $type==Object::TYPE_I18N_HTML)
            {
                $getter .= 'Id';
            }
            $val = $o->$getter();
            if ($val === null) {
                $val = 'NULL';
            } else {
                if ($type == Object::TYPE_STRING) {
                    // Suppression des \r\n avant ADOConnection::qstr()
                    $val = trim($val);
                }
                if ($type == Object::TYPE_PASSWORD && strlen($val) != 40) {
                    // XXX pas trouvé mieux ici, en fait si le mot de passe est
                    // déjà encodé il ne faut pas le ré-encoder, strlen teste
                    // donc cela, mais ça peut poser pb dans le cas d'un mot de
                    // passe qui contient réellement 40 caractères...
                    $val = sha1($val);
                }
                $val = Database::connection()->qstr($val);
            }
            $sql .= $padding . '_' . $name . ' = ' . $val;
            $padding = ', ';
        }
        if ($o->useInheritance()) {
            $sql .= ', _ClassName = \'' . get_class($o) . '\'';
        }
        $sql .= ' WHERE _Id=' . $o->getId();
        return $sql;
    }

    // }}}
    // Mapper::_getSQLRequest() {{{

    /**
     * Construit la requête SELECT complète. Cette méthode est utilisé par
     * Mapper::load et Mapper::loadCollection
     *
     * @access private
     * @param  mixed $attributeFilters un tableau ou un objet filtre
     * @param  array $sortOrder un tableau pour les tris
     * @return string la requête SELECT
     */
    private function _getSQLRequest($attributeFilters = array(),
        $sortOrder = array(), $fields = array())
    {
        $filteraddon = $params = array();
        if (false != call_user_func(array($this->_cls, 'getParentClassName'))) {
            $filteraddon['ClassName'] = $this->_cls;
            $params[] = 'ClassName';
        }
        if ($attributeFilters instanceof FilterComponent) {
            $filter = SearchTools::buildFilterFromArray($filteraddon,
                $attributeFilters);
            $filterMacros = $attributeFilters->CollectMacros();
        } else if (is_array($attributeFilters)) {
            $filter = SearchTools::buildFilterFromArray(
                array_merge($filteraddon, $attributeFilters), false, $this->_cls);
            $filterMacros = $filter->CollectMacros();
        }

        // si fields est un tableau
        if (empty($fields)) {
            $params = array('*');
        } else {
            if (!in_array('Id', $fields)) {
                $params[] = 'Id';
            }
            if (defined('DATABASE_ID') && !in_array('DBId', $fields)) {
                $params[] = 'DBId';
            }
            if (property_exists($this->_cls, 'lastModified')) {
                $params[] = 'LastModified';
            }
            $params = array_merge($params, $fields);
        }
        // on appelle le state machine pour construire la requête
        $stateMachine = new StateMachine($this->_cls, $params,
            $filterMacros, $sortOrder);
        return $stateMachine->toSQL($filter);
    }

    // }}}
}

?>
