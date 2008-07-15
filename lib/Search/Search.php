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

class SearchTools {
    // SearchTools::getToSqlOperator() {{{

    /**
     * Retourne un operateur sous forme de string.
     *
     * @param string $operator "Like" ou "Equal" ou "NotLike", ...:
     * cf ds lib/filter tous les operateurs disponibles
     *
     * @static
     * @return string
     */
    static function getToSqlOperator($operator='Equals')
    {
         $OperatorArray =
                array(
                    'Like' => FilterRule::OPERATOR_LIKE,
                    'NotLike' => FilterRule::OPERATOR_NOT_LIKE,
                    'Equals' => FilterRule::OPERATOR_EQUALS,
                    'NotEquals' => FilterRule::OPERATOR_NOT_EQUALS,
                    'GreaterThan' => FilterRule::OPERATOR_GREATER_THAN,
                    'GreaterThanOrEquals' => FilterRule::OPERATOR_GREATER_THAN_OR_EQUALS,
                    'LowerThan' => FilterRule::OPERATOR_LOWER_THAN,
                    'LowerThanOrEquals' => FilterRule::OPERATOR_LOWER_THAN_OR_EQUALS,
                    'In' => FilterRule::OPERATOR_IN,
                    'NotIn' => FilterRule::OPERATOR_NOT_IN,
                    'IsNull' => FilterRule::OPERATOR_IS_NULL,
                    'IsNotNull' => FilterRule::OPERATOR_IS_NOT_NULL
                );
            return $OperatorArray[$operator];

    }

    // }}}
    // SearchTools::newFilterComponent() {{{

