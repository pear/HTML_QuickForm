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
// | Authors: Alexey Borzov <borz_off@cs.msu.su>                          |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// |          Thomas Schulz <ths@4bconsult.de>                            |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'HTML/QuickForm/Renderer/Array.php';

/**
 * A static renderer for HTML_QuickForm, makes an array of form content
 * useful for an Smarty template
 *
 * Based on old toArray() code and ITStatic renderer.
 * 
 * The form array structure is the following:
 * Array (
 *  [frozen]       => whether the complete form is frozen'
 *  [javascript]   => javascript for client-side validation
 *  [attributes]   => attributes for <form> tag
 *  [hidden]       => html of all hidden elements
 *  [requirednote] => note about the required elements
 *  [errors] => Array
 *      (
 *          [1st_element_name] => Error for the 1st element
 *          ...
 *          [nth_element_name] => Error for the nth element
 *      )
 *
 *  [header] => Array
 *      (
 *          [1st_header_name] => Header text for the 1st header
 *          ...
 *          [nth_header_name] => Header text for the nth header
 *      )
 *
 *  [1st_element_name] => Array for the 1st element
 *  ...
 *  [nth_element_name] => Array for the nth element
 *
 * // where an element array has the form:
 *      (
 *          [name]      => element name
 *          [value]     => element value,
 *          [type]      => type of the element
 *          [frozen]    => whether element is frozen
 *          [label]     => label for the element
 *          [required]  => whether element is required
 * // if element is not a group:
 *          [html]      => HTML for the element
 * // if element is a group:
 *          [separator] => separator for group elements
 *          [1st_gitem_name] => Array for the 1st element in group
 *          ...
 *          [nth_gitem_name] => Array for the nth element in group
 *      )
 * )
 * 
 * @access public
 */
class HTML_QuickForm_Renderer_ArraySmarty extends HTML_QuickForm_Renderer_Array
{
   /**
    * Current element index
    * @var integer
    */
    var $_elementIdx;

   /**
    * The current element index inside a group
    * @var integer
    */
    var $_groupElementIdx = 0;

   /**
    * How to handle the required tag for required fields
    * @var string
    * @see      setRequiredTemplate()
    */
    var $_required = '';

   /**
    * How to handle error messages in form validation
    * @var string
    * @see      setErrorTemplate()
    */
    var $_error = '';

   /**
    * Constructor
    *
    * @access public
    */
    function HTML_QuickForm_Renderer_ArraySmarty()
    {
        $this->HTML_QuickForm_Renderer_Array(true);
    } // end constructor


    function renderHeader(&$header)
    {
        if ($name = $header->getName()) {
            $this->_ary['header'][$name] = $header->toHtml();
        } else {
            $this->_ary['header'][$this->_sectionCount] = $header->toHtml();
        }
        $this->_currentSection = $this->_sectionCount++;
    } // end func renderHeader


    function startGroup(&$group, $required, $error)
    {
        parent::startGroup($group, $required, $error);
        $this->_groupElementIdx = 1;
    } // end func startGroup


   /**
    * Creates an array representing an element containing
    * the key for storing this
    * 
    * @access private
    * @param  object    An HTML_QuickForm_element object
    * @param  bool      Whether an element is required
    * @param  string    Error associated with the element
    * @return array
    */
    function _elementToArray(&$element, $required, $error)
    {
        $ret = parent::_elementToArray($element, $required, $error);
        if ('group' == $ret['type']) {
            $ret['html'] = $element->toHtml();
            // we don't need this field, see the array structure
            unset($ret['elements']);
        }
        if ($required) {
            $this->_renderRequired($ret['label'], $ret['html']);
        }
        if (!empty($error)) {
            $this->_renderError($ret['label'], $ret['html'], $error);
        }
        
        // create a simple element key
        $ret['key'] = $ret['name'];
        if (strstr($ret['key'], '[')) {
            $keys = explode('_', str_replace(array('[', ']'), '_', $ret['key']));
            $ret['key'] = $keys[1];
            if (empty($ret['key'])) {
                $ret['key'] = $this->_groupElementIdx++;
            }
        } elseif (empty($ret['key'])) {
            $ret['key'] = 'element_' . $this->_elementIdx;
        }
        $this->_elementIdx++;
        return $ret;
    }


