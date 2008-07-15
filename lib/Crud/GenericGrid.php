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

define('MANYTOMANY_GRIDCOLUMN', 'GenericSubGrid');

// }}}
// doc {{{

/**
 * Classe GenericGrid :
 *
 * {@inheritdoc}
 *
 * Génére le SearchForm et le Grid d'un l'objet entity à partir
 * des méthodes getMapping() et getFeatures() définies dans cet objet.
 *
 * voir aussi : {@link GenericAddEdit GenericAddEdit}.
 *
 * Méthodes surchargable dans la classe MonObjetGrid extends GenericGrid.
 *
 *     - renderSearchFormFieldsProperties() : définie les paramètres des champs
 *       du Searchform.
 *     - renderSearchFormActionsProperties() : définie les paramètres des
 *       actions du SearchForm.
 *     - renderGridColumnsProperties() : définie les paramètres des colonnes
 *       du Grid.
 *     - renderGridActionsProperties() : définit les paramètres des actions
 *       du Grid.
 *     - getGridFilter() : sera "mergé" avec le filtre issu des résultats du
 *       SearchForm
 *     - getGridSortOrder() : sera utilisé pour le tri initial du grid (par
 *       défaut tri alphabétique sur première colonne).
 *
 * Pour chaque property définie dans le Mapping on peut définir une méthode
 * pour le grid et/ou une autre pour le SearchForm pour ne pas utilisé le
 * render par défaut.
 *     - renderGridColumnMyProperty()
 * <code>
 * public function renderGridColumnTVA() {
 *     return array(
 *         'type'   => 'FieldMapper',
 *         'name'   => 'TVA',
 *         'params' => array('Macro'=>'%TVA.Rate%'));
 * }
 * </code>
 *
 *     - renderSearchFormMyProperty()
 * <code>
 * public render function renderSearchFormMyProperty() {
 *     return array(
 *         'type'       => 'select',
 *         'name'       => 'FlowType_Charges',
 *         'caption'    => _('Charges'),
 *         'attributes' => array(
 *             SearchTools::CreateArrayIDFromCollection(
 *                 'FlowType',
 *                 array('Type'=>CHARGE),
 *                 'Sélectionner les charges',
 *                 'Name'
 *              )
 *          ),
 *          'path'      => 'FlowType().Id'
 *      );
 * }
 * </code>
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package Framework
 * @subpackage Crud
 * @tutorial GenericGrid.cls
 */ // }}}
class GenericGrid extends GenericController {
    // propriétés {{{

    const addUrlTpl  = 'dispatcher.php?action=add&amp;entity={CLSNAME}&amp;altname={ALTNAME}';
    const editUrlTpl = 'dispatcher.php?action=edit&amp;entity={CLSNAME}&amp;altname={ALTNAME}&amp;objID={ID}';
    const delUrlTpl  = 'dispatcher.php?action=del&amp;entity={CLSNAME}&amp;altname={ALTNAME}';
    const viewUrlTpl = 'dispatcher.php?action=view&amp;entity={CLSNAME}&amp;altname={ALTNAME}&amp;objID={ID}';

    /**
     * True si le grid à un SearchForm.
     * @var boolean
     */
    private $_withSearhForm = false;

    /**
     * Tableau contenant les noms d'élements qui sont des checkboxes.
     * @var array
     */
    private $_checkboxes = array();

    /**
     * Tableau contenant les actions construites par defaut par GenericGrid.
     * Le tableau peut contenir les actions suivantes:
     * <code>
     * array(
     *    'add'    => $actionAdd,
     *    'edit'   => $actionEdit,
     *    'delete' => $actionDelete,
     *    'view'   => $actionView,
     *    'print'  => $actionPrint,
     *    'export' => $actionExport
     * );
     * </code>
     *
     * @var array
     * @access protected
     */
    protected $actions = array();

    /**
     * SearchForm
     * @var Object SearchForm
     */
    public $searchForm = null;

    /**
     * Grid
     * @var Object Grid
     */
    public $grid;

