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

define('SHOWHIDE_IMAGE', 'images/searchform_showhide.png');

/**
 * Requirements
 */
require_once('HTML/QuickForm.php');
require_once('HTML/QuickForm/Renderer/ArraySmarty.php');

/**
 * SearchForm
 *
 * Permet d'encapsuler un formulaire construit avec Pear::HTML_QuickForm
 * servant de moteur de recherche sur un Grid.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package Framework
 * @subpackage Search
 */
class SearchForm {
    // propriétés {{{

    /**
     * Nom du formulaire: par defaut, le nom du script sans l'extension
     *
     * @access private
     */
    private $_name;

    /**
     *
     * @var object HTML_QuickForm
     * @access private
     */
    private $_form;

    /**
     *
     * @var object _smarty
     * @access private
     */
    private $_smarty;

    /**
     * Contient les infos pour construire le filtre pour une recherche
     *
     * @var array of strings
     * @access private
     */
    private $_elementsForSearch = array();

    /**
     * Contient les infos pour construire le formulaire a afficher
     *
     * @var array of strings
     * @access private
     */
    private $_elementsToDisplay = array();

    /**
     * Contient la Collection a passer au Grid::render(), si ce n'est un Mapper
     *
     * @var object Collection
     * @access private
     */
    private $_itemsCollection = false;

    /**
     * Contient les valeurs par defaut des elements du formulaire
     *
     * @var array of strings
     * @access private
     */
    private $_defaultValues = array();

    /**
     * Contient les filtercomponents a appliquer pour la recherche
     *
     * @var array of strings
     * @access private
     */
    private $_filterComponentArray = array();

    /**
     * Contient des eventuelles variables smarty supplementaires
     *
     * @var array of strings: array(name => value, ...)
     * @access private
     */
    private $_additionalSmartyValues = array();

    /**
     * Contient un tableau d'actions eventuelles
     *
     * @access private
     */
    private $_actionArray = array();

    /**
     * true=> on affiche le form, false => non
     *
     * @access private
     */
    private $_displayForm = true;

    /**
     * type d'entite cherchee: correspond a l'entity du Mapper du Grid
     * resultat de la recherche
     *
     * @access public
     */
    public $entity = true;

    /**
     * Booleen: aficher ou pas le bouton reset
     *
     * @access private
     */
    public $withResetButton = true;
    
    /**
     * Permet d'afficher ou pas la personnalisation des searchforms par user,
     * sur le modele de la customisation des grids.
     *
     * @var boolean $customizationEnabled
     * @access public
     */
    public $customizationEnabled = false;
    
    /**
     * Criteres à masquer par défaut si aucune preference ne précise de les
     * afficher.
     *
     * @var array $hiddenCriteriaByDefault
     * @access public
     */
    public $hiddenCriteriaByDefault = array();

    /**
     * Colonnes à masquer pour l'utilisateur.
     *
     * @var array $hiddenColumnsByUser
     * @access public
     */
    public $hiddenCriteriaByUser = array();

    // }}}
    // Constructeur {{{

    /**
     * Constructor
     *
     * @param $entitySearched: nom du type d'entite cherchee:
     * param obligatoire s'il y a une relation 1..* pour un des criteres
     * @access public
     */
    public function __construct($entitySearched='') {
        $path = explode('.', basename($_SERVER['PHP_SELF']));
        $form = new HTML_QuickForm($path[0], 'post', $_SERVER['PHP_SELF']);
        $this->_form = $form;
        // XXX XHTML 1.0 strict n'accepte pas d'attribut "name" pour le tag form
        unset($this->_form->_attributes['name']);
        $form->addElement('hidden', 'formSubmitted', '', 'id="formsubmitted"');
        $smarty = new Template();
        $this->_smarty = $smarty;
        $this->_name = ($path[0] == 'dispatcher') ? $_REQUEST['entity'] : $path[0];
        $this->entity = $entitySearched;
    }

    // }}}
    // Accesseurs {{{

    /**
     * Get accessor for $_name property
     *
     * @return array of strings
     * @access public
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get accessor for $_itemsCollection property
     *
     * @return array of strings
     * @access public
     */
    public function getItemsCollection()
    {
        return $this->_itemsCollection;
    }
    /**
     * Set accessor for $_itemsCollection property
     *
     * @param array $ of strings
     * @access public
     */
    public function setItemsCollection($value)
    {
        $this->_itemsCollection = $value;
    }

    /**
     * Get accessor for $_displayForm property
     *
     * @access public
     */
    public function getDisplayForm()
    {
        return $this->_displayForm;
    }
    /**
     * Set accessor for $_displayForm property
     *
     * @access public
     */
    public function setDisplayForm($value)
    {
        $this->_displayForm = $value;
    }

    /**
     * Get accessor for $_defaultValues property
     *
     * @return array of strings
     * @access public
     */
    public function getDefaultValues()
    {
        return $this->_defaultValues;
    }

    /**
     * Set accessor for $_defaultValues property
     *
     * @param array $ of strings
     * @access public
     */
    public function setDefaultValues($value)
    {
        $this->_defaultValues = $value;
    }
    // }}}
    // SearchForm::setQuickFormAttributes() {{{

