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
 * HTML class for a textarea type field
 * 
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.0
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_textarea extends HTML_QuickForm_element {
    
    /**
     * Default value
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_value = "";
        
    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $value          (optional)Input field value
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_textarea ($elementName, $value="", $attributes=null)
    {
        HTML_QuickForm_element::HTML_QuickForm_element('textarea', $elementName, $value, $attributes);
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
     * Sets wrap type for textarea element
     * 
     * @param     string    $wrap  Wrap type
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setWrap($wrap)
    {
        $this->updateAttributes(array("wrap"=>$wrap));
    } //end func setWrap
    
    /**
     * Sets height in rows for textarea element
     * 
     * @param     string    $rows  Height expressed in rows
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setRows($rows)
    {
        $this->updateAttributes(array("rows"=>$rows));
    } //end func setRows

    /**
     * Sets width in cols for textarea element
     * 
     * @param     string    $cols  Width expressed in cols
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */ 
    function setCols($cols)
    {
        $this->updateAttributes(array("cols"=>$cols));
    } //end func setCols

    /**
     * Returns the textarea element in HTML
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
            $html = "\n$tabs<TEXTAREA".$this->_getAttrString($this->_attributes).">";
            $html .= $this->_value;
            $html .= "</TEXTAREA>";
        }
        return $html;
    } //end func toHtml
    
    /**
     * Returns the value of field without HTML tags (in this case, value is changed to a mask)
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function getFrozenHtml()
    {
        $html = "<PRE>$this->_value</PRE>";
        return $html;
    } //end func getFrozenHtml

} //end class HTML_QuickForm_textarea
?>