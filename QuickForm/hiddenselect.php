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

require_once('HTML/QuickForm/select.php');

/**
 * This class takes the same arguments as a select element, but instead
 * of creating a select ring it creates hidden elements for all values
 * already selected with setDefault or setConstant.  This is useful if
 * you have a select ring that you don't want visible, but you need all
 * selected values to be passed.
 *
 * @author       Isaac Shepard <ishepard@bsiweb.com>
 * 
 * @version      1.0
 * @since        2.1
 * @access       public
 */
class HTML_QuickForm_hiddenselect extends HTML_QuickForm_select {
    // {{{ constructor
        
    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $options        (optional)Input field value
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_hiddenselect($elementName=null, $elementLabel=null, $options=null, $attributes=null)
    {
        HTML_QuickForm_element::HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'hiddenselect';
        if (isset($options)) {
            $this->load($options);
        }
    } //end constructor
    
    // }}}
    // {{{ toHtml()

    /**
     * Returns the SELECT in HTML
     *
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function toHtml()
    {
        // put this down here since it changes the name
        if ($this->getAttribute('multiple')) {
            $this->setMultiple(true);
        }

        $tabs = $this->_getTabs();
        $name = isset($this->_attributes['name']) ? $this->_attributes['name'] : '' ;
        $strHtml = '';

        for ($counter=0; $counter < count($this->_options); $counter++) {
            $value = $this->_options[$counter]['attr']['value'];
            $attrString = $this->_getAttrString($this->_options[$counter]['attr']);
        
            if (is_array($this->_values) && in_array($value, $this->_values)) {
                $strHtml .= $tabs . '<input type="hidden" name="' . $name . '" value="' . $value . '" />' . "\n";
            }
        }

        return $strHtml;
    } //end func toHtml
    
    // }}}
} //end class HTML_QuickForm_hiddenselect
?>
