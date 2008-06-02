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

define('XML_HEADER', '<?xml version="1.0" encoding="iso-8859-1" standalone="yes"?>');
define('XML_END_LINE', "\n");
define('XML_INDENT', '    ');
define('MAX_CHAR_PER_LINE', 79);
/**#@-*/

// class XMLElement {{{

/**
 * XMLElement 
 * 
 * @uses SimpleXMLElement
 * @version $Id$
 * @author guillaume luchet <guillaume@geelweb.org> 
 * @package Framework 
 * @subpackage Frontend
 */
class XMLElement extends SimpleXMLElement {
    // XMLElement::asPrettyXML() {{{

    /**
     * Get an "human readable" XML string.
     *
     * The XML string was build with end line character (define by XML_END_LINE 
     * constant) and indentation (define width XML_INDENT constant).
     * 
     * Try to limit the number of chars per line (define by MAX_CHAR_PER_LINE 
     * constant).
     *
     * @param integer $level Node level
     * @param string $filename file name
     * @return string
     */
    public function asPrettyXML($level=0, $filename=NULL) {
        $padding = str_repeat(XML_INDENT, $level);
        $s = '';
        // je sais pas ou SimpleXMLElement met le header, je l'ajoute à la porc
        if($level==0) {
            $s .= XML_HEADER . XML_END_LINE;
        }
        
        $a = array();
        $comment = '';
        if($this->getName() == 'entity') {
            $comment = XML_END_LINE . $padding . '<!-- Entity ' . (String)$this['name'] . ' -->' . XML_END_LINE;
        }
        $a[0] = $comment . $padding . '<' . $this->getName() ;
        $l = 0;
        foreach($this->attributes() as $k=>$v) {
            $b = " $k=\"$v\"";
            if((strlen($a[$l]) + strlen($b))>MAX_CHAR_PER_LINE) {
                $a[$l+1] = $padding . XML_INDENT . trim($b);
                $l++;
            } else {
                $a[$l] .= $b;
            }
        }
        $s .= implode(XML_END_LINE, $a);

        $c = '';
        $value = (String)$this;
        if(strlen($value)>0) {
            $c = $padding . XML_INDENT . $value . XML_END_LINE;
        }
        $level++;
        $cn = '';
        foreach($this->children() as $child) {
            $cn .= $child->asPrettyXML($level);
        }
        
        if(strlen($cn)>0 || strlen($c)>0) {
            $s .= '>';
            $s .= XML_END_LINE;
            $s .= (strlen($c)>0) ? $c : '';
            $s .= (strlen($cn)>0) ? $cn : '';
            $s .= $padding;
            $s .= '</' .$this->getName(). '>';
        } else {
            $s .= ' />';
        }
        
        $s = (strlen($s) < (MAX_CHAR_PER_LINE + 2)) ? 
            $padding . str_replace(XML_END_LINE, "", str_replace(XML_INDENT, "", $s)) : $s;
        $s .= XML_END_LINE;
        
        if($filename != NULL) {
            return $this->_saveFile($filename, $s);
        }
        return $s;
    }

    // }}}
    // XMLElement::_saveFile() {{{

    /**
     * Write content in a file.
     *
     * @param string $filename file name
     * @param string $content file content
     * @return bool true if success.
     */
    private function _saveFile($filename, $content) {
        try {
            $fh = fopen($filename, "w");
        } catch(Exception $e) {
            throw new Exception('Error during file opening ' . $filename . 
                ': ' . $e->getMessage());
            exit(-1);
        }
        if($fh != false) {
            try {
                return fwrite($fh, $content) != false;
            } catch (Exception $e) {
                throw new Exception('Error during file writing: ' . 
                    $e->getMessage());
                exit(-1);
            }
        }
        return false;
    }
    
    // }}}
    // XMLElement::orderByEntity() {{{
    