    /**
     * nombre de colonnes avec un sous-grid
     * @var integer
     */
    public $nbSubGridColumns = 0;

    /**
     * true pour afficher l'actionprint
     * @var boolean
     * @access public
     */
    public $showPrintAction = true;

    /**
     * true pour afficher l'action export
     * @var boolean
     * @access public
     */
    public $showExportAction = true;

    /**
     * un tableau contenant le contenu HTML à ajouter avant ou après le grid de
     * la forme:
     * <code>
     * array(
     *     'beforeForm' => 'content',
     *     'between'    => 'content',
     *     'afterGrid'  => 'content'
     * )
     * </code>
     *
     * @see SearchForm::displayResult()
     * @var array
     * @access public
     */
    public $addionalContent = array();

    /**
     * true si le grid à une action "Editer" à la place d'un lien vers le
     * formulaire d'édition.
     *
     * @access public
     * @var boolean
     */
    public $haveEditButton = false;

    /**
     * forceGridDisplay 
     * 
     * True pour masquer le SearchForm et forcer l'affichage du grid.
     *
     * @var bool
     * @access public
     */
    public $forceGridDisplay = false;

    // }}}
    // Constructeur {{{

    /**
     * Charge l'objet, initialise les variables et récupère les
     * propiétés du SearchForm et du Grid.
     *
     * Le tableau de paramètre peut prendre les clé suivante:
     * - 'altname'         => nom de l'objet
     * - 'itemsperpage'    => le nombre d'éléments par page
     * - 'profiles'        => un tableau de profile
     * - 'template'        => template à utiliser
     * - 'title'           => titre du grid
     *
     * @param string $entity nom de l'objet
     * @param array $params tableau de paramètres
     * @return void
     */
    public function __construct($params=array()) {
        parent::__construct($params);
        $this->_withSearhForm = in_array(
            GenericController::FEATURE_SEARCHFORM, $this->features);
        $this->mapping = $this->getMapping();

        foreach($this->mapping as $property=>$param) {
            if($this->getElementType($property) == Object::TYPE_MANYTOMANY &&
            in_array('grid', $param['usedby'])) {
                $this->nbSubGridColumns++;
            }
        }

        if ($this->_withSearhForm) {
            $this->searchForm = new SearchForm($this->clsname);
            $this->searchForm->setQuickFormAttributes(array(
                'name' => $this->clsname,
                'id'   => $this->clsname
            ));
            $this->searchForm->buildHiddenField(array('entity' => $this->clsname));
            $this->searchForm->buildHiddenField(array('altname' => $this->altname));
        }
        $this->grid = new Grid();

        if (isset($params['itemsperpage'])) {
            $this->grid->itemPerPage = $params['itemsperpage'];
        }
        if(isset($params['showPrintAction'])) {
            $this->showPrintAction = $params['showPrintAction'];
        }
        if(isset($params['showExportAction'])) {
            $this->showExportAction = $params['showExportAction'];
        }
    }

    // }}}
    // GenericGrid::render() {{{

    /**
     * Effectue l'affichage du SearchForm et du Grid.
     * Note : les méthodes sont séparées pour une éventuelle
     * personalisation.
     *
     * @param string $title titre du grid
     * @return void
     */
    public function render($title=false, $template=false) {
        $this->includeSessionRequirements();
        $this->session = Session::singleton();
        unset($_SESSION['_' . $this->clsname . '_']);

        $this->auth();

        $title = !$title ? $this->title : $title;
        $template = !$template ? $this->htmlTemplate : $template;

        if($this->_withSearhForm) {
            $this->buildSearchForm();
            $this->searchForm->setDisplayForm(!$this->forceGridDisplay);
            
            if ($this->searchForm->displayGrid($this->forceGridDisplay)) {
                if (!empty($this->_checkboxes)) {
                    SearchTools::cleanCheckBoxDataSession($this->_checkboxes);
                }
                $filter = $this->getGridFilter();
                if (!is_array($filter)) {
                    $filter = array($filter);
                }
                // mettre 1 pour préserver les checkbox coché ici empêche de 
                // décoché des checkbox lors que l'on fait plusieurs rechreche 
                // à la suite
                $filter = array_merge($filter, $this->searchForm->BuildFilterComponentArray());
                $filter = SearchTools::FilterAssembler($filter);

                $this->buildGrid();
                $order = $this->getGridSortOrder();
                $this->searchForm->displayResult($this->grid, true, $filter,
                    $order, $title, $this->jsRequirements,
                    $this->addionalContent, 'page');
            } else {
                Template::page($title, $this->searchForm->Render() . '</form>',
                    $this->jsRequirements, $this->cssRequirements, $template);
            }
        } else {
            $this->buildGrid();
            $filter = $this->getGridFilter();
            $order = $this->getGridSortOrder();

            Template::pageWithGrid($this->grid, $this->clsname,
                $title, $filter, $order, $template);
        }
    }

