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

require_once("HTML/QuickForm/element.php");

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

    // {{{ properties

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
     * String to seperator elements
     * @var       string
     * @since     1.1
     * @access    private
     */
    var $_seperator = "";

    // }}}
    // {{{ constructor

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
    function HTML_QuickForm_group($elementName=null, $elementLabel=null, $elements=null, $seperator=null)
    {
        HTML_Common::HTML_Common();
        if (isset($elementName)) {
            $this->setName($elementName);
        }
        if (isset($elementLabel)) {
            $this->setLabel($elementLabel);
        }
        $vars = array_merge($GLOBALS['HTTP_GET_VARS'], $GLOBALS['HTTP_POST_VARS']);
        if (isset($vars[$this->getName()])) {
            $submitValue = (is_string($vars[$this->getName()])) ? 
                stripslashes($vars[$this->getName()]) : $vars[$this->getName()];
            $this->setValue($submitValue);
        }
        $this->_type = 'group';
        if (isset($elements) && is_array($elements)) {
            $this->_elements = $elements;
        }
        if (isset($seperator)) {
            $this->_seperator = $seperator;
        }
    } //end constructor
    
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
        $this->_name = $name;
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
        return $this->_name;
    } //end func getName

    // }}}
    // {{{ setValue()

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
        return $this->_value;
    } // end func getValue

    // }}}
    // {{{ setElements()

    /**
     * Sets the grouped elements
     *
     * @param     array     $elements   Array of elements
     * @since     1.1
     * @access    public
     * @return    void
     * @throws    
     */
    function setElements($elements)
    {
        $this->_elements = $elements;
    } // end func setElements

    // }}}
    // {{{ toHtml()

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
        $html = '';
        $name = $this->getName();
        $value = $this->getValue();
        foreach ($this->_elements as $key => $element) {
            if (PEAR::isError($element)) {
                return $element;
            }
            $elementName = $element->getName();
            $index = (!empty($elementName)) ? $elementName : $key;
            
            $elementType = $element->getType();
            if (!empty($name) && isset($elementName)) {
                $element->setName($name . "[$elementName]");
            } elseif (!empty($name)) {
                $element->setName($name);
            }
            if (is_array($value)) {
                if (isset($value[$index])) {
                    $element->onQuickFormEvent('setGroupValue', $value[$index], $this);
                }
            } elseif (isset($value)) {
                $element->onQuickFormEvent('setGroupValue', $value, $this);
            }
            if ($this->_flagFrozen) {
                $element->freeze();
            }
            $element->_tabOffset = $this->_tabOffset;
            $html[] = $element->toHtml();
        }
        $html = join($this->_seperator, $html);
        return $html;
    } //end func toHtml
    
    // }}}
    // {{{ getFrozenHtml()

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
    } // end func onQuickFormEvent

    // }}}
} //end class HTML_QuickForm_group
?>