<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
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

require_once("HTML/QuickForm/element.php");

/**
 * HTML class for static data
 * 
 * @author       Wojciech Gdela <eltehaem@poczta.onet.pl>
 * @access       public
 */
class HTML_QuickForm_static extends HTML_QuickForm_element {
    
    // {{{ properties

    /**
     * Display text
     * @var       string
     * @access    private
     */
    var $_text = null;

    // }}}
    // {{{ constructor
    
    /**
     * Class constructor
     * 
     * @param     string    $elementLabel   (optional)Label
     * @param     string    $text           (optional)Display text
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_static($elementName=null, $elementLabel=null, $text=null)
    {
        HTML_QuickForm_element::HTML_QuickForm_element($elementName, $elementLabel);
        $this->_persistantFreeze = false;
        $this->_type = 'static';
        $this->_text = $text;
    } //end constructor
    
    // }}}
    // {{{ setName()

    /**
     * Sets the element name
     * 
     * @param     string    $name   Element name
     * @access    public
     * @return    void
     */
    function setName($name)
    {
        $this->updateAttributes(array('name'=>$name));
    } //end func setName
    
    // }}}
    // {{{ getName()

    /**
     * Returns the element name
     * 
     * @access    public
     * @return    string
     */
    function getName()
    {
        return $this->getAttribute('name');
    } //end func getName

    // }}}
    // {{{ setText()

    /**
     * Sets the text
     *
     * @param     string    $text
     * @access    public
     * @return    void
     */
    function setText($text)
    {
        $this->_text = $text;
    } // end func setText

    // }}}
    // {{{ toHtml()

    /**
     * Returns the static text element in HTML
     * 
     * @access    public
     * @return    string
     */
    function toHtml()
    {
        $tabs = $this->_getTabs();
        $html = $tabs.$this->_text;
        return $html;
    } //end func toHtml
    
    // }}}
    // {{{ getFrozenHtml()

    /**
     * Returns the value of field without HTML tags
     * 
     * @access    public
     * @return    string
     */
    function getFrozenHtml()
    {
        return $this->toHtml();
    } //end func getFrozenHtml

    // }}}

} //end class HTML_QuickForm_static
?>
