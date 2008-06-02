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

class GridColumnFieldMapper extends AbstractGridColumn {
    
    /**
     * Sert a customizer le rendu
     * Valeurs possibles: text (pour varchar), select (pour FK)
     * Todo: gerer aussi: radio (pour bool), ...
     * @access public
     **/
    public $render = array();
    
    /**
     * GridColumnFieldMapper::__construct()
     *
     * @param string $title
     * @param array $params
     * @return void
     */
    function __construct($title = '', $params = array()) {
        parent::__construct($title, $params);
        if (isset($params['Macro'])) {
            $this->macro = $params['Macro'];
            if (!$this->sortField) {
                // enlever le html éventuel
                $macro = strip_tags($this->macro);
                if (preg_match(Tools::FIRST_PASS_MACRO_REGEX, $macro, $tokens)) {
                    $this->sortField = $tokens[1];
                }
            }
            if (isset($params['Render'])) {
                $this->render = $params['Render'];
            }
        }
    }

    /**
     *
     * @access private
     */
    private $macro = false;

    /**
     * GridColumnFieldMapper::render()
     *
     * @param  $object
     * @return string
     */
    public function render($object) {
        $return = Tools::getValueFromMacro($object, $this->macro);
        // Le rendu par defaut
        if (empty($this->render)) {
            return $return;
        }else {
            $render = $this->render;
            if ($render['Type'] == 'text') {
                // On supprime les '%' si Name non fourni
                $name = (!isset($render['Name']))?strtr($this->macro, array('%' => '')):$render['Name'];
                return sprintf('<input type="text" size="12" value="%s" name="%s[]" />', $return, $name);
            }elseif ($render['Type'] == 'select') {
                if (!isset($render['Coll'])) {
                    die('Error: specifiez une collection au FieldMapper!!');
                }
                // On supprime les '%' et le '.Id' final si Name non fourni
                if (!isset($render['Name'])) {
                    $cleanedMacro = strtr($this->macro, array('%' => ''));
                    $render['Name'] = substr($cleanedMacro, 0, strlen($cleanedMacro) - 3);
                }
                $name = $render['Name'];                
                $options = FormTools::writeOptionsFromCollection($render['Coll'], $return);
                return sprintf(
                    '<select name="%s[]" >%s</select>', 
                    $name, 
                    implode("\n\t", $options)
                );
            }
            
        }
        
    }
}

?>