    /**
     * Permet de modifier les attributs du formulaire
     *
     * @param array $ : tableau associatif de la forme:
     *         array('action'   => $action,
     *               'method'   => $method,
     *               'name'     => $formName,
     *               'id'       => $formName,
     *               'target'   => $target,
     *               'onsubmit' => $mafonctionjs)
     * Le tableau ci dessus n'est pas exaustif.
     *
     * @access public
     * @return void
     */
    public function setQuickFormAttributes($attributes) {
        $form = $this->_form;
        $form->updateAttributes($attributes);
    }

    // }}}
    // SearchForm::addSmartyValues() {{{

    /**
     * Permet d'ajouter des variables smarty
     *
     * @param array $ : tableau associatif de la forme:
     *         array('varName1' => 'value1',
     *               'varName2' => 'value2',
     *               .... )
     * @access public
     */
    public function addSmartyValues($value) {
        $smarty = $this->_smarty;
        $SmartyValues = $this->_additionalSmartyValues;
        $this->_additionalSmartyValues = $SmartyValues + $value;
    }

    // }}}
    // SearchForm::addElement() {{{

    /**
     * Ajoute un element de formulaire
     *
     * @param  $type string: type du chps de formulaire: text, select, date...
     * @param  $name string: nom du chps de formulaire
     * @param  $caption string: intitule du chps de formulaire: text, select, ...
     * @param  $attributes array: attributs optionnels : cf documentation de QuickForm
     * @param  $searchOptions array: Options relatives a la recherche:
     *             'Path' => 'Product.BaseReference',
     *           'Operator' => 'Like'
     *             'Value' => 'toto'
     *             'Disable' => true (false par defaut): si on ne veut pas que
     *                          le chps de form genere automatiquemt un
     *                          FilterComponent, pour le gerer differemment
     * @access public
     * @return void
     */
    public function addElement($type, $name, $caption = '', $attributes = array(),
            $searchOptions = array())
    {
        $id = ' id="' . strtolower($name) . '"';
        // Pour la mise en page a 100% des width pour text, textarea, select
        if ($type == 'text' || $type == 'textarea') {
            $class = ' class="' . $type . '"' . $id;
            $attributes[0] = (isset($attributes[0]))?$attributes[0].$class:$class;
        }
        elseif ($type == 'select') {
            $attributes[1] = (isset($attributes[1]))?
                    $attributes[1] . ' class="select"' . $id:'class="select"' . $id;
        }
        elseif ($type == 'checkbox') {
            // $attributes[0] : label output after checkbox
            $attributes[0] = (isset($attributes[0]))?$attributes[0]:'';
            $attributes[1] = (isset($attributes[1]))?$attributes[1] . $id:$id;
        }
        if ((isset($searchOptions['Path']) && !(false === strpos($searchOptions['Path'], '()')))
            && $this->entity == '') {
            // INUTILE DE TRADUIRE !!!
            trigger_error('You have to define EntitySearched in constructor call.',
                           E_USER_ERROR);
            exit;
        }

        /*  Construction du formulaire  */
        $addInstruction = "\$this->_form->addElement(\$type, \$name"; // $form
        if ($caption != '' || $type == 'date') {
            $addInstruction .= ", \$caption";
        }

        if ($type == 'date') { // Cas particulier d'un date
            // L'affichage depend de la langue du user connecte

            if (!isset($attributes['language'])) {
                $attributes['language'] = I18N::getLocaleCode(true);
            }
            if (!isset($attributes['format'])) {
                $attributes['format'] = I18N::getHTMLSelectDateFormat();
            }
            // Gestion du filtre resultant
            $FilterComponentArray = array();
            if ((isset($_REQUEST[$name]) || (isset($_SESSION[$name])) &&
                        (!isset($_REQUEST['formSubmitted']) && !$this->isFirstArrival()))) {
                $Var = (isset($_REQUEST[$name]))?$_REQUEST[$name]:$_SESSION[$name];
                //global ${$Var};
                $path = (!isset($searchOptions['Path']))?$name:$searchOptions['Path'];
                $ope = (!isset($searchOptions['Operator']))?'Equals':$searchOptions['Operator'];

                if (!isset($searchOptions['Disable'])) {
                    $searchOptions['Disable'] = false;
                }
                if ($searchOptions['Disable'] == false) {
                    // Attention, ne gere pas plusieurs widgets de date, TODO!
                    $FilterComponentArray[] = SearchTools::NewFilterComponent(
                            'aDate', $path, $ope,
                            $Var['Y'] . '-' . $Var['m'] . '-' . $Var['d'] . ' 00:00:00',
                            1);
                    $initFilterComponentArray = $this->_filterComponentArray;
                    $this->_filterComponentArray = array_merge(
                            $initFilterComponentArray, $FilterComponentArray);
                }
            }
            // Affiche le form avec les valeurs par defaut
            $Default = $this->getDefaultValues();
            if (isset($attributes['Value'])) {
	           $this->setDefaultValues(array_merge($Default, $attributes['Value']));
            }
        }

        if ($type == 'select') { // Cas particulier d'un select
            $addInstruction .= ", \$attributes[0]";
            $addInstruction = (isset($attributes[1]))?
                    $addInstruction . ", \$attributes[1]":$addInstruction;
            // Faut determiner si c'est un select multiple, pour la fction js reset
            if (isset($attributes[1]) && strpos($attributes[1], 'multiple') !== false) {
                $selectType = 'selectM'; // select multiple !!
            }
        }

        // teste si $attributes est un tableau associatif
        elseif (!empty($attributes)) {
            $keys = array_keys($attributes);
            if (!is_int($keys[0]) || $type == 'date') {
                $addInstruction .= ", \$attributes";
            } else {
                $addInstruction .= ", '";
                $addInstruction .= implode("', '", $attributes);
                $addInstruction .= "'";
            }
        }

        $addInstruction .= ');';
        eval($addInstruction);

        if ($type != 'hidden') {
            $this->_elementsToDisplay[$name] = (isset($selectType))?$selectType:$type;
        }

        if (false === $searchOptions) {
            return;
        }
        /*  Enregistrement des elements servant pour les recherches :
        *   si pas desactive ou pas un checkbox de creneau de dates  */
        if (!(isset($searchOptions['Disable']) && true === $searchOptions['Disable'])
                && (strpos($name, 'DateOrder') === false) && $type != 'date') {

            if (!isset($searchOptions['Path'])) {
                $searchOptions['Path'] = "";
            }
            // c'est la valeur par defaut: 'Like' pour text, 'Equals' pour les autres
            if (!isset($searchOptions['Operator'])) {
                $searchOptions['Operator'] = ($type == 'text')?'Like':'Equals';
            }
            if (!isset($searchOptions['Value'])) {
                $searchOptions['Value'] = "";
            }
            if (!isset($searchOptions['Disable'])) {
                $searchOptions['Disable'] = false;
            }
            $this->_elementsForSearch[] = array(
                    'Name' => $name,
                    'Path' => $searchOptions['Path'],
                    'Operator' => $searchOptions['Operator'],
                    'Value' => $searchOptions['Value'],
                    'Disable' => $searchOptions['Disable']);
        }
    }

