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
// |          Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'HTML/QuickForm/Renderer.php';

/**
 * A concrete renderer for HTML_QuickForm, makes an array of form contents
 *
 * Based on old toArray() code.
 * 
 * The form array structure is the following:
 * array(
 *   'frozen'           => 'whether the form is frozen',
 *   'validationScript' => 'javascript for client-side validation',
 *   'attributes'       => 'attributes for <form> tag',
 *   'requiredNote      => 'note about the required elements',
 *   // if there were some validation errors:
 *   'errors' => array(
 *     '1st element name' => 'Error for the 1st element',
 *     ...
 *     'nth element name' => 'Error for the nth element'
 *   ),
 *   // if there are no headers in the form:
 *   'elements' => array(
 *     element_1,
 *     ...
 *     element_N
 *   )
 *   // if there are headers in the form:
 *   'sections' => array(
 *     array(
 *       'header'   => 'Header text for the first header',
 *       'elements' => array(
 *          element_1,
 *          ...
 *          element_K1
 *       )
 *     ),
 *     ...
 *     array(
 *       'header'   => 'Header text for the Mth header',
 *       'elements' => array(
 *          element_1,
 *          ...
 *          element_KM
 *       )
 *     )
 *   )
 * );
 * 
 * where element_i is an array of the form:
 * array(
 *   'name'      => 'element name',
 *   'value'     => 'element value',
 *   'type'      => 'type of the element',
 *   'frozen'    => 'whether element is frozen',
 *   'label'     => 'label for the element',
 *   'required'  => 'whether element is required',
 *   'error'     => 'error associated with the element',
 *   // if element is not a group
 *   'html'      => 'HTML for the element'
 *   // if element is a group
 *   'separator' => 'separator for group elements',
 *   'elements'  => array(
 *     element_1,
 *     ...
 *     element_N
 *   )
 * );
 * 
 * @access public
 */
class HTML_QuickForm_Renderer_Array extends HTML_QuickForm_Renderer
{
   /**
    * An array being generated
    * @var array
    */
    var $_ary;

   /**
    * Number of sections in the form (i.e. number of headers in it)
    * @var integer
    */
    var $_sectionCount;

   /**
    * Current section number
    * @var integer
    */
    var $_currentSection;

   /**
    * Array representing current group
    * @var array
    */
    var $_currentGroup = null;

   /**
    * Constructor
    *
    * @access public
    */
    function HTML_QuickForm_Renderer_Array()
    {
        $this->HTML_QuickForm_Renderer();
    } // end constructor


   /**
    * Returns the resultant array
    * 
    * @access public
    * @return array
    */
    function toArray()
    {
        return $this->_ary;
    }


    function startForm(&$form)
    {
        $this->_ary = array(
            'frozen'            => $form->isFrozen(),
            'validationScript'  => $form->getValidationScript(),
            'attributes'        => $form->getAttributesString(),
            'requiredNote'      => $form->getRequiredNote()
        );
        $this->_elementIdx     = 1;
        $this->_currentSection = null;
        $this->_sectionCount   = 0;
    } // end func startForm


    function renderHeader(&$header)
    {
        $this->_ary['sections'][$this->_sectionCount] = array('header' => $header->toHtml());
        $this->_currentSection = $this->_sectionCount++;
    } // end func renderHeader


    function renderElement(&$element, $required, $error)
    {
        $elAry = $this->_elementToArray($element, $required, $error);
        if (!empty($error)) {
            $this->_ary['errors'][$elAry['name']] = $error;
        }
        $this->_storeArray($elAry);
    } // end func renderElement


    function renderHidden(&$element)
    {
        $this->renderElement($element, false, null);
    } // end func renderHidden


    function startGroup(&$group, $required, $error)
    {
        $this->_currentGroup = $this->_elementToArray($group, $required, $error);
        if (!empty($error)) {
            $this->_ary['errors'][$this->_currentGroup['name']] = $error;
        }
    } // end func startGroup


    function finishGroup(&$group)
    {
        $this->_storeArray($this->_currentGroup);
        $this->_currentGroup = null;
    } // end func finishGroup


   /**
    * Creates an array representing an element
    * 
    * @access private
    * @param  object    An HTML_QuickForm_element object
    * @param  bool      Whether an element is required
    * @param  string    Error associated with the element
    * @return array
    */
    function _elementToArray(&$element, $required, $error)
    {
        $ret = array(
            'name'      => $element->getName(),
            'value'     => $element->getValue(),
            'type'      => $element->getType(),
            'frozen'    => $element->isFrozen(),
            'label'     => $element->getLabel(),
            'required'  => $required,
            'error'     => $error
        );
        if ('group' == $ret['type']) {
            $ret['separator'] = $element->_separator;
            $ret['elements']  = array();
        } else {
            $ret['html']      = $element->toHtml();
        }
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
        // where should we put this element...
        if (is_array($this->_currentGroup) && ('group' != $elAry['type'])) {
            $this->_currentGroup['elements'][] = $elAry;
        } elseif (isset($this->_currentSection)) {
            $this->_ary['sections'][$this->_currentSection]['elements'][] = $elAry;
        } else {
            $this->_ary['elements'][] = $elAry;
        }
    }
}
?>
