<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('HTML/QuickForm/checkbox.php');

/**
 * HTML class for an advanced checkbox type field
 *
 * Basically this fixes a problem that HTML has had
 * where checkboxes can only pass a single value (the
 * value of the checkbox when checked).  A value for when
 * the checkbox is not checked cannot be passed, and 
 * furthermore the checkbox variable doesn't even exist if
 * the checkbox was submitted unchecked.
 *
 * It works by creating a hidden field with the passed-in name
 * and creating the checkbox with no name, but with a javascript
 * onclick which sets the value of the hidden field.
 * 
 * @author       Jason Rust <jrust@php.net>
 * @since        2.0
 * @access       public
 */
class HTML_QuickForm_advcheckbox extends HTML_QuickForm_checkbox {
    // {{{ properties

    /**
     * The values passed by the hidden elment
     *
     * @var array
     * @access private
     */
    var $_values = null;

    /**
     * The default value
     *
     * @var boolean
     * @access private
     */
    var $_defaultValue = null;

    /**
     * The constant value
     *
     * @var boolean
     * @access private
     */
    var $_constantValue = null;

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $elementLabel   (optional)Input field label 
     * @param     string    $text           (optional)Text to put after the checkbox
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array
     * @param     mixed     $values         (optional)Values to pass if checked or not checked 
     *
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_advcheckbox($elementName=null, $elementLabel=null, $text=null, $attributes=null, $values=null)
    {
        $this->setValues($values);
        HTML_QuickForm_input::HTML_QuickForm_input($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_text = $text;
        $this->setType('checkbox');
        $this->updateAttributes(array('value'=>1));
        if (isset($this->_attributes['onclick'])) {
            $this->_attributes['onclick'] .= $this->getOnclickJs($elementName);
        }
        else {
            $this->updateAttributes(array('onclick' => $this->getOnclickJs($elementName)));
        }
    } //end constructor
    
    // }}}
    // {{{ getPrivateName()

    /**
     * Gets the pribate name for the element
     *
     * @param   string  $elementName The element name to make private
     *
     * @access public
     * @return string
     */
    function getPrivateName($elementName)
    {
        return '__'.$elementName;
    }

    // }}}
    // {{{ getOnclickJs()

    /**
     * Create the javascript for the onclick event which will
     * set the value of the hidden field
     *
     * @param     string    $elementName    The element name
     *
     * @access public
     * @return string
     */
    function getOnclickJs($elementName)
    {
        $onclickJs = 'if (this.checked) { this.form[\''.$elementName.'\'].value=\''.addcslashes($this->_values[1], '\'').'\'; }';
        $onclickJs .= 'else { this.form[\''.$elementName.'\'].value=\''.addcslashes($this->_values[0], '\'').'\'; }';
        return $onclickJs;
    }

    // }}}
    // {{{ setValues()

    /**
     * Sets the values used by the hidden element
     *
     * @param   mixed   $values The values, either a string or an array
     *
     * @access public
     * @return void
     */
    function setValues($values)
    {
        if (empty($values)) {
            // give it default checkbox behavior
            $vals[0] = '';
            $vals[1] = 1;
        }
        elseif (is_string($values)) {
            // if it's string, then assume the value to 
            // be passed is for when the element is checked
            $vals[0] =  '';
            $vals[1] = $values;
        }
        else {
            $vals = $values;
        }

        $this->_values = $vals;
    }

    // }}}
    // {{{ toHtml()

    /**
     * Returns the checkbox element in HTML
     * and the additional hidden element in HTML
     * 
     * @access    public
     * @return    string
     */
    function toHtml()
    {
        $oldName = $this->getName();
        $newName = $this->getPrivateName($oldName); 
        // set it to unchecked to begin with and let it be set
        // by GET/POST, defaultValue, or constantValue
        // run it through the input constructor again with the new name
        HTML_QuickForm_input::HTML_QuickForm_input($newName, $this->getLabel(), $this->getAttributes());
        $vars = array_merge($_GET, $_POST);
        if (isset($vars[$oldName]) && $vars[$oldName] == $this->_values[1]) {
            $this->setChecked(true);
        }
        elseif ($this->_defaultValue == $this->_values[1]) {
            $this->setChecked(true);
        }
        else {
            $this->setChecked(false);
        }

        if ($this->_constantValue == $this->_values[1]) {
            $this->setChecked(true);
        }
        elseif ($this->_constantValue === $this->_values[0]) {
            $this->setChecked(false);
        }

        // set hidden element's default value
        $tmp_value = $this->_values[(int) $this->getChecked()];

        return HTML_QuickForm_input::toHtml() . $this->_text . '<input type="hidden" name="'.$oldName.'" value="'.$tmp_value.'" />';
    } //end func toHtml
    
    // }}}
    // {{{ onQuickFormEvent()

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string    $event  Name of event
     * @param     mixed     $arg    event arguments
     * @param     object    $caller calling object
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                // constant values override both default and submitted ones
                // default values are overriden by submitted
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_submitValues);
                    if (null === $value) {
                        $value = $this->_findValue($caller->_defaultValues);
                        if (null !== $value && empty($caller->_submitValues)) {
                            $this->_defaultValue = $value;
                        }
                    }
                } else {
                    $this->_constantValue = $value;
                }
                if (null !== $value) {
                    $this->setChecked($value);
                }
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    } // end func onQuickFormLoad

    // }}}
} //end class HTML_QuickForm_advcheckbox
?>