    // }}}
    // GenericGrid::buildGrid() {{{

    /**
     * buildGrid
     *
     * @access protected
     * @return void
     */
    protected function buildGrid() {
        $putEditLink = in_array(GenericController::FEATURE_EDIT, $this->features);
        foreach($this->mapping as $property=>$params) {
            if(!in_array(GenericController::FEATURE_GRID, $params['usedby'])) {
                continue;
            }

            $customMethod = 'renderColumn' . $property;
            if (method_exists($this, $customMethod)) {
                $ret = $this->$customMethod();
                if ($ret !== false) {
                    continue;
                }
            }

            $columnType = $this->_getColumnType($property);
            $columnName = ($columnType == MANYTOMANY_GRIDCOLUMN) ?
                array($params['shortlabel']) : $params['shortlabel'];
            $columnParams = $this->_getColumnParams($property);

            if($putEditLink && $columnType != MANYTOMANY_GRIDCOLUMN && !$this->haveEditButton) {
                $columnParams['Macro'] = '<a href="' .
                    str_replace(
                        array('{CLSNAME}', '{ALTNAME}', '{ID}'),
                        array($this->clsname, $this->altname, '%ID%'),
                        GenericGrid::editUrlTpl) .
                    '">' . $columnParams['Macro'] . '</a>';
                $putEditLink = false;
            }

            $this->grid->newColumn($columnType, $columnName, $columnParams);
        }
        $this->additionalGridColumns();

        if($this->nbSubGridColumns > 0) {
            $this->grid->setNbSubGridColumns($this->nbSubGridColumns);
            //$this->grid->withNoSortableColumn = true;
        }

        $this->createGridActions();
    }

    // }}}
    // GenericGrid::buildSearchForm() {{{

    /**
     * buildSearchForm
     *
     * @access protected
     * @return void
     */
    protected function buildSearchForm() {
        $date2dateIndex = 0;

        foreach($this->mapping as $property=>$params) {
            if(!in_array(GenericController::FEATURE_SEARCHFORM, $params['usedby'])) {
                continue;
            }

            $customMethod = 'renderSearchForm' . $property;
            if (method_exists($this, $customMethod)) {
                $ret = $this->$customMethod();
                if ($ret !== false) {
                    continue;
                }
            }

            $elmType = $this->_getSearchFormType($property);
            if($elmType == 'blankElement') {
                $this->searchForm->addBlankelement();
                continue;
            }
            if($elmType == 'date2date') {
                $date2dateIndex++;
                $this->searchForm->addElement(
                    'checkbox', 'DateOrder'.$date2dateIndex,
                    _('Filter by') . ' ' . $params['label'],
                    array('', 'onclick="$(\\\'Date'.$date2dateIndex.'\\\').'
                    . 'style.display=this.checked?\\\'block\\\':\\\'none\\\';"'));
                $this->_checkboxes[] = 'DateOrder'.$date2dateIndex;
                $this->searchForm->addDate2DateElement(
                    array(
                        'Name' => 'StartDate_'.$property,
                        'Path' => $property),
                    array(
                        'Name' => 'EndDate_'.$property,
                        'Path' => $property),
                    array(
                        'StartDate_'.$property => array(
                            'Y'=>date('Y')),
                        'EndDate_'.$property => array(
                            'd' => date('d'),
                            'm' => date('m'),
                            'Y' => date('Y')
                        )
                    )
                );
            }
            $attrs = $this->_getSearchFormAttributes($property);
            $opts = $this->_getSearchFormOptions($property);

            if($this->isDynamicElement($property)) {
                $this->searchForm->addDynamicElement($type, $property,
                    $params['label'], $attrs, $opts);
                continue;
            }

            $this->searchForm->addElement($elmType, $property,
                $params['label'], $attrs, $opts);
            if ($elmType == 'checkbox') {
                $this->_checkboxes[] = $property;
            }
        }

        if(in_array(GenericController::FEATURE_ADD, $this->features)) {
            $this->searchForm->addAction(array(
                'URL' => str_replace(
                    array('{CLSNAME}', '{ALTNAME}'),
                    array($this->clsname, $this->altname),
                    GenericGrid::addUrlTpl)
                )
            );
        }

        $this->onAfterBuildSearchForm();
    }

