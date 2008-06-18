[[foreach item=entity from=$entities]]
[[if $entity.crudExist neq true ]]
<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * $[[$file.cvssource]]
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
 * [[$entity.name]]AddEdit class
 *
[[if $file.package ne '']]
 * @package [[$file.package]]
[[/if]]
[[if $file.subpackage ne '']]
 * @subpackage [[$file.subpackage]]
[[/if]]
 */
class [[$entity.name]]AddEdit extends GenericAddEdit {
    // [[$entity.name]]::__construct() {{{

    /**
     * Constructor
     *
     * @param array $params
     * @return void
     */
    public function __construct($params=array()) {
        // put additional params here
        parent::__construct($params);
    }

    // }}}
    // [[$entity.name]]::getMapping() {{{
    
    /**
     * Return the object "mapping" for crud generic controller
     *
     * @access public
     * @return array
     */
    public function getMapping() {
        $return = array(
[[foreach name=mapping item=property from=$mapping]]
            '[[$property.name]]' => array(
                'label'        => _('[[$property.label]]'),
                'shortlabel'   => _('[[$property.shortlabel]]'),
                'usedby'       => array('[["', '"|implode:$property.usedby]]'),
                'required'     => [[$property.required]],
                'inplace_edit' => [[$property.inplace_edit]],
                'add_button'   => [[$property.add_button]],
                'section'      => '[[$property.section]]'
            )[[if not $smarty.foreach.mapping.last]],
[[/if]]
[[/foreach]]);
        return $return;
    }

    // }}}
}

?>
[[/if]]
[[/foreach]]