    /**
     * Retourne un FilterComponent
     *
     * @param string $Attribute nom de l'attribut sur lequel s'effectue le filtre
     * @param string $DbTablesPath chemin en cas de jointure, ou '' si pas de jointure
     * Exple: ''ActivatedOperation.ActivatedChain.CommandItem().Command.CommandNo'
     * les () expriment une relation 1..* ou *..*
     * Autre type de path autorise: Command@ProductCommand.CommandItem().Product.BaseReference
     * le "@" permet de gerer le pb de l'heritage: on force la classe ProductCommand ici
     * @param string $Operator "Like" ou "Equal" ou "NotLike", ...:
     * cf ds SearchTools::getToSqlOperator() tous les operateurs disponibles
     * @param string $Value valeur du champs: facultatif
     * @param int $force (facultatif): si vaut 1 (0 par defaut), force la
     * construction du FilterComponent,
     * même si pas de var REQUEST ou SESSION reçue
     * @param string $startEntity nom de l'entité cherché dans le cas où il y a
     * une relation 1..* ou *..*
     * @static
     * @return object FilterComponent ou false
     */
    static function newFilterComponent($Attribute, $DbTablesPath,
        $Operator='Equals', $Value='', $force=0, $startEntity='')
    {
        $OperatorArray = array('Like', 'NotLike', 'Equals', 'NotEquals',
                'GreaterThan', 'GreaterThanOrEquals', 'LowerThan', 'LowerThanOrEquals',
                'In', 'NotIn', 'IsNull', 'IsNotNull');
        if (!in_array($Operator, $OperatorArray)) {
            Template::errorDialog(
                'Bad operator. Filter can\'t be builded.',
                $_SERVER['PHP_SELF']);
            exit;
        }
        unset($FilterComponent);
        $FilterComponent = new FilterComponent();
        $FilterRuleOperator = SearchTools::getToSqlOperator($Operator);


        $pos = strpos($DbTablesPath, "()");
        if ($pos === false) { // Pas de relation * ds la jointure eventuelle

            if ((isset($_REQUEST[$Attribute]) && $_REQUEST[$Attribute] != ''
                    && $_REQUEST[$Attribute] != '##')
                    || (isset($_SESSION[$Attribute]) && $_SESSION[$Attribute] != ''
                            && $_SESSION[$Attribute] != '##') || $force == 1) {
                // || !(is_string($Value) && $Value == '')
                // selon si jointure ou pas
                $DbTablesPath = ($DbTablesPath == "")?$Attribute:$DbTablesPath;

                /* si c'est c'est un tab de valeurs, par exple un select multiple  */
                if ((isset($_REQUEST[$Attribute]) && is_array($_REQUEST[$Attribute]))
                        || (isset($_SESSION[$Attribute]) && is_array($_SESSION[$Attribute]))
                        || is_array($Value)) {
                    if ($force == 1) {
                        $Array = $Value;
                    }
                    else {
                        $Array = isset($_REQUEST[$Attribute])?
                                $_REQUEST[$Attribute]:$_SESSION[$Attribute];
                    }
                    // pour une raison que j'ignore, $Array n'est dans certains 
                    // cas pas un array :/ on fait ce truc ici donc pour s'en 
                    // assurer:
                    if (!is_array($Array)) {
                        $Array = array($Array);
                    }
                    if (!in_array('##', $Array, true)) { // si la bonne selection
                        foreach($Array as $key => $val) {
                            $Array[$key] = (($Operator == 'Like') || ($Operator == 'NotLike'))?
                                    str_replace('*', "%", $val):$val;
                        }
                        if (in_array($Operator, array('Equals', 'In'))) {
                            $ope = FilterRule::OPERATOR_IN;
                        }
                        elseif (in_array($Operator, array('NotEquals', 'NotIn'))) {
                            $ope = FilterRule::OPERATOR_NOT_IN;
                        }
                        elseif (in_array($Operator, array('Like', 'NotLike'))) {
                            foreach($Array as $key => $val) {
                                $FilterComponent->setItem(
                                    new FilterRule(
                                        $DbTablesPath,
                                        $FilterRuleOperator,
                                        $Array[$key]
                                    )
                                );
                                // c'est un OR ds ce cas!!
                                $FilterComponent->operator = FilterComponent::OPERATOR_OR;
                            }
                            return $FilterComponent;
                        }
                        else return null; // Pas de sens dans ce cas (autres operateurs)
                        $FilterComponent->setItem(new FilterRule($DbTablesPath, $ope, $Array));

                    } else return false; // si '##' selectionne
                } else {
                    if (is_string($Value) && $Value == "") { // !empty($Value)
                        if ($force == 0) {
                            if (isset($_REQUEST[$Attribute])
                            && ($_REQUEST[$Attribute] == '' || $_REQUEST[$Attribute] == '##')) {
                                return false;  // pas de filtre dans ce cas
                            }
                            $Value = (isset($_REQUEST[$Attribute]))?
                                strtoupper($_REQUEST[$Attribute]):
                                strtoupper($_SESSION[$Attribute]);
                        }
                    } else {
                        $Value = (is_string($Value))?strtoupper($Value):$Value;
                    }
                    $AttributeValue = (($Operator == 'Like') || ($Operator == 'NotLike'))?
                            str_replace('*', "%", $Value):$Value;
                    $FilterComponent->setItem(
                        new FilterRule(
                            $DbTablesPath,
                            $FilterRuleOperator,
                            $AttributeValue
                        )
                    );
                }
                return $FilterComponent;
            }
        }

        else {  // Au moins une relation 1..* ou *..* ds la jointure eventuelle
            if ((isset($_REQUEST[$Attribute]) && $_REQUEST[$Attribute] != ''
                    && $_REQUEST[$Attribute] != '##')
                    || (isset($_SESSION[$Attribute]) && $_SESSION[$Attribute] != ''
                    && $_SESSION[$Attribute] != '##') || $force == 1 || !empty($Value)) {
                // ne pas oublier de preciser ce parametre pour les relation 1..*
                if ($startEntity == '') {
                    trigger_error('There is a 1..* relation in search criterions:
                                   you have to define EntitySearched in SearchForm constructor call !!',
                                   E_USER_ERROR);
                    exit;
                }

                if ($Value == '' && SearchTools::requestOrSessionExist($Attribute) !== false) {
                    $Value = SearchTools::requestOrSessionExist($Attribute);
                }
                if (is_string($Value)) {
                    $Value = strtoupper($Value);
                } elseif (is_array($Value) && in_array('##', $Value)) {
                    return false;
                }

                // Recuperation des Id qui conviennent
                $sql = SearchTools::getSQLfromMacro(
                        $startEntity, $DbTablesPath, $FilterRuleOperator, $Value);
                $result = Database::connection()->execute($sql);

                if (false === $result) {  // si erreur sql
                    if (DEV_VERSION) {
                        echo $sql . '<br />';
                    }
                    trigger_error(Database::connection()->ErrorMsg(), E_USER_ERROR);
                }

                if ($result->_numOfRows == 0) {  // si 0 resultat
                    $FilterComponent->setItem(new FilterRule('Id',
                        FilterRule::OPERATOR_EQUALS, 0));
                    return $FilterComponent;
                }

                $valueArray = array();
                while (!$result->EOF) {
                    $valueArray[] = (int)$result->fields['_Id'];
                    $result->moveNext();
                }

                $FilterComponent->setItem(new FilterRule('Id',
                    FilterRule::OPERATOR_IN, $valueArray));
                return $FilterComponent;
             }
        }
        return false;
    }