   /**
    * Stores an array representation of an element in the form array
    * 
    * @access private
    * @param array  Array representation of an element
    * @return void
    */
    function _storeArray($elAry)
    {
        $key = $elAry['key'];
        unset($elAry['key']);
        // where should we put this element...
        if (is_array($this->_currentGroup) && ('group' != $elAry['type'])) {
            $this->_currentGroup[$key] = $elAry;
        } else {
            $this->_ary[$key] = $elAry;
        }
    }


   /**
    * Called when an element is required
    *
    * This method will add the required tag to the element label and/or the element html
    * such as defined with the method setRequiredTemplate
    *
    * @param    string      The element label
    * @param    string      The element html rendering
    * @see      setRequiredTemplate()
    * @access   private
    * @return   void
    */
    function _renderRequired(&$label, &$html)
    {
        if (!empty($label) && strpos($this->_required, '{$label}') !== false) {
            $label = str_replace('{$label}', $label, $this->_required);
        }
        if (!empty($html) && strpos($this->_required, '{$html}') !== false) {
            $html = str_replace('{$html}', $html, $this->_required);
        }
    } // end func _renderRequired


   /**
    * Called when an element has a validation error
    *
    * This method will add the error message to the element label or the element html
    * such as defined with the method setErrorTemplate. If the error placeholder is not found
    * in the template, the error will be displayed in the form error block.
    *
    * @param    string      The element label
    * @param    string      The element html rendering
    * @param    string      The element error
    * @see      setErrorTemplate()
    * @access   private
    * @return   void
    */
    function _renderError(&$label, &$html, &$error)
    {
        if (!empty($label) && strpos($this->_error, '{$label}') !== false) {
            $label = str_replace(array('{$label}', '{$error}'), array($label, $error), $this->_error);
        } elseif (!empty($html) && strpos($this->_error, '{$html}') !== false) {
            $html = str_replace(array('{$html}', '{$error}'), array($html, $error), $this->_error);
        }
        $error = str_replace(
            array('{$label}', '{$html}' , '{$error}'), 
            array(''        , ''        , $error    ), 
            $this->_error
        );
    }// end func _renderError


   /**
    * Sets the way required elements are rendered
    *
    * You can use {$label} or {$html} placeholders to let the renderer know where
    * where the element label or the element html are positionned according to the
    * required tag. They will be replaced accordingly with the right value.
    * For example:
    * <span style="color: red;">*</span>{$label}
    * will put a red star in front of the label if the element is required.
    *
    * @param    string      The required element template
    * @access   public
    * @return   void
    */
    function setRequiredTemplate($template)
    {
        $this->_required = $template;
    } // end func setRequiredTemplate


   /**
    * Sets the way elements with validation errors are rendered
    *
    * You can use {$label} or {$html} placeholders to let the renderer know where
    * where the element label or the element html are positionned according to the
    * error message. They will be replaced accordingly with the right value.
    * The error message will replace the {$error} placeholder.
    * For example:
    * <span style="color: red;">{$error}</span><br />{$html}
    * will put the error message in red on top of the element html.
    *
    * If you want all error messages to be output in the main error block, use
    * the {$form.errors} part of the rendered array that collects all raw error 
    * messages.
    *
    * If you want to place all error messages manually, do not specify {$html}
    * nor {$label}.
    *
    * Groups can have special layouts. With this kind of groups, you have to 
    * place the formated error message manually. In this case, use {$form.group.error}
    * where you want the formated error message to appear in the form.
    *
    * @param    string      The element error template
    * @access   public
    * @return   void
    */
    function setErrorTemplate($template)
    {
        $this->_error = $template;
    } // end func setErrorTemplate
}
?>
