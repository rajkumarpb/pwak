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
 * @version   SVN: $Id: CodeGenerator.php,v 1.20 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

require_once 'smarty/libs/Smarty.class.php';

/**
 * CodeGenerator
 * 
 * @package    Framework
 * @subpackage Frontend
 */
class CodeGenerator
{
    // class properties {{{

    protected $smarty = false;

    protected $xml = array();

    private $_projectPath = '';
    /**
     * Array containing various informations depending on data type.
     *
     * @access protected
     * @var array
     */
    protected static $propertyTypeMap = array(
        'string'      => array(
            'const'      => 'TYPE_STRING',
            'default'    => "''",
            'sqldefault' => "NULL",
            'sqltype'    => 'VARCHAR',
            'length'     => '255'
        ),
        'password'    =>  array(
            'const'   => 'TYPE_PASSWORD',
            'default' => "''",
            'sqldefault' => "NULL",
            'sqltype' => 'VARCHAR',
            'length'  => '40'
        ),
        'file'        => array(
            'const'   => 'TYPE_FILE',
            'default' => "''",
            'sqldefault' => "NULL",
            'sqltype' => 'VARCHAR',
            'length'  => '255'
        ),
        'image'       => array(
            'const'   => 'TYPE_IMAGE',
            'default' => "''",
            'sqldefault' => "NULL",
            'sqltype' => 'LONGBLOB',
            'length'  => false
        ),
        'file_upload' => array(
            'const'   => 'TYPE_FILE_UPLOAD',
            'default' => "''",
            'sqldefault' => "NULL",
            'sqltype' => 'LONGBLOB',
            'length'  => false
        ),
        'text'        => array(
            'const'   => 'TYPE_TEXT',
            'default' => "''",
            'sqldefault' => "NULL",
            'sqltype' => 'TEXT',
            'length'  => false
        ),
        'longtext'    => array(
            'const'   => 'TYPE_LONGTEXT',
            'default' => "''",
            'sqldefault' => "NULL",
            'sqltype' => 'LONGTEXT',
            'length'  => false
        ),
        'email'       => array(
            'const'   => 'TYPE_EMAIL',
            'default' => "''",
            'sqldefault' => "NULL",
            'sqltype' => 'VARCHAR',
            'length'  => '255'
        ),
        'url'         =>  array(
            'const'   => 'TYPE_URL',
            'default' => "''",
            'sqldefault' => "NULL",
            'sqltype' => 'VARCHAR',
            'length'  => '255'
        ),
        'html'        => array(
            'const'   => 'TYPE_HTML',
            'default' => "''",
            'sqldefault' => "NULL",
            'sqltype' => 'LONGTEXT',
            'length'  => false
        ),
        'int'         => array(
            'const'   => 'TYPE_INT',
            'default' => 'null',
            'sqldefault' => "NULL",
            'sqltype' => 'INT',
            'length'  => '11'
        ),
        'const'       => array(
            'const'   => 'TYPE_CONST',
            'default' => '0',
            'sqldefault' => "NULL",
            'sqltype' => 'INT',
            'length'  => '3'
        ),
        'float'       => array(
            'const'   => 'TYPE_FLOAT',
            'default' => 'null',
            'sqldefault' => "NULL",
            'sqltype' => 'FLOAT',
            'length'  => false
        ),
        'decimal'     => array(
            'const'   => 'TYPE_DECIMAL',
            'default' => 'null',
            'sqldefault' => "NULL",
            'sqltype' => 'DECIMAL',
            'length'  => '10,2'
        ),
        'bool'        => array(
            'const'   => 'TYPE_BOOL',
            'default' => 'false',
            'sqldefault' => "0",
            'sqltype' => 'INT',
            'length'  => '1'
        ),
        'date'        => array(
            'const'   => 'TYPE_DATE',
            'default' => "'0'",
            'sqldefault' => "NULL",
            'sqltype' => 'DATE',
            'length'  => '10'
        ),
        'time'        => array(
            'const'   => 'TYPE_TIME',
            'default' => "'0'",
            'sqldefault' => "NULL",
            'sqltype' => 'TIME',
            'length'  => '10'
        ),
        'datetime'    => array(
            'const'   => 'TYPE_DATETIME',
            'default' => "'0'",
            'sqldefault' => "NULL",
            'sqltype' => 'DATETIME',
            'length'  => '19'
        ),
        'foreignkey'  => array(
            'const'   => 'TYPE_FKEY',
            'default' => '0',
            'sqldefault' => "0",
            'sqltype' => 'INT',
            'length'  => '11'
        ),
        'onetomany'   => array(
            'const'   => 'TYPE_ONETOMANY',
            'default' => 'false',
            'sqldefault' => "NULL",
            'sqltype' => false,
            'length'  => false
        ),
        'manytomany'  => array(
            'const'   => 'TYPE_MANYTOMANY',
            'default' => 'false',
            'sqldefault' => "NULL",
            'sqltype' => false,
            'length'  => false
        ),
        'i18n_string'  => array(
            'const'      => 'TYPE_I18N_STRING',
            'default'    => '0',
            'sqldefault' => "0",
            'sqltype'    => 'INT',
            'length'     => '11'
        ),
        'i18n_text'   => array(
            'const'   => 'TYPE_I18N_TEXT',
            'default' => '0',
            'sqldefault' => "0",
            'sqltype' => 'INT',
            'length'  => '11'
        ),
        'i18n_text'   => array(
            'const'   => 'TYPE_I18N_HTML',
            'default' => '0',
            'sqldefault' => "0",
            'sqltype' => 'INT',
            'length'  => '11'
        )
    );