    // }}}
    // GenericGrid::createGridActions() {{{

    /**
     * createGridActions.
     *
     * Ajoute les actions au grid.
     *
     * @access protected
     * @return void
     */
    protected function createGridActions() {
        if(in_array('add', $this->features)) {
            $this->actions['add'] = $this->grid->newAction('AddEdit', array(
                'Action' => 'Add',
                'URL'     => str_replace(
                    array('{CLSNAME}', '{ALTNAME}'),
                    array($this->clsname, $this->altname),
                    GenericGrid::addUrlTpl),
                'Caption' => A_ADD,
                'Renderer' => 'Button'
                )
            );
        }
        if(in_array('view', $this->features)) {
            $this->actions['view'] = $this->grid->newAction('Redirect', array(
                'URL'     => str_replace(
                    array('{CLSNAME}', '{ALTNAME}', '{ID}'),
                    array($this->clsname, $this->altname, '%d'),
                    GenericGrid::viewUrlTpl),
                'Caption' => A_VIEW
                )
            );
        }

        if (in_array(GenericController::FEATURE_EDIT, $this->features)
        && $this->haveEditButton) {
            $this->actions['edit'] = $this->grid->newAction('Redirect', array(
                'URL' => str_replace(
                    array('{CLSNAME}', '{ALTNAME}', '{ID}'),
                    array($this->clsname, $this->altname, '%d'),
                    GenericGrid::editUrlTpl),
                'Caption' => A_UPDATE
                )
            );
        }

        if(in_array('del', $this->features)) {
            $this->actions['delete'] = $this->grid->newAction('Redirect', array(
                'URL'     => str_replace(
                    array('{CLSNAME}', '{ALTNAME}'),
                    array($this->clsname, $this->altname),
                    GenericGrid::delUrlTpl),
                'Caption' => A_DELETE,
                'TransmitedArrayName' => 'objID'
                )
            );
        }

        $this->additionalGridActions();

        if($this->showExportAction) {
            $this->actions['export'] = $this->grid->newAction('Export',
                array('Filename' => $this->clsname));
        }
        if($this->showPrintAction) {
            $this->actions['print'] = $this->grid->newAction('Print');
        }
    }

    // }}}
    // GenericGrid::onAfterBuildSearchForm() {{{

    /**
     * onAfterBuildSearchForm
     *
     * Méthode appelé aprés la construction du searchForm.
     *
     * @access protected
     * @return void
     */
    protected function onAfterBuildSearchForm() {
    }

    // }}}
    // GenericGrid::additionalGridActions() {{{

    /**
     * additionalGridActions
     *
     * Méthode appelée aprés l'ajout des action add, edit et del et avant l'ajout
     * des actions export et print, permet d'ajouter des actions au grid.
     *
     * @access protected
     * @return void
     */
    protected function additionalGridActions() {
    }

    // }}}
    // GenericGrid::additionalGridColumns() {{{

    /**
     * additionalGridColumns
     *
     * Méthode appelé entre l'ajout des colonne et des actions du grid.
     * @access protected
     * @return void
     */
    protected function additionalGridColumns() {
    }