    // }}}
    // SearchForm::addDynamicElement() {{{

    /**
     * Ajoute un element de formulaire pour une propriété dynamique
     *
     * @param  $type string: type du chps de formulaire: text, select, ...
     * @param  $name string: nom du chps de formulaire
     * @param  $caption string: intitule du chps de formulaire: text, select...
     * @param  $attributes array: attributs optionnels,
     *            cf documentation de QuickForm
     * @param  $searchOptions array: Options relatives a la recherche:
     *             'PropertyType' => 'IntValue', // cf PropertyValue
     *           'Operator' => 'Like'
     *             'Value' => 'toto'
     *             'Disable' => true (false par defaut): si on ne veut pas que le
     *                         chps de form genere automatiquemt un
     *                         FilterComponent, pour le gerer differemment
     * @access public
     * @return void
     */
    public function addDynamicElement($type, $name, $caption = '', $attributes = array(),
            $searchOptions = array()) {
        require_once(MODELS_DIR . '/Product.php');
        // on appelle addElement sauf pour la partie recherche
        $this->addElement($type, $name, $caption, $attributes, false);
        // on gère la recherche ici
        if (!(isset($searchOptions['Disable']) && true === $searchOptions['Disable']) &&
            (strpos($name, 'DateOrder') === false)) {
            // Si propertyType non precise, et si cette Property n'est pas
            // stockee ds la table Product
            if (!isset($searchOptions['PropertyType'])
                        && !(in_array($name, array_keys(Product::getProperties())))) {
                $PropertyMapper = Mapper::singleton('Property');
                $Property = $PropertyMapper->load(array('Name' => $name));
                $searchOptions['PropertyType'] = (Tools::isEmptyObject($Property))?
                        'StringProperty':getPropertyTypeColumn($Property->getType());
            }
            if (!isset($searchOptions['Operator'])) {
                $searchOptions['Operator'] = ($type == 'text')?'Like':'Equals';
            }
            if (!isset($searchOptions['Value'])) {
                $searchOptions['Value'] = "";
            }
            if (!isset($searchOptions['Disable'])) {
                $searchOptions['Disable'] = false;
            }

            $paramsArray = array('Name' => $name,
                                 'Operator' => $searchOptions['Operator'],
                                 'Value' => $searchOptions['Value'],
                                 'Disable' => $searchOptions['Disable']);

            if (isset($searchOptions['PropertyType'])) {
                $paramsArray['PropertyType'] = $searchOptions['PropertyType'];
            }
            else {
                $paramsArray['Path'] = $name;
            }

            $this->_elementsForSearch[] = $paramsArray;

        }
        // todo
    }

    // }}}
    // SearchForm::addDate2DateElement() {{{

