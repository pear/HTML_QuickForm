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

require_once("HTML/QuickForm/Elements/element.php");

/**
 * HTML class for a form element group
 * 
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.0
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_group extends HTML_QuickForm_element {

    /**
     * Value of the element
     * @var       mixed
     * @since     1.0
     * @access    private
     */
    var $_value = null;
        
    /**
     * Name of the element
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_name = "";

    /**
     * Array of grouped elements
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_elements = "";

    /**
     * Layout of elements within the group
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_layout = "";

    /**
     * Class constructor
     * 
     * @param     string    $elementName    Input field name attribute
     * @param     array     $elements       (optional)Group elements
     * @param     string    $layout         (optional)Group layout
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_group ($elementName=null, $elements=array(), $layout='rows')
    {
        HTML_QuickForm_element::HTML_QuickForm_element('group', $elementName);
        $this->_elements = $elements;
        $this->_layout = $layout;
    } //end constructor
    
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
        $this->_name = $name;
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
        return $this->_name;
    } //end func getName

    /**
     * Sets value for textarea element
     * 
     * @param     string    $value  Value for password element
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setValue($value)
    {
        $this->_value = $value;
    } //end func setValue
    
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
        return $this->_value;
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
        $html = "";
        $name = $this->getName();
        $value = $this->getValue();
        for ($i=0; $i < count($this->_elements); $i++ ) {
            $element = $this->_elements[$i];
            if (PEAR::isError($element)) {
                return $element;
            }
            $elementName = $element->getName();
            $elementType = $element->getType();
            if (!empty($name) && $elementType == "radio") {
                $element->setName($name);
            } elseif (!empty($name) && $elementType != "radio") {
                $element->setName($name . "[]");
            }
            if (isset($value) && $elementType == "radio") {
                if (isset($value) && ($element->getValue() == $value)) {
                    $element->setChecked(true);
                } else {
                    $element->setChecked(false);
                }
            } elseif (isset($value) && $elementType == "checkbox") {
                if (!is_array($value)) {
                    $arrValue = split('[ ]?,[ ]?', $value);
                } else {
                    $arrValue = $value;
                }
                if (in_array($element->getValue(), $arrValue)) {
                    $element->setChecked(true);
                } else {
                    $element->setChecked(false);
                }
            } elseif ($elementType != "submit" && $elementType != "button" && $elementType != "reset" && $elementType != "image") {
                if (isset($value) && is_array($value)) {
                    $element->setValue($value[$i]);
                } elseif (isset($value)) {
                    $element->setValue($value);
                }
            }
            if ($this->_flagFrozen) {
                $element->freeze();
            }
            $element->_tabOffset = $this->_tabOffset + 1;
            $html .= $element->toHtml();
            $html .= ($this->_layout == "rows") ? "&nbsp;" : "<BR>\n";
        }
        return $html;
    } //end func toHtml
    
    /**
     * Returns the value of field without HTML tags
     * 
     * @since     1.3
     * @access    public
     * @return    string
     * @throws    
     */
    function getFrozenHtml()
    {
        $tmp = $this->_frozenFlag;
        $this->_frozenFlag = true;
        $html = $this->toHtml();
        $this->_frozenFlag = $tmp;
        return $html;
    } //end func getFrozenHtml

} //end class HTML_QuickForm_group
?>