    // }}}
    // SearchTools::NewFilterComponentOverDynamicProperty() {{{

    /**
     * construit un $FilterComponent sur une Propriete dynamique
     * TODO: Gerer les operateurs In et NotIn !!!!!!!!!!!!!!!!!
     *
     * @param string $attr nom de l'attribut sur lequel s'effectue le filtre
     * @param string $type le type de valeur de la propriété (ex: IntValue)
     *           cf. PropertyValue
     * @param string $op "Like" ou "Equal" ou "NotLike", ...:
     *           cf ds lib/filter tous les operateurs disponibles
     * @param string $value valeur du champs: facultatif
     * @static
     * @return object FilterComponent ou false
     */
    static function newFilterComponentOverDynamicProperty($attr, $type, $op, $value='') {
        if ((isset($_REQUEST[$attr]) && !empty($_REQUEST[$attr])) ||
            (isset($_SESSION[$attr]) && !empty($_SESSION[$attr])) ||
            !empty($value)) {
            if (!empty($value)) {
                $value = (is_string($value))?strtoupper($value):$value;
            } else {
                $value = -1;
                if (isset($_REQUEST[$attr])) {
                    $value = $_REQUEST[$attr];
                } elseif (isset($_SESSION[$attr])) {
                    $value = $_SESSION[$attr];
                }
            }
            $operator = SearchTools::getToSqlOperator($op);
            require_once('SQLRequest.php');
            $ids = array();
            if (!is_array($value)) {
                $value = array($value);
            }
            if (in_array('##', $value)) {
                return false;
            }

            foreach($value as $val){
                $val = (($op == 'Like') || ($op == 'NotLike'))?
                        str_replace('*', "%", $val):$val;
                $result = request_DynamicProperties_Search($attr, $type, $operator, $val);
                if (false != $result) {
                    while(!$result->EOF){
                        $ids[] = (int)$result->fields['_Product'];
                        $result->MoveNext();
                    }
                }
            }
            $comp = new FilterComponent();

            if (!empty($ids)) {
                $comp->setItem(new FilterRule('Id', FilterRule::OPERATOR_IN, $ids));
            } else { // un filtre vide
                $comp->setItem(new FilterRule('Id', FilterRule::OPERATOR_EQUALS, 0));
            }
            return $comp;
        }
        return false;
    }

    // }}}
    // SearchTools::filterAssembler() {{{

    /**
     * Créé un objet filtre complet a partir d'un tableau d'objets
     * FilterComponent et d'un operateur (AND ou OR).
     *
     * @static
     * @param  array $componentArray tableau de FilterComponent
     * @param  string $operator Operateur (AND, OR, ...)
     * @return mixed object Filter or empty array
     */
    static function filterAssembler($componentArray, $operator = 'AND')
    {
        // pour supprimer les entrées nulles du tableau de filtres
        $components = array();
        foreach($componentArray as $filter) {
            if ($filter != null) {
                $components[] = $filter;
            }
        }
        if (count($components) == 0) {
            return array();
        }
        return Tools::filterComponentsToFilter($components, ' '.$operator.' ');
    }

    // }}}
    // SearchTools::inputDataInSession() {{{

    /**
     * Mise en session des saisies dans le form.
     *
     * @param integer $preserveGridItems
     *      - par defaut 0: on veut effacer les traces des cases cochees
     *      - 1: utilisé notamment pour la commande: on doit garder en session
     *           les Product selectionnes
     * @param string $namePrefix prefixe a donner aux noms des var de session,
     * @param boolean $isSearchForm true si on est dans un formulaire de recherche,
     * auquel cas il faut creer $_SESSION["LastEntitySearched"]
     * pour eviter les interferences avec d'autres formulaires
     * @static
     * @return void
     */
    static function inputDataInSession($preserveGridItems=0,
            $namePrefix='', $saveLastEntitySearched=false)
    {
        $session = Session::Singleton();
        foreach($_REQUEST as $key => $value) {
            $session->register($namePrefix . $key, $value, 2); // En session pour 3 pages
        }
        // Pour eviter l'interaction entre differents forms de recherche de l'appli
        if ($saveLastEntitySearched) {
            SearchTools::saveLastEntitySearched();
        }
        SearchTools::prolongDataInSession($preserveGridItems);
    }