    /**
     * Default file data array.
     *
     * @static
     * @access protected
     * @var array
     */
    protected static $fileData = array(
        'cvssource'  => 'Source$',
        'cvsid'      => 'Id$',
        'package'    => '',
        'subpackage' => '',
        'copyright'  => '',
        'licence'    => ''
    );

    /**
     * Default entity data array.
     *
     * @static
     * @access protected
     * @var array
     */
    protected static $entityData = array(
        'name'             => '',
        'tablename'        => '',
        'label'            => '',
        'tostringproperty' => '',
        'parentclass'      => 'Object',
        'parentfile'       => false,
        'properties'       => array(),
        'allproperties'    => array(),
        'fkeys'            => array(),
        'links'            => array(),
        'features'         => array()
    );

    protected static $propertyMapping = array(
        'name'         => '',
        'label'        => '',
        'shortlabel'   => '',
        'usedby'       => array(),
        'required'     => 'false',
        'inplace_edit' => 'false',
        'add_button'   => 'false',
        'section'      => ''
    );

    private $_files = array(
        '_entity' => array(),
        'entity'  => array(),
        'crud' => array(),
        'grid' => array());

    // }}}
    // Constructeur {{{

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($path, $entitiesToGen=array()) {
        $xmlfile = Frontend::cleanPath($path) . '/config/xml/project.xml';
        $this->_projectPath = Frontend::cleanPath($path);
        $this->xml = simplexml_load_file($xmlfile);
        $this->entitiesToGen = $entitiesToGen;
        
        $this->smarty = new Smarty();
        $this->smarty->compile_dir = SMARTY_COMPILE_DIR;
        $this->smarty->template_dir = dirname(__FILE__) . '/templates';
        $this->smarty->force_compile = true;
        $this->smarty->left_delimiter  = '[[';
        $this->smarty->right_delimiter = ']]';
        $this->smarty->caching = false;
        $file = self::$fileData;
        
        $entities = $links = array();
        foreach ($this->xml->entity as $entity) {
            $e = $this->handleEntity($entity);
            $entities[strval($e['name'])] = $e;
            foreach ($e['links'] as $link) {
                if (!isset($links[strval($link['linktable'])])) {
                    $links[strval($link['linktable'])] = array(
                        'tablename' => $link['linktable'],
                        'field'     => $link['field'],   
                        'linkfield' => $link['linkfield']
                    );
                }
            }
        }

        // Add links to navigable foreign keys
        foreach($entities as $entity) {
            foreach($entity['fkeys'] as $p) {
                if(isset($entities[strval($p['class'])]) && $p['navigable']==1) {
                    $linkType = 'onetomany'; // XXX
                    $link = array();
                    $link['name'] = !empty($p['navigablename'])?$p['navigablename']:$entity['name'];
                    $link['class'] = $entity['name'];
                    $link['field'] = $p['name'];
                    $link['ondelete'] = !empty($p['ondelete'])?$p['ondelete']:'nullify';
                    $link['multiplicity'] = $linkType;
                    $link['type'] = self::$propertyTypeMap[$linkType];
                    $link['type']['name'] = $linkType;
                    $entities[strval($p['class'])]['links'][] = $link;
                }
            }
        }
        $this->_entities = $entities;
        // manage mapping
        $this->fullMapping = array();
        foreach($entities as $entity) {
            $this->fullMapping[strval($entity['name'])] = $this->buildMapping($entity);
        }

        $this->smarty->assign('file', $file);
        $this->smarty->assign('entities', $entities);
        $this->smarty->assign('links', $links); // to generate links tables in sql
    }

