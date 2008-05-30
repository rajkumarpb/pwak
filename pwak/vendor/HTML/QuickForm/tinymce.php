<?php
/**
 * HTML_QuickForm_tinymce.
 *
 * @author guillaule l. <guillaume@geelweb.org>
 * @license PHP License http://www.php.net/license/3_0.txt
 * $Id: tinymce.php,v 1.1.1.1 2007-06-06 11:14:50 david Exp $
 */

/**
 * include parent class.
 */
require_once 'HTML/QuickForm/textarea.php';

/**
 * Register QuickForm element type
 */
HTML_QuickForm::registerElementType('tinymce', 'HTML/QuickForm/tinymce.php', 'HTML_QuickForm_tinymce');

/**
 * HTML_QuickForm_tinymce
 *
 * Add a web based Javascript HTML WYSIWYG editor control based on 
 * {@link http://tinymce.moxiecode.com TinyMCE}.
 *
 * Example:
 * <code>
 * ...
 * require_once 'HTML/QuickForm/tinymce.php';
 *
 * $options = array(
 *    'baseURL'    => 'js/tinymce/jscript/tiny_mce/',
 *    'configFile' => 'js/tinymce/tinymce.inc.js');
 * $attributes = array(
 *    'class' => 'mceEditor');
 * 
 * $element = HTML_QuickForm::createElement('tinymce', 'tinymceEditor', 
 *    'TinyMCE Editor', $options, $attributes);
 * $form->addElement($element);
 * ...
 * </code>
 * @author guillaume l. <guillaume@geelweb.org>
 * @version 1.0
 * @license PHP License http://www.php.net/license/3_0.txt
 */
class HTML_QuickForm_tinymce extends HTML_QuickForm_textarea {
    var $_options = array();
    /**
     * Constructor
     *
     * @param string $elementName Input field name attribute
     * @param string $elementLabel Label for field
     * @param array $options array for TinyMCE options (needed keys are 'baseURL' and 
     * 'configFile')
     * @param mixed $attributes Either a typical HTML attribute string or an 
     * associative array
     * @since 1.0
     * @access public
     * @return void
     */
    function HTML_QuickForm_tinymce($elementName=null, $elementLabel=null, 
    $options=array(), $attributes=null) {
        $this->HTML_QuickForm_textarea($elementName, $elementLabel, $attributes);
        $this->_type = 'tinymce';

        if(is_array($options)) {
            $this->_options = $options;
        }
    }

    /**
     * Returns the element in HTML
     *
     * @since 1.0
     * @access public
     * @return string
     */
    function toHtml() {
        if($this->_flagFrozen) {
            return $this->getFrozenHtml();
        }
        $html = '';
        if(!defined('HTML_QUICKFORM_TINYMCE_LOADED')) {
            $html = sprintf(
                '<script type="text/javascript" src="%stiny_mce.js"></script>' .
                '<script type="text/javascript" src="%s"></script>',
                $this->_options['baseURL'], $this->_options['configFile']);
            define('HTML_QUICKFORM_TINYMCE_LOADED', true);
        }
        $html .= $this->_getTabs() . 
            '<textarea' . $this->_getAttrString($this->_attributes) . '>' .
            // because we wrap the form later we don't want the text indented
            preg_replace("/(\r\n|\n|\r)/", '&#010;', htmlspecialchars($this->_value)) .
            '</textarea>';

        return $html;
    }
}

?>