    // }}}
    // SearchTools::saveLastEntitySearched() {{{

    /**
     * Ajoute en session le LastEntitySearched,
     * pour eviter l'interaction entre differents forms de recherche de l'appli
     *
     * @access public
     * @static
     * @return void
     **/
    static function saveLastEntitySearched() {
        $path_parts = basename($_SERVER['PHP_SELF']);
        $tab = explode('.', $path_parts);
        $entity = ($tab[0] == 'dispatcher') ? $_REQUEST['entity'] : $tab[0];
        $session = Session::Singleton();
        $session->register($entity, '', 2); //  mise en session pour 2 pages
        $session->register('LastEntitySearched', $entity, 3);
    }

    // }}}
    // SearchTools::dataInSessionToDisplay() {{{

    /**
     * Retourne les valeurs par defaut a afficher ds les chps du formulaire, à
     * partir de $_SESSION
     *
     * @static
     * @return array tableau des valeurs a afficher ds les chps du form
     */
    static function dataInSessionToDisplay()
    {
        $DefaultValuesArray = array();
        $sessionVarName = SearchTools::getGridItemsSessionName();
        foreach($_SESSION as $key => $value) {
            if (!in_array($key, array('URLs', USER_SESSION_NAME,
                REALM_SESSION_NAME, 'session_timeout', 'vars_timeout',
                $sessionVarName, 'PageIndex', 'gridItems', 'actionId',
                'toRemove', 'x', 'y')))
            {
                $DefaultValuesArray[$key] = $value;
            }
        }
        return $DefaultValuesArray;
    }

    // }}}
    // SearchTools::prolongDataInSession() {{{

    /**
     * Prolongation des var en session pour les pages accedees a partir des actions
     * du Grid, afin qu'au retour au form de recherche, on retrouve les valeurs à
     * afficher ds les chps du form
     *
     * @param integer $preserveGridItems
     *      - par defaut 0: on veut effacer les traces des cases cochees
     *      - 1: utilise notamment pour la commande: on doit garder en session les
     *           Product selectionnes
     * @param int $pagenum le nombre de pages de conservation de la session
     * @static
     * @return void
     */
    static function prolongDataInSession($preserveGridItems=0, $pagenum=3)
    {
        $session = Session::Singleton();
        $varName = SearchTools::getGridItemsSessionName();
        // XXX pas très beau tout ça
        $blacklist = array('URLs', USER_SESSION_NAME, REALM_SESSION_NAME,
            'session_timeout', 'vars_timeout', $varName, 'gridItems',
            'OLS_ShoppingCart');
        foreach($_SESSION as $key => $value) {
            if (!in_array($key, $blacklist)) {
                $session->prolong($key, $pagenum);
            }
        }
        // Suppression des var de session qui laissent des cases cochees qd il faut pas
        if (isset($_SESSION['LastEntitySearched']) && ($preserveGridItems == 0)) {
            $variable_completement_inutile = 42;
            $oldVarName = SearchTools::getGridItemsSessionName(
                $_SESSION['LastEntitySearched']);
            if($varName != $oldVarName || isset($_REQUEST['formSubmitted'])) {
                unset($_SESSION['gridItems'], $_SESSION[$oldVarName]);
            }
        }
    }

    // }}}
    // SearchTools::createArrayIDFromCollection() {{{

    /**
     * Permet a partir d'une collection, de creer un tableau d'Id
     *
     * @param mixed $Entity nom ou tableau des noms du ou des type d'objet(s)
     * @param mixed $Filter filtre (array or Object Filter)
     * @param string $message  message de 1ere item. Exple: Sélectionner des opérations
     * @param string $toString nom de l'attribut (=> lazy loading dans ce cas),
     * si on ne veut pas utiliser toString()
     * @static
     * @return array
     */
    static function createArrayIDFromCollection($Entity, $Filter = array(), $message = '',
        $toString = 'toString', $order = array()) {
        $itemsArray = array();
        if (!is_array($Entity)) {
            $Entity = array($Entity);
        }
        for($i = 0; $i < count($Entity); $i++) {
            $itemsArray = $itemsArray + SearchTools::toStringArray(
                $Entity[$i], $Filter, $toString, $order);
        }
        asort($itemsArray);  // asort plutot que natcasesort, finalement
        if ($message != '') {
            $itemsArray = array('##' => $message) + $itemsArray;
        }
        return $itemsArray;
    }

