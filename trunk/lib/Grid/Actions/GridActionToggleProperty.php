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

class GridActionToggleProperty extends AbstractGridAction {
    /**
     * GridActionToggleProperty::__construct()
     *
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {
        $this->useButton = true;  // par defaut ici
        $this->allowEmptySelection = false;
        parent::__construct($params);
        if (!isset($params['Property'])) {
            trigger_error(
                'Property est requis pour une action ToggleProperty',
                E_USER_ERROR
            );
        }
        $this->property = $params['Property'];
        if (isset($params['ToggleOnLabel'])) {
            $this->toggleOnLabel = $params['ToggleOnLabel'];
        } else {
            $this->toggleOnLabel = A_ACTIVATE;
        }
        if (isset($params['ToggleOnGlyph'])) {
            $this->toggleOnGlyph = $params['ToggleOnGlyph'];
        }
        if (isset($params['ToggleOnValue'])) {
            $this->toggleOnValue = $params['ToggleOnValue'];
        }
        if (isset($params['ToggleOffLabel'])) {
            $this->toggleOffLabel = $params['ToggleOffLabel'];
        } else {
            $this->toggleOffLabel = A_DISABLE;
        }
        if (isset($params['ToggleOffGlyph'])) {
            $this->toggleOffGlyph = $params['ToggleOffGlyph'];
        }
        if (isset($params['ToggleOffValue'])) {
            $this->toggleOffValue = $params['ToggleOffValue'];
        }
    }

    // propriétés
    public $property = false;
    public $toggleOnLabel  = '';
    public $toggleOnGlyph  = 'images/toggle_on.gif';
    public $toggleOnValue  = true;
    public $toggleOffLabel = '';
    public $toggleOffGlyph = 'images/toggle_off.gif';
    public $toggleOffValue = false;

    /**
     * GridActionToggleProperty::render()
     * Retourne le code html des 2 actions.
     *
     * @return string
     */
    public function render() {
        if (!$this->enabled) {
            return;
        }

        $actionOnOnClick = ' onclick="'
                . $this->jsOwnerForm
                . '.elements[\'Grid_ToggleAction\'].value=1;'
                . 'return fw.grid.triggerAction('
                . $this->jsOwnerForm . ', '
                . $this->index . ');"';
        $actionOffOnClick = ' onclick="'
                . $this->jsOwnerForm
                . '.elements[\'Grid_ToggleAction\'].value=0;'
                . 'return fw.grid.triggerAction('
                . $this->jsOwnerForm . ', '
                . $this->index . ');"';

        /*
         * XXX le hidden Grid_ToogleAcion à été déplacé dans le template 
         * Grid.html, lorsque le grid à plus de 20 enregistrement et que les 
         * actions sont en haut et en bas du grid, cela évite d'avoir 2 fois le
         * champ
         */
        //$result = '<input type="hidden" value="0" name="Grid_ToggleAction" id="Grid_ToggleActionId"/>';
        $result = '';
        if($this->useButton) {
            // action on
            $result .= '<input type="button" value="' . $this->toggleOnLabel . '"'
                . $actionOnOnClick
                . ' class="button" name="button_' . $this->index . '_0" />&nbsp;';
            $result .= "\n";
            // action off
            $result .= '<input type="button" value="' . $this->toggleOffLabel . '"'
                . $actionOffOnClick
                . ' class="button" name="button_' . $this->index . '_1" />&nbsp;';
        } else {
            // action on
            $result .= '<a href="javascript:void(0);" ' . $actionOnOnClick
                . ' onmouseover="window.status='
                . JsTools::JSQuoteString($this->toggleOnLabel)
                . '; return true;" onmouseout="window.status=\'\';return true;" '
                . 'title="' . $this->toggleOnLabel . '">'
                . '<img src="' . $this->toggleOnGlyph . '" alt="'
                . $this->toggleOnLabel . '" /></a>&nbsp;';
            $result .= "\n";
            // action off
            $result .= '<a href="javascript:void(0);" ' . $actionOffOnClick
                . ' onmouseover="window.status='
                . JsTools::JSQuoteString($this->toggleOffLabel)
                . '; return true;" onmouseout="window.status=\'\';return true;" '
                . 'title="' . $this->toggleOffLabel . '">'
                . '<img src="' . $this->toggleOffGlyph . '" alt="'
                . $this->toggleOffLabel . '" /></a>&nbsp;';
        }
        return $result;
    }

    /**
     * GridActionToggleProperty::execute()
     * Execute les actions togglePropertyOn/Off.
     *
     * @param array $objects
     * @return void
     */
    public function execute($objects, $itemIds=array()) {
        $result = parent::execute($objects, $itemIds);
        if (Tools::isException($result)) {
            return $result;
        }
        $count = count($objects);
        $method = 'set' . $this->property;
        for ($i=0; $i<$count; $i++) {
            $object = $objects[$i];
            // ne pas planter si le setter n'existe pas
            if (method_exists($object, $method)) {
                if (isset($_POST['Grid_ToggleAction'])) {
                    if ($_POST['Grid_ToggleAction']=='1') {
                        $object->$method($this->toggleOnValue);
                    } else {
                        $object->$method($this->toggleOffValue);
                    }
                    $object->save();
                }
            }
        }
        // redirection pour rappeler le render() du grid
        Tools::redirectTo(!empty($this->returnURL) ? 
            $this->returnURL : $_SERVER['REQUEST_URI']);
    }

    public function buttonRenderer() {
        return $this->render();
    }
}

?>
