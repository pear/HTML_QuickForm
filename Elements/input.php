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

require_once("HTML_QuickForm/Elements/element.php");

/**
 * Base class for input form elements
 * 
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.0
 * @since        PHP4.04pl1
 * @access       public
 * @abstract
 */
class HTML_QuickForm_input extends HTML_QuickForm_element {
        
    /**
     * Class constructor
     * 
     * @param    mixed   $attributes     (optional)Associative array of table tag attributes 
     *                                   or HTML attributes name="value" pairs
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_element($type=null, $elementName=null, $value=null, $attributes=null)
    {
        HTML_QuickForm_element::HTML_QuickForm_element($type, $elementName, $value, $attributes);
    } //end constructor

    /**
     * Sets the element type
     *
     * @param     string    $type   Element type
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setType($type)
    {
        HTML_QuickForm_element::setType($type);
        $this->updateAttributes(array('type'=>$type));
    } // end func setType
    
    /**
     * Sets the input field name
     * 
     * @param     string    $name   Input field name attribute
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setName($name)
    {
        $this->updateAttributes(array('name'=>$name));
    } //end func setName
    
    /**
     * Returns the element name
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function getName()
    {
        return $this->getAttribute('name');
    } //end func getName
    
    /**
     * Sets the value of the form element
     *
     * @param     string    $value      Default value of the form element
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setValue($value)
    {
        $this->updateAttributes(array('value'=>$value));
    } // end func setValue

    /**
     * Returns the value of the form element
     *
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function getValue()
    {
        return $this->getAttribute('value');
    } // end func getValue
    
    /**
     * Returns the input field in HTML
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function toHtml()
    {
        if ($this->_flagFrozen) {
            $html = $this->getFrozenHtml();
        } else {
            $tabs = $this->_getTabs();
            $html = "\n$tabs<INPUT".$this->_getAttrString($this->_attributes)."></INPUT>";
        }
        return $html;
    } //end func toHtml

} // end class HTML_QuickForm_element
?>