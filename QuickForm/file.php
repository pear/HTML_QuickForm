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
require_once("HTML/QuickForm/input.php");

/**
 * HTML class for a file type element
 * 
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.0
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_file extends HTML_QuickForm_input
{
    // {{{ properties

   /**
    * Uploaded file data, from $_FILES
    * @var array
    */
    var $_value = null;

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * 
     * @param     string    $elementName    Input field name attribute
     * @param     string    $elementLabel   Input field label
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_file($elementName=null, $elementLabel=null, $attributes=null)
    {
        HTML_QuickForm_input::HTML_QuickForm_input($elementName, $elementLabel, $attributes);
        $this->setType('file');
    } //end constructor
    
    // }}}
    // {{{ setSize()

    /**
     * Sets size of file element
     * 
     * @param     int    $size  Size of password field
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setSize($size)
    {
        $this->updateAttributes(array("size"=>$size));
    } //end func setSize
    
    // }}}
    // {{{ getSize()

    /**
     * Returns size of file element
     * 
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function getSize()
    {
        return $this->getAttribute("size");
    } //end func getSize

    // }}}
    // {{{ freeze()

    /**
     * Freeze the element so that only its value is returned
     * 
     * @access    public
     * @return    void
     * @throws    
     */
    function freeze()
    {
        return false;
    } //end func freeze

    // }}}
    // {{{ setValue()

    /**
     * Sets value for file element.
     * 
     * Actually this does nothing. The function is defined here to override
     * HTML_Quickform_input's behaviour of setting the 'value' attribute. As
     * no sane user-agent uses <input type="file">'s value for anything 
     * (because of security implications) we implement file's value as a 
     * read-only property with a special meaning.
     * 
     * @param     mixed    $value  Value for file element
     * @since     3.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setValue($value)
    {
        return null;
    } //end func setValue
    
    // }}}
    // {{{ getValue()

    /**
     * Returns information about the uploaded file
     *
     * @since     3.0
     * @access    public
     * @return    array
     * @throws    
     */
    function getValue()
    {
        return $this->_value;
    } // end func getValue

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
                if ($caller->getAttribute('method') == 'get') {
                    $caller->_submitValues = $GLOBALS['_POST'];
                }
                $this->_value = $this->_findValue($caller->_submitFiles);
                $caller->updateAttributes(array('method' => 'post', 'enctype' => 'multipart/form-data'));
                $caller->setMaxFileSize();
                break;
            case 'addElement':
                $this->onQuickFormEvent('createElement', $arg, $caller);
                $this->onQuickFormEvent('updateValue', null, $caller);
                break;
            case 'createElement':
                $className = get_class($this);
                $this->$className($arg[0], $arg[1], $arg[2]);
                break;
        }
        return true;
    } // end func onQuickFormEvent

    // }}}

} // end class HTML_QuickForm_file
?>