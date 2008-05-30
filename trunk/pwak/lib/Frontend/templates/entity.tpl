[[foreach item=entity from=$entities]]
[[if $entity.entityExist neq true ]]
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
 * [[$entity.name]] class
 *
[[if $file.package ne '']]
 * @package [[$file.package]]
[[/if]]
[[if $file.subpackage ne '']]
 * @subpackage [[$file.subpackage]]
[[/if]]
 */
class [[$entity.name]] extends _[[$entity.name]]
{
    // [[$entity.name]]::__construct() {{{

    /**
     * Constructor
     * 
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    // }}}

    /**
     * WRITE HERE YOUR CUSTOM METHODS FOR THIS ENTITY
     */
}
?>

[[/if]]
[[/foreach]]