    /**
     * orderByEntity 
     * 
     * @access public
     * @return void
     */
    public function orderByEntity($entity='entity', $attr='name') {
        $tags = $this->xpath('//application/' . $entity);
        $array = array();
        foreach($tags as $key=>$tag) {
            $array[(String)$tag[$attr]] = $key;
        }
        ksort($array);
        $xml = new XMLElement(XML_HEADER . '<' . $this->getName() . '/>');
        foreach($this->attributes() as $k=>$v) {
            $xml->addAttribute($k, $v);
        }
        foreach($this->children() as $name=>$child) {
            if($name != $entity) {
                $xml->addNode($child);
            }
        }
        foreach($array as $key=>$value) {
            $xml->addNode($tags[$value]);
        }
        return $xml;
    }
    
    // }}}
    // ArgoUmlXmi2Xml::addNode() {{{

    /**
     * addNode 
     * 
     * @param mixed $node 
     * @access public
     * @return void
     */
    public function addNode($node) {
        $newNode = $this->addChild($node->getName());
        foreach($node->attributes() as $key=>$value) {
            $newNode->addAttribute($key, $value);
        }
        foreach($node->children() as $child) {
            $newNode->addNode($child);
        }
    }

    // }}}
}

// }}}
// class ArgoUmlXmi2Xml {{{

/**
 * ArgoUmlXmi2Xml 
 * 
 * @version $Id$
 * @author guillaume luchet <guillaume@geelweb.org> 
 * @package Framework 
 * @subpackage Frontend
 */
class ArgoUmlXmi2Xml {
    // properties {{{

    /**
     * xmi 
     * 
     * @var mixed
     * @access public
     */
    public $xmi;

    /**
     * _projectPath 
     * 
     * @var mixed
     * @access private
     */
    private $_projectPath;

    /**
     * xml 
     * 
     * @var mixed
     * @access public
     */
    public $xml;
    
    /**
     * generalization 
     * 
     * @var array
     * @access public
     */
    public $generalization = array();
    
    /**
     * parentClasses 
     * 
     * @var array
     * @access public
     */
    public $parentClasses = array();
    
    /**
     * classes 
     * 
     * @var array
     * @access public
     */
    public $classes = array();
    
    /**
     * datatype 
     * 
     * @var array
     * @access public
     */
    public $datatype = array();
    
    /**
     * links 
     * 
     * @var array
     * @access public
     */
    public $links = array();

    // }}}
    // ArgoUmlXmi2Xml::__construct() {{{

    /**
     * __construct 
     * 
     * @param mixed $xmi 
     * @access public
     * @return void
     */
    public function __construct($xmi, $projectPath) {
        $this->xmi = simplexml_load_file($xmi);
        $this->_projectPath = $projectPath;
        $this->getGeneralization();
        $this->getClasses();
        $this->getDataTypes();
        $this->getLinks();
        $this->process();
    }

    // }}}
    // ArgoUmlXmi2Xml::initXML() {{{
    
    /**
     * initXML 
     * 
     * @access public
     * @return void
     */
    public function initXML() {
        $this->xml = new XMLElement(XML_HEADER . '<application/>');

        $this->xml->addAttribute('name', 'project');
        $this->xml->addAttribute('outputdir', $this->_projectPath . '/lib/models');
        $this->xml->addAttribute('addonsdir', $this->_projectPath . '/lib/models/addons');
        $this->xml->addAttribute('sqloutputdir', $this->_projectPath . '/config/sql');

        $dbopts = $this->xml->addChild('databaseoptions');
        $dbopts->addAttribute('type', 'MySQL');
        $dbopts->addAttribute('name', 'project');
        $dbopts->addAttribute('fieldprefix', '_');
    }
    
    // }}}
    // ArgoUmlXmi2Xml::getGeneralization() {{{
    
