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
    var $_name = '';

    /**
     * Array of grouped elements
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_elements = array();

    /**
     * String to seperator elements
     * @var       mixed
     * @since     2.5
     * @access    private
     */
    var $_seperator = null;

    /**
     * Group template
     * @var       string
     * @since     2.5
     * @access    private
     */
    var $_groupTemplate = '';

    /**
     * Grouped element template
     * @var       string
     * @since     2.5
     * @access    private
     */
    var $_elementTemplate = '';

    /**
     * Required elements in this group
     * @var       array
     * @since     2.5
     * @access    private
     */
    var $_required = array();

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Group name
     * @param     array     $elementLabel   (optional)Group label
     * @param     array     $elements       (optional)Group elements
     * @param     mixed     $seperator      (optional)Use a string for one seperator,
     *                                      use an array to alternate the seperators,
     *                                      don't use seperator if you are using templates.
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
    // {{{ getElements()

    /**
     * Gets the grouped elements
     *
     * @since     2.4
     * @access    public
     * @return    array
     */
    function &getElements()
    {
        return $this->_elements;
    } // end func getElements

    // }}}
    // {{{ getGroupType()

    /**
     * Gets the group type based on its elements
     * Will return 'mixed' if elements contained in the group
     * are of different types.
     *
     * @access    public
     * @return    string    group elements type
     * @throws    
     */
    function getGroupType()
    {
        $prevType = '';
        foreach ($this->_elements as $element) {
            $type = $element->getType();
            if ($type != $prevType) {
                return 'mixed';
            }
            $prevType = $type;
        }
        return $type;
    } // end func getGroupType

    // }}}
    // {{{ toHtml()

    /**
     * Returns the input field in HTML
     * 
     * @since       1.0
     * @access      public
     * @return      string
     * @throws    
     */
    function toHtml()
    {
        $html = '';
        $htmlArr = array();
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
                $element->setName($name . '['.$elementName.']');
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
            $required = in_array($element->getName(), $this->_required);
            if ($this->_elementTemplate != '') {
                $htmlArr[] = $this->_wrapElement($element->getLabel(), $element->toHtml(), $required);
            } else {
                $htmlArr[] = $element->toHtml(); 
            }
        }
        if ($this->_groupTemplate != '') {
            $html = $this->_wrapGroup($htmlArr);
        } elseif (is_array($this->_seperator)) {
            $count = count($this->_seperator);
            $i = 0;
            foreach ($htmlArr as $content) {
                if ($i >= $count)
                    $i = 0;
                $html .= $content.$this->_seperator[$i];
                $i++;
            }
            $html = substr($html, 0, -(strlen($this->_seperator[$i-1])));
        } else {
            if (is_null($this->_seperator)) {
                $this->_seperator = '&nbsp;';
            }
            $html = implode((string)$this->_seperator, $htmlArr);
        }
        return $html;
    } //end func toHtml
    
    // }}}
    // {{{ setElementTemplate()

    /**
     * Sets the template for the group elements
     * 
     * @param     string     $template   Template string
     * @since     2.5
     * @access    public
     * @return    void
     */
    function setElementTemplate($template)
    {
        $this->_elementTemplate = $template;
    } //end func setElementTemplate

    // }}}
    // {{{ setGroupTemplate()

    /**
     * Sets the template for the group
     * 
     * @param     string     $template   Template string
     * @since     2.5
     * @access    public
     * @return    void
     */
    function setGroupTemplate($template)
    {
        $this->_groupTemplate = $template;
    } //end func setGroupTemplate

    // }}}
    // {{{ _wrapElement()

    /**
     * Create the formatted html for a group element
     * 
     * @param     string     $label     Label of the element if any
     * @param     string     $raw       Raw html of the element
     * @param     bool       $require   Is element required ?
     * @since     2.5
     * @access    public
     * @return    string
     * @throws    
     */
    function _wrapElement($label, $raw, $required)
    {
        $html = '';
        $html = str_replace('{label}', $label, $this->_elementTemplate);
        if ($required) {
            $html = str_replace('<!-- BEGIN required -->', '', $html);
            $html = str_replace('<!-- END required -->', '', $html);
        } else {
            $html = preg_replace("/([ \t\n\r]*)?<!-- BEGIN required -->(\s|\S)*<!-- END required -->([ \t\n\r]*)?/i", '', $html);
        }
        $html = str_replace('{element}', $raw, $html);
        return $html;
    } //end func _wrapElement

    // }}}
    // {{{ _wrapGroup()

    /**
     * Create the formatted html for the group
     * 
     * @param     array     $htmlArr    Array of formatted html for each elements in group
     * @since     2.5
     * @access    public
     * @return    string
     * @throws    
     */
    function _wrapGroup($htmlArr)
    {
        $html = '';
        $content = implode('', $htmlArr);
        $html = str_replace('{content}', $content, $this->_groupTemplate);
        return $html;
    } //end func _wrapGroup

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
    function onQuickFormEvent($event, $arg, &$callerLocal)
    {
        global $caller;
        // make it global so we can access it in any of the other methods if needed
        $caller =& $callerLocal;

        $className = get_class($this);
        switch ($event) {
            case 'addElement':
            case 'createElement':
                $this->$className($arg[0], $arg[1], $arg[2], $arg[3]);
                // need to set the submit value in case setDefault never gets called
                $elementName = $this->getName();
                if (isset($caller->_submitValues[$elementName])) {
                    $value = $caller->_submitValues[$elementName];
                    if (is_string($value) && get_magic_quotes_gpc() == 1) {
                        $value = stripslashes($value);
                    }
                    $this->setValue($value);
                }
                break;
            case 'setDefault':
                // In form display, default value is always overidden by submitted value
                $elementName = $this->getName();
                if (isset($caller->_submitValues[$elementName])) {
                    $value = $caller->_submitValues[$elementName];
                    if (is_string($value) && get_magic_quotes_gpc() == 1) {
                        $value = stripslashes($value);
                    }
                } else {
                    if (count($caller->_submitValues) > 0) {
                        // Form has been submitted and value was not set
                        $value = null;
                    } else {
                        $value = $arg;
                    }
                }
                $this->setValue($value);
                break;
            case 'setConstant':
                // In form display, constant value overides submitted value
                // but submitted value is kept in _submitValues array
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