    // }}}
    // CodeGenerator::generateModels() {{{

    /**
     * Generate the php models.
     * If the array $entities is not empty only the specified models are 
     * generated. If $test is true, the code is generated and printed to
     * stdout instead of being written to disk, this is mainly useful for
     * testing purposes.
     *
     * @access public
     * @param  boolean $fake
     * @return boolean
     * @throw  Exception
     */
    public function generateModels($fake = false)
    {
        if($fake) {
            // generate the _Entity classes
            $contents = $this->smarty->fetch('_entity.tpl');
            echo $contents;
            // generate the Entity extends _Entity classes
            $contents = $this->smarty->fetch('entity.tpl');
            echo $contents;
            return;
        }
        
        // generate the _Entity classes
        foreach($this->_files['_entity'] as $cls) {
            $entity = array($this->_entities[$cls]);
            $this->smarty->assign('entities', $entity);
            //$contents = $this->smarty->fetch('php.tpl');
            $contents = $this->smarty->fetch('_entity.tpl');
            $this->_writeFile($cls, '_entity', $contents);
        }
        // generate the Entity extends _Entity classes
        foreach($this->_files['entity'] as $cls) {
            $entity = array($this->_entities[$cls]);
            $this->smarty->assign('entities', $entity);
            $contents = $this->smarty->fetch('entity.tpl');
            $this->_writeFile($cls, 'entity', $contents);
        }
        $this->generateControllers($fake);
    }

    // }}}
    // CodeGenerator::generateSQL() {{{

    /**
     * Generate the sql code.
     * If the array $entities is not empty only the sql tables corresponding
     * to the specified models are rebuilt. If $commit is true, the sql is
     * generated and commited to the db instead of being printed to stdout.
     *
     * @access public
     * @param  boolean $fake
     * @return boolean
     * @throw  Exception
     */
    public function generateSQL($fake = false) {
        $sqlEntities = array();
        foreach($this->_entities as $entity) {
            $clsName = strval($entity['name']);
            $o = $entity;
            while($o['ischild']==1) {
                $parent = $this->_entities[strval($o['parentclass'])];
                $clsName = strval($parent['name']);
                $o = $this->_entities[$clsName];
            }
            if(!isset($sqlEntities[$clsName])) {
                $sqlEntities[$clsName] = $this->_entities[$clsName];
            } else {
                $sqlEntities[$clsName]['allproperties'] = array_merge(
                    $sqlEntities[$clsName]['allproperties'], 
                    $entity['allproperties']);
            }
        }
        $this->smarty->assign('entities', $sqlEntities);
        $contents = $this->smarty->fetch('sql.tpl');
        if($fake) {
            echo $contents;
            return;
        }
        $this->_writeFile(NULL, 'sql', $contents);
    }

    // }}}
    // CodeGenerator::generateControllers() {{{

    /**
     * generate the crud and grid generic controllers 
     * 
     * @access public
     * @return void
     */
    public function generateControllers($fake=false) {
        if($fake) {
            // generate the crud generic controller
            $contents = $this->smarty->fetch('crudController.tpl');
            echo $contents;
            // generate the grid generic controller
            $contents = $this->smarty->fetch('gridController.tpl');
            echo $contents;
            return;
        }
        // generate the crud generic controller
        foreach($this->_files['crud'] as $cls) {
            $entity = array($this->_entities[$cls]);
            $this->smarty->assign('entities', $entity);
            $this->smarty->assign('mapping', $this->fullMapping[$cls]);
            $contents = $this->smarty->fetch('crudController.tpl');
            $this->_writeFile($cls, 'crud', $contents);
        }
        // generate the grid generic controller
        foreach($this->_files['grid'] as $cls) {
            $entity = array($this->_entities[$cls]);
            $this->smarty->assign('entities', $entity);
            $this->smarty->assign('mapping', $this->fullMapping[$cls]);
            $contents = $this->smarty->fetch('gridController.tpl');
            $this->_writeFile($cls, 'grid', $contents);
        }
    }

