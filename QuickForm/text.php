<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
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

require_once("HTML/QuickForm/input.php");

/**
 * HTML class for a text field
 * 
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.0
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_text extends HTML_QuickForm_input {
                
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
    function HTML_QuickForm_text ($elementName=null, $value="", $attributes=null)
    {
        HTML_QuickForm_input::HTML_QuickForm_input('text', $elementName, $value, $attributes);
    } //end constructor
        
    /**
     * Sets size of text field
     * 
     * @param     string    $size  Size of password field
     * @since     1.3
     * @access    public
     * @return    void
     * @throws    
     */
    function setSize($size="")
    {
        $this->updateAttributes(array("size"=>$size));
    } //end func setSize

    /**
     * Sets maxlength of text field
     * 
     * @param     string    $maxlength  Maximum length of password field
     * @since     1.3
     * @access    public
     * @return    void
     * @throws    
     */
    function setMaxlength($maxlength="")
    {
        $this->updateAttributes(array("maxlength"=>$maxlength));
    } //end func setMaxlength
    
    
} //end class HTML_QuickForm_text
?>