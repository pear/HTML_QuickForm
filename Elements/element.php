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

require_once("HTML/Common.php");

/**
 * Base class for form elements
 * 
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.3
 * @since        PHP4.04pl1
 * @access       public
 * @abstract
 */
class HTML_QuickForm_element extends HTML_Common {
    
    /**
     * Form element type
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_type = '';

    /**
     * Flag to tell if element is frozen
     * @var       
     * @since     1.0
     * @access    private
     */
    var $_flagFrozen = false;
    
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
        HTML_Common::HTML_Common($attributes);
        $this->setType($type);
        if ($elementName != null) {
            $this->setName($elementName);
        }
        if ($value != null) {
            $this->setValue($value);
        }
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
        $this->_type = $type;
    } // end func setType
    
    /**
     * Returns element type
     *
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function getType()
    {
        return $this->_type;
    } // end func getType

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
        // interface method
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
        // interface method
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
        $this->updateAttributes(array("value"=>$value));
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
        return isset($this->_attributes["value"]) ? $this->_attributes["value"] : null;
    } // end func getValue
    
    /**
     * Freeze the element so that only its value is returned
     * 
     * @access    public
     * @return    void
     * @throws    
     */
    function freeze()
    {
        $this->_flagFrozen = true;
    } //end func freeze

    /**
     * Returns the value of field without HTML tags
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function getFrozenHtml()
    {
        $html = $this->getValue();
        return $html;
    } //end func getFrozenHtml

    /**
     * Returns the form element type
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @abstract    
     */
    function getType()
    {
        return $this->_type;
    } //end func getType

    /**
     * Returns whether element value should persist after a freeze
     * 
     * @since     1.0
     * @access    public
     * @return    bool
     * @abstract    
     */
    function persistantFreeze()
    {
        return true;
    } //end func persistantFreeze

} // end class HTML_QuickForm_element
?>