    // }}}
    // GenericGrid::isDynamicElement() {{{

    /**
     * isDynamicElement
     *
     * Méthode à surchager, doit retourner true si le searchForm doit utiliser
     * addDynamicElement au lieu de addElement pour la property.
     *
     * @param string $elmName Nom de la property.
     * @access protected
     * @return boolean
     */
    protected function isDynamicElement($elmName) {
        // XXX ???
        return false;
    }

    // }}}
    // GenericGrid::getGridFilter() {{{

    /**
     * Méthode à surcharger dans les classes filles.
     * Retourne un tableau de FilterComponent
     *
     * <code>
     * protected function getGridFilter() {
     *     return array(
     *         SearchTools::NewFilterComponent('Number','Number','Like','40%',1));
     * }
     * </code>
     *
     * @access protected
     * @return FilterComponent
     */
    protected function getGridFilter() {
        return array();
    }

    // }}}
    // GenericGrid::getGridSortOrder() {{{

    /**
     * Méthode à surcharger dans les classes filles
     * Par defaut: tri croissant sur la 1ere colonne
     *
     * <code>
     * protected function getGridSortOrder() {
     *     return array('Number'=>SORT_ASC);
     * }
     * </code>
     *
     * @access protected
     * @return array
     */
    protected function getGridSortOrder() {
        $keys  = array_keys($this->mapping);
        $props = array_keys($this->attrs);
        $propTypes = array_values($this->attrs);

        // Si la property associee de l'objet,
        // correspondant a la 1ere colonne est de type FK
        $propType = $propTypes[0];
        if (is_string($propType)) {
            $instance = new $propType();
            $toStringAttribute = $instance->getToStringAttribute();
            $order = array($keys[0] . '.' . $toStringAttribute => SORT_ASC);
        }
        else {
            $order = array($keys[0] => SORT_ASC);
        }
        return (isset($keys[0]) && in_array($keys[0], $props))?$order:array();
    }

    // }}}
    // GenericGrid::_getColumnParams() {{{

    /**
     * _getColumnParams
     *
     * Retourne les paramètres de la colonne pour la propriété $elmName .
     *
     * @param string $elmName Nom de la propriété.
     * @access private
     * @return array
     */
    private function _getColumnParams($elmName) {
        $elmType = $this->getElementType($elmName);

        if ($elmType == Object::TYPE_BOOL) {
            return array(
                'Macro'          => '%' . $elmName . '%',
                'TranslationMap' => array(
                    false => A_NO,
                    true  => A_YES
                ),
                'Sortable' => true
            );
        }
        if ($elmType == Object::TYPE_CONST) {
            $method = sprintf('get%sConstArray', $elmName);
            $arr = call_user_func(array($this->clsname, $method));
            return array(
                'Macro'          => '%' . $elmName . '%',
                'TranslationMap' => $arr,
                'Sortable' => false
            );
        }
        if ($elmType == Object::TYPE_FKEY) {
            return array(
                'Macro'          => '%' . $elmName . '%',
                'TranslationMap' => array(0 => 'N/A'),
                'Sortable' => true
            );
        }
        if ($elmType == Object::TYPE_MANYTOMANY) {
            $clsName   = $this->links[$elmName]['linkClass'];
            $tmpobject = Object::load($clsName);
            $macro = call_user_func(
                array($tmpobject, 'getToStringAttribute'));
            if(is_array($macro)) {
                $macro = implode($macro, '% %');
            }
            return array(
                'Macro' => '%' . $macro . '%',
                'link'  => $elmName,
                'Sortable' => true
            );
        }
        $filter = '';
        if ($elmType == Object::TYPE_DATE) {
            $filter = '|formatDate@DATE_LONG';
        }
        if ($elmType == Object::TYPE_DATETIME) {
            $filter = '|formatDate@DATETIME_LONG';
        }
        if ($elmType == Object::TYPE_FLOAT || $elmType == Object::TYPE_DECIMAL) {
            $dec_num = isset($this->mapping[$elmName]['dec_num']) ?
                $this->mapping[$elmName]['dec_num'] : 2;
            $filter = '|formatNumber@' . $dec_num;
        }
        return array('Macro' => '%' . $elmName . $filter . '%', 'Sortable' => true);
    }

