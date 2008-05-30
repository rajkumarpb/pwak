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
 * @version   SVN: $Id: GridColumnFieldMapperWithTranslationExpression.php,v 1.4 2008-05-30 09:23:48 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GridColumnFieldMapperWithTranslationExpression extends
GridColumnFieldMapperWithTranslation {
    /**
     * GridColumnFieldMapperWithTranslationExpression::__construct()
     *
     * @param string $title
     * @param array $params
     * @return void
     */
    function __construct($title = '', $params = array()) {
        parent::__construct($title, $params);
    }

    /**
     * GridColumnFieldMapperWithTranslationExpression::render()
     *
     * @param  $object
     * @return string
     */
    public function render($object) {
        $macro = parent::render($object);
        return Tools::getValueFromMacro($object, $macro);
    }
}

?>