    // }}}
    // SearchTools::toStringArray() {{{

    /**
     * Charge une collection d'objet en fonction du filtre et de l'ordre et
     * récupère pour chacun d'eux les valeurs en fonction de $toString
     *
     * @param string $Entity nom de l'entité
     * @param mixed $Filter filtre (array or Filter)
     * @param string $toString nom de l'attribut à récupérer
     * @param array $order ordre de tri
     * @static
     * @return array
     */
    static function toStringArray($Entity, $Filter = array(),
        $toString = 'toString', $order = array())
    {
        $ValuesArray = array();
        $mapper = Mapper::singleton($Entity);
        if ($toString!='toString' && $toString != 'toStringLite') {
            if (property_exists($Entity, '_' . $toString)) {
                $fields = array($toString);
            } else {
                // methode addon
                $fields = array();
            }
        } else {
            $toStringAttribute = Tools::getToStringAttribute($Entity);
            $fields = is_array($toStringAttribute)?
                    $toStringAttribute:array($toStringAttribute);
        }
        $Collection = $mapper->loadCollection($Filter, $order, $fields);

        if (!Tools::isEmptyObject($Collection)) {
            $count = $Collection->getCount();
            $getter = ($toString == 'toString' || $toString == 'toStringLite')?
                    $toString:'get' . $toString;
            for($i = 0; $i < $count; $i++) {
                $item = $Collection->getItem($i);
                $ValuesArray[$item->getId()] = $item->$getter();
            }
        }
        return $ValuesArray;
    }

    // }}}
    // SearchTools::getCompletionArray() {{{

    /**
     * Retourne un tableau avec toutes les valeurs affecté à une propriété d'un
     * objet
     *
     * @param string $entity nom de l'objet
     * @param string $attribute nom de la propriété
     * @static
     * @return array
     */
    static function getCompletionArray($entity, $attribute)
    {
        $values = array();
        $table = call_user_func(array($entity, 'getTableName'));
        $sql = sprintf('SELECT _%s FROM %s', $attribute, $table);
        $result = Database::connection()->execute($sql);
        while (!$result->EOF) {
            $values[] = $result->fields['_' . $attribute];
            $result->moveNext();
        }
        return $values;
    }

    // }}}
    // SearchTools::requestOrSessionExist() {{{

    /**
     * Si 2nd parametre non renseigne, determine si une var existe en $_REQUEST
     * ou en $_SESSION
     * Si 2nd parametre renseigne, retourne true si $_REQUEST[$varName]
     * ou $_SESSION[$varName] vaut $value, sinon retourne false
     *
     * @param string $varName nom de variable
     * @param mixed $value valeur de variable
     * @param string $operator opérateur 'Equals' pour vérifier que
     * $varName=$value sinon, vérifie qu'ils sont différents
     * @static
     * @return boolean
     */
    static function requestOrSessionExist($varName, $value = false, $operator='Equals')
    {
        if (isset($_REQUEST[$varName])) {
            $var = $_REQUEST[$varName];
        } elseif (isset($_SESSION[$varName])) {
            $var = $_SESSION[$varName];
        } else return false;

        if ($value === false) {
            return $var;
        } else {
            if ($operator == 'Equals') {
                return $var == $value;
            } else {
                return $var != $value;
            }
        }
    }

    /**
     * Remplit un tableau de valeurs par defaut en se basant sur $_REQUEST;
     * Sert pour remplir un formulaire QuickForm
     *
     * @static
     * @return array
     **/
    static function createDefaultValueArray() {
        $defaultValues = array();
        foreach($_REQUEST as $name => $value) {
            $defaultValues[$name] = $value;
        }
        return $defaultValues;
    }

    // }}}
    // SearchTools::cleanDataSession() {{{

