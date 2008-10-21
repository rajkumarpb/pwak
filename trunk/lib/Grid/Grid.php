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

class Grid extends AbstractGrid {

    /**
     * Permet d'afficher ou pas la personnalisation des grids par user.
     *
     * @var boolean $customizationEnabled
     * @access public
     */
    public $customizationEnabled = false;

    /**
     * Colonnes à masquer par défaut si aucune preference ne précise de les
     * afficher.
     *
     * @var array $hiddenColumnsByDefault
     * @access public
     */
    public $hiddenColumnsByDefault = array();

    /**
     * Colonnes à masquer pour l'utilisateur.
     *
     * @var array $hiddenColumnsByUser
     * @access public
     */
    public $hiddenColumnsByUser = array();

    public $hasInlineActions = false;

    /**
     * Constructor
     *
     * @access protected
     */
    function __construct()
    {
        parent::__construct();
        // Pour gerer plusieurs grids dans le meme script
        static $_instances = 0;
        $_instances++;
        $this->NbGridsInPage = $_instances;
    }

    /**
     *
     * @access public
     * @static
     */
    private $NbGridsInPage = 0;

    /**
     *
     * @param boolean
     * @access private
     */
    private $_withMultipleGridsInForm = false;

    /**
     * Grid::setWithMultipleGridsInForm()
     *
     * @param boolean $bool
     * @return void
     **/
    public function setWithMultipleGridsInForm($bool) {
        $this->_withMultipleGridsInForm = $bool;
    }
    /**
     *
     * @access private
     */
    private $_NbSubGridColumns = 0;

    /**
     * Grid::getNbSubGridColumns()
     *
     * @return integer
     **/
    public function getNbSubGridColumns()
    {
        return $this->_NbSubGridColumns;
    }

    /**
     * Grid::setNbSubGridColumns()
     *
     * @param $value
     * @return void
     **/
    public function setNbSubGridColumns($value)
    {
        // '+1' necessaire si contient un sous-Grid
        $this->_NbSubGridColumns = $value + 1;
    }

    /**
     * Permet d'afficher ou non la pagination
     *
     * @var boolean $value
     * @access public
     */
    public $paged = true;

    /**
     * Permet d'afficher ou pas le bouton 'Annuler les tris'
     *
     * @param boolean $value
     * @access public
     */
    public $displayCancelFilter = true;

    /**
     * Permet de ne pas afficher les checkbox
     * @var boolean
     * @access public
     */
    public $withNoCheckBox = false;

    /**
     * Tableau des élements à pré-selectionner
     *
     * @access private
     **/
    private $_PreselectedItems = array();

    /**
     * Grid::getPreselectedItems()
     *
     * @return array
     **/
    public function getPreselectedItems(){
        return $this->_PreselectedItems;
    }

    /**
     * Grid::SetPreselectedItems()
     *
     * @param $value
     * @return void
     **/
    public function setPreselectedItems($value){
        $this->_PreselectedItems = $value;
    }

    /**
     * Tableau des variables smarty qui seront assignées au moment du render
     *
     * @var array $_extraVars
     **/
    private $_extraVars = array();

    /**
     * @var string
     * @access public
     */
    public $javascriptFormOwnerName = 'document.forms[0]';

    /**
     *
     * @access private
     */
    private $_Action = array();

    /**
     *
     * @access public
     * @return void
     */
    public function NewAction($actionType, $params = array())
    {
        $className = 'GridAction' . $actionType;
        loadGridComponent('Action', $className);
        $params['JsOwnerForm'] = $this->javascriptFormOwnerName == 'document.forms[0]'?
                'document.forms[0]':
                'document.forms[\'' . $this->javascriptFormOwnerName . '\']';

        $action = new $className($params);
        $this->_Action[] = $action;
        $action->index = $this->_getActionCount() - 1;
        if($action->renderer == 'Inline') {
            $this->hasInlineActions = true;
        }
        return $action;
    }