    // }}}
    // CodeGenerator::handleEntity() {{{

    /**
     * Generate the php code for the specified entity.
     *
     * @access protected
     * @param  object SimpleXMLElement $entity
     * @return string
     * @throw  Exception
     */
    protected function handleEntity($entity)
    {
        $e = self::$entityData;
        $attrs = $entity->attributes();
        if (!$attrs['name']) {
            $err = 'Attribute "name" is missing for some entity';
            throw new Exception('Invalid xml file: ' . $err);
        }
        if(empty($this->entitiesToGen) || in_array(strval($attrs['name']), $this->entitiesToGen)) {
            $this->_files['_entity'][] = strval($attrs['name']);
        }
        $e['name'] = $attrs['name'];
        $e['tablename'] = isset($attrs['tablename'])?$attrs['tablename']:$e['name'];
        $e['label'] = isset($attrs['label'])?$attrs['label']:$e['name'];
        if (isset($attrs['features'])) {
            $e['features'] = $this->explode($attrs['features']);
        } else {
            $e['features'] = array('add', 'edit', 'view', 'del', 'grid', 'searchform');
        }
        if (isset($attrs['tostring'])) {
            $e['tostring_property'] = $attrs['tostring'];
        }
        if (isset($attrs['parent'])) {
            $e['isparent'] = $this->getBool($attrs['parent']);
        }
        if (isset($attrs['extends'])) {
            $e['parentclass'] = $attrs['extends'];
            $e['ischild'] = true;
        }
        if (isset($entity->property)) {
            foreach ($entity->property as $property) {
                $p = $this->handleProperty($property);
                if ($p['type']['const'] == 'TYPE_FKEY') {
                    $e['fkeys'][] = $p;
                } else if ($p['type']['const'] == 'TYPE_ONETOMANY' ||
                           $p['type']['const'] == 'TYPE_MANYTOMANY') {
                    $e['links'][] = $p;
                } else {
                    if ($p['type']['const'] == 'TYPE_CONST') {
                        if(!isset($e['constants'])) {
                            $e['constants'] = array();
                        }
                        $e['constants'] = array_merge($e['constants'], $p['constarray']);
                    }
                    $e['properties'][] = $p;
                }
                if($p['i18n']==1) {
                    $e['i18n'][] = $p['name'];
                }
                if($p['emptyfordelete']) {
                    $e['emptyfordeleteproperties'][] = $p['name'];
                }
                if($p['unique']==1) {
                    $e['uniqueproperties'][] = $p['name'];
                }
                if(!empty($p['mapping'])) {
                    $e['mapping'][] = $p;
                }
                $e['allproperties'][] = $p;

            }
        }
        $e['entityExist'] = $this->_checkFileExist($entity['name']);
        $e['crudExist'] = $this->_checkFileExist($entity['name'], 'crud');
        $e['gridExist'] = $this->_checkFileExist($entity['name'], 'grid');
        return $e;
    }

    // }}}
    // CodeGenerator::handleProperty() {{{

