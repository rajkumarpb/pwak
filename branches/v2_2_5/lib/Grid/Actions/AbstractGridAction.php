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

class AbstractGridAction {
    /**
     * AbstractGridAction::__construct()
     *
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {

        if (isset($params['Caption'])) {
            $this->caption = $params['Caption'];
        }
        // Contenu d'une infobulle
        $this->title = isset($params['Title'])?$params['Title']:$this->caption;

        if (isset($params['Enabled'])) {
            $this->enabled = $params['Enabled'];
        }
        if (isset($params['Profiles'])) {
            $this->profiles = $params['Profiles'];
        }
        $this->enabled = ($this->enabled && $this->hasProfilesToExecute());

        if (isset($params['Renderer'])) {
            $this->renderer = $params['Renderer'];
        }
        if (isset($params['GlyphDisabled'])) {
            $this->glyphDisabled = $params['GlyphDisabled'];
        }
        if (isset($params['GlyphEnabled'])) {
            $this->glyphEnabled = $params['GlyphEnabled'];
        }
        $glyph = $this->enabled?$this->glyphEnabled:$this->glyphDisabled;
        // Si pas de fichier image trouve, 'button' par defaut
        if ($this->renderer == 'image') {
            if (empty($glyph) || !file_exists($glyph)) {
                $this->renderer = 'button';
            }
            else {
                $this->glyph = $glyph;
                $this->status = ' onmouseover="window.status='
                        . JsTools::JSQuoteString($this->title) . '; return true;"'
                        .' onmouseout="window.status=\'\';return true;"';
            }
        }
        // pour definir si l'action declenche l'ouverture d'un popup
        if (isset($params['TargetPopup'])) {
            $this->targetPopup = $params['TargetPopup'];
        }
        // pour definir si le grid est ds un popup ou non
        if (isset($params['GridInPopup'])) {
            $this->gridInPopup = $params['GridInPopup'];
        }
        // pour definir si une url de retour est donnee ou non
        if (isset($params['ReturnURL'])) {
            $this->returnURL = $params['ReturnURL'];
        }
        if (isset($params['AllowEmptySelection'])) {
            $this->allowEmptySelection = $params['AllowEmptySelection'];
        }
        if (isset($params['WithJSConfirm'])) {
            $this->withJSConfirm = $params['WithJSConfirm'];
        }
        if (isset($params['ConfirmMessage'])) {
            $this->confirmMessage = $params['ConfirmMessage'];
        }
        if (isset($params['JsOwnerForm'])) {
            $this->jsOwnerForm = $params['JsOwnerForm'];
        }
    }

    /**
     *
     * @access public
     **/
    public $index = -1;
     // L'action declenche l'ouverture d'un popup. Accede dans Searchform::displayResult()
    public $targetPopup = false;
    public $caption = '';
    public $title = '';
    public $renderer = 'Button';
    public $glyphDisabled = '';
    public $glyphEnabled = '';
    public $glyph = '';
    public $enabled = true;
    
    /**
     *
     * @access protected
     */
    protected $profiles = array();
    protected $returnURL = '';
    protected $transmitedArrayName = '';
    // Relatif a window.status sur le onMouseOver...
    protected $status = '';
    protected $withTriggerAction = true;
    // tableau d'instructions a executer dans le onclick
    protected $jsActionArray = array();
    // Possibilite de ne pas selectionner d'item
    protected $allowEmptySelection = true;
    // Si un confirm() js avant tout sur le onclick
    protected $withJSConfirm = false;
    // Message dans le confirm() js, oubien dans la demande de confirm de GADelete
    protected $confirmMessage = I_CONFIRM_ACTION;
    // Le grid se trouve dans un popup si true
    public $gridInPopup = false;
    // Par defaut, oubien 'document.forms["'.$grid->javascriptFormOwnerName.'"]'
    protected $jsOwnerForm = 'document.forms[0]';


    /**
     * AbstractGridAction::render()
     *
     * @return string
     */
    public function render() {
        $title = empty($this->title)?$this->caption:$this->title;
        $result = '';
        switch($this->renderer){
            case 'Image':
                $result = '<img src="' . $this->glyph . '" alt="' . $this->caption
                        . '" />';
                if ($this->enabled) {
                    $result = '<a href="javascript:void(0);"' . $this->buildOnClick()
                            . $this->status
                            . ' title="' . $this->caption . '">' . $result . '</a>';
                }
                break;
            case 'Select':
            case 'SelectForSingleSelection':
                // ToDo...
                break;
            default: // button
                if ($this->enabled) {
                    $result = '<input type="button" value="' . $this->caption . '"';
                    $result .= ' title="' . $this->title . '"';
                    $result .= $this->buildOnClick();
                    $result .= ' class="button" name="button_' . $this->index . '"/>';
                }
                // else: on n'affiche pas le bouton
        } // switch

        return $result;
    }

    /**
     * AbstractGridAction::buildOnClick()
     * Construit la partie onclick="..."
     *
     * @return string
     */
    public function buildOnClick() {
        $onclick = '';

        // Si popup
        if ($this->targetPopup) {
            $onclick .= 'window.open(\'empty.html\',\'popup\','
                    . '\'width=800,height=600,scrollbars=yes\');';
        }
        // Si des instructions js a executer
        if (!empty($this->jsActionArray)) {
            $onclick .= implode(';', $this->jsActionArray) . ';';
        }
        // Si triggerAction necessaire
        if ($this->withTriggerAction) {
            $triggerAction = $this->targetPopup?'triggerPopupAction':'triggerAction';
            $onclick .= 'return fw.grid.' . $triggerAction . '('
                . $this->jsOwnerForm . ', ' . $this->index . ');';
        }

        // Si demande de confirm js
        if ($this->withJSConfirm) {
            $onclick =  sprintf('if (confirm(\'%s\')) { %s }',
                    $this->confirmMessage, $onclick);
        }

        return ($onclick != '')?' onclick="' . $onclick . '" ':'';
    }

    /**
     * AbstractGridAction::execute()
     *
     * @param  $objects
     * @return void
     */
    public function execute($objects, $itemsIds=array()) {
        // Retourne une Exception si pas d'item selectionne alors qu'il faut
        if ((!$this->allowEmptySelection) && empty($objects)) {
            return new Exception(I_NEED_SELECT_ITEM);
        }
        return true;
    }

    /**
     * AbstractGridAction::hasProfilesToExecute()
     * check Profiles for executing action
     *
     * @return boolean
     */
    public function hasProfilesToExecute() {
        if (!empty($this->profiles)) {
            $Auth = Auth::Singleton();
            return $Auth->checkProfiles($this->profiles, array('showErrorDialog' => false));
        }
        return true;
    }
}

?>
