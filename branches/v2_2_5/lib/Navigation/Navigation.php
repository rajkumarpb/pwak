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

class Navigation
{
    // constants {{{

    const NAVIGATION_VAR = 'nav';
    const XML_ENC = 'UTF-8';
    const PHP_ENC = 'ISO-8859-1';

    // }}}
    // {{{ properties

    /**
     * The current menu renderer.
     *
     * @var    object Renderer $renderer
     * @access public
     */
    public $renderer = false;

    /**
     * determine if the menu use a cookie to store the selected tabs.
     *
     * @var    boolean $useCookie
     * @access public
     */
    public $useCookie = false;

    /**
     * Callable auth function
     *
     * @var    array $metadata
     * @access public
     */
    public $authFunction = false;

    /**
     * Titre de la page selectionnÃe
     *
     * @var    string activeTitle
     * @access public
     */
    public $activeTitle = '';

    /**
     * Instance de la classe utilisée pour le singleton
     *
     * @static
     * @var    object Navigation
     * @access protected
     */
    protected static $instance = false;

    /**
     * Array containing the menu metadata.
     *
     * @var    array $metadata
     * @access public
     */
    protected $metadata = array();

    /**
     * Array of profiles authorized for active item of each level
     *
     * @var array
     */
    protected $authorizedProfiles = array();

    /**
     * Array containing boolean values for each levels that tell if an active
     * item has already been set.
     *
     * @var    array $hasActiveItem
     * @access protected
     */
    protected $hasActiveItem = array();

    /**
     * Array of html code for each levels
     *
     * @var    array $_html
     * @access private
     */
    private $_html = array();

    // }}}
    // constructor {{{

    /**
     * Constructor
     *
     * @access public
     * @param  array $metadata
     * @return void
     */
    public function __construct()
    {
        if(MENU_METADATA_FORMAT == 'xml') {
            $file = PROJECT_ROOT . '/' . MENU_METADATA;
            $this->metadata = $this->fromXML($file);
        } else {
            require MENU_METADATA;
            $this->metadata = $menu_metadata;
        }
    }

    // }}}
    // singleton {{{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public static function singleton()
    {
        if (!self::$instance) {
            self::$instance = new Navigation();
        }
        return self::$instance;
    }
    // }}}
    // Navigation::render() {{{

    /**
     * Return the html code for the level $level, if $level is ommited all
     * the html code is returned.
     *
     * @access private
     * @param  integer $level
     * @return string
     */
    public function render($level = false) {
        // if nothing in GET and cookie set the default tab to the first tab
        if ($this->useCookie && !isset($_COOKIE[self::NAVIGATION_VAR])) {
            $_COOKIE[self::NAVIGATION_VAR] = '0';
        } else if (!isset($_GET[self::NAVIGATION_VAR])) {
            $_GET[self::NAVIGATION_VAR] = '0';
        }
        // will parse the metadata only if necessary
        $this->parseMetadata();
        if ($level === false) {
            return implode("\n", $this->_html);
        } else if(isset($this->_html[$level])) {
            return $this->_html[$level];
        } else {
            return '';
        }
    }

    // }}}
    // Navigation::parseMetadata() {{{