    /**
     * Ajoute un element de type creneau de date
     *
     * @access public
     * @param  $begin array pour construire le 1er widget de date
     * @param  $end array pour construire le 2nd widget de date
     * @param  $defaultValues array valeurs par defaut
     * @return void
     */
    public function addDate2DateElement($begin, $end, $defaultValues)
    {
        /*  Construction du formulaire  */
        // L'affichage depend de la langue du user connecte
        if (!isset($begin['Format']['language'])) {
            $begin['Format']['language'] = I18N::getLocaleCode(true);
        }
        if (!isset($begin['Format']['format'])) {
            $begin['Format']['format'] = I18N::getHTMLSelectDateFormat();
        }
        if (!isset($begin['Label'])) {
            $begin['Label'] = _('From');
        }
        if (!isset($begin['Format']['minYear'])) {
            $begin['Format']['minYear'] = date('Y') - 1;
        }
        if (!isset($begin['Format']['maxYear'])) {
            $begin['Format']['maxYear'] = date('Y') + 1;
        }
        /////
        if (!isset($end['Format']['language'])) {
            $end['Format']['language'] = I18N::getLocaleCode(true);
        }
        if (!isset($end['Format']['format'])) {
            $end['Format']['format'] = I18N::getHTMLSelectDateFormat();
        }
        if (!isset($end['Label'])) {
            $end['Label'] = _(' to ');
        }
        if (!isset($end['Format']['minYear'])) {
            $end['Format']['minYear'] = date('Y') - 1;
        }
        if (!isset($end['Format']['maxYear'])) {
            $end['Format']['maxYear'] = date('Y') + 1;
        }

        $addInstruction = "\$this->_form->addElement('date', \$begin['Name'], \$begin['Label'], \$begin['Format']);";
        $addInstruction .= "\$this->_form->addElement('date', \$end['Name'], \$end['Label'], \$end['Format']);";
        eval($addInstruction);
        // le type est dynamique, pour lier ensemble les 2 criteres begin et end
        $types = array_values($this->_elementsToDisplay);
        $nbTypeDate2Date = 0;
        for ($i = 0; $i < count($types); $i++) {
            if (!(false === strpos($types[$i], 'date2date'))) {
                $nbTypeDate2Date += 1;
            }
        }

        $Date2DateIndex = ($nbTypeDate2Date / 2) + 1;
        $this->_elementsToDisplay[$begin['Name']] = 'date2date' . $Date2DateIndex;
        $this->_elementsToDisplay[$end['Name']] = 'date2date' . $Date2DateIndex;

        $Default = $this->getDefaultValues();
        // affiche le form avec les valeurs par defaut
        $this->setDefaultValues(array_merge($Default, $defaultValues));
        $smarty = $this->_smarty;
        $smarty->assign('DisplayDate' . $Date2DateIndex, 'none');

        /*  Traitement special pour les creneaux de date  */
        $FilterComponentArray = array();
        if (isset($_REQUEST['DateOrder' . $Date2DateIndex]) ||
                (isset($_SESSION['DateOrder' . $Date2DateIndex]) &&
                    (!isset($_REQUEST['formSubmitted']) && !$this->isFirstArrival()))) {

            $FilterComponentArray = $this->getDate2DateFilterComponent(
                    $Date2DateIndex, $begin, $end);
        }
        // n'est pas vide si des creneaux de date
        $initFilterComponentArray = $this->_filterComponentArray;
        $this->_filterComponentArray = array_merge($initFilterComponentArray,
                                                   $FilterComponentArray);
    }

    // }}}
    // SearchForm::getDate2DateFilterComponent() {{{

    /**
     *
     * @access public
     * @static
     * @param  integer $Date2DateIndex
     * @param  array $begin
     * @param  array $end
     * @return FilterComponent array
     **/
    public function getDate2DateFilterComponent($Date2DateIndex=1, $begin, $end) {
        $FilterComponentArray = array();

        if (!isset($begin['Disable'])) {
            $begin['Disable'] = false;
        }
        if (!isset($end['Disable'])) {
            $end['Disable'] = false;
        }

        $Var = (isset($_REQUEST['DateOrder' . $Date2DateIndex]))?'_REQUEST':'_SESSION';
        global ${$Var};  // var dynamique

        $begin['Path'] = (!isset($begin['Path']))?$begin['Name']:$begin['Path'];
        $end['Path'] = (!isset($end['Path']))?$end['Name']:$end['Path'];
        $attrs = call_user_func(array($this->entity, 'getProperties'));

        if ($begin['Disable'] == false) {
            $type = isset($attrs[$begin['Path']]) ? $attrs[$begin['Path']] : Object::TYPE_DATETIME;
            $value = ${$Var}[$begin['Name']]['Y'] . '-' .
                ${$Var}[$begin['Name']]['m'] . '-' .
                ${$Var}[$begin['Name']]['d'];
            $value .= $type==Object::TYPE_DATETIME ? ' 00:00:00' : '';
            $FilterComponentArray[] = SearchTools::NewFilterComponent(
                'BDate', $begin['Path'],
                'GreaterThanOrEquals', $value, 1,
                $this->entity);
        }
        if ($end['Disable'] == false) {
            $type = isset($attrs[$end['Path']]) ? $attrs[$end['Path']] : Object::TYPE_DATETIME;
            $value = ${$Var}[$end['Name']]['Y'] . '-' .
                ${$Var}[$end['Name']]['m'] . '-' .
                ${$Var}[$end['Name']]['d'];
            $value .= $type==Object::TYPE_DATETIME ? ' 23:59:59' : '';
            $FilterComponentArray[] = SearchTools::NewFilterComponent(
                'EDate', $end['Path'], 'LowerThanOrEquals', $value,
                1, $this->entity);
        }
        return $FilterComponentArray;
    }

    // }}}
    // SearchForm::addBlankElement() {{{

    /**
     * Pour la mise en page: permet de creer des elements vides
     *
     * @access public
     * @return void
     */
    public function addBlankElement() {
        $types = array_values($this->_elementsToDisplay);
        $nbTypeBlank = 0;
        for ($i = 0; $i < count($types); $i++) {
            if (!(false === strpos($types[$i], 'blank'))) {
                $nbTypeBlank += 1;
            }
        }
        $BlankIndex = $nbTypeBlank + 1;
        $this->_elementsToDisplay['blank' . $BlankIndex] = 'blank';
    }

