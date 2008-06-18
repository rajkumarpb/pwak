<?php
/**
 * HTML_QuickForm_colorpicker.
 *
 * @author guillaule l. <guillaume@geelweb.org>
 * @license PHP License http://www.php.net/license/3_0.txt
 * $Id: colorpicker.php,v 1.1.1.1 2007-06-06 11:14:50 david Exp $
 */

/**
 * include parent class.
 */
require_once 'HTML/QuickForm/text.php';

/**
 * Register QuickForm element type
 */
HTML_QuickForm::registerElementType(
    'colorpicker',
    'HTML/QuickForm/colorpicker.php',
    'HTML_QuickForm_colorpicker'
);

/**
 * HTML_QuickForm_colorpicker
 *
 * Add a javascript color picker to a textfield.
 *
 * Example:
 * <code>
 * ...
 * require_once 'HTML/QuickForm/colorpicker.php';
 *
 * $options = array(
 *     'jsFile'  => 'js/colorpicker.js',
 *     'cssFile' => 'css/png.css'
 * );
 * 
 * $element = HTML_QuickForm::createElement(
 *     'colorpicker',
 *     'colorpicker', 
 *     'Choose your favorite color',
 *     $options
 * );
 * $form->addElement($element);
 * ...
 * </code>
 *
 * @author David JL <david@ateor.com>
 * @version 1.0
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class HTML_QuickForm_colorpicker extends HTML_QuickForm_text {

    var $_options = array();

    /**
     * Constructor
     *
     * @param string $elementName Input field name attribute
     * @param string $elementLabel Label for field
     * @param array $options array of options for colorpicker (required)
     * @param mixed $attributes 
     * @since 1.0
     * @access public
     * @return void
     */
    function HTML_QuickForm_colorpicker($elementName=null, $elementLabel=null, 
        $options=array(), $attributes=array())
    {
        $this->HTML_QuickForm_text($elementName, $elementLabel, $attributes);
        $this->_type = 'colorpicker';
        $this->_options = $options;
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
        $id = $this->getAttribute('id');
        if (!$id) {
            $this->setAttribute('id', $this->getName());
            $id = $this->getName();
        }
        $value = $this->getAttribute('value');
        if (!$id) {
            $value = '#ddd';
        }
        if(!defined('HTML_QUICKFORM_colorpicker_LOADED')) {
            $html  = "&nbsp;<input type=\"button\" value=\"\" ";
            $html .= sprintf(
                'style="background-color:%s;border:1px solid #000;'
              . 'padding:0px;width:20px;cursor:pointer" id="%s_trigger"/>',
                $value, $id
            );
            if (isset($this->_options['jsFile'])) {
                $html .= sprintf(
                    "\n<script type=\"text/javascript\" src=\"%s\"></script>",
                    $this->_options['jsFile']
                );
            }
            if (isset($this->_options['cssFile'])) {
                $html .= sprintf(
                    "\n<style type=\"text/css\">@import \"%s\";</style>",
                    $this->_options['cssFile']
                );
            }
            $html .= sprintf(
                "\n<script type=\"text/javascript\">\n"
              . "//<![CDATA[\n"
              . "connect(window, 'onload', function() { if($('%s')) "
              . "attachColorPicker($('%s'), $('%s_trigger'), false); });\n"
              . "//]]>\n"
              . "</script>\n",
                $id, $id, $id
            );
            define('HTML_QUICKFORM_colorpicker_LOADED', true);
        }
        $html = parent::toHtml() . $html;
        return $html;
    }
}

?>