    /**
     * Nettoie les variables en session commençant par le préfixe passé en paramètre
     *
     * @param string $sessionPrefix le préfixe des variables à supprimmer,
     * oubien 'noPrefix': ds ce cas, on supprime tout sauf les exceptions passees en 2nd param
     * @param array $exceptions les variables a ne pas tuer
     * @return boolean
     */
    static function cleanDataSession($sessionPrefix = false,
        $exceptions = array('URLs', USER_SESSION_NAME, REALM_SESSION_NAME, 'session_timeout','vars_timeout'))
    {
        if (false == $sessionPrefix) {
            // pas de préfixe on retourne false
            return false;
        }
        foreach($_SESSION as $key => $val) {
            if ((($sessionPrefix == 'noPrefix') && !in_array($key, $exceptions))
             || (substr($key, 0, strlen($sessionPrefix)) == $sessionPrefix)) {
                unset($_SESSION[$key]);
            }
        }

        return true;
    }

    // }}}
    // SearchTools::cleanYesNoDataSession() {{{

    /**
     * Nettoie les variables en session: utilise dans les form de recherche
     * contenant 2 checkBox pour un booleen.
     * exple: WorkOrder.State dans WOList.php
     *
     * @param string $yesVarName le nom de la variable "yes" a traiter
     * @param string $noVarName le nom de la variable "no" a traiter
     * @static
     * @return void
     */
    static function cleanYesNoDataSession($yesVarName, $noVarName)
    {
        if (isset($_POST['formSubmitted'])) {
            if (isset($_POST[$yesVarName])) {
                unset($_SESSION[$noVarName]);
            }
            elseif (isset($_POST[$noVarName])) {
                unset($_SESSION[$yesVarName]);
            }
            else {
                unset($_SESSION[$yesVarName], $_SESSION[$noVarName]);
            }
        }
    }

    // }}}
    // SearchTools::cleanCheckBoxDataSession() {{{

    /**
     * Nettoie les variables en session: utilise dans les form de recherche
     * contenant 1 checkBox.
     *
     * @param mixed $varName le nom ou un tableau de noms de la ou des variable(s) a traiter
     * @static
     * @return void
     */
    static function cleanCheckBoxDataSession($varName)
    {
        $varNames = (!is_array($varName))?array($varName):$varName;

        foreach($varNames as $varName) {
            if (isset($_POST['formSubmitted']) && !isset($_POST[$varName])) {
                unset($_SESSION[$varName]);
            }
        }
    }

    // }}}
    // SearchTools::getSQLfromMacro() {{{