    // }}}
    // SearchForm::addAction() {{{

    /**
     * Permet d'ajouter une action, en plus des boutons "OK" et "X"
     *
     * @access public
     * @param array $ du type: array(
     *         'Caption' => 'Ajouter',
     *         'URL' => 'WorkOrder.php?action=add',
     *         'Profiles' => array(PROFILE_ADMIN, PROFILE_ACTOR, PROFILE_SUPERVISOR),
     *         'Name' => 'myactionName': optionnel, pour affecter le name et
     *                   l'id de l'input type button
     * ));
     * $params['Enable'] est a true par defaut
     * @return void
     */
    public function addAction($params)
    {
        if (!isset($params['Caption'])) {
            $params['Caption'] = _('Add');
        }
        if (!isset($params['Enable'])) {
            $params['Enable'] = true;
        }
        $this->_actionArray[] = $params;
    }

    // }}}
    // SearchForm::buildFilterComponentArray() {{{

    /**
     * Retourne un tableau de FilterComponents construit a partir des elements du form
     * Appelle aussi checkPreferences() pour prendre en compte les criteres masques
     *
     * @access public
     * @param integer $preserveGridItems :
     * - par defaut 0: on veut effacer les traces des cases cochees
     * - 1: utilise notamment pour la commande: on doit garder en session les
     * Product selectionnes
     * @return void
     */
    public function buildFilterComponentArray($preserveGridItems=0)
    {
        $FilterComponentArray = array(); // Tableau de filtres
        if (!$this->isFirstArrival() || isset($_REQUEST['formSubmitted'])) {
            
            // Prend en compte les preferences pour l'user connecte, si besoin
            $this->checkPreferences();
        
            // n'est pas vide si des creneaux de date
            $initFilterComponentArray = $this->_filterComponentArray;
            $elements = $this->_elementsForSearch;
            for ($i = 0; $i < count($elements); $i++) {
                if (true === $elements[$i]['Disable']) {
                    continue;
                }
                if (isset($elements[$i]['PropertyType'])) {
                    $FilterComponent = SearchTools::NewFilterComponentOverDynamicProperty(
                            $elements[$i]['Name'],
                            $elements[$i]['PropertyType'],
                            $elements[$i]['Operator'],
                            $elements[$i]['Value']
                    );
                } else {
                    $FilterComponent = SearchTools::NewFilterComponent(
                            $elements[$i]['Name'],
                            $elements[$i]['Path'],
                            $elements[$i]['Operator'],
                            $elements[$i]['Value'],
                            0,
                            $this->entity
                    );
                }
                if (!$FilterComponent) {
                    continue;
                }
                $FilterComponentArray[] = $FilterComponent;
            }

            $this->_filterComponentArray = array_merge($initFilterComponentArray,
                                                   $FilterComponentArray);
        }

        // met les saisies en session, sauf LastEntitySearched, qui sera maj
        // apres le render()
        if(isset($_REQUEST['formSubmitted'])) {
            SearchTools::inputDataInSession($preserveGridItems);
        }
        return $this->_filterComponentArray;
    }

    // }}}
    // SearchForm::render() {{{