    /**
     * AbstractGrid::NewColumnGroup()
     * Ajoute un groupe de colonnes du style:
     *
     * Example:
     * --------
     * $grid->NewColumn('FieldMapper', _('Colonne 1'), array('Macro'=>'%foo%'));
     * $col1 = $grid->NewColumn('FieldMapper', _('A'), array('Macro'=>'%A%'));
     * $col2 = $grid->NewColumn('FieldMapper', _('B'), array('Macro'=>'%B%'));
     * $col3 = $grid->NewColumn('FieldMapper', _('C'), array('Macro'=>'%C%'));
     * $grid->NewColumnGroup('Mon groupe', array($col1, $col2, $col3));
     * $grid->NewColumn('FieldMapper', _('Colonne 2'), array('Macro'=>'%bar%'));
     *
     * donnera un grid du style:
     * +------------------------------------+
     * |            |Mon groupe |           |
     * | Colonne 1  +-----------| Colonne 2 |
     * |            | A | B | C |           |
     * +------------------------------------+
     * |  foo       | 1 | 2 | 3 |  bar      |
     * +------------------------------------+
     * |  baz       | 3 | 4 | 2 |  foobar   |
     * +------------------------------------+
     *
     * @param string $groupTitle le titre du groupe
     * @param array $columns un tableau d'objets GridColumn
     * @return void
     */
    public function NewColumnGroup($groupTitle = '', $columns = array()) {
        if (count($columns) < 2) {
            trigger_error(
                'A column group have to contain at least 2 columns.',
                E_USER_ERROR
            );
        }
        // il faut supprimer les colonnes du tableau columns
        $count = count($columns);
        for($i = 0; $i < $count; $i++){
            $column = $columns[$i];
            $this->columns[$column->index]->groupCount = $count;
            $this->columns[$column->index]->groupCaption = $groupTitle;
        }
    }

    /**
     *
     * @access public
     * @return void
     **/
    public function NewSeparator(){
        return $this->NewAction('Separator');
    }


    /**
     * Grid::getAction()
     *
     * @param $index
     * @return object AbstractGridAction
     **/
    public function getAction($index)
    {
        return isset($this->_Action[$index])?$this->_Action[$index]:null;
    }

    /**
     * Grid::_getActionCount()
     *
     * @return integer
     **/
    private function _getActionCount()
    {
        return count($this->_Action);
    }

    /**
     * Grid::_getDataCollection()
     *
     * @param $entityName
     * @param $ordre
     * @param array $filtre
     * @return object Collection
     **/
    private function _getDataCollection($entityName, $ordre, $filtre = array())
    {
        $pageIndex = isset($_REQUEST['PageIndex'])?$_REQUEST['PageIndex']:0;
        if ($entityName instanceof Collection) {
            // On a déjà une collection, on l'utilise tel
            $ordre = isset($_REQUEST['order'])?
                    $this->_getOrderArray($_REQUEST['order']):$ordre;
            foreach($ordre as $key => $value) {
                $entityName->sort($key, $value);
            }
            return $entityName;
        }
        if (is_string($entityName)) {
            $entityName = Mapper::singleton($entityName);
        }
        $rows = ($this->paged)?$this->itemPerPage:0;
        $ret = $entityName->loadCollection($filtre, $ordre,
                array(), $rows, $pageIndex);
        return $ret;
    }