    /**
     * Parse the metadata and build the corresponding html code for each
     * navigation level.
     *
     * @access protected
     * @param  array $metadata
     * @return void
     */
    protected function parseMetadata($metadata=false, $indexStack=array()) {
        static $level = 1;
        if (isset($this->_html[$level])) {
            return;
        }
        $this->hasActiveItem[$level] = false;
        $children = false;
        if (!$this->renderer) {
            $this->renderer = new NavigationRendererDefault();
        }
        if (!$metadata) {
            $metadata = $this->metadata;
        }
        $ret[] = $this->renderer->open($level);
        // Trade context
        $tradeContext = Preferences::get('TradeContext');
        foreach ($metadata as $index=>$data) {
            // formatte le tableau de l'élément avec les paramètres par défaut
            // et met son état à actif ou inactif
            if (isset($data['restrict_to']) &&
                !call_user_func($this->authFunction, $data['restrict_to'], array('showErrorDialog'=>false)))
            {
                    continue;
            }
            $this->handleData($data, $level, $index, $indexStack);
            if(isset($data['restrict_to'])) {
                $pn = UrlTools::getPageNameFromURL($data['link']);
                $this->authorizedProfiles[$pn] = $data['restrict_to'];
            }
            if ((is_null($tradeContext) && isset($data['restrict_to_context'])) ||
                (isset($data['restrict_to_context'])  &&
                array_intersect($data['restrict_to_context'], $tradeContext) == array()))
            {
                    continue;
            }
            $id  = implode('_', $indexStack);
            $id .= empty($id)?$index:'_'.$index;
            $ret[] = $this->renderer->render($level, $index, $data,
                $this->useCookie, $id);
            if ($data['active'] && isset($data['children'])) {
                $stack = $indexStack;
                $stack[] = $index;
                $children = $data['children'];
            } else if ($data['active']) {
                $this->activeTitle = $data['description'];
            }
        }
        $ret[] = $this->renderer->close($level);
        $this->_html[$level] = implode("\n", $ret);
        if ($children) {
            $level++;
            $this->parseMetadata($children, $stack);
        }
    }

    // }}}
    // Navigation::handleData() {{{