    /**
     * getGeneralization 
     * 
     * @access public
     * @return void
     */
    public function getGeneralization() {
        foreach($this->xmi->xpath('//XMI.content/UML:Model/*/UML:Generalization') as $gen) {
            $child = $gen->xpath('UML:Generalization.child/UML:Class');
            $parent = $gen->xpath('UML:Generalization.parent/UML:Class');
            $this->generalization[(String)$child[0]['xmi.idref']] = (String)$parent[0]['xmi.idref'];
            $this->parentsClasses[] = (String)$parent[0]['xmi.idref'];
        }
    }
    
    // }}}
    // ArgoUmlXmi2Xml::getClasses() {{{
    
    /**
     * getClasses 
     * 
     * @access public
     * @return void
     */
    public function getClasses() {
        foreach($this->xmi->xpath('//XMI.content/UML:Model/*/UML:Class') as $cls) {
            $this->classes[(String)$cls['xmi.id']] = (String)$cls['name'];
        }
    }
    
    // }}}
    // ArgoUmlXmi2Xml::getDataTypes() {{{
    
    /**
     * getDataTypes 
     * 
     * @access public
     * @return void
     */
    public function getDataTypes() {
        foreach($this->xmi->xpath('//XMI.content/UML:Model/*/UML:DataType') as $type) {
            $this->datatype[(String)$type['xmi.id']] = (String)$type['name'];
        }
    }
    
    // }}}
    // ArgoUmlXmi2Xml::getLinks() {{{
    
    /**
     * getLinks 
     * 
     * @access public
     * @return void
     */
    public function getLinks() {
        foreach($this->xmi->xpath('//XMI.content/UML:Model/*/UML:Association') as $assoc) {
            $ranges = $assoc->xpath('UML:Association.connection/UML:AssociationEnd/UML:AssociationEnd.multiplicity/*/*/UML:MultiplicityRange');
            $clss = $assoc->xpath('UML:Association.connection/UML:AssociationEnd/UML:AssociationEnd.participant/UML:Class');
            if(((int)$ranges[0]['lower'])==1 && ((int)$ranges[0]['upper'])==1) {
                $base = (String)$clss[1]['xmi.idref'];
                $dest = $this->classes[(String)$clss[0]['xmi.idref']];
                if(!isset($this->links[$base])) {
                    $this->links[$base] = array();
                }
                $this->links[$base][] = array('type'=>'foreignkey', 'class'=>$dest);
                continue;
            }
            if(((int)$ranges[1]['lower'])==1 && ((int)$ranges[1]['upper'])==1) {
                $base = (String)$clss[0]['xmi.idref'];
                $dest = $this->classes[(String)$clss[1]['xmi.idref']];
                if(!isset($this->links[$base])) {
                    $this->links[$base] = array();
                }
                $this->links[$base][] = array('type'=>'foreignkey', 'class'=>$dest);
                continue;
            }
            if((((int)$ranges[0]['lower'])==0 && ((int)$ranges[0]['upper'])==-1) 
                && (((int)$ranges[1]['lower'])==0 && ((int)$ranges[1]['upper'])==-1)) {
                $base = (String)$clss[1]['xmi.idref'];
                $dest = $this->classes[(String)$clss[0]['xmi.idref']];
                if(!isset($this->links[$base])) {
                    $this->links[$base] = array();
                }
                $this->links[$base][] = array('type'=>'manytomany', 'class'=>$dest);
                continue;
            } 
        }
    }
    
    // }}}
    // ArgoUmlXmi2Xml::process() {{{
    
    /**
     * process 
     * 
     * @access public
     * @return void
     */
    public function process() {
        $this->initXML();
        foreach($this->xmi->xpath('//XMI.content/UML:Model/*/UML:Class') as $cls) {
            // attributes {{{
            $name = (String)$cls['name'];
            $foreignkeys = array();
            $id = (String)$cls['xmi.id'];
            $idtablename = $id;
            $entity = $this->xml->addChild('entity');
            $entity->addAttribute('name', $name);
            if(in_array($id, $this->parentsClasses)) {
                $entity->addAttribute('parent', 1);
            }
            if(isset($this->generalization[$id])) {
                $entity->addAttribute('extends', $this->classes[$this->generalization[$id]]);
                while(isset($this->generalization[$idtablename])) {
                    $idtablename = $this->generalization[$idtablename];
                }
            }
            $entity->addAttribute('tablename', $this->classes[$idtablename]);
            // CRUD
            $entity->addAttribute('features', 'add,edit,del,grid,searchform');
            $entity->addAttribute('label', $this->_formatLabel($cls['name'].'s'));
            // }}}
            // properties {{{
    
            foreach($cls->xpath('UML:Classifier.feature/UML:Attribute') as $attr) {
                $property = $entity->addChild('property');
                $type = 'unknow';
                $fk = false;
                foreach($attr->xpath('UML:StructuralFeature.type/UML:DataType') as $foo) {
                    $type = isset($this->datatype[(String)$foo['xmi.idref']]) ? $this->datatype[(String)$foo['xmi.idref']]:'';
                }
                foreach($attr->xpath('UML:StructuralFeature.type/UML:Class') as $foo) {
                    if(isset($this->classes[(String)$foo['xmi.idref']])) {
                        $type = 'foreignkey';
                        $fk = $this->classes[(String)$foo['xmi.idref']];
                    }
                }
                $property->addAttribute('type', $type);
                if($type == 'decimal') {
                    $property->addAttribute('length', '10,2');
                }
                $property->addAttribute('name', $attr['name']);
                if($fk) {
                    $property->addAttribute('class', $fk);
                    $foreignkeys[$fk] = $attr['name'];
                }
                // CRUD
                $label = $this->_formatLabel($attr['name']);
                $property->addAttribute('label', $label);
                if(strlen($label)>8) {
                    $property->addAttribute('shortlabel', $label);
                }
                $property->addAttribute('usedby', 'addedit,grid,searchform');
            }

            // }}}
            // links {{{

            if(isset($this->links[$id])) {
                foreach($this->links[$id] as $link) {
                    if($link['type']== 'foreignkey' && isset($foreignkeys[$link['class']])
                    && $link['class'] == $foreignkeys[$link['class']]) {
                        // fk already added
                        continue;
                    }
                    $property = $entity->addChild('property');
                    $property->addAttribute('type', $link['type']);
                    $property->addAttribute('name', $link['class']);
                    $property->addAttribute('class', $link['class']);
                    if($link['type'] == 'manytomany') {
                        $property->addAttribute('field', 'From' . $name);
                        $property->addAttribute('linkfield', 'To' . $link['class']);
                        $table = $name . $link['class'];
                        $table = strtolower(substr($table, 0, 1)) . substr($table, 1);
                        $property->addAttribute('linktable', $table);
                    }
                    // CRUD
                    $label = $this->_formatLabel($attr['name']);
                    $property->addAttribute('label', $label);
                    if(strlen($label)>8) {
                        $property->addAttribute('shortlabel', $label);
                    }
                    $property->addAttribute('usedby', 'addedit,grid,searchform');
                }
            }

            // }}}
        }
    }
    
    // }}}
    // ArgoUmlXmi2Xml::render() {{{
    
    /**
     * render 
     * 
     * @param mixed $filename 
     * @access public
     * @return void
     */
    public function render($filename=NULL) {
        $this->xml = $this->xml->orderByEntity();
        return $this->xml->asPrettyXML(0, $filename);
    }
    
    // }}}
    // ArgoUmlXmi2Xml::_formatLabel() {{{

    /**
     * _formatLabel 
     * 
     * @param mixed $cls 
     * @access private
     * @return void
     */
    private function _formatLabel($cls) {
        preg_match_all("([A-Z][a-z]+)", $cls, $tokens);
        if(empty($tokens[0])) {
            return $cls;
        }
        $label = '';
        $padding = '';
        foreach($tokens[0] as $k=>$v) {
            $label .= $padding . strtolower($v);
            $padding = ' ';
        }
        return strtoupper(substr($label, 0, 1)) . substr($label, 1);
    }

    // }}}
}

// }}}

?>