    /**
     * Retourne une requete SQL.
     *
     * @param string $startEntity nom de l'entite de depart
     * @param string $macro chemin en cas de jointure, mais pas encadre par des '%'
     * Exple: 'FlyType().Name'
     * les () expriment une relation 1..* ou *..*
     * @param string $operator FilterRule::OPERATOR_IN, ...:
     * cf ds SearchTools::getToSqlOperator() tous les operateurs disponibles
     * @param string $value valeur du champs
     *
     * @return string
     */
    static function getSQLfromMacro($startEntity, $macro, $operator, $value) {
        $tableArray = array();  // contiendra les tables pour la req SQL
        $join         = array();  // contiendra les clauses de jointure pour la req SQL
        $parts  = explode('.', $macro);
        $criteria = array_pop($parts);

        require_once(MODELS_DIR . '/' . $startEntity.'.php');
        $leftTable = call_user_func(array($startEntity, 'getTableName'));
        $tableArray[] = $leftTable . ' T0';
        $currentEntity = $startEntity;

        foreach($parts as $key => $attributeName) {
            $pos = strpos($attributeName, "()");
            require_once(MODELS_DIR . '/' . $currentEntity.'.php');
            $inherits = explode('@', $attributeName);  // strpos($attributeName, "@")
            if ($pos === false) { // attribut de type FK
                $attributeName = $inherits[0];
                $rightTable = Registry::getPropertyTableName($currentEntity, $attributeName);
                $tableArray[] = $rightTable . ' T' . strval($key+1);
                if (count($inherits) == 1) {
                    $EntitiesArray = call_user_func(array($currentEntity, 'getProperties'));
                    $currentEntity = $EntitiesArray[$attributeName];
                } else {
                    $currentEntity = $inherits[1];
                }
                $join[] = ' T' . strval($key+1) . '._Id = T' . strval($key) . '._' . $attributeName;
            }
            else {  // attribut de type collection
                $attributeName = $inherits[0];
                $attributeName = substr($attributeName, 0, strlen($attributeName) - 2);  // suppression des ()
                $links = call_user_func(array($currentEntity, 'getLinks'));
                $attributeForJoin = $links[$attributeName]['field'];
                if (count($inherits) == 1) {
                    $currentEntity = $links[$attributeName]['linkClass'];
                } else {
                    $currentEntity = $inherits[1];
                }
                require_once(MODELS_DIR . '/' . $currentEntity.'.php');
                $rightTable = call_user_func(array($currentEntity, 'getTableName'));
                $tableArray[] = $rightTable . ' T' . strval($key+1);

                if (!isset($links[$attributeName]['linkTable'])) {  // relation 1..*
                    $join[] = ' T' . strval($key) . '._Id = T' . strval($key+1) . '._' . $attributeForJoin;
                }
                else {  // relation *..*: intervention en plus de la table de liens!!
                    $tableArray[] = $links[$attributeName]['linkTable'] . ' T' . strval($key+100);  // table de liens
                    $join[] = ' T' . strval($key) . '._Id = T' . strval($key+100) . '._' . $links[$attributeName]['field'];
                    $join[] = ' T' . strval($key+1) . '._Id = T' . strval($key+100) . '._' . $links[$attributeName]['linkField'];
                }
            }
        }

        $sql = 'SELECT DISTINCT T0._Id FROM '.
            implode(',', $tableArray). ' WHERE '.
            implode(' AND ', $join) . ' AND T' . strval($key+1) . '._' . $criteria;
        // Attention: si $value est un array, l'Operator doit etre In ou NotIn
        if (is_array($value)) {
            $value = "('" . implode("','", $value) . "')";
        } else {
            $value = "'".str_replace("*", "%", $value)."'";
        }

        $sql .= $operator . $value;
        if (defined('DATABASE_ID') && !Object::isPublicEntity($startEntity)) {
            $sql .= ' AND (T0._DBId IS NULL OR T0._DBId = ' . DATABASE_ID . ')';
        }
        
        return $sql;
    }

    // }}}
    // SearchTools::getGridItemsSessionName() {{{

    /**
     * Retourne le nom de la variable pour les cases à cocher des grids.
     * La construction du nom est differente selon qu'il s'agit d'un écran
     * générique ou non.
     *
     * @static
     * @access public
     * @param string $path optionnel (si vide c'est le SCRIPT_NAME)
     * @return string
     */
    public static function getGridItemsSessionName($path = false) {
        if (!$path) {
            $path = basename($_SERVER['SCRIPT_NAME']);
        }
        $slashpos = strrpos($path, '/');
        $slashpos = $slashpos !== false?$slashpos+1:0;
        $dotpos   = strrpos($path, '.');
        $dotpos   = $dotpos !== false?$dotpos:strlen($path);
        $entity   = substr($path, $slashpos, $dotpos-$slashpos);
        if ($entity == 'dispatcher') {
            $entity = $_REQUEST['entity'];
        }
        return $entity . '_griditems';
    }
    // }}}
    // SearchTools::buildFilterFromArray() {{{

    /**
     * Appelé entre autres par Mapper::_getSQLRequest() pour construire un objet
     * Filter si le paramètre $attributeFilters est un tableau
     *
     * @access public
     * @param  array $array le tableau filtre
     * @return object FilterComponent
     */
    public static function buildFilterFromArray($array = array(), $filter = false, $cls='') {
        $metafilter = new FilterComponent();
        $metafilter->operator = FilterComponent::OPERATOR_AND;
        if ($filter instanceof FilterComponent) {
            if (empty($array)) {
                return $filter;
            }
            $metafilter->setItem($filter);
        }
        foreach($array as $filterName => $filterValue) {
            if(false !== strpos($filterName, '.')) {
                $operator = (is_array($filterValue))?'In':'Equals';
                $component = SearchTools::newFilterComponent($filterName, $filterName, $operator, $filterValue, 1, $cls);
                $metafilter->setItem($component);
            } else {
                $operator = (is_array($filterValue))?
                    FilterRule::OPERATOR_IN:FilterRule::OPERATOR_EQUALS;
                $rule = new FilterRule($filterName, $operator, $filterValue);
                $metafilter->setItem($rule);
            }
        }
        return $metafilter;
    }

    // }}}
}
?>