    /**
     * Retourne le rendu HTML du formulaire, en appliquant le template
     *
     * @access public
     * @param  $template string: emplacement du template
     * (par defaut, le template generique, construit dynamiquement)
     * @return string
     */
    public function render($template = '') {
        $smarty = $this->_smarty;
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
        $form = $this->_form;

        $reset = ""; // Contient le code HTML pour reset le form
        $Elements = $this->_elementsToDisplay;
        $ElementsToDisplay = array();
        $elts = $form->_elements;
    
        // Prend en compte les preferences pour l'user connecte, si besoin
        $this->checkPreferences();

        foreach ($Elements as $name => $type) {
            if (in_array($name, $this->hiddenCriteriaByUser)) {
                continue;
            }
            if ($type == 'checkbox') {
                $ElementsToDisplay[] = "<label for=\"" . strtolower($name)
                        . "\">{\$form." . $name . ".label}</label>&nbsp;&nbsp;{\$form."
                        . $name . ".html}";
            } elseif (!(false === strpos($type, 'date2date'))) { // si creneau de dates
                // si plusieurs creneaux de date, indice du creneau
                $Date2DateIndex = substr($type, strlen($type)-1, 1);
                $DisplayDate = 'DisplayDate' . $Date2DateIndex;
                // Si le checkBox associe est masque, on ignore ce critere
                if (in_array('DateOrder' . $Date2DateIndex, $this->hiddenCriteriaByUser)) {
                    continue;
                }
                $keys = array_keys($Elements, $type);
                if (isset($keys[1])) {
                    $Endname = $keys[1];
                    unset($Elements[$Endname]); // suppression de cet element
                    // s'il faut afficher les widgets de date
                    if (isset($_REQUEST['DateOrder' . $Date2DateIndex]) ||
                            (isset($_SESSION['DateOrder' . $Date2DateIndex]) &&
                                (!isset($_REQUEST['formSubmitted']) && !$this->IsFirstArrival()))) {
                        $DisplayDate = 'block';
                    } else $DisplayDate = 'none';
                    $smarty->assign('DisplayDate'. $Date2DateIndex, $DisplayDate);

                    $ElementsToDisplay[] = "<div id='Date" . $Date2DateIndex
                        . "' style=\"display: {\$DisplayDate" . $Date2DateIndex . "}\">{\$form."
                        . $name . ".label}&nbsp;{\$form." . $name . ".html}&nbsp;{\$form."
                         . $Endname . ".label}&nbsp;{\$form." . $Endname . '.html}</div>';
                }
            } elseif ($type == 'blank') {
                $ElementsToDisplay[] = '&nbsp;';
                // Si nombre impair d'elements a afficher, on rajoute un <td>&nbsp;</td>
                if (count($ElementsToDisplay) % 2 != 0) {
                    $ElementsToDisplay[] = '&nbsp;';
                }
            } else {
                // On verifie s'il y a un label a afficher
                for($i = 0; $i < count($elts); $i++){
                    $attr = $elts[$i]->_attributes;
                    if ((isset($attr['name']) && $attr['name'] == $name)
                            || (isset($elts[$i]->_name) && $elts[$i]->_name == $name)) {
                        $label = $elts[$i]->_label;
                        break;
                    }
                }
                if (!empty($label)) {
                    $ElementsToDisplay[] = "<label for=\"" . strtolower($name)
                        . "\">{\$form." . $name . ".label}&nbsp;</label>:";
                }
                // Si c'est un type date, on encapsule dans un DIV
                $ElementsToDisplay[] = ($type != 'date')?"{\$form." . $name . ".html}"
                        :"<div id=\"" . $name . "\">{\$form." . $name . ".html}</div>";
            }

            /*  Generation du js pour reinitialiser le form  */
            if ($type == 'text') {
                $reset .= '$(\'' . $this->_name . '\').' . $name . ".value='';";
            }
            if ($type == 'checkbox') {
                // Ici, on prend en compte le defaultValues pour reinitialiser
                // (serait généralisable aux autres types de champs)
                $values = $this->getDefaultValues();
                $resetValue = (isset($values[$name]) && $values[$name] == 1)?
                        'true':'false';
                $reset .= '$(\'' . $this->_name . '\').' . $name . '.checked='. $resetValue . ';';
            }
            if ($type == 'selectM') {  // select multiple
                $reset .= '$(\'' . $this->_name . '\').' . "elements['"
                        . $name . "[]'].selectedIndex=0;";
            }
            if ($type == 'select') {
                $reset .= '$(\'' . $this->_name . '\').' . "elements['"
                        . $name . "'].selectedIndex=0;";
            }
            if (0 < strpos($type, "2date")) { // si creneau de dates
                $reset .= "$('Date" . $Date2DateIndex . "').style.display='{\$Display}';";
            }
        }
        $smarty->assign('reset', $reset);

        /*  Gestion des valeurs affichees par defaut ds le form  */
        $defaultValues = array();
        if (SearchTools::requestOrSessionExist('LastEntitySearched', $this->_name)) {
            // recupere les saisies faites precedemment et mises en session
            $defaultValues = SearchTools::dataInSessionToDisplay();
        }

        // affiche le form avec les valeurs par defaut
        $form->setDefaults(array_merge($this->getDefaultValues(), $defaultValues));
        $form->accept($renderer); // affecte au form le renderer personnalise
        $smarty->assign('form', $renderer->toArray());

        /*  Pour completer correctement le tableau HTML si besoin  */
        if (count($ElementsToDisplay) % 4 != 0) {
            $nbTD = 4 - count($ElementsToDisplay) % 4;
            $TD = "";
            for ($i = 0;$i < $nbTD;$i++) {
                $TD .= '<td>&nbsp;</td>';
            }
            $smarty->assign('ExtraTD', $TD);
        }

        $smarty->assign('FormElements', $ElementsToDisplay);
        $smarty->assign('FormName', $this->_name);
        $smarty->assign('ShowHideImageSource', SHOWHIDE_IMAGE);
        $smarty->assign('withResetButton', $this->withResetButton);
        $smarty->assign('CustomDisplayImage', AbstractGrid::CUSTOM_DISPLAY_IMAGE);
        $smarty->assign('CustomDisplayBarImage', AbstractGrid::CUSTOM_DISPLAY_BAR_IMAGE);
        $smarty->assign('customizationEnabled', $this->customizationEnabled);
        
        // Gestion du customize searchform: il est possible que precedemment dans
        // render(), on ait supprime 1 ou n criteres (enabled=false)
        if ($this->customizationEnabled/* && count($this->columns) > 1*/) {
            $this->_customDisplay($smarty);
        }

        /*  Gestion des actions eventuelles  */
        if (count($this->_actionArray) > 0) {
            $actions = ""; // va recevoir le contenu HTML a afficher
            $Auth = Auth::Singleton();
            foreach ($this->_actionArray as $index => $params) {
                // Test si Enable ou pas
                if ($params['Enable'] == false) {
                    continue;
                }
                /*  Test s'il y a des restrictions par profile  */
                if (!isset($params['Profiles']) ||
                    in_array($Auth->getProfile(), $params['Profiles'])) {
                    $actionName = isset($params['Name'])?
                            $params['Name']:'searchAction_' . $index;
                    $actionId = strtolower($actionName);
                    $actions .= '<input type="button" name="'.$actionName.'" '
                        . 'id="'.$actionId.'" class="button" value="'
                        . $params['Caption'] . '" onclick="'
                        . 'location.href=\'' . $params['URL'] . '\'"/>&nbsp;';
                }
            }
            $smarty->assign('actions', $actions);
        }

        /*  Gestion des variables smarty additionnelles: ds le template par defaut,
            placees en debut de form  */
        foreach ($this->_additionalSmartyValues as $name => $value) {
            $smarty->assign($name, $value);
        }
        $smarty->assign('AdditionalSmartyValues', $this->_additionalSmartyValues);

        /*  Si on affiche le grid, on n'affiche pas le form par defaut  */
        if (true === $this->displayGrid() || false === $this->getDisplayForm()) {
            $smarty->assign('DisplayForm', 'none');
        }
        $template = ($template != '')?$template:SEARCHFORM_TEMPLATE;
        $pageContent = $smarty->fetch($template);
        return $pageContent;
    }

