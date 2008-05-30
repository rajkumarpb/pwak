[[foreach item=entity from=$entities]]
<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * $[[$file.cvssource]]
 *
 * This is a generated file. DO *NOT* EDIT this file.
 *
 * @version $[[$file.cvsid]]
[[if $file.license ne '']]
 * @license [[$file.license]]
[[/if]]
[[if $file.copyright ne '']]
 * @copyright [[$file.copyright]]
[[/if]]
[[if $file.package ne '']]
 * @package [[$file.package]]
[[/if]]
[[if $file.subpackage ne '']]
 * @subpackage [[$file.subpackage]]
[[/if]]
 */

/**
 * _[[$entity.name]] class
 *
 * @use [[$entity.parentclass]]
[[if $file.package ne '']]
 * @package [[$file.package]]
[[/if]]
[[if $file.subpackage ne '']]
 * @subpackage [[$file.subpackage]]
[[/if]]
 */
class _[[$entity.name]] extends [[$entity.parentclass]] {
[[* class constants {{{ *]]
[[if count($entity.constants) gt 0]]
    // class constants {{{

[[foreach item=constant from=$entity.constants]]
    const [[$constant.name]] = [[$constant.value]];
[[/foreach]]

    // }}}
[[/if]]
[[* }}} *]]
[[* constructor {{{ *]]
    // _[[$entity.name]]::__construct() {{{

    /**
     * _[[$entity.name]]::__construct()
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    // }}}
[[* }}} *]]
[[* simple properties {{{ *]]
[[if $lastModified]]
    // lastmodified property {{{

    /**
     * _[[$entity.name]]::lastModified()
     * Return last mofified date in datetime format (YYYY-MM-DD HH:MM:SS)
     *
     * @var string $lastModified
     * @access public
     */
     public $lastModified = 0;

    // }}}
[[/if]]
[[include file="properties.tpl"]]
[[* }}} *]]
[[* foreign keys properties {{{ *]]
[[foreach item=fkey from=$entity.fkeys]]
    // [[$fkey.name]] foreignkey and getter/setter {{{

    /**
     * _[[$entity.name]]::[[$fkey.name]]
     *
     * @access protected
     * @var mixed object [[$fkey.class]] or integer
     */
    protected $[[$fkey.name]] = false;

    /**
     * _[[$entity.name]]::get[[$fkey.name]]()
     *
     * @access public
     * @return object [[$fkey.class]]
     */
    public function get[[$fkey.name]]()
    {
        if (is_int($this->[[$fkey.name]]) && $this->[[$fkey.name]] > 0) {
            $mapper = Mapper::singleton('[[$fkey.class]]');
            $this->[[$fkey.name]] = $mapper->load(
                array('Id'=>$this->[[$fkey.name]]));
        }
        return $this->[[$fkey.name]];
    }

    /**
     * _[[$entity.name]]::get[[$fkey.name]]Id()
     *
     * @access public
     * @return integer
     */
    public function get[[$fkey.name]]Id()
    {
        if ($this->[[$fkey.name]] instanceof [[$fkey.class]]) {
            return $this->[[$fkey.name]]->getId();
        }
        return (int)$this->[[$fkey.name]];
    }

    /**
     * _[[$entity.name]]::set[[$fkey.name]]()
     *
     * @access public
     * @param mixed object [[$fkey.class]] or integer $value
     * @return void
     */
    public function set[[$fkey.name]]($value)
    {
        if (is_numeric($value)) {
            $this->[[$fkey.name]] = (int)$value;
        } else {
            $this->[[$fkey.name]] = $value;
        }
    }

    // }}}
[[/foreach]]
[[* }}} *]]
[[* links properties {{{ *]]
[[foreach item=link from=$entity.links]]
    // [[$link.name]] [[$link.type.name]] relation and getter/setter {{{

    /**
     * _[[$entity.name]]::[[$link.name]]Collection
     *
     * @access protected
     * @var object Collection [[$link.name]]Collection
     */
    protected $[[$link.name]]Collection = false;

    /**
     * _[[$entity.name]]::get[[$link.name]]Collection()
     *
     * @access public
     * @return object Collection
     */
    public function get[[$link.name]]Collection($filter = array(),
        $sortOrder = array(), $fields = array())
    {
        if (!empty($filter) || !empty($sortOrder) || !empty($fields)) {
            $mapper = Mapper::singleton('[[$entity.name]]');
            return $mapper->get[[$link.type.name]]($this->getId(),
                '[[$link.class]]', $filter, $sortOrder, $fields);
        }
        if (false == $this->[[$link.name]]Collection) {
            $mapper = Mapper::singleton('[[$entity.name]]');
            $this->[[$link.name]]Collection = $mapper->get[[$link.type.name]](
                $this->getId(), '[[$link.class]]');
        }
        return $this->[[$link.name]]Collection;
    }

    /**
     * _[[$entity.name]]::get[[$link.name]]CollectionIds()
     *
     * @access public
     * @param $filter FilterComponent or array
     * @return array
     */
    public function get[[$link.name]]CollectionIds($filter = array()) {
[[if $link.type.name eq 'onetomany']]
        $col = $this->get[[$link.name]]Collection($filter, array(), array('Id'));
        return ($col instanceof Collection)?$col->getItemIds():array();
[[else]]
        if (!empty($filter)) {
            $col = $this->get[[$link.name]]Collection($filter, array(), array('Id'));
            return ($col instanceof Collection)?$col->getItemIds():array();
        }
        if (false == $this->[[$link.name]]Collection) {
            $mapper = Mapper::singleton('[[$entity.name]]');
            return $mapper->getManyToManyIds($this->getId(), '[[$link.class]]');
        }
        return $this->[[$link.name]]Collection->getItemIds();
[[/if]]
    }

    /**
     * _[[$entity.name]]::set[[$link.name]]Collection()
     *
     * @access public
     * @param object Collection $value
     * @return void
     */
    public function set[[$link.name]]Collection($value) {
        $this->[[$link.name]]Collection = $value;
    }

[[if $link.type.name eq 'manytomany']]
    /**
     * _[[$entity.name]]::set[[$link.name]]CollectionIds()
     *
     * @access public
     * @param array $itemIds
     * @return void
     */
    public function set[[$link.name]]CollectionIds($itemIds) {
        $this->[[$link.name]]Collection = new Collection();
        $this->[[$link.name]]Collection->entityName = '[[$link.class]]';
        $this->[[$link.name]]Collection->_Items = $itemIds;
        $this->[[$link.name]]Collection->_ItemIDs = $itemIds;
    }

[[/if]]
    // }}}
[[/foreach]]
[[* }}} *]]
[[* getTablename {{{ *]]
    // _[[$entity.name]]::getTableName() {{{

    /**
     * Return the corresponding sql table name
     *
     * @static
     * @access public
     * @return string
     * @see Object.php
     */
    public static function getTableName() {
        return '[[$entity.tablename]]';
    }

    // }}}
[[* }}} *]]
[[* getObjectLabel {{{ *]]
    // _[[$entity.name]]::getObjectLabel() {{{

    /**
     * Return the class label (used for display)
     *
     * @static
     * @access public
     * @return string
     * @see Object.php
     */
    public static function getObjectLabel() {
        return _('[[$entity.label]]');
    }

    // }}}
[[* }}} *]]
[[* getProperties {{{ *]]
[[if count($entity.properties) gt 0 or count($entity.fkeys) gt 0]]
    // _[[$entity.name]]::getProperties() {{{

    /**
     * Return an array of properties (including foreignkeys).
     *
     * @static
     * @access public
     * @return array
     * @see Object.php
     */
    public static function getProperties($ownOnly=false) {
        $return = array(
[[foreach name=properties item=property from=$entity.properties]]
            '[[$property.name]]' => Object::[[$property.type.const]][[if not $smarty.foreach.properties.last or count($entity.fkeys) gt 0]],[[/if]]

[[/foreach]]
[[foreach name=fkeys item=fkey from=$entity.fkeys]]
            '[[$fkey.name]]' => '[[$fkey.class]]'[[if not $smarty.foreach.fkeys.last]],[[/if]]

[[/foreach]]
        );
        return $ownOnly?$return:array_merge(parent::getProperties(), $return);
    }

    // }}}
[[/if]]
[[* }}} *]]
[[* getLinks {{{ *]]
[[if count($entity.links) gt 0]]
    // _[[$entity.name]]::getLinks() {{{

    /**
     * Return links array (OneToMany and ManyToMany relations)
     *
     * @static
     * @access public
     * @return array
     * @see Object.php
     */
    public static function getLinks($ownOnly=false) {
        $return = array(
[[foreach name=links item=link from=$entity.links]]
            '[[$link.name]]' => array(
                'linkClass'    => '[[$link.class]]',
                'field'        => '[[$link.field]]',
[[if $link.type.name == 'onetomany']]
                'ondelete'     => '[[$link.ondelete]]',
[[else]]
                'linkTable'    => '[[$link.linktable]]',
                'linkField'    => '[[$link.linkfield]]',
                'bidirectional' => [[$link.bidirectional]],
[[/if]]
                'multiplicity' => '[[$link.multiplicity]]'
            )[[if not $smarty.foreach.links.last]],
[[/if]]
[[/foreach]]

        );
        return $ownOnly?$return:array_merge(parent::getLinks(), $return);
    }

    // }}}
[[/if]]
[[* }}} *]]
[[* getUniqueProperties {{{ *]]
[[if count($entity.uniqueproperties) gt 0]]
    // _[[$entity.name]]::getUniqueProperties() {{{

    /**
     * Return an array of properties that must be unique
     *
     * @static
     * @access public
     * @return array
     */
    public static function getUniqueProperties() {
        $return = array('[["', '"|implode:$entity.uniqueproperties]]');
        return array_merge(parent::getUniqueProperties(), $return);
    }

    // }}}
[[/if]]
[[* }}} *]]
[[* getEmptyForDeleteProperties {{{ *]]
[[if count($entity.emptyfordeleteproperties) gt 0]]
    // _[[$entity.name]]::getEmptyForDeleteProperties() {{{

    /**
     * Return an array of properties that must be empty to be deleted
     *
     * @static
     * @access public
     * @return array
     */
    public static function getEmptyForDeleteProperties() {
        $return = array('[["', '"|implode:$entity.emptyfordeleteproperties]]');
        return array_merge(parent::getEmptyForDeleteProperties(), $return);
    }

    // }}}
[[/if]]
[[* }}} *]}
[[* getFeatures {{{ *]]
[[if count($entity.features) gt 0]]
    // _[[$entity.name]]::getFeatures() {{{

    /**
     * Return an array of "features" for the current class.
     *
     * @static
     * @access public
     * @return array
     * @see Object.php
     */
    public static function getFeatures() {
        return array('[["', '"|implode:$entity.features]]');
    }

    // }}}
[[/if]]
[[* }}} *]]
[[* getMapping {{{ *]]
[[if count($entity.mapping) gt 0]]
    // _[[$entity.name]]::getMapping() {{{

    /**
     * Return the object "mapping" for crud and grid generic controllers
     *
     * @static
     * @access public
     * @return array
     * @see Object.php
     */
    public static function getMapping($ownOnly=false) {
        $return = array(
[[foreach name=mapping item=property from=$entity.mapping]]
            '[[$property.name]]'=>array(
                'label'      => _('[[$property.label]]'),
                'shortlabel' => _('[[$property.shortlabel]]'),
                'usedby'     => array('[["', '"|implode:$property.usedby]]'),
                'required'   => [[$property.required]],
[[if $property.dec_num]]
                'dec_num'    => [[$property.dec_num]],
[[/if]]
                'section'    => '[[$property.section]]'
            ),
[[/foreach]]
        );
        return $ownOnly?$return:array_merge(parent::getMapping(), $return);
    }

    // }}}
[[/if]]
[[* }}} *]]
[[* toString {{{ *]]
[[if $entity.tostringproperty neq '']]
    // _[[$entity.name]]::toString() {{{

    /**
     * Return the string representation of instances of this class.
     *
     * @access public
     * @return string
     */
    public function toString() {
        return $this->get[[$entity.tostringproperty]]();
    }

    // }}}
    // _[[$entity.name]]::getToStringAttribute() {{{

    /**
     * Attribute used for the string representation of instances of this class.
     *
     * @access public
     * @return string
     */
    public static function getToStringAttribute() {
        return '[[$entity.tostringproperty]]';
    }

    // }}}
[[/if]]
[[* }}} *]]
[[* useInheritance {{{ *]]
[[if $entity.isparent or $entity.ischild]]
    // _[[$entity.name]]::useInheritance() {{{

    /**
     * Return true if the object has children or has a parent different than 
     * Object.
     *
     * @static
     * @access public
     * @return boolean
     * @see Object.php
     */
    public static function useInheritance() {
        return true;
    }

    // }}}
[[/if]]
[[* }}} *]
[[* mutate {{{ *]]
[[if $entity.isparent and !$entity.ischild]]
    // _[[$entity.name]]::mutate() {{{

    /**
     * "Mutation" method for objects that use inheritance
     *
     * @access public
     * @param string $class
     * @return object
     **/
    public function mutate($entity) {
        // on instancie le bon objet
        require_once('Objects/' . $entity . '.php');
        $mutant = new $entity();
        if(!($mutant instanceof [[$entity.name]])) {
            trigger_error('Invalid classname provided.', E_USER_ERROR);
        }
        // propriétés fixes
        $mutant->hasBeenInitialized = $this->hasBeenInitialized;
        $mutant->readonly = $this->readonly;
        $mutant->setId($this->getId());
        // propriétés simples
        $properties = $this->getProperties();
        foreach($properties as $property=>$type){
            $getter = 'get' . $property;
            $setter = 'set' . $property;
            if (method_exists($mutant, $setter)) {
                $mutant->$setter($this->$getter());
            }
        }
        // relations
        $links = $this->getLinks();
        foreach($links as $property=>$data){
            $getter = 'get' . $property . 'Collection';
            $setter = 'set' . $property . 'Collection';
            if (method_exists($mutant, $setter)) {
                $mutant->$setter($this->$getter());
            }
        }
        return $mutant;
    }

    // }}}
[[/if]]
[[* }}} *]]
[[* getParentClassName {{{ *]]
[[if $entity.ischild]]
    // _[[$entity.name]]::getParentClassName() {{{

    /**
     * Retourne le nom de la première classe parente
     *
     * @static
     * @access public
     * @return string
     */
    public static function getParentClassName() {
        return '[[$entity.parentclass]]';
    }

    // }}}
[[/if]]
[[* }}} *]]
}

?>
[[/foreach]]
