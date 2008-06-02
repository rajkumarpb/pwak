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

class ImageManager extends Upload {
    // ImageManager::__construct() {{{

	/**
     * Constructor
     *
     * @param string $path chemin vers l'image
     * @param string $ext extention de l'image
     * @return void
     */
	public function __construct($name) {
        parent::__construct($name);
        $this->ext = $this->infos['extension'];
        if ($this->ext == 'jpg') {
            $this->ext = 'jpeg';
        }
        $this->maxsize = 512000; // 1MO
    }

    // }}}
    // ImageManager::resize() {{{
	
	/**
	 * Redimensionne l'image
     * 
     * @param float $newwidth nouvelle largeur
     * @param float $newheight nouvelle hauteur
	 * @access public
	 * @return void 
	 */
	public function resize($newwidth=200, $newheight=200){
        try {
            $this->check();
        } catch (Exception $exc) {
            throw $exc;
        }
        $createfunc = 'imagecreatefrom' . $this->ext;
        if(!function_exists($createfunc)) {
            throw new Exception(sprintf(E_UPLOAD_UNSUPPORTED_EXTENSION,
                    $this->infos['name'], implode(', ', $this->extensions)));
        }
		$array = getimagesize($this->infos['tmp_name']);
		list($width, $height) = $array;
		if($width >= $height && $newheight < $height){
		    $newheight = $height / ($width / $newwidth);
		} else if ($width < $height && $newwidth < $width) {
		    $newwidth = $width / ($height / $newheight);   
		} else {
		    $newwidth = $width;
		    $newheight = $height;
		}
		$thumb  = imagecreatetruecolor($newwidth, $newheight);
		$source = $createfunc($this->infos['tmp_name']);
		imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight,
            $width, $height);
        $displayfunc = 'image' . $this->ext;
		$displayfunc($thumb, $this->infos['tmp_name']);
    }

    // }}}
}

?>