    /**
     * Grid::Render()
     *
     * @param mixed $aMapper:
     *     - Collection: on utilise la collection pour le rendu,
     *     - Mapper: on effectue un LoadObjectCollection pour récupérer
     *       une collection qui sera utilisée pour le rendu,
     *     - string: on crée le mapper correspondant au nom de l'objet
     *       donné (Ex: ActivatedChainTask) qui est utilisé comme en 2.
     * @param boolean $pager
     * @param array $filtre
     * @param array $ordre
     * @param string $templateName
     * @return string
     */
    public function render($aMapper, $pager=false, $filtre=array(), $ordre=array(), $templateName=GRID_TEMPLATE)
    {
        // Pas besoin de customisation dans ce cas
        if (count($this->columns) == 1) {
            $this->customizationEnabled = false;
        }
        // Prend en compte les preferences pour l'user connecte, si besoin
        $this->checkPreferences();
        if (isset($_REQUEST['export'])) {  // si export demande!
            $pager = false;
            $this->itemPerPage = 1000000;
            $_REQUEST['PageIndex'] = 0;
        }
        $smarty = new Template();
        $gridJS = '';
        // pour le comportement "sortable des lignes de grid"
        // ne marche que si 1 seul grid dans la page et grid paramétré pour
        if ($this->dndSortable && $this->dndSortableField !== NULL && $this->NbGridsInPage == 1) {
            // on désactive toute possibilité de tris
            $this->withNoSortableColumn = true;
            // l'ordre doit être forcément sur le dndSortableField SORT_ASC
            $ordre = array($this->dndSortableField=>SORT_ASC);
            // initialise ajax
            $cli = new AjaxClient();
            $smarty->assign('AJAXJavascript', $cli->initialize());
            // initialisation du comportement sortable
            $gridJS .= "connect(window, 'onload', fw.grid.sortableInit);\n";
            $smarty->assign('GridDndSortable', true);
            $smarty->assign('GridDndSortableField', $this->dndSortableField);
        }

        $ordre = isset($_REQUEST['order'])?$this->_getOrderArray($_REQUEST['order']):$ordre;
        $aCollection = $this->_getDataCollection($aMapper, $ordre, $filtre);
        $gridHeader = array();
        $gridHeaderGroups = array();
        $gridHeaderGroupItemsCount = 0;
        $tab_ordre = isset($_REQUEST['order'])?$_REQUEST['order']:array();
        $processedGroups = array();
        $ordermap = array(0=>'NONE', SORT_ASC=>'ASC', SORT_DESC=>'DESC');
        foreach($this->columns as $colIndex => $column) {
            if (!$column->enabled) {
                unset($this->columns[$colIndex]);
                continue; // On n'affiche que les column actives
            }
            if (in_array($colIndex, $this->hiddenColumnsByUser)) {
                continue; // On n'affiche que les column non cachees par le user
            }
            
            $colSortLink = ($this->withNoSortableColumn)?
                    '':$column->getSortLink($tab_ordre);
            $colSortOrder = ($this->withNoSortableColumn)?
                    false:$ordermap[$column->getSortOrder($tab_ordre)];

            $gridHeaderItem = array();
            if ($column->groupCount) {
                $gridHeaderItem['GroupCount'] = $column->groupCount;
                $gridHeaderItem['GroupCaption'] = $column->groupCaption;
                $gridHeaderGroups[] = array(
                    'Caption' => $column->title,
                    'Link' => $colSortLink,
                    'SortOrder' => $colSortOrder
                );
                if (!isset($processedGroups[$column->groupCaption])) {
                    $gridHeader[] = $gridHeaderItem;
                    $processedGroups[$column->groupCaption] = true;
                }
                $gridHeaderGroupItemsCount++;
            } else {
                $gridHeaderItem['Caption'] = $column->title;
                $gridHeaderItem['Link'] = $colSortLink;
                $gridHeaderItem['SortOrder'] = $colSortOrder;
                // Pour l'alignement a droite ou a gauche
                switch ($column->datatype) {
                        case Object::TYPE_INT:
                        case Object::TYPE_FLOAT:
                        case Object::TYPE_DECIMAL:
                        case 'numeric':
                            $gridHeaderItem['DataType'] = 'numeric';
                    		break;
                    	default: // alphanumeric
                            $gridHeaderItem['DataType'] = 'alphanumeric';

                    }
                $gridHeader[] = $gridHeaderItem;
            }
        }
        if($this->hasInlineActions) {
            $gridHeader[] = array('Caption' => '&nbsp;'/*_('actions')*/);
        }
        $gridObjectIds = array();
        $highlightedRows = array();  // les lignes qui seront affichees en vert
        $gridObjectIdsChecked = array();
        $gridContent = array();
        $gridItemsChecked = $this->_getItemIds();
        $rowActions = array();
        $count = $aCollection->getCount();
        for($i = 0; $i < $count; $i++) {
            $gridContent[$i] = array();
            $objectInstance = $aCollection->getItem($i);
            $instanceID = $objectInstance->_Id;
            if ($instanceID) {
                $gridObjectIds[$i] = $instanceID;
                $gridObjectIdsChecked[$i] = in_array($instanceID, $gridItemsChecked)?'checked="checked"':'';
            } else {
                $gridObjectIds[$i] = $i;
            }
            // highlighted rows
            $highlightedRows[$i] = 0;
            $cond = $this->highlightCondition;
            if (!empty($cond)) {
                $objValue = Tools::getValueFromMacro($objectInstance, $cond['Macro']);
                $cond['Operator'] = ($cond['Operator'] == '=')?'==':$cond['Operator'];
                $instr  = '$isHighlighted = ($objValue '
                        . $cond['Operator'] . ' "' . $cond['Value'] . '");';
                eval($instr);

                if ($isHighlighted == true) {
                    $highlightedRows[$i] = 1;
                }
            }

            foreach($this->columns as $column) {
                if (!$column->enabled) {
                    continue;  // On n'affiche que les column actives
                }
                if (in_array($column->index, $this->hiddenColumnsByUser)) {
                    continue; // On n'affiche que les column non cachees par le user
                }
                $cellContent = $column->render($objectInstance);
                $gridContent[$i][] = get_class($cellContent)=='exception'?'N/A':$cellContent;
            }
            if($this->hasInlineActions) {
                $renderer = new GridActionRendererInline($this->javascriptFormOwnerName, $gridObjectIds[$i]);
                $rowActions[$i] = $renderer->render($this->_Action);
            }
        }

        // Si EXPORT demande
        if (isset($_REQUEST['export'])) {
            // On memorise le gridcontent
            $this->gridContent = $gridContent;
            $GridExport = $this->_CSVExport();
            exit;
        }

        $rendererCls = 'GridActionRenderer';
        $actionRenderer = new $rendererCls($this->javascriptFormOwnerName);
        $actions = $actionRenderer->render($this->_Action);

        $smarty->assign('RowActions', $rowActions);

        $smarty->assign('GridEntityName', $aCollection->entityName);
        $smarty->assign('SortDescImage', parent::SORT_DESC_IMAGE);
        $smarty->assign('SortAscImage', parent::SORT_ASC_IMAGE);
        $smarty->assign('CancelFilterImage', parent::CANCEL_FILTER_IMAGE);
        $smarty->assign('CustomDisplayImage', parent::CUSTOM_DISPLAY_IMAGE);
        $smarty->assign('CustomDisplayBarImage', parent::CUSTOM_DISPLAY_BAR_IMAGE);
        // Assignation des eventuelles variables supplementaires
        if (is_array($this->_extraVars)) {
            foreach($this->_extraVars as $key => $val) {
                $smarty->assign($key, $val);
            }
        }

        $items = isset($_REQUEST['items'])?$_REQUEST['items']:array();
        $smarty->assign ('items', $items);
        $smarty->assign('Pager', $pager);
        // pour le rowspan
        $gridHeaderGroupsCount = count($gridHeaderGroups);
        if ($gridHeaderGroupsCount > 0) {
            $smarty->assign('RowSpan', 2);
        }
        $smarty->assign('GridHeader', $gridHeader);
        $smarty->assign('GridHeaderCount', count($gridHeader));
        $smarty->assign('GridHeaderGroupItemsCount', $gridHeaderGroupItemsCount);
        $smarty->assign('GridHeaderGroupsCount', $gridHeaderGroupsCount);
        $smarty->assign('GridHeaderGroups', $gridHeaderGroups);
        $CurrentPage = ($aCollection->currentPage == 0)?1: abs($aCollection->currentPage);
        $smarty->assign('CurrentPageIndex', $CurrentPage);
        $PageTotal = ($aCollection->lastPageNo == 0)?1: abs($aCollection->lastPageNo);
        $smarty->assign('PageTotal', $PageTotal);
        $TotalRowCount = (!$pager)?$count:$aCollection->totalCount;
        $smarty->assign('GridTotalRowCount', $TotalRowCount);
        $smarty->assign('GridRow', $gridContent);
        $smarty->assign('GridHighlightedRows', $highlightedRows); // highlighted rows
        $smarty->assign('GridObjectIds', $gridObjectIds);
        $smarty->assign('GridObjectIdsChecked', $gridObjectIdsChecked);
        $smarty->assign('Actions', $actions);
        // + 1 pour les colspan...
        $smarty->assign('NbSubColumn',
                $this->_NbSubGridColumns==0?0:$this->_NbSubGridColumns + 1);
        $smarty->assign('DisplayCancelFilter', $this->displayCancelFilter);
        $smarty->assign('WithCheckBox', !$this->withNoCheckBox);
        $smarty->assign('firstGridInPage', $this->NbGridsInPage);
        $smarty->assign('withMultipleGridsInForm', $this->_withMultipleGridsInForm);
        $smarty->assign('customizationEnabled', $this->customizationEnabled);

        if ($pager != false) {
            $smarty->assign('paging', $this->pagingRender($aCollection->currentPage, $aCollection->lastPageNo));
            $smarty->assign ('dropfiltertarget', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
        }

        // Gestion du js pour les tris multiples, si 1 seul grid
        if ($this->NbGridsInPage == 1) {
            $gridJS .= "// Objet contenant les ordre précédemment sélectionnés pour le grid courant\n";
            $gridJS .= "function gridSortOrderItem(filterOrder, columnIndex, sortOrder){\n";
            $gridJS .= "	this.filterOrder = filterOrder;\n";
            $gridJS .= "	this.columnIndex = columnIndex;\n";
            $gridJS .= "	this.sortOrder = sortOrder;\n";
            $gridJS .= "}\n";
            $gridJS .= "// Rempli lors de désélection d'un item du grid pour \n";
            $gridJS .= "// le supprimer en session aussi\n";
            $gridJS .= "var toRemove = new Array();\n";
            $gridJS .= "var gridSortOrderList = new Array();\n";
            if (isset($_REQUEST['order']) && is_array($_REQUEST['order'])){
            	foreach($_REQUEST['order'] as $filterOrder => $filterData){
            		$column = key($filterData);
            		$sortOrder = current($filterData);
            		$gridJS .= "gridSortOrderList[" . $filterOrder .
                        "] = new gridSortOrderItem(" . (int)$filterOrder .
                        ", " . (int)$column . ", " . (int)$sortOrder . ");\n";
            	}
            }
            // Pour gestion des tris lors de la pagination
            $gridJS .= "connect(window, 'onload', fw.grid.populateGridSortLayer);";
        }

        $smarty->assign('gridJS', $gridJS);

        $divAddon = ($this->NbGridsInPage > 1)?'':'<div id="GridSortAddon"></div>';

        // Gestion du customize grid: il est possible que precedemment dans
        // render(), on ait supprime 1 ou n colonnes (enabled=false)
        if ($this->customizationEnabled && count($this->columns) > 1) {
            $this->_customDisplay($smarty);
        }
        return $smarty->fetch($templateName) . $divAddon;
    }

    /**
     * Grid::imagename_NO()
     * retourne le nom de l'image "off" à partir d'un nom d'image "On"
     *
     * @param $name
     * @static
     * @return string
     **/
    public function imagename_NO($name)
    {
        $tokens = explode(".", $name);
        return ($tokens[0] . "_no." . $tokens[1]);
    }

    /**
     * Grid::isPendingAction()
     *
     * @access public
     * @return boolean
     **/
    public function isPendingAction()
    {
        return isset($_REQUEST['actionId']) && ($_REQUEST['actionId'] >= 0)
            && ($_REQUEST['actionId'] < $this->_getActionCount());
    }

    /**
     * Grid::DispatchAction()
     *
     * @param $aCollection
     * @return mixed
     */
    public function dispatchAction($aCollection)
    {
        $mapper = $this->getMapper();
        $objects = array();
        $triggeredAction = $this->getAction($_REQUEST['actionId']);
        $itemsIds = $this->_getItemIds();
        foreach($itemsIds as $objectId) {
            unset($selectedObject);
            if ($aCollection instanceof Collection) {
                $selectedObject = $aCollection->getItemById($objectId);
            }else {
                $selectedObject = $mapper->load(array('Id' => $objectId));
            }
            if (is_object($selectedObject)) {
                $objects[] = $selectedObject;
            }
        }
        return $triggeredAction->execute($objects, $itemsIds);
    }

    /**
     * Renvoie un tableau compatible avec le Framework de sérialisation d'objets
     * constitué d'un ensemble de couples clef/valeur.
     *
     * @param array $tab_ordre Tableau contenant les tri à effectuer sur les
     * enregistrements de la grille. Il s'agit d'un tableau de tableaux.
     * Le premier définit l'ordre des tri à appliquer, le second définit une
     * association Clef/Valeur où la clef est le nom du champ concerné et la
     * clef est soit SORT_ASC ou SORT_DESC.
     * @return array
     */
    private function _getOrderArray($tab_ordre)
    {
        $ordre = array();
        if (is_array($tab_ordre)) {
            $count = count($tab_ordre);
            for ($i = 0; $i < $count;$i++) {
                $tab_fields = $tab_ordre[$i];
                foreach($tab_fields as $attribut => $tri) {
                    $sortField = $this->columns[$attribut]->sortField == ''?
                            'Id':$this->columns[$attribut]->sortField;
                    $ordre[$sortField] = $tri;
                }
            }
        }
        return $ordre;
    }

    /**
     * _gridItems 
     * 
     * @var array
     * @access private
     */
    private $_gridItems = array();

    /**
     * Grid::getItemIds()
     * retourne un tableau des items checkbox checkés
     *
     * @access private
     * @return array
     **/
    private function _getItemIds()
    {
        $sessionVarName = SearchTools::getGridItemsSessionName();
        if (!isset($_SESSION[$sessionVarName])) $_SESSION[$sessionVarName] = array();
        if (!isset($_REQUEST['gridItems'])) $_REQUEST['gridItems'] = array();
        if (!isset($_REQUEST['toRemove'])) $_REQUEST['toRemove'] = '';
        $gridItems = array_unique(array_merge($_SESSION[$sessionVarName],
                $_REQUEST['gridItems']));
        $gridItems = array_diff($gridItems, explode('|', $_REQUEST['toRemove']));
        $session = Session::Singleton();
        $session->register($sessionVarName, $gridItems, 2);
        unset($_REQUEST['toRemove'], $_REQUEST['gridItems']);
        $return = array_merge($this->getPreselectedItems(), $gridItems);
        if(!empty($return)) {
            $this->_gridItems = $return;
        }
        return $this->_gridItems;
    }

    /**
     * Grid::Execute()
     *
     * @param array $filter
     * @param array $order
     * @return string
     */
    public function execute($filter = array(), $order = array())
    {
        $mapper = $this->getMapper();
        if ($this->isPendingAction()) {
            $col = $mapper->loadCollection(array('Id' => $this->_getItemIds()));
            return $this->dispatchAction($col);
        }
        return $this->render($mapper, $this->paged, $filter, $order);
    }

    /**
     * Assigne une variable au template du grid
     *
     * @access public
     * @param string $var le nom de la variable
     * @param mixed $value sa valeur
     * @return void
     **/
    public function assign($var, $value){
        $this->_extraVars[$var] = $value;
    }


    /**
     * Permet d'exporter au format csv
     * @param $mapper Entity Mapper
     * @param $filter array or Filter object
     * @access public
     * @return void
     **/
    private function _CSVExport(){  /*$mapper, $filter=array(), $order=array()*/
        ///set_time_limit(180);  // pour les gros exports ! INUTILE, car 300 par defaut sur KURO
        //Make sure that IE can download the attachments under https.
        header('Pragma: public');
        header('Content-type: application/force-download');
        header('Content-Disposition: attachment;filename=' . $_REQUEST['export'] . '.csv');
        $fp = fopen('php://stdout','wb');

        $NEWLINE = "\r\n";
        $BUFLINES = 100;
        $sep = $sepreplace = ';';
        $s = '';
        $line = 0;
        $columnNameArray = array();
        $realColumnNumber = 0; // Tient compte des colonnes cachees par le user
        foreach($this->columns as $column) {
            if (in_array($column->index, $this->hiddenColumnsByUser)) {
                continue; // On n'affiche que les column non cachees par le user
            }
            $columnNameArray[] = $column->title;
            $realColumnNumber++;
        }

        $DataArray = $this->gridContent;
        $DataArray[-1] = $columnNameArray;  // rajout des entetes de colonne

        for ($i=-1;$i < count($DataArray)-1;$i++)    {
            $elements = array();
            for ($j=0; $j < $realColumnNumber; $j++) {
                if (strpos($DataArray[$i][$j],"<td>") !== false) {          // Si sous-grid
                    $SubGridDataArray = explode("</td>\n<td>", $DataArray[$i][$j]);
                    $array = array_slice($SubGridDataArray, 1, count($SubGridDataArray) - 2);  // extrait les donnees
                    if (count($array) > 0) {
                        for ($k=0;$k<count($array);$k++) {
                            $array[$k] = (strpos($array[$k],"\n\t\t\t") !== false)?substr(str_replace("\n\t\t\t", ", ", $array[$k]), 2):$array[$k];
                            $elements[] = self::formatDataForExport($array[$k]);
                        }
                    }
                }
                else {
                    $elements[] = self::formatDataForExport($DataArray[$i][$j]);
                }
            }
            $s .= implode($sep, $elements).$NEWLINE;
            $line += 1;
            if ($line % $BUFLINES == 0) {
                echo $s;
                $s = '';
            }
        }
        echo $s;
        $s = '';
        fclose($fp);
        return $s;
    }

    /**
     *
     * @access public
     * @param string
     * @return string
     **/
    public static function formatDataForExport($data){
        $quote = '"';
        $escquote = '"';
        $replaceNewLine = ' ';
        $escquotequote = $escquote.$quote;
        $sep = $sepreplace = ';';

        $v = str_replace('<br>', ' ', $data);
        $v = str_replace('<br />', ' ', $v);
        $v = trim(html_entity_decode(strip_tags($v)));
        $v = str_replace('&euro;', '€', $v);
        if ($escquote) $v = str_replace($quote, $escquotequote,$v);
        $v = strip_tags(str_replace("\n", $replaceNewLine,str_replace($sep,$sepreplace,$v)));

        if (strpos($v, $sep) !== false || strpos($v,$quote) !== false) return "$quote$v$quote";
        else return $v;
    }

    /**
     * Retourne le code html pour la pagination des grids.
     *
     * Affiche les numéros des pages depuis la page courante moins le delta de
     * la pagination jusque la page courante plus le delta de la pagination
     * encadrés des liens précédents et suivants.
     *
     * Si useImage vaut true affiche les images "premiére page", "page
     * précedente", "page suivante" et "dernière page" à la place de la
     * numérotation
     *
     * @param int $pageIndex numéro de la page courante
     * @param int $pageTotal nombre de pages du grid
     * @param int $pagingDelta delta de la pagination
     * @param bool $useImage affiche des images à la place des liens texte
     * @access public
     * @return string
     */
    public function pagingRender($pageIndex=1, $pageTotal=1, $pagingDelta=5, $useImage=false) {
        $formname = $this->javascriptFormOwnerName;
        $formname = ($formname == 'document.forms[0]')?$formname:'document.forms[\'' . $formname . '\']';

        $first = $pageIndex - $pagingDelta;
        $first = ($first < 1) ? 1 : $first;
        $last = $pageIndex + $pagingDelta;
        $last = ($last > $pageTotal) ? $pageTotal : $last;

        $firstPageTitle = _('First page');
        $previousPageTitle = _('Previous page');
        $nextPageTitle = _('Next page');
        $lastPageTitle = _('Last page');

        $linkTpl = '<a href="javascript:void(0);" onclick="fw.grid.jumpToPage({PAGE}, {FORMNAME}); return false;" onmouseover="window.status=\'{TITLE}\'; return true;" title="{TITLE}">{LINK}</a>&nbsp;';
        $html = '';

        // liens "première page" et "précédent"
        if($pageIndex > 1) {
            $firstPageLink = '<<';
            $previousLink = '<';
            if($useImage) {
                $firstPageLink = '<img src="' . parent::FIRST_PAGE_IMAGE . '" alt="' . $firstPageTitle . '" />';
                $previousLink = '<img src="' . parent::PREVIOUS_PAGE_IMAGE . '" alt="' . $previousPageTitle . '" />';
            }
            // première page
            $html .= str_replace(
                array('{PAGE}', '{FORMNAME}', '{TITLE}', '{LINK}'),
                array(1, $formname, $firstPageTitle, $firstPageLink),
                $linkTpl);
            // précédent
            $previous = $pageIndex - 1;
            $html .= str_replace(
                array('{PAGE}', '{FORMNAME}', '{TITLE}', '{LINK}'),
                array($previous, $formname, $previousPageTitle, $previousLink),
                $linkTpl);
        }

        // pagination
        if(!$useImage) {
            for($i=$first ; $i<=$last ; $i++) {
                if($i == $pageIndex) {
                    $html .= $i. '&nbsp;';
                    continue;
                }
                $html .= str_replace(
                    array('{PAGE}', '{FORMNAME}', '{TITLE}', '{LINK}'),
                    array($i, $formname, _('page') . ' ' . $i, $i),
                    $linkTpl);
            }
        }

        // liens "suivant" et "dernière page"
        if($pageIndex < $pageTotal) {
            $nextLink = '>';
            $lastPageLink = '>>';
            if($useImage) {
                $nextLink = '<img src="' . parent::NEXT_PAGE_IMAGE . '" alt="' . $nextPageTitle . '" />';
                $lastPageLink = '<img src="' . parent::LAST_PAGE_IMAGE . '" alt="' . $lastPageTitle . '" />';
            }
            // suivant
            $next = $pageIndex + 1;
            $html .= str_replace(
                array('{PAGE}', '{FORMNAME}', '{TITLE}', '{LINK}'),
                array($next, $formname, $nextPageTitle, $nextLink),
                $linkTpl);
            // derniére page
            $html .= str_replace(
                array('{PAGE}', '{FORMNAME}', '{TITLE}', '{LINK}'),
                array($pageTotal, $formname, $lastPageTitle, $lastPageLink),
                $linkTpl);
        }

        return $html;
    }

    /**
     * Remplit le layer de customisation des grids.
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

        $form = new HTML_QuickForm('gridCustomDisplay', 'post', $_SERVER['PHP_SELF']);
        $form->removeAttribute('name');        // XHTML compliance
        $defaultValues = array();  // Valeurs par defaut des champs du form
        $form->updateAttributes(array('onsubmit' => "return checkBeforeSubmit();"));
        //$form->updateAttributes(array('onsubmit' => '$(\'customDisplayUpdated\').value=1;'));

        $form->addElement('hidden', 'customDisplayUpdated', '0');


        // Les plus souvent utilises
        $nbItemsArray = array(30 => 30, 50 => 50, 100 => 100, 150 => 150,
                200 => 200, 300 => 300, 500 => 500);
        if ($this->paged) {
            $defaultItemPerPage = $this->itemPerPage;
            if (!in_array($defaultItemPerPage, $nbItemsArray)) {
                $nbItemsArray[$defaultItemPerPage] = $defaultItemPerPage;
                sort($nbItemsArray);
            }
            $form->addElement('select', 'customDisplayItemPerPage',
			    _('Items number per page'), $nbItemsArray);
			    // ToDo: MODIFIER en fction des saisies!!
			$defaultValues['customDisplayItemPerPage'] = $defaultItemPerPage;
        }

        $cols = array();
         foreach($this->columns as $colIndex => $column) {
            // Groupement de colonnes: toDo?
            // $column->groupCaption . ' ' . $column->title;
            if (!$column->enabled || $column->groupCount) {
                // On n'affiche que les column actives et
                continue;
            }
            // Pour la gestion des sous-grids, on nettoie l'entete
            $cols[$colIndex] = (stripos($column->title, '</td><td>') !== false)?
                    substr(str_replace('</td><td>', ', ', $column->title), 2, -2):
                    $column->title;
        }
        $labels = array(_('Columns') . ':', _('Available columns'),
                _('Columns to hide'));
        $elt = HTML_QuickForm::createElement(
            'advmultiselect', 'customDisplayHiddenColumns', $labels,
            $cols, array('style' => 'width:100%;'));
        // Necessaire pour externaliser la tonne de js, si include js trouve
        $js = (file_exists('JS_AdvMultiSelect.php'))?'':'{javascript}';
        $jsValidation = '<script type="text/javascript">
                //<![CDATA[
            function checkBeforeSubmit() {
                if ($(\'__customDisplayHiddenColumns\').options.length == 0) {
                    alert("' . _("You can't hide all columns.") . '");
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

        $form->addElement('submit', 'customDisplaySubmit', A_VALIDATE,
                'onclick="this.form.customDisplayUpdated.value=1;"');
        /*$form->addElement('button', 'customDisplaySetDefault', _('Default values'),
		      'onclick="fw.dom.selectOptionByValue($(\'customDisplayColumns\'), '
            . JsTools::JSArray($nbItemsArray) . ');this.form.submit();"');
            $(\'customDisplayUpdated\').value=1*/
        $form->addElement('button', 'customDisplayCancel', A_CANCEL,
		      'onclick="fw.dom.toggleElement($(\'custom_display_layer\'));"');

        $defaultValues['customDisplayHiddenColumns'] = $this->hiddenColumnsByUser;
        $form->setDefaults($defaultValues);
        // PATCH car advmultiselect buggé!!
        $elt->_values = $defaultValues['customDisplayHiddenColumns'];
        // end PATCH
        $form->accept($renderer); // affecte au form le renderer personnalise
        $tpl->assign('form', $renderer->toArray());
    }

