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
     * String to separate elements
     * @var       mixed
     * @since     2.5
     * @access    private
     */
    var $_separator = null;

    /**
     * Required elements in this group
     * @var       array
     * @since     2.5
     * @access    private
     */
    var $_required = array();

   /**
    * Whether to change elements' names to $groupName[$elementName] or leave them as is 
    * @var      bool
    * @since    3.0
    * @access   private
    */
    var $_appendName = true;

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Group name
     * @param     array     $elementLabel   (optional)Group label
     * @param     array     $elements       (optional)Group elements
     * @param     mixed     $separator      (optional)Use a string for one separator,
     *                                      use an array to alternate the separators.
     * @param     bool      $appendName     (optional)whether to change elements' names to
     *                                      the form $groupName[$elementName] or leave 
     *                                      them as is.
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_group($elementName=null, $elementLabel=null, $elements=null, $separator=null, $appendName = true)
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
        if (isset($separator)) {
            $this->_separator = $separator;
        }
        if (isset($appendName)) {
            $this->_appendName = $appendName;
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
        if ($this->_appendName) {
            $this->_value = $value;
        } else {
            foreach (array_keys($this->_elements) as $key) {
                $v = $this->_elements[$key]->_findValue($value);
                if (null !== $v) {
                    $this->_elements[$key]->onQuickFormEvent('setGroupValue', $v, $this);
                }
            }
        }
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
     * Returns Html for the group
     * 
     * @since       1.0
     * @access      public
     * @return      string
     */
    function toHtml()
    {
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer =& new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        $this->accept($renderer);
        return $renderer->toHtml();
    } //end func toHtml
    
    // }}}
    // {{{ setElementTemplate()

    /**
     * Sets the template for the group elements
     * 
     * @param     string     $template   Template string
     * @since     2.5
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setElementTemplate($template)
    {
        $renderer =& HTML_QuickForm::defaultRenderer();
        $renderer->setGroupElementTemplate($template, $this->_name);
    } //end func setElementTemplate

    // }}}
    // {{{ setGroupTemplate()

    /**
     * Sets the template for the group
     * 
     * @param     string     $template   Template string
     * @since     2.5
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setGroupTemplate($template)
    {
        $renderer =& HTML_QuickForm::defaultRenderer();
        $renderer->setGroupTemplate($template, $this->_name);
    } //end func setGroupTemplate

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
        switch ($event) {
            case 'updateValue':
                if ($this->_appendName) {
                    parent::onQuickFormEvent('updateValue', $arg, $caller);
                } else {
                    foreach (array_keys($this->_elements) as $key) {
                        $this->_elements[$key]->onQuickFormEvent('updateValue', $arg, $caller);
                    }
                }
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    } // end func onQuickFormEvent

    // }}}
    // {{{ accept()

   /**
    * Accepts a renderer
    *
    * @param object     An HTML_QuickForm_Renderer object
    * @param bool       Whether a group is required
    * @param string     An error message associated with a group
    * @access public
    * @return void 
    */
    function accept(&$renderer, $required = false, $error = null)
    {
        $renderer->startGroup($this, $required, $error);
        $name  = $this->getName();
        $value = $this->getValue();
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            if (PEAR::isError($element)) {
                return $element;
            }
            
            if ($this->_appendName) {
                $elementName = $element->getName();
                $index       = (!empty($elementName)) ? $elementName : $key;
                if (isset($elementName)) {
                    $element->setName($name . '['.$elementName.']');
                } else {
                    $element->setName($name);
                }
                if (is_array($value)) {
                    if (isset($value[$index])) {
                        $element->onQuickFormEvent('setGroupValue', $value[$index], $this);
                    }
                } elseif (isset($value)) {
                    $element->onQuickFormEvent('setGroupValue', $value, $this);
                }
            }

            if ($this->_flagFrozen) {
                $element->freeze();
            }
            $required = in_array($element->getName(), $this->_required);

            $element->accept($renderer, $required);

            // restore the element's name
            if ($this->_appendName) {
                $element->setName($elementName);
            }
        }
        $renderer->finishGroup($this);
    } // end func accept

    // }}}

} //end class HTML_QuickForm_group
?>