    // }}}
    // GenericGrid::_getColumnType() {{{

    /**
     * _getColumnType.
     *
     * Retourne le type de colonne pour la propriété $elmName.
     *
     * @param string $elmName Nom de la propriété.
     * @access private
     * @return void
     */
    private function _getColumnType($elmName) {
        $elmType = $this->getElementType($elmName);

        if(in_array($elmType, array(Object::TYPE_BOOL, Object::TYPE_CONST, Object::TYPE_FKEY))) {
            return 'FieldMapperWithTranslation';
        }
        if($elmType == Object::TYPE_MANYTOMANY) {
            return MANYTOMANY_GRIDCOLUMN;
        }
        return 'FieldMapper';
    }

    // }}}
    // GenericGrid::_getSearchFormType() {{{

    /**
     * _getSearchFormType
     *
     * Retourne le type d'élément à utiliser dans le searchForm pour la
     * propriété $elmName.
     *
     * @param string $elmName Nom de la propriété.
     * @access private
     * @return void
     */
    private function _getSearchFormType($elmName) {
        $elmType = $this->getElementType($elmName);
        if(in_array($elmType, array(Object::TYPE_DATE, Object::TYPE_DATETIME))) {
            return 'date2date';
        }
        if(in_array($elmType, array(Object::TYPE_CONST, Object::TYPE_FKEY, 
        Object::TYPE_MANYTOMANY, Object::TYPE_BOOL))) {
            return 'select';
        }
        // and the others types !?
        return 'text';
    }

    // }}}
    // GenericGrid::_getSearchFormAttributes() {{{

    /**
     * _getSearchFormAttributes
     *
     * Retourne les attributs à utiliser pour l'ajout de l'élément au
     * searchform.
     *
     * @param string $elmName Nom de la propriété
     * @access private
     * @return void
     */
    private function _getSearchFormAttributes($elmName) {
        $elmType = $this->getElementType($elmName);

        if($elmType == Object::TYPE_CONST) {
            $method = sprintf('get%sConstArray', $elmName);
            $array = call_user_func(array($this->clsname, $method));
            return array(
                array(GenericController::FAKE_INDEX => MSG_SELECT_AN_ELEMENT) + $array
            );
        }
        if($elmType == Object::TYPE_FKEY) {
            $clsName = $this->attrs[$elmName];
            $fGetter = 'getFilterFor'.$elmName;
            $filter = method_exists($this, $fGetter) ? $this->$fGetter() : array();
            return array(
                SearchTools::CreateArrayIDFromCollection($clsName, $filter,
                    MSG_SELECT_AN_ELEMENT)
            );
        }
        if($elmType == Object::TYPE_BOOL) {
            return array(
                array('##' => _('Any'), '1' => _('Yes'), '0' => _('No'))
                    );
        }
        if($elmType == Object::TYPE_MANYTOMANY) {
            $elmName = $this->links[$elmName]['linkClass'];
            return array(
                SearchTools::CreateArrayIDFromCollection($elmName, array(),
                    MSG_SELECT_MANY_ELEMENTS, 'toString', array())
            );
        }
        return array();
    }

    // }}}
    // GenericGrid::_getSearchFormOptions() {{{

    /**
     * _getSearchFormOptions
     *
     * Retourne les options à utiliser pour l'ajout de l'élément au searchForm.
     *
     * @param string $elmName Nom de la propriété
     * @access private
     * @return void
     */
    private function _getSearchFormOptions($elmName) {
        $elmType = $this->getElementType($elmName);

        if($elmType == Object::TYPE_FKEY) {
            return array(
                'Path' => $elmName
            );
        }
        if($elmType == Object::TYPE_MANYTOMANY) {
            return array(
                'Path'     => $this->links[$elmName]['linkClass'] . '().Id',
                'Operator' => 'In'
            );
        }

        return array();
    }

    // }}}
}

?>