    /**
     * Verifie et prend en compte les Preferences d'affichage s'il y en a.
     *
     * @param int $pageIndex numéro de la page courante
     * @param int $pageTotal nombre de pages du grid
     * @param int $pagingDelta delta de la pagination
     * @param bool $useImage affiche des images à la place des liens texte
     * @access public
     * @return void
     */
    public function checkPreferences() {
        if (!$this->customizationEnabled) {
            return;
        }
        $gridName = $this->getName();
        // test si des preferences viennent d'etre mises a jour
        $pref = array();
        if (!empty($_REQUEST['customDisplayUpdated'])) {
            $pref['itemPerPage'] = $_REQUEST['customDisplayItemPerPage'];
            $pref['hiddenColumns'] = (isset($_REQUEST['customDisplayHiddenColumns']))?
                    $_REQUEST['customDisplayHiddenColumns']:null;
        }
        if (!empty($pref)) {
            PreferencesByUser::set($gridName, $pref);
            PreferencesByUser::save();
        }

        $pref = PreferencesByUser::get($gridName);
        if ($pref != null) {
            if (isset($pref['itemPerPage'])) {
                $this->itemPerPage = $pref['itemPerPage'];
            }
            if (isset($pref['hiddenColumns'])) {
                $this->hiddenColumnsByUser = $pref['hiddenColumns'];
            }
        } else {
            // colonnes masquées par défaut
            $this->hiddenColumnsByUser = $this->hiddenColumnsByDefault;
        }
    }


    /**
     * Retourne le nom du grid. Sert d'identifiant pour le stockage des preferences
     *
     * @access public
     * @param string $path optionnel (si vide c'est le SCRIPT_NAME)
     * @return string
     */
    function getName($path = false) {
        if (!$path) {
            $path = basename($_SERVER['SCRIPT_NAME']);
        }
        $slashpos = strrpos($path, '/');
        $slashpos = $slashpos !== false?$slashpos+1:0;
        $dotpos = strrpos($path, '.');
        $dotpos = $dotpos !== false?$dotpos:strlen($path);
        $entity = substr($path, $slashpos, $dotpos-$slashpos);
        $entity = ($entity == 'dispatcher')?$_REQUEST['entity']:$entity;
        return $entity;
    }

}

?>
