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

require_once('HTML/Common.php');

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

    // {{{ properties

    /**
     * Label of the field
     * @var       string
     * @since     1.3
     * @access    private
     */
    var $_label = '';

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
     * Does the element support persistant data when frozen
     * @var       boolean
     * @since     1.3
     * @access    private
     */
    var $_persistantFreeze = false;
    
    // }}}
    // {{{ constructor
    
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
    function HTML_QuickForm_element($elementName=null, $elementLabel=null, $attributes=null)
    {
        HTML_Common::HTML_Common($attributes);
        if (isset($elementName)) {
            $this->setName($elementName);
        }
        if (isset($elementLabel)) {
            $this->setLabel($elementLabel);
        }
        $vars = array_merge($GLOBALS['HTTP_GET_VARS'], $GLOBALS['HTTP_POST_VARS']);
        if (isset($vars[$this->getName()])) {
            if (is_string($vars[$this->getName()]) && get_magic_quotes_gpc() == 1) {
                $submitValue = stripslashes($vars[$this->getName()]);
            } else {
                $submitValue = $vars[$this->getName()];
            }
            $this->setValue($submitValue);
        }
    } //end constructor
    
    // }}}
    // {{{ apiVersion()

    /**
     * Returns the current API version
     *
     * @since     1.0
     * @access    public
     * @return    float
     */
    function apiVersion()
    {
        return 2.0;
    } // end func apiVersion

    // }}}
    // {{{ getType()

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

    // }}}
    // {{{ setName()

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
    
    // }}}
    // {{{ getName()

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
    
    // }}}
    // {{{ setValue()

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
        // interface
    } // end func setValue

    // }}}
    // {{{ getValue()

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
        // interface
        return null;
    } // end func getValue
    
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
        $this->_flagFrozen = true;
    } //end func freeze

    // }}}
    // {{{ getFrozenHtml()

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
        $value = $this->getValue();
        if (!empty($value)) {
            $html = $value;
        } else {
            $html = '&nbsp;';
        }
        if ($this->_persistantFreeze) {
            $html .= '<input type="hidden" name="' . 
                $this->getName() . '" value="' . $value . '" />';
        }
        return $html;
    } //end func getFrozenHtml
    
    // }}}
    // {{{ isFrozen()

    /**
     * Returns whether or not the element is frozen
     *
     * @since     1.3
     * @access    public
     * @return    void
     * @throws    
     */
    function isFrozen()
    {
        return $this->_flagFrozen;
    } // end func isFrozen

    // }}}
    // {{{ setPersistantFreeze()

    /**
     * Sets wether an element value should be kept in an hidden field
     * when the element is frozen or not
     * 
     * @param     bool    $persistant   True if persistant value
     * @since     2.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setPersistantFreeze($persistant=false)
    {
        $this->_persistantFreeze = $persistant;
    } //end func setPersistantFreeze

    // }}}
    // {{{ toArray()

    /**
     * Returns the element as an array
     *
     * @since     1.1
     * @access    public
     * @return    array
     * @throws    
     */
    function toArray()
    {
        $arr = array();
        $arr['html'] = $this->toHtml();
        $arr['value'] = $this->getValue();
        $arr['type'] = $this->getType();
        $arr['frozen'] = $this->_flagFrozen;
        $arr['label'] = $this->getLabel();
        return $arr;
    } // end func toArray

    // }}}
    // {{{ setLabel()

    /**
     * Sets display text for the element
     * 
     * @param     string    $label  Display text for a checkbox
     * @since     1.3
     * @access    public
     * @return    void
     * @throws    
     */
    function setLabel($label)
    {
        $this->_label = $label;
    } //end func setLabel

    // }}}
    // {{{ getLabel()

    /**
     * Returns display text for the element
     * 
     * @since     1.3
     * @access    public
     * @return    string
     * @throws    
     */
    function getLabel()
    {
        return $this->_label;
    } //end func getLabel

    // }}}
    // {{{ onQuickFormEvent()

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string    $event  Name of event
     * @param     mixed     $arg    event arguments
     * @param     object    $callerLocal calling object
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function onQuickFormEvent($event, $arg, &$callerLocal)
    {
        global $caller;
        // make it global so we can access it in any of the other methods if needed
        $caller = $callerLocal;
        $className = get_class($this);
        switch ($event) {
            case 'addElement':
            case 'createElement':
                $this->$className($arg[0], $arg[1], $arg[2], $arg[3]);
                break;
            case 'setDefault':
                $vars = array_merge($GLOBALS['HTTP_GET_VARS'], $GLOBALS['HTTP_POST_VARS']);
                if (!isset($vars[$this->getName()])) {
                    $this->setValue($arg);
                }
                break;
            case 'setConstant':
                $this->setValue($arg);
                break;
            case 'setGroupValue':
                $this->setValue($arg);
            break;
        }
        return true;
    } // end func onQuickFormLoad

    // }}}

} // end class HTML_QuickForm_element
?>