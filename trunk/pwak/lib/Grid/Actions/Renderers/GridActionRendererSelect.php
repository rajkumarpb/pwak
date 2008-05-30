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
 * @version   SVN: $Id: GridActionRendererSelect.php,v 1.6 2008-05-30 09:23:48 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GridActionRendererSelect extends GridActionRenderer {
    // properties {{{

    /**
     * Message avant le select
     * 
     * @var string
     * @access public
     */
    public $message = '';

    /**
     * Template html du select. 
     * 
     * @var string
     * @access public
     */
    public $selectTpl = '<select id="buttonRendererSelectId" name="buttonRendererSelect">{OPTIONS}</select>';
    
    /**
     * Template html des options
     * 
     * @var string
     * @access public
     */
    public $optionTpl = '<option value="{VALUE}">{NAME}</option>';
    
    /**
     * Template html du boiton de validation de l'action sélectionnée.
     * 
     * @var string
     * @access public
     */
    public $buttonTpl = '<input type="button" name="button" value="ok" onclick="{ONCLICK}" />';
    
    /**
     * Template html de l'action à éxecuter.
     * 
     * @var string
     * @access public
     */
    public $onclickTpl = 'return fw.grid.triggerAction({FORM}, {INDEX});';

    // }}}
    // GridActionRendererSelect::__construct() {{{

    /**
     * __construct 
     * 
     * @param string $form nom du formulaire
     * @access public
     * @return void
     */
    public function __construct($form) {
        parent::__construct($form);
        $this->type = 'Select';
        $this->message = _('For selected items: ');
    }

    // }}}
    // GridActionRendererSelect::render() {{{

    /**
     * Crée les select des actions, si une action à sa propriété $Renderer qui à 
     * une valeur différente de 'Select', l'action n'est pas ajouté au select. 
     * La méthode render() de cette action est appelée et elle est ajouté avant 
     * le select.
     * 
     * @param array $actions tableau contenant les actions
     * @access public
     * @return void
     */
    public function render($actions, $onlyOptions=false) {
        $actions = (!is_array($actions))?array($actions):$actions;
        $return = array();
        $options = '';
        foreach($actions as $anAction) {
            $html = $this->checkRenderer($anAction);
            if($html != false) {
                $options .= $html;
                continue;
            }
            if ($anAction->enabled) {
                $options .= str_replace(
                    array('{VALUE}', '{NAME}'),
                    array($anAction->index, $anAction->caption),
                    $this->optionTpl
                );
            }
        }
        if($onlyOptions) {
            return $options;
        }
        $return[] = $this->message;
        $select = str_replace(
            '{OPTIONS}', $options, $this->selectTpl
        );
        $return[] = $select;
        $onclick = str_replace(
            array('{FORM}', '{INDEX}'),
            array($this->formName, 
                  "$('buttonRendererSelectId').value"),
                    $this->onclickTpl
                );
        $return[] = str_replace(
            '{ONCLICK}', $onclick, $this->buttonTpl
        );
        return $return;
    }

    // }}}
}

?>