    /**
     * Set the default values for missing data keys and set the state of the
     * item according to GET data or COOKIE data.
     *
     * @access public
     * @param  integer $level
     * @param  integer $index
     * @param  array $data
     * @return void
     */
    protected function handleData(&$data, $level, $index, $indexStack=array()) {
        // default values
        if (!isset($data['title'])) {
            $data['title'] = 'Untitled ' . $index;
        }
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['accesskey'])) {
            $data['accesskey'] = '';
        }
        $indexes = '';
        $padding = '';
        $indexStack[] = $index;
        foreach ($indexStack as $i) {
            $indexes .= $padding . $i;
            $padding = ',';
        }
        if ($this->useCookie) {
            $data['onclick'] = 'fw.cookie.create(\''
                . self::NAVIGATION_VAR . '\', \'' . $indexes . '\')';
        } else {
            // if not cookie pass the tab index infos in the url
            $qs = false === strrpos($data['link'], '?')?'?':'&';
            $data['link'] .= $qs . self::NAVIGATION_VAR . '=' . $indexes;
            $data['onclick'] = false;
        }
        // state of the item
        $active = false;
        if (!$this->hasActiveItem[$level]) {
            // there's no selected item for this level yet
            $array = $this->useCookie?$_COOKIE:$_GET;
            if (isset($array[self::NAVIGATION_VAR])) {
                $a = explode(',', $array[self::NAVIGATION_VAR]);
                $active = isset($a[$level-1]) && $index == $a[$level-1];
            }
        }
        $data['active'] = $active;
        // Pour afficher directement le sous onglet si un seul possible
        if (isset($data['children'])) {
            $children = $data['children'];
            $chidrenIndexes = $this->_getChildrenForAuth($children);
            $uri = explode('?', $_SERVER['REQUEST_URI']);
            $url = basename($_SERVER['PHP_SELF']) . (isset($uri[1])?'?' . $uri[1]:'');
            if (count($chidrenIndexes) == 1) {
                $data['link'] = $children[$chidrenIndexes[0]]['link'];
                $indexes .= ',' . $chidrenIndexes[0];
                if ($this->useCookie) {
                    $data['onclick'] = 'fw.cookie.create(\''
                        . self::NAVIGATION_VAR . '\', \'' . $indexes . '\')';
                }else {
                    $qs = (false === strrpos($data['link'], '?'))?'?':'&';
                    $data['link'] .= $qs . self::NAVIGATION_VAR . '=' . $indexes;
                }
            }
        }
        $data['link'] = UrlTools::compliantURL($data['link']);
        return $active;
    }

    // }}}
    // Navigation::getProfileArrayForActivePage() {{{

    /**
     * Return an array with the profiles which are authorized for active page.
     *
     * @access public
     * @return array
     */
    public function getProfileArrayForActivePage($page) {
        $this->parseMetadata();
        if (array_key_exists($page, $this->authorizedProfiles)) {
            return $this->authorizedProfiles[$page];
        }
        return array();
    }

    // }}}
    // Navigation::getChildrenForAuth() {{{
    /**
     * Return an array with the children indexes which are authorized for active tab.
     *
     * @param array $children
     * @access private
     * @return array
     */
    private function _getChildrenForAuth($children) {
        $return = array();
        foreach($children as $key => $data) {
            if (!isset($data['restrict_to'])
            || call_user_func($this->authFunction, $data['restrict_to'], array('showErrorDialog'=>false))) {
                $return[] = $key;
            }
        }
        return $return;
    }

    // }}}

    // Navigation::fromXML() {{{
    /**
     * Parse un fichier XML représentant un menu et retourne un tableau.
     *
     * Format du XML:
     * <code>
     * <?xml version="1.0" encoding="ISO-8859-1" ?>
     * <menu>
     *     <item title="Menu1" link="menu1.php" description="Une description ici..."
     *     accesskey="A" >
     *         <item title="Sous Menu 1" link="sub11.php" />
     *         <item title="Sous Menu 2" link="sub12.php" />
     *         <restrict_to>
     *             <profile name="PROFILE_ADMIN" />
     *             <profile name="PROFILE_USER" />
     *         </restrict_to>
     *     </item>
     *     <item title="Menu2" link="menu2.php"
     *     description="Une autre description ici..." accesskey="B">
     *         <item title="Sous Menu 1" link="sub21.php" />
     *         <item title="Sous Menu 2" link="sub22.php" />
     *         <restrict_to>
     *             <profile name="PROFILE_ADMIN" />
     *         </restrict_to>
     *     </item>
     * </menu>
     * </code>
     *
     * @access public
     * @param string $file path to the xmlfile
     * @return array
     */
    public function fromXML($file) {
        if (!($fp = @fopen($file, "r"))) {
            return false;
        }
        $xml = simplexml_load_file($file);
        return $this->_xmlToArray($xml);
    }

    // }}}
    // Navigation::_xmlToArray() {{{

    /**
     * Converti l'objet SimpleXMLElement correspondant au fichier en un tableau
     * php.
     *
     * @param object $xml objet SimpleXMLElement
     * @param string $baseEnc encoding du fichier xml.
     * @param string $destEnc encoding de destination.
     * @return array
     * @access private
     */
    private function _xmlToArray($xml) {
        $array = array();
        foreach($xml->item as $item) {
            $r = array();
            foreach($item->attributes() as $key=>$value) {
                if($value) {
                    $value = iconv(self::XML_ENC, self::PHP_ENC, $value);
                    if ($key == 'title' || $key == 'description') {
                        $value = _($value);
                    }
                    $r[$key] = $value;
                }
            }
            if($item->restrict_to) {
                $r['restrict_to'] = array();
                foreach($item->restrict_to->profile as $profile) {
                    foreach($profile->attributes() as $key=>$value) {
                        if($value && $key=='name') {
                            $r['restrict_to'][] = constant('UserAccount::'.$value);
                        }
                    }
                }
            }
            if($item->restrict_to_context) {
                $r['restrict_to_context'] = array();
                foreach($item->restrict_to_context->context as $context) {
                    $r['restrict_to_context'][] = $context;
                }
            }
            $children = $this->_xmlToArray($item->children());
            if(!empty($children)) {
                $r['children'] = $children;
            }
            $array[] = $r;
        }
        return $array;
    }

    // }}}
}


?>
