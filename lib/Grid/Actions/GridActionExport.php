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

class GridActionExport extends GridActionJS {
    /**
     * GridActionExport::__construct()
     *
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {
        if (false == isset($params['GlyphEnabled'])) {
            $params['GlyphEnabled'] = 'images/envoyer.gif';
        }
        if (false == isset($params['GlyphDisabled'])) {
            $params['GlyphDisabled'] = 'images/envoyer_no.gif';
        }
        if (false == isset($params['Caption'])) {
            $params['Caption'] = A_EXPORT;
        }
        if (false == isset($params['Title'])) {
            $params['Title'] = _('Export list in csv format (Excel)');
        }
        // nom du fichier csv
        $fileName = isset($params['FileName'])?$params['FileName']:'export';
        // 1ere arrivee ou pas sur l'ecran
        $firstArrival = isset($params['FirstArrival'])?$params['FirstArrival']:0;

        if (strrpos($_SERVER['REQUEST_URI'], '?') === false) {
            $url = $_SERVER['REQUEST_URI'] . '?';
        } else {
            $url = $_SERVER['REQUEST_URI'] . '&amp;';
        }
        $url = $firstArrival?$url . 'FirstArrival=1&amp;':$url;
        $url = $this->returnURL?$url.'returnURL='.$this->returnURL.'&amp;':$url;
        // Pour les GenericGrid
        $url .= (isset($_REQUEST['entity']) && stripos($url, 'entity=') === false)?
                'entity=' . $_REQUEST['entity'] . '&amp;':'';
        $url .= 'export=' . $fileName;
        $url = UrlTools::compliantURL($url);

        $params['jsActionArray'] = array('window.location=\'' . $url  . '\'');
        parent::__construct($params);
    }

}

?>