    // }}}
    // SearchForm::_customDisplay() {{{
    /**
     * Remplit le layer de customisation des searchforms.
     *
     * @param object $tpl instanceof Template
     * @access private
     * @return void
     */
    private function _customDisplay($tpl) {
        require_once ('HTML/QuickForm.php');
        require_once('HTML/QuickForm/Renderer/ArraySmarty.php');
        require_once('HTML/QuickForm/advmultiselect.php');
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);

        $form = new HTML_QuickForm('CustomSearch', 'post', $_SERVER['PHP_SELF']);
        $form->removeAttribute('name');        // XHTML compliance
        $defaultValues = array();  // Valeurs par defaut des champs du form
        $form->updateAttributes(array('onsubmit' => "return checkBeforeSubmit();"));
        $form->addElement('hidden', 'customSearchUpdated', '0');

        $criteria = array();
        foreach($this->_elementsToDisplay as $elementName => $type) {
            if ($type == 'blank' || substr($type, 0, 4) == 'date') {
                continue;
            }
            $criteria[$elementName] = $this->_form->getElement($elementName)->getLabel();
        }
        $labels = array(_('criteria') . ':', _('Available criteria'),
                _('Criteria to hide'));
        $elt = HTML_QuickForm::createElement(
            'advmultiselect', 'customSearchHiddenCriteria', $labels,
            $criteria, array('style' => 'width:100%;'));
        // Necessaire pour externaliser la tonne de js, si include js trouve
        $js = (file_exists('JS_AdvMultiSelect.php'))?'':'{javascript}';
        $jsValidation = '<script type="text/javascript">
                //<![CDATA[
            function checkBeforeSubmit() {
                if ($(\'__customSearchHiddenCriteria\').options.length == 0) {
                    alert("' . _("You can't hide all criteria.") . '");
                    return false;
                }
                return true;
            }

            //]]>
             </script>';
        $eltTemplate = $js . $jsValidation . '
            <table{class}>
              <tr><th>{label_2}</th><th>&nbsp;</th><th>{label_3}</th></tr>
            <tr>
              <td valign="top">{unselected}</td>
              <td align="center">{add}{remove}</td>
              <td valign="top">{selected}</td>
            </tr>
            </table>
            ';
        $elt->setElementTemplate($eltTemplate);
        $form->addElement($elt);

        $form->addElement('submit', 'customSearchSubmit', A_VALIDATE,
                'onclick="this.form.customSearchUpdated.value=1;"');
        $form->addElement('button', 'customSearchCancel', A_CANCEL,
		      'onclick="fw.dom.toggleElement($(\'custom_search_layer\'));"');

        $defaultValues['customSearchHiddenCriteria'] = $this->hiddenCriteriaByUser;
        $form->setDefaults($defaultValues);
        // PATCH car advmultiselect buggé!!
        $elt->_values = $defaultValues['customSearchHiddenCriteria'];
        // end PATCH
        $form->accept($renderer); // affecte au form le renderer personnalise
        $tpl->assign('customSearchForm', $renderer->toArray());
    }
    
    // }}}
    // SearchForm::checkPreferences() {{{
    
    /**
     * Verifie et prend en compte les Preferences concernant les criteres de rech
     * s'il y en a.
     *
     * @access public
     * @return void
     */
    public function checkPreferences() {
        if (!$this->customizationEnabled) {
            return;
        }
        $searchformName = $this->getName();
        // test si des preferences viennent d'etre mises a jour
        $pref = array();
        if (!empty($_REQUEST['customSearchUpdated'])) {
            $pref['hiddenCriteria'] = (isset($_REQUEST['customSearchHiddenCriteria']))?
                    $_REQUEST['customSearchHiddenCriteria']:null;
        }
        if (!empty($pref)) {
            PreferencesByUser::set($searchformName, $pref);
            PreferencesByUser::save();
        }

        $pref = PreferencesByUser::get($searchformName);
        if ($pref != null) {
            if (isset($pref['hiddenCriteria'])) {
                $this->hiddenCriteriaByUser = $pref['hiddenCriteria'];
                // On supprime les valeurs en session correspondant a des criteres 
                // qui viennent d'etre masques
                foreach ($pref['hiddenCriteria'] as $criteria) {
                    unset($_SESSION[$criteria]);
                }
            }
        } else {
            // criteres masques par défaut
            $this->hiddenCriteriaByUser = $this->hiddenCriteriaByDefault;
        }
    }
    
    // }}}
    // SearchForm::BuildHiddenField() {{{

    /**
     * Permet de construire une serie de champs caches
     *
     * @param  $values array: tableau du type: array('name1' => 'value1', ...)
     * @access public
     * @return string
     */
    public function buildHiddenField($values) {
        $form = $this->_form;
        foreach ($values as $name=>$value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $form->addElement('hidden', $name . '[]', $val);
                } // for
            } else {
                $form->addElement('hidden', $name, $value, 'id="'.strtolower($name).'"');
            }
        }
    }

    // }}}
    // SearchForm::displayGrid() {{{

    /**
     * Retourne true s'il faut afficher le Grid, false sinon
     *
     * @access public
     * @param integer $force 0 par defaut; si 1 return true
     * @return boolean
     */
    public function displayGrid($force=0) {
         // Si on vient de cliquer sur OK pour lancer une recherche ou
         // si la derniere recherche a ete faite sur la meme entite ou
         // si on a cliquer sur l'action export excel
        if ($force == 1 || isset($_REQUEST['formSubmitted']) ||
           (isset($_SESSION['formSubmitted']) &&
           (isset($_SESSION['LastEntitySearched']) &&
            $_SESSION['LastEntitySearched'] == $this->_name)) ||
            isset($_REQUEST['export'])) {
            return true;
        }
        return false;
    }

    // }}}
    // SearchForm::IsFirstArrival() {{{

    /**
     * Retourne true si 1er acces a cette page (acces par le menu)
     *
     * @access public
     * @return boolean
     */
    public function isFirstArrival() {
        if ((!(isset($_SESSION['LastEntitySearched'])
                && $_SESSION['LastEntitySearched'] == $this->_name))
            || isset($_REQUEST['FirstArrival']))
        {
            return true;
        }
        /*|| (isset($_SESSION["LastEntitySearched"]) && $_SESSION["LastEntitySearched"]
         == $this->_name && isset($_SESSION["FirstArrival"]))*/
        return false;
    }

    // }}}
    // SearchForm::displayResult() {{{

    /**
     * Affiche le resultat de la recherche
     *
     * @param object $grid objet de type Grid
     * @param boolean $pager : pagination
     * @param array or object $filter
     * @param array $order
     * @param string $title titre de la page
     * @param array $JSRequirements
     * @param string $addContent Contenu html à ajouter avant ou apres le Grid
     * de la forme: array('beforeForm' => '...', // avant le SearchForm
     *                       'between' => '...',  // entre le SearchForm et le Grid...
     *                       'afterGrid' => '...')
     * @return string
     */
    public function displayResult($grid, $pager=false, $filter=array(), $order=array(),
        $title='', $JSRequirements=array(), $addContent=array(), $renderFunc='page')
    {
        // Si on ne passe pas une Collection directemt au Grid::render()
        if ($this->getItemsCollection() === false) {
            $mapper = Mapper::singleton($this->entity);
            $grid->setMapper($mapper);
        }

        if ($grid->isPendingAction()) {
            $dispatchResult = $grid->dispatchAction($this->getItemsCollection());
            if (Tools::isException($dispatchResult)) {
                $urlVarArray = array();
                // On passe ds l'url les valeurs des hidden s'il y en a
                $hiddenFields = $this->getHiddenFields();
                foreach($hiddenFields as $key => $value) {
                    if ($key == 'formSubmitted') {
                        continue;
                    }
                    $urlVarArray[] = $key . '=' . $value;
                }
                $urlComplement = (empty($urlVarArray))?'':'?' . implode('&', $urlVarArray);

                // L'action est-elle de type Popup:
                $triggeredAction = $grid->getAction($_REQUEST['actionId']);

                if ($triggeredAction->targetPopup === true) {
                    $tpl = BASE_POPUP_TEMPLATE;
                    $returnURL = 'javascript:window.close()';
                } else {
                    $tpl = BASE_TEMPLATE;
                    $returnURL = basename($_SERVER['PHP_SELF']) . $urlComplement;
                }
                Template::errorDialog($dispatchResult->getMessage(), $returnURL, $tpl);
                exit();
            }
        } else {
            if ($this->getItemsCollection() !== false) {
                $mapper = $this->getItemsCollection();
            }
            $result = $grid->render($mapper, $pager, $filter, $order);

            $addContent['beforeForm'] = (isset($addContent['beforeForm']))?
                    $addContent['beforeForm']:'';
            $addContent['between'] = (isset($addContent['between']))?
                    $addContent['between']:'';
            $addContent['afterGrid'] = (isset($addContent['afterGrid']))?
                    $addContent['afterGrid']:'';

            $pageContent = $addContent['beforeForm'] . $this->render()
                         . $addContent['between'] . $result
                         . $addContent['afterGrid'];

            if(isset($_REQUEST['formSubmitted'])) {
                SearchTools::saveLastEntitySearched();
            }
            Template::$renderFunc($title, $pageContent . '</form>', $JSRequirements);
            exit();
        }
    }

    // }}}
    // SearchForm::getHiddenFields() {{{

    /**
     * Retourne les champs hidden : array('Chps_1' => 'val_1', ...)
     *
     * @access public
     * @return array
     **/
    public function getHiddenFields() {
        $hiddenArray = array();
        $form = $this->_form;
        $elements = $form->_elements;
        $count = count($elements);
        for($i = 0; $i < $count; $i++) {
            $element = $elements[$i];
            if ($element->_type == 'hidden') {
                $hiddenArray[$element->_attributes['name']] = $element->_attributes['value'];
            }
        }
        return $hiddenArray;
    }

    // }}}
}

?>
