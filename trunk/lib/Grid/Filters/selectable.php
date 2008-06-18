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

F
<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * $Source: /home/cvs/framework/lib/Grid/Filters/selectable.php,v $
 *
 * Plugin de formatage qui transforme un texte en un widget select.
 *
 * @version    CVS: $Id$
 * @copyright  2002-2006 ATEOR - All rights reserved
 * @package Framework
 * @subpackage GridFilters
 */

/**
 * Plugin qui permet de transformer le texte d'une cellule du grid en un
 * select de formulaire.
 * 
 * Example:
 * <code>
 * // ici, selectable@CoverType@CoverType correspond respectivement:
 * //    - selectable = le nom de la fonction filtre
 * //    - CoverType  = le nom du select
 * //    - CoverType  = le nom de l'objet avec lequel on va générer les options
 * $grid->NewColumn(
 *     'FieldMapper', 
 *     'Type de colis', 
 *     array('Macro' => '%CoverType.Id|selectable@CoverType@CoverType%')
 * );
 * </code>
 * 
 * Pour un example réel voir www/TransportCommand.php.
 * 
 * @param  string $content Contenu de la cellule à formater
 * @param  string $selectName le nom qu'aura le select
 * @param  string $objName le nom de l'objet pour générer le select
 * @param  string $methodName le nom de la fonction (si pas objet) qui renvoie 
 *                            un tableau d'options.
 * @param  string $filter: un filtre de type: "Generic_EQ_0_AND_Active_EQ_1"
 * @param  array $order: un ordre de type: "Name_EQ_SORT_ASC"
 * @access public 
 * @return string 
 */
function grid_filter_selectable($content, $selectName, $objName, 
    $methodName=false, $filter=array(), $order=array()) {
    // evaluer les eventuels $filter et $order
    if (is_string($filter)) {
        $newfilter = array();
        $filters = explode('_AND_', $filter);
        foreach($filters as $rule){
            list($key, $val) = explode('_EQ_', $rule);
            $newfilter[$key] = $val;
        }
        $filter = $newfilter;
    }
    if (is_string($order)) {
        $neworder = array();
        list($key, $val) = explode('_EQ_', $order);
        $neworder[$key] = $val;
        $order = $neworder;
    }
    $template = '<select name="%s[]">%s</select>';
    if ($methodName != false) {
        $options = $methodName(intval($content));
    } else {
        $options = FormTools::writeOptionsFromObject($objName, 
            intval($content), $filter, $order);
    }
    return sprintf($template, $selectName, implode("\n", $options));
} 

?>