    /**
     * Generate the php code for the specified property.
     *
     * @access protected
     * @param  object SimpleXMLElement $property
     * @return string
     * @throw  Exception
     */
    protected function handleProperty($property) {
        $attrs = $property->attributes();
        if (!isset($attrs['name'])) {
            $err = 'Attribute "name" is missing for some property';
            throw new Exception('Invalid xml file' . $err);
        }
        if (!isset($attrs['type'])) {
            $err = 'Attribute "type" is missing for some property';
            throw new Exception('Invalid xml file' . $err);
        }
        if (!isset(self::$propertyTypeMap[strval($attrs['type'])])) {
            $err = 'Type "' . $attrs['type'] . '" is not supported';
            throw new Exception('Invalid xml file' . $err);
        }
        $p['name'] = $attrs['name'];
        $p['i18n'] = isset($attrs['i18n'])?
            $this->getBool($attrs['i18n']):false;
        $type = strval($attrs['type']);
        if($p['i18n']==1 && in_array($type, array('string', 'text'))) {
            $type = 'i18n_' . $type;
        }
        $p['type'] = self::$propertyTypeMap[$type];
        $p['type']['name'] = $type;
        $p['label'] = isset($attrs['label'])?$attrs['label']:$p['name'];
        $p['shortlabel'] = isset($attrs['shortlabel'])?
            $attrs['shortlabel']:$p['label'];
        $p['section'] = isset($attrs['section'])?$attrs['section']:'';
        $p['usedby'] = isset($attrs['usedby'])?
            $this->explode($attrs['usedby']):array();
        $p['mapping'] = isset($attrs['usedby'])?
            $this->explode($attrs['usedby']):array();
        $p['default'] = isset($attrs['default'])?
            $attrs['default']:$p['type']['default'];
        $p['sqldefault'] = isset($attrs['default'])?
            $attrs['default']:$p['type']['sqldefault'];
        $p['length'] = isset($attrs['length'])?
            $attrs['length']:$p['type']['length'];
        if ($type == 'decimal') {
            $p['dec_num'] = (int)substr($p['length'], strpos($p['length'], ',')+1);
        }
        $p['required'] = isset($attrs['required'])?
            ($this->getBool($attrs['required'])?'true':'false'):'false';
        $p['inplace_edit'] = isset($attrs['inplace_edit'])?
            ($this->getBool($attrs['inplace_edit'])?'true':'false'):'false';
        $p['add_button'] = isset($attrs['add_button'])?
            ($this->getBool($attrs['add_button'])?'true':'false'):'false';
        $p['unique'] = isset($attrs['unique'])?
            $this->getBool($attrs['unique']):false;
        $p['navigable'] = isset($attrs['navigable'])?
            $this->getBool($attrs['navigable']):false;
        $p['navigablename'] = isset($attrs['navigablename'])?$attrs['navigablename']:'';
        $p['bidirectional'] = isset($attrs['bidirectional'])?
            $this->getBool($attrs['bidirectional']):'false';
        $p['multiplicity'] = isset($attrs['multiplicity'])?$attrs['multiplicity']:'';
        $p['ondelete'] = isset($attrs['ondelete'])?$attrs['ondelete']:'';
        $p['emptyfordelete'] = isset($attrs['emptyfordelete'])?
            $this->getBool($attrs['emptyfordelete']):false;
        $p['class'] = isset($attrs['class'])?$attrs['class']:'';
        $p['linktable'] = isset($attrs['linktable'])?$attrs['linktable']:false;
        $p['field']     = isset($attrs['field'])?$attrs['field']:false;
        $p['linkfield'] = isset($attrs['linkfield'])?$attrs['linkfield']:false;
        if($p['type']['const']=='TYPE_CONST') {
            $p['constarray'] = array();
            foreach($property->const as $const) {
                $p['constarray'][] = array(
                    'name'  => $const['name'],
                    'value' => $const['value'],
                    'label' => $const['label']
                );
            }
        }
        return $p;
    }

    // }}}
    // CodeGenerator::getBool() {{{

    /**
     * Return a boolean according to true/false possible strings
     *
     * @static
     * @access protected
     * @return boolean
     */
    protected static function getBool($b)
    {
        return in_array(strval($b), array('true', '1', 'on'));
    }

    // }}}
    // CodeGenerator::explode() {{{

    /**
     * Return an array of strings.
     *
     * @static
     * @access protected
     * @return array
     */
    protected static function explode($str)
    {
        $ret = array();
        $exp = explode(',', $str);
        foreach ($exp as $token) {
            $ret[] = trim($token);
        }
        return $ret;
    }

    // }}}
    // CodeGenerator::_checkFileExist() {{{
    
    /**
     * check if the class file with user's methods already exist for the given 
     * entity $cls and the file type $type.
     * 
     * @param string $cls entity name
     * @param string $type file type (crud, grid, entity)
     * @access private
     * @return bool
     */
    private function _checkFileExist($cls, $type='entity') {
        if ($type == 'crud') {
            $name = $cls . 'AddEdit.php';
            $file = $this->_projectPath . '/' . LIB_DIR . '/' . CUSTOM_CONTROLLER_DIR . '/' . $name;
        } elseif ($type == 'grid') {
            $name = $cls . 'Grid.php';
            $file = $this->_projectPath . '/' . LIB_DIR . '/' . CUSTOM_CONTROLLER_DIR . '/' . $name;
        } elseif ($type == 'entity'){
            $name = $cls . '.php';
            $file = $this->_projectPath . '/' . LIB_DIR . '/' . MODELS_DIR . '/' . $name;
        }
        if(!file_exists($file)) {
            if(empty($this->entitiesToGen) || in_array(strval($cls), $this->entitiesToGen)) {
                $this->_files[$type][] = strval($cls);
            }
            return false;
        } 
        return true;
    }

    // }}}
    // CodeGenerator::_writeFile() {{{

    /**
     * write content in file
     * 
     * @param mixed $cls class name
     * @param mixed $type class type
     * @param mixed $content content
     * @param mixed $fake 
     * @access private
     * @return void
     */
    private function _writeFile($cls, $type, $content, $fake=false) {
        if($fake) {
            echo $content;
            return;
        }
        if ($type == '_entity') {
            $file = $this->_projectPath . '/' . LIB_DIR . '/' . MODELS_DIR . '/_' . $cls . '.php';
        } elseif ($type=='entity') {
            $file = $this->_projectPath . '/' . LIB_DIR . '/' . MODELS_DIR . '/' . $cls . '.php';
        } elseif ($type == 'crud') {
            $file = $this->_projectPath . '/' . LIB_DIR . '/' . CUSTOM_CONTROLLER_DIR . '/' . $cls . 'AddEdit.php';
        } elseif ($type=='grid') {
            $file = $this->_projectPath . '/' . LIB_DIR . '/' . CUSTOM_CONTROLLER_DIR . '/' . $cls . 'Grid.php';
        } elseif ($type=='sql') {
            $file = $this->_projectPath . '/config/sql/project.sql';
        }
        if(!$fh = fopen($file, "w")) {
            throw new Exception('Cannot open file "%s".', $file);
        }
        if (fwrite($fh, $content)===false) {
            throw new Exception('Cannot write in file "%s".', $file);
        }
        printf(" -> create file \"%s\"\n", $file);
        fclose($fh);
    }

    // }}}
    // CodeGenerator::buildMapping() {{{
    
    /**
     * buildMapping 
     * 
     * build the full mapping of an entity include parents entities mapping.
     * 
     * @param mixed $entity 
     * @access public
     * @return void
     */
    public function buildMapping($entity) {
        $mapping = array();
        $propertyDone = array();
        if(isset($entity['mapping'])) {
            foreach($entity['mapping'] as $property) {
                $pmap = self::$propertyMapping;
                $pmap['name'] = $property['name'];
                $pmap['label'] = $property['label'];
                $pmap['shortlabel'] = $property['shortlabel'];
                $pmap['usedby'] = $property['usedby'];
                $pmap['required'] = $property['required'];
                $pmap['inplace_edit'] = $property['inplace_edit'];
                $pmap['add_button'] = $property['add_button'];
                $pmap['section'] = $property['section'];
                if (isset($property['dec_num'])) {
                    $pmap['dec_num'] = $property['dec_num'];
                }
                $mapping[] = $pmap;
                $propertyDone[] = strval($property['name']);
            }
        }
        if(isset($entity['allproperties'])) {
            foreach($entity['allproperties'] as $property) {
                if(in_array(strval($property['name']), $propertyDone)) {
                    continue;
                }
                $pmap = self::$propertyMapping;
                $pmap['name'] = $property['name'];
                $pmap['label'] = $property['label'];
                $pmap['shortlabel'] = $property['shortlabel'];
                $mapping[] = $pmap;
            }
        }
        if (isset($entity['ischild'])) {
            $mapping = array_merge($this->buildMapping($this->_entities[strval($entity['parentclass'])]), $mapping);
        }
        return $mapping;
    }
    
    // }}}
}

?>
