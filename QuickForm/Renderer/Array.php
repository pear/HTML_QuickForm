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
    * Current element index
    * @var integer
    */
    var $_elementIdx;

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
        $elAry = $this->_elementToArray($element, $required);
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
        $this->_currentGroup = $this->_elementToArray($group, $required);
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
    * @return array
    */
    function _elementToArray(&$element, $required)
    {
        $ret = array(
            'name'      => $element->getName(),
            'value'     => $element->getValue(),
            'type'      => $element->getType(),
            'frozen'    => $element->isFrozen(),
            'label'     => $element->getLabel(),
            'required'  => $required
        );
        if ('group' == $ret['type']) {
            $ret['elements'] = array();
        } else {
            $ret['html']     = $element->toHtml();
        }
        if (empty($ret['name'])) {
            $ret['name'] = 'element_' . $this->_elementIdx;
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
        // where should we put this element...
        if (is_array($this->_currentGroup) && ('group' != $elAry['type'])) {
            $ary =& $this->_currentGroup['elements'];
        } elseif (isset($this->_currentSection)) {
            $ary =& $this->_ary['sections'][$this->_currentSection]['elements'];
        } else {
            $ary =& $this->_ary['elements'];
        }
        // should we create an array of such elements?
        if (isset($ary[$elAry['name']])) {
            if (isset($ary[$elAry['name']][0])) {
                $ary[$elAry['name']][] = $elAry;
            } else {
                $ary[$elAry['name']] = array($ary[$elAry['name']], $elAry);
            }
        } else {
            $ary[$elAry['name']] = $elAry;
        }
    }
}
?>
