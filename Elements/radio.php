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

require_once("HTML_QuickForm/Elements/input.php");

/**
 * HTML class for a radio type element
 * 
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.0
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_radio extends HTML_QuickForm_input {

    /**
     * Default label of the field
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_label = "";

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
    function HTML_QuickForm_radio ($elementName=null, $value=null, $attributes=null)
    {
        HTML_QuickForm_input::HTML_QuickForm_input('radio', $elementName, $value, $attributes);
    } //end constructor
    
    /**
     * Sets whether radio button is checked
     * 
     * @param     bool    $checked  Whether the field is checked or not
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setChecked($checked)
    {
        if (!$checked) {
            $this->removeAttribute("checked");
        } else {
            $this->updateAttributes(array("checked"));
        }
    } //end func setChecked

    /**
     * Returns whether radio button is checked
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function getChecked()
    {
        return $this->getAttribute("checked");
    } //end func getChecked
    
    /**
     * Sets display text (label) for radio button
     * 
     * @param     string    $label  Display text for radio button
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setLabel($label)
    {
        $this->_label = $label;
    } //end func setLabel
    
    /**
     * Returns the radio element in HTML
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function toHtml()
    {
        return HTML_QuickForm_input::toHtml() . " " . $this->_label;
    } //end func toHtml
    
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
        $checked = $this->getChecked();
        if ($checked) {
            $html = "(x)";
        } else {
            $html = "( )";
        }
        return $html;
    } //end func getFrozenHtml

} //end class HTML_QuickForm_checkbox
?>