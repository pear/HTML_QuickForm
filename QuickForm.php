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

require_once('PEAR.php');
require_once('HTML/Common.php');

$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'] = 
        array(
            'group'         =>array('HTML/QuickForm/group.php','HTML_QuickForm_group'),
            'hidden'        =>array('HTML/QuickForm/hidden.php','HTML_QuickForm_hidden'),
            'reset'         =>array('HTML/QuickForm/reset.php','HTML_QuickForm_reset'),
            'checkbox'      =>array('HTML/QuickForm/checkbox.php','HTML_QuickForm_checkbox'),
            'file'          =>array('HTML/QuickForm/file.php','HTML_QuickForm_file'),
            'image'         =>array('HTML/QuickForm/image.php','HTML_QuickForm_image'),
            'password'      =>array('HTML/QuickForm/password.php','HTML_QuickForm_password'),
            'radio'         =>array('HTML/QuickForm/radio.php','HTML_QuickForm_radio'),
            'button'        =>array('HTML/QuickForm/button.php','HTML_QuickForm_button'),
            'submit'        =>array('HTML/QuickForm/submit.php','HTML_QuickForm_submit'),
            'select'        =>array('HTML/QuickForm/select.php','HTML_QuickForm_select'),
            'hiddenselect'  =>array('HTML/QuickForm/hiddenselect.php','HTML_QuickForm_hiddenselect'),
            'text'          =>array('HTML/QuickForm/text.php','HTML_QuickForm_text'),
            'textarea'      =>array('HTML/QuickForm/textarea.php','HTML_QuickForm_textarea'),
            'link'          =>array('HTML/QuickForm/link.php','HTML_QuickForm_link'),
            'advcheckbox'   =>array('HTML/QuickForm/advcheckbox.php','HTML_QuickForm_advcheckbox')
        );

// {{{ error codes

/*
 * Error codes for the QuickForm interface, which will be mapped to textual messages
 * in the QuickForm::errorMessage() function.  If you are to add a new error code, be
 * sure to add the textual messages to the QuickForm::errorMessage() function as well
 */

define('QUICKFORM_OK',                      1);
define('QUICKFORM_ERROR',                  -1);
define('QUICKFORM_INVALID_RULE',           -2);
define('QUICKFORM_NONEXIST_ELEMENT',       -3);
define('QUICKFORM_INVALID_FILTER',         -4);
define('QUICKFORM_UNREGISTERED_ELEMENT',   -5);

// }}}

/**
* Create, validate and process HTML forms
*
* @author      Adam Daniel <adaniel1@eesus.jnj.com>
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @version     2.0
* @since       PHP 4.0.3pl1
*/
class HTML_QuickForm extends HTML_Common {
    // {{{ properties

    /**
     * Array containing the form fields
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_elements = array();

    /**
     * Array containing element name to index map
     * @since     1.1
     * @var  array
     * @access   private
     */
    var $_elementIndex = array();

    /**
     * Array containing required field IDs
     * @since     1.0
     * @var  array
     * @access   private
     */ 
    var $_required = array();

    /**
     * Prefix message in javascript alert if error
     * @since     1.0
     * @var  string
     * @access   public
     */ 
    var $_jsPrefix = 'Invalid information entered.';

    /**
     * Postfix message in javascript alert if error
     * @since     1.0
     * @var  string
     * @access   public
     */ 
    var $_jsPostfix = 'Please correct these fields.';

    /**
     * Array of default form values
     * @since     2.0
     * @var  array
     * @access   private
     */
    var $_defaultValues = array();

    /**
     * Array of constant form values
     * @since     2.0
     * @var  array
     * @access   private
     */
    var $_constantValues = array();

    /**
     * Array of submitted form values
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_submitValues = array();

    /**
     * Array of submitted form files
     * @since     1.0
     * @var  integer
     * @access   public
     */
    var $_submitFiles = array();

    /**
     * Value for maxfilesize hidden element if form contains file input
     * @since     1.0
     * @var  integer
     * @access   public
     */
    var $_maxFileSize = 1048576; // 1 Mb = 1048576

    /**
     * Flag to know if all fields are frozen
     * @since     1.0
     * @var  boolean
     * @access   private
     */
    var $_freezeAll = false;

    /**
     * Array containing the form rules
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_rules = array();

    /**
     * Array containing the form filters
     * @since     2.0
     * @var  array
     * @access   private
     */
    var $_filters = array();

    /**
     * Array containing the validation errors
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_errors = array();

    /**
     * Note for required fields in the form
     * @var       string
     * @since     1.0
     * @access    public
     */
    var $_requiredNote = '<font size="1" color="#FF0000">*</font><font size="1"> denotes required field</font>';

    /**
     * Array of registered element types
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_registeredTypes = array();

    /**
     * Array of registered element types
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_registeredRules = 
        array(
            'required'      =>array('regex', '/(\s|\S)/'),
            'maxlength'     =>array('regex', '/^(\s|\S){0,%data%}$/'),
            'minlength'     =>array('regex', '/^(\s|\S){%data%,}$/'),
            'rangelength'   =>array('regex', '/^(\s|\S){%data%}$/'),
            'regex'         =>array('regex', '%data%'),
            'email'         =>array('regex', '/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?)$/'),
            'emailorblank'  =>array('regex', '/(^$)|(^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?)$)/'),
            'lettersonly'   =>array('regex', '/^[a-zA-Z]*$/'),
            'alphanumeric'  =>array('regex', '/^[a-zA-Z0-9]*$/'),
            'numeric'       =>array('regex', '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/'),
            'nopunctuation' =>array('regex', '/^[^().\/\*\^\?#!@$%+=,\"\'><~\[\]{}]*$/'),
            'nonzero'       =>array('regex', '/^[1-9][0-9]*/'),
            'uploadedfile'  =>array('function', '_ruleIsUploadedFile'),
            'maxfilesize'   =>array('function', '_ruleCheckMaxFileSize'),
            'mimetype'      =>array('function', '_ruleCheckMimeType'),
            'filename'      =>array('function', '_ruleCheckFileName')
        );

    /**
     * Array of registered filters types
     * @var       array
     * @since     2.0
     * @access    private
     */
    var $_registeredFilters = 
        array(
            'trim'          =>'_filterTrim',
            'intval'        =>'_filterIntval',
            'strval'        =>'_filterStrval',
            'doubleval'     =>'_filterDoubleval',
            'boolval'       =>'_filterBoolval',
            'stripslashes'  =>'_filterStripslashes',
            'addslashes'    =>'_filterAddslashes'
        );

    /**
     * Header Template string
     * @var       string
     * @since     2.0
     * @access    private
     */
    var $_headerTemplate = 
        "\n\t<tr>\n\t\t<td nowrap=\"nowrap\" align=\"left\" valign=\"top\" colspan=\"2\" bgcolor=\"#CCCCCC\"><b>{header}</b></td>\n\t</tr>";

    /**
     * Element template string
     * @var       string
     * @since     2.0
     * @access    private
     */
    var $_elementTemplate = 
        "\n\t<tr>\n\t\t<td align=\"right\" valign=\"top\"><!-- BEGIN required --><font color=\"red\">*</font><!-- END required --><b>{label}</b></td>\n\t\t<td nowrap=\"nowrap\" valign=\"top\" align=\"left\"><!-- BEGIN error --><font color=\"#FF0000\">{error}</font><br><!-- END error -->\t{element}</td>\n\t</tr>";

    /**
     * Form template string
     * @var       string
     * @since     2.0
     * @access    private
     */
    var $_formTemplate = 
        "\n<table border=\"0\">\n\t<form{attributes}>{content}\n\t</form>\n</table>";

    /**
     * Required Note template string
     * @var       string
     * @since     2.0
     * @access    private
     */
    var $_requiredNoteTemplate = 
        "\n\t<tr>\n\t\t<td></td>\n\t<td align=\"left\" valign=\"top\">{requiredNote}</td>\n\t</tr>";

    /**
     * Array containing the templates for customised elements
     * @since     2.3
     * @var       array
     * @access    private
     */
    var $_templates = array();

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * @param    string      $formName          Form's name.
     * @param    string      $method            (optional)Form's method defaults to 'POST'
     * @param    string      $action            (optional)Form's action
     * @param    string      $target            (optional)Form's target defaults to '_self'
     * @param    array       $attributes        (optional)Associative array of form tag extra attributes
     * @access   public
     */
    function HTML_QuickForm($formName='', $method='POST', $action='', $target='_self', $attributes=null)
    {
        HTML_Common::HTML_Common($attributes);
        $method = (strtoupper($method) == 'GET') ? 'GET' : 'POST';
        $action = ($action == '') ? $_SERVER['PHP_SELF'] : $action;
        $this->updateAttributes(array('action'=>$action, 'method'=>$method, 'name'=>$formName, 'target'=>$target));
        $this->_registeredTypes = &$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'];
        $this->_submitValues = $GLOBALS['_' . $method];
        $this->_submitFiles = $_FILES;
    } // end constructor

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
    // {{{ registerElementType()

    /**
     * Registers a new element type
     *
     * @param     string    $typeName   Name of element type
     * @param     string    $include    Include path for element type
     * @param     string    $className  Element class name
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function registerElementType($typeName, $include, $className)
    {
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][strtolower($typeName)] = array($include, $className);
    } // end func registerElementType

    // }}}
    // {{{ registerRule()

    /**
     * Registers a new validation rule
     *
     * @param     string    $ruleName   Name of validation rule
     * @param     string    $type       Either: 'regex' or 'function'
     * @param     string    $data1      Name of function or regular expression
     * @param     string    $data2      Object parent of above function
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function registerRule($ruleName, $type, $data1, $data2=null)
    {
        $this->_registeredRules[$ruleName] = array($type, $data1, $data2);
    } // end func registerRule

    // }}}
    // {{{ registerFilter()

    /**
     * Registers a new filter
     *
     * @param     string    $filterName   Name of filter
     * @param     string    $filterFunc   Associated function or method
     * @since     2.0
     * @access    public
     * @return    void
     * @throws    
     */
    function registerFilter($filterName, $filterFunc)
    {
        $this->_registeredFilters[$filterName] = $filterFunc;
    } // end func registerFilter

    // }}}
    // {{{ elementExists()

    /**
     * Returns true if element is in the form
     *
     * @param     string   $element         form name of element to check
     * @since     1.0
     * @access    public
     * @return    boolean
     * @throws    
     */
    function elementExists($element=null)
    {
        return isset($this->_elementIndex[$element]);
    } // end func elementExists

    // }}}
    // {{{ setDefaults()

    /**
     * Initializes default form values
     *
     * @param     array    $defaultValues       values used to fill the form
     * @param     mixed    $filter              (optional) filter(s) to apply to all default values
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setDefaults($defaultValues=null, $filter=null)
    {
        if (is_array($defaultValues)) {
            if (isset($filter)) {
                if (is_array($filter)) {
                    foreach ($filter as $val) {
                        $defaultValues = $this->_recursiveFilter($val, $defaultValues);
                    }
                } else {
                    $defaultValues = $this->_recursiveFilter($filter, $defaultValues);
                }
            }
            foreach ($defaultValues as $key => $value) {
                $this->_defaultValues[$key] = $value;
            }
        }
    } // end func setDefaults

    // }}}
    // {{{ setConstants()

    /**
     * Initializes constant form values.
     * These values won't get overridden by POST or GET vars
     *
     * @param     array   $constantValues        values used to fill the form    
     * @param     mixed    $filter              (optional) filter(s) to apply to all default values    
     *
     * @since     2.0
     * @access    public
     * @return    void
     */
    function setConstants($constantValues=null, $filter=null)
    {
        if (is_array($constantValues)) {
            if (isset($filter)) {
                if (is_array($filter)) {
                    foreach ($filter as $val) {
                        $constantValues = $this->_recursiveFilter($val, $constantValues);
                    }
                } else {
                    $constantValues = $this->_recursiveFilter($filter, $constantValues);
                }
            }
            foreach ($constantValues as $key => $value) {
                $this->_constantValues[$key] = $value;
            }
        }
    } // end func setConstants

    // }}}
    // {{{ moveUploadedFile()

    /**
     * Moves an uploaded file into the destination 
     * @param    string  $element  
     * @param    string  $dest
     * @since     1.0
     * @access   public
     */
    function moveUploadedFile($element, $dest, $fileName='')
    {
        $file = $this->_submitFiles[$element];
        if ($dest != ''  && substr($dest, -1) != '/')
            $dest .= '/';
        $fileName = ($fileName != '') ? $fileName : $file['name'];
        if (copy($file['tmp_name'], $dest . $fileName)) {
            @unlink($file['tmp_name']);
            return true;
        } else {
            return false;
        }
    } // end func moveUploadedFile
    
    // }}}
    // {{{ &createElement()

    /**
     * Returns a new form element of the given type
     *
     * @param    string     $elementType    type of element to add (text, textarea, file...)
     * @param    string     $elementName    form name of this element
     * @param    mixed      $mixed          (optional)value of this element
     * @param    string     $elementLabel   (optional)label of this element
     * @param    array      $attributes     (optional)associative array with extra attributes (can be html or custom)
     * @since     1.0
     * @access    public
     * @return    object extended class of HTML_element
     * @throws    
     */
    function &createElement($elementType)
    {
        $args = func_get_args();
        $elementObject = &HTML_QuickForm::_loadElement('createElement', $elementType, array_slice($args, 1));
        return $elementObject;
    } // end func createElement
    
    // }}}
    // {{{ _loadElement()

    /**
     * Returns a form element of the given type
     *
     * @param     string   $event 
     * @param     string   $type 
     * @param     array    $args 
     * @since     2.0
     * @access    private
     * @return    element object
     * @throws    
     */
    function _loadElement($event, $type, $args)
    {
        $type = strtolower($type);
        if (!HTML_QuickForm::isTypeRegistered($type)) {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$type' does not exist in HTML_QuickForm::_loadElement()", 'HTML_QuickForm_Error', true);
        }
        $className = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][1];
        $includeFile = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][0];
        include_once $includeFile;
        $elementObject = new $className();
        foreach (range(0, 4) as $i) {
            if (!isset($args[$i])) {
                $args[$i] = null;
            }
        }
        $err = $elementObject->onQuickFormEvent($event, $args, $this);
        if ($err != true) {
            return $err;
        }
        return $elementObject;
    } // end func _loadElement

    // }}}
    // {{{ addElement()

    /**
     * Adds an element into the form
     *
     * @param    string     $element        element object or type of element to add (text, textarea, file...)
     * @since    1.0
     * @return   index of element 
     * @access   public
     */
    function addElement($element)
    {
        if (is_object($element) && is_subclass_of($element, 'html_quickform_element')) {
           $elementObject = &$element;
        } else {
            $args = func_get_args();
            $elementObject = &$this->_loadElement('addElement', $element, array_slice($args, 1));
            if (PEAR::isError($elementObject)) {
                return $elementObject;
            }
        }
        $elementName = $elementObject->getName();
        $elementLabel = $elementObject->getLabel();
        if (isset($this->_defaultValues[$elementName])) {
            $elementObject->onQuickFormEvent('setDefault', $this->_defaultValues[$elementName], $this);
        }
        if (isset($this->_constantValues[$elementName])) {
            $elementObject->onQuickFormEvent('setConstant', $this->_constantValues[$elementName], $this);
        }
        $this->_elements[] = $elementObject;
        end($this->_elements);
        $index = key($this->_elements);
        $this->_elementIndex[$elementName] = $index;

        return $index;
    } // end func addElement
    
    // }}}
    // {{{ addElementGroup()

    /**
     * Adds an element group
     * @param    array      $elements       array of elements composing the group
     * @param    string     $label          (optional)group label
     * @param    string     $name           (optional)group name
     * @param    string     $separator      (optional)string to seperate elements
     * @return   index of element 
     * @since     1.0
     * @access   public
     * @throws   PEAR_Error
     */
    function addElementGroup($elements, $groupLabel='', $name=null, $separator=null)
    {
        return $this->addElement('group', $name, $groupLabel, $elements, $separator);
    } // end func addElementGroup
    
    // }}}
    // {{{ &getElement()

    /**
     * Returns a reference to the element
     *
     * @param     string     $element    Element name
     * @since     2.0
     * @access    public
     * @return    reference to element
     * @throws    
     */
    function &getElement($element)
    {
        if (isset($this->_elementIndex[$element])) {
            return $this->_elements[$this->_elementIndex[$element]];
        } else {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElement()", 'HTML_QuickForm_Error', true);
        }
    } // end func getElement

    // }}}
    // {{{ &getElementValue()

    /**
     * Returns the elements raw value such as submitted by the form
     *
     * @param     string     $element    Element name
     * @since     2.0
     * @access    public
     * @return    element value
     * @throws    
     */
    function &getElementValue($element)
    {
        if (isset($this->_elementIndex[$element])) {
            return $this->_elements[$this->_elementIndex[$element]]->getValue();
        } else {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElementValue()", 'HTML_QuickForm_Error', true);
        }
    } // end func getElementValue

    // }}}
    // {{{ getSubmitValue()

    /**
     * Returns the elements value after submit and filter
     *
     * @param     string     $element    Element name
     * @since     2.0
     * @access    public
     * @return    submitted element value or null if not set
     * @throws    
     */    
    function getSubmitValue($element)
    {
        if (isset($this->_submitValues[$element])) {
            return $this->_submitValues[$element];
        }
        return null;
    } // end func getSubmitValue

    // }}}
    // {{{ getElementError()

    /**
     * Returns error corresponding to validated element
     *
     * @param     string    $element        Name of form element to check
     * @since     1.0
     * @access    public
     * @return    string    error message corresponding to checked element
     * @throws    
     */
    function getElementError($element)
    {
        if (isset($this->_errors[$element])) {
            return $this->_errors[$element];
        }
    } // end func getElementError
    
    // }}}
    // {{{ setElementError()

    /**
     * Set error message for a form element
     *
     * @param     string    $element    Name of form element to set error for
     * @param     string    $message    Error message
     * @since     1.0       
     * @access    public
     * @return    void
     * @throws    
     */
    function setElementError($element,$message)
    {
        $this->_errors[$element] = $message;
    } // end func setElementError
         
     // }}}
     // {{{ getElementType()

     /**
      * Returns the type of the given element
      *
      * @param      string    $element    Name of form element
      * @since      1.1
      * @access     public
      * @return     string
      */
     function getElementType($element)
     {
         if (isset($this->_elementIndex[$element])) {
             return $this->_elements[$this->_elementIndex[$element]]->getType();
         }
         return false;
     } // end func getElementType

    // }}}
    // {{{ renderElement()

    /**
     * Renders an element, outputting the html if the element is not
     * frozen
     *
     * @param string $elementName The element name
     * @param optional boolean $remove Remove the element after rendering?
     * @param optional boolean $removeRules Remove all rules associated
     *                                      with this element?
     *
     * @access public
     * @since 2.0
     * @return string
     */
    function renderElement($elementName, $remove = false, $removeRules = false)
    {
        $element = $this->getElement($elementName);
        if (is_null($element) || PEAR::isError($element)) {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$elementName' does not exist in HTML_QuickForm::renderElement()", 'HTML_QuickForm_Error', true);
        }

        $html = $this->_buildElement($element);

        if ($remove) {
            $this->removeElement($elementName, $removeRules);
        }

        return $html;
    } // end func renderElement

    // }}}
    // {{{ removeElement()

    /**
     * Removes an element
     *
     * @param string $elementName The element name
     * @param optional boolean $removeRules True if rules for this element are to be removed too                     
     *
     * @access public
     * @since 2.0
     * @return void
     */
   function removeElement($elementName, $removeRules = true)
    {
        if (isset($this->_elementIndex[$elementName])) {
            unset($this->_elements[$this->_elementIndex[$elementName]]);
            unset($this->_elementIndex[$elementName]);
            if ($removeRules) {
                unset($this->_rules[$elementName]);
            }
        } else {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$elementName' does not exist in HTML_QuickForm::removeElement()", 'HTML_QuickForm_Error', true);

        }
    } // end func removeElement

    // }}}
    // {{{ addHeader()

    /**
     * Adds a header in the form
     *
     * @param     string    $label      label of header
     * @since     1.0   
     * @access    public
     * @return    void
     * @throws    
     */
    function addHeader($label)
    {
        $this->_elements[] = array('header'=>$label);
    } // end func addHeader

    // }}}
    // {{{ addRule()

    /**
     * Adds a validation rule for the given field
     *
     * @param    string     $element       Form element name
     * @param    string     $message       Message to display for invalid data
     * @param    string     $type          Rule type use getRegisteredType to get types
     * @param    string     $format        (optional)Required for extra rule data
     * @param    string     $validation    (optional)Where to perform validation: "server", "client"
     * @param    boolean    $reset         Reset the form element to it's original val if there is an error?
     * @param    boolean    $force         Force the rule to be applied, even if the target form element does not exist
     * @since    1.0
     * @access   public
     */
    function addRule($element, $message='', $type='', $format='', $validation='server', $reset = false, $force = false)
    {
        if (!$force) {
            if (!$this->elementExists($element)) {
                return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
            }
        }
        if ($this->getElementType($element) == 'group') {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$element' is a group. Use HTML_QuickForm::addGroupRule() instead.", 'HTML_QuickForm_Error', true);
        }
        if ($type == 'required' || $type == 'uploadedfile') {
            $this->_required[] = $element;
        }
        if (!isset($this->_rules[$element])) {
            $this->_rules[$element] = array();
        }
        if ($validation == 'client') {
            $this->updateAttributes(array('onsubmit'=>'return validate_'.$this->_attributes['name'] . '();'));
        }
        $this->_rules[$element][] = array('type'=>$type, 'format'=>$format, 'message'=>$message, 'validation'=>$validation, 'reset'=>$reset);
    } // end func addRule

    // }}}
    // {{{ addGroupRule()

    /**
     * Adds a validation rule for the given group of elements
     *
     * Only groups with a name can be assigned a validation rule
     *
     * @param    string     $group         Form group name
     * @param    mixed      $arg1          Array for multiple elements or error message string for one element
     * @param    string     $type          (optional)Rule type use getRegisteredType to get types
     * @param    string     $format        (optional)Required for extra rule data
     * @param    int        $howmany       (optional)0 for all element or a number of elements in the group
     * @since    2.5
     * @access   public
     */
    function addGroupRule($group, $arg1, $type='', $format='', $howmany=0)
    {
        if (!$this->elementExists($group)) {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Group '$group' does not exist in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
        }
        if (!isset($this->_rules[$group])) {
            $this->_rules[$group] = array();
        }
        if (is_array($arg1)) {
            $groupObj =& $this->getElement($group);
            foreach ($arg1 as $elementIndex => $rules) {
                foreach ($rules as $rule) {
                    $format = (isset($rule[2])) ? $rule[2] : '';
                    $type = $rule[1];
                    $this->_rules[$group][] = array(
                        'type'=> $type, 'format'=>$format, 
                        'message'=>$rule[0], 'validation'=>'server', 'elementIndex'=>$elementIndex);
                    if ($type == 'required') {
                        $this->_required[] = $group.'['.$elementIndex.']';
                        $groupObj->_required[] = $group.'['.$elementIndex.']';
                    }
                }
            }
        } elseif (is_string($arg1)) {
            $this->_rules[$group][] = array('type'=>$type, 'format'=>$format, 
                'message'=>$arg1, 'validation'=>'server', 'howmany'=>$howmany);
            if ($type == 'required') {
                $this->_required[] = $group;
            }
        }
    } // end func addGroupRule

    // }}}
    // {{{ addData()

    /**
     * Adds data to the form (i.e. html or text)
     *
     * @param string $data The data to add to the form object
     * @return void
     */
    function addData($data)
    {
        $this->_elements[] = array('data'=>$data);
    }

    // }}}
    // {{{ applyFilter()

    /**
     * Applies a data filter for the given field
     *
     * @param    string     $element       Form element name
     * @param    string     $type          Filter type use getRegisteredFilters to get filters
     * @since    2.0
     * @access   public
     */
    function applyFilter($element, $type)
    {
        if ($this->isFilterRegistered($type)) {
            $filterData = $this->_registeredFilters[$type];
        } else {
            return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Filter function '$type' is not registered in QuickForm::applyFilter()", 'HTML_QuickForm_Error', true);
        }
        if ($element == '__ALL__') {
            $this->_submitValues = $this->_recursiveFilter($filterData);
        } else {
            if (isset($this->_submitValues[$element])) {
                $this->_submitValues[$element] = $this->_recursiveFilter($filterData, $this->_submitValues[$element]);
            } else {
                return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::applyFilter()", 'HTML_QuickForm_Error', true);
            }
        }
    } // end func applyFilter

    // }}}
    // {{{ _recursiveFilter()

    /**
     * Recursively apply a filter function
     *
     * @param     string   $filter    filter to apply
     * @param     mixed    $value     submitted values
     * @since     2.0
     * @access    private
     * @return    cleaned values
     * @throws    
     */
    function _recursiveFilter($filter, $value = null)
    {
        if (!isset($value)) {
            $value = $this->_submitValues;
        }
        if (is_array($value)) {
            $cleanValues = array();
            foreach ($value as $k => $v) {
                $cleanValues[$k] = $this->_recursiveFilter($filter, $value[$k]);
            }
            return $cleanValues;
        } else {
            if (method_exists($this, $filter)) {
                return $this->$filter($value);
            } elseif (function_exists($filter)) {
                return $filter($value);
            } else {
                return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Filter function '$filter' does not exist QuickForm::_recursiveFilter()", 'HTML_QuickForm_Error', true);
            }
        }
    } // end func _recursiveFilter

    // }}}
    // {{{ _wrapElement()

    /**
     * Html Wrapper method for form elements (inputs...)
     *
     * @param     object    $element    Element to be wrapped
     * @since     1.0
     * @access    private
     * @return    void
     * @throws    
     */
    function _wrapElement(&$element, $label=null, $required=false, $error=null)
    {
        $tabs = $this->_getTabs();
        $html = '';
        if (isset($this->_templates[$element->getName()])) {
            $html = str_replace('{label}', $label, $this->_templates[$element->getName()]);
        } else {
            $html = str_replace('{label}', $label, $this->_elementTemplate);
        }
        if ($required) {
            $html = str_replace('<!-- BEGIN required -->', '', $html);
            $html = str_replace('<!-- END required -->', '', $html);
        } else {
            $html = preg_replace("/([ \t\n\r]*)?<!-- BEGIN required -->(\s|\S)*<!-- END required -->([ \t\n\r]*)?/i", '', $html);
        }
        if (isset($error)) {
            $html = str_replace('{error}', $error, $html);
            $html = str_replace('<!-- BEGIN error -->', '', $html);
            $html = str_replace('<!-- END error -->', '', $html);
        } else {
            $html = preg_replace("/([ \t\n\r]*)?<!-- BEGIN error -->(\s|\S)*<!-- END error -->([ \t\n\r]*)?/i", '', $html);
        }
        $html = str_replace('{element}', $element->toHtml(), $html);
        return $html;
    } // end func _wrapElement
    
    // }}}
    // {{{ _wrapHeader ()

    /**
     * Html Wrapper method for form headers
     *
     * @param     string    $header header to be wrapped
     * @since     1.0
     * @access    private
     * @return    void
     * @throws    
     */
    function _wrapHeader ($header)
    {
        $tabs = $this->_getTabs();
        $html = "";
        $html = str_replace('{header}', $header, $this->_headerTemplate);
        return $html;
    } // end func _wrapHeader
        
    // }}}
    // {{{ _wrapForm()

    /**
     * Puts the form in a HTML decoration (should be overriden)
     *
     * @param    mixed     $content     can be a string with html or a HTML_Table object
     * @since     1.0   
     * @access    private
     * @return    string    Html string of the wrapped form
     * @throws    
     */
    function _wrapForm($content)
    {
        $html = '';
        $html = str_replace('{attributes}', 
            $this->_getAttrString($this->_attributes), $this->_formTemplate);
        $tabs = $this->_getTabs();
        $html = str_replace('{content}', $content, $html);
        $html = str_replace("\n", "\n$tabs\t", $html);
        return $html;
    } // end func _wrapForm

    // }}}
    // {{{ _wrapRequiredNote()

    /**
     * Wrap footnote for required fields
     *
     * @param    object     $formTable      HTML_Table object
     * @since     1.0   
     * @access    private
     * @return    void
     * @throws    
     */
    function _wrapRequiredNote(&$formTable)
    {
        $html = '';
        $html = str_replace('{requiredNote}', $this->_requiredNote, $this->_requiredNoteTemplate);
        return $html;
    } // end func setCaption

    // }}}
    // {{{ _buildElement()

    /**
     * Builds the element as part of the form
     *
     * @param     array     $element    Array of element information
     * @since     1.0       
     * @access    private
     * @return    void
     * @throws    
     */
    function _buildElement(&$element)
    {
        $html        = '';
        $object      = $element;
        $label       = $object->getLabel();
        $elementName = $object->getName();
        $elementType = $object->getType();
        $required    = ($this->isElementRequired($elementName) && $this->_freezeAll == false);
        $error       = $this->getElementError($elementName);
        if ($object->getType() != 'hidden') {
            $html = $this->_wrapElement($object, $label, $required, $error);
        } else {
            $html = "\n" . $this->_getTabs() . "\t" . $object->toHtml();
        }
        return $html;
    } // end func _buildElement
    
    // }}}
    // {{{ _buildHeader()

    /**
     * Builds a form header
     *
     * @param     string    $element    header to be built
     * @since     1.0    
     * @access    private
     * @return    void
     * @throws    
     */
    function _buildHeader($element)
    {
        $header = $element['header'];
        return $this->_wrapHeader($header);
    } // end func _buildHeader
    
    // }}}
    // {{{ _buildRules()

    /**
     * Adds javascript needed for clientside validation
     *
     * @since     1.0
     * @access    private
     * @return    string    javascript for clientside validation
     * @throws    
     */
    function _buildRules()
    {
        $html = '';
        $tabs = $this->_getTabs();
        $test = array();
        for (reset($this->_rules); $elementName = key($this->_rules); next($this->_rules)) {
            $rules = pos($this->_rules);
            foreach ($rules as $rule) {
                $type       = $rule['type'];
                // error out if the rule is not registered
                if (!$this->isRuleRegistered($type)) {
                    return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
                }
                $validation = $rule['validation'];
                $message    = $rule['message'];
                $format     = $rule['format'];
                $reset      = (isset($rule['reset'])) ? $rule['reset'] : false;
                $ruleData = $this->_registeredRules[$type];
                if ($validation == 'client') {
                    $tmp_reset = ($reset) ? "$tabs\t\tfield.value = field.defaultValue;\n" : '';
                    switch ($ruleData[0]) {
                        case 'regex':
                            $regex = str_replace('%data%', $format, $ruleData[1]);
                            $test[] =
                                "$tabs\t\tvar field = frm.elements['$elementName'];\n"  .
                                "$tabs\t\tvar regex = $regex;\n"  .
                                "$tabs\t\tif (!regex.test(field.value) && !errFlag['$elementName']) {\n" .
                                "$tabs\t\t\terrFlag['$elementName'] = true;\n" .
                                "$tabs\t\t\t_qfMsg = unescape(_qfMsg + '\\n - ".rawurlencode($message)."');\n".
                                $tmp_reset.
                                "$tabs\t\t}";
                            break;
                        case 'function':
                            $test[] =
                                "$tabs\t\tvar field = frm.elements['$elementName'];\n"  .
                                "$tabs\t\tif (!" . $ruleData[1] . "('$elementName', field.value) && !errFlag['$elementName']) {\n" .
                                "$tabs\t\t\terrFlag['$elementName'] = true;\n" .
                                "$tabs\t\t\t_qfMsg = _qfMsg + '\\n - $message';\n" .
                                "$tabs\t\t}";
                            break;
                    }
                }
            }
        }
        if (is_array($test) && count($test) > 0) {
            $html .=
                "$tabs\tfunction validate_" . $this->_attributes['name'] . "() {\n" .
                "$tabs\t\tvar errFlag = new Array();\n" .
                "$tabs\t\t_qfMsg = '';\n" .
                "$tabs\t\tvar frm = document.forms['" . $this->_attributes['name'] . "'];\n";
            $html .= join("\n", $test);
            $html .=
                "$tabs\t\tif (_qfMsg != '') {\n" .
                "$tabs\t\t\t_qfMsg = '$this->_jsPrefix' + _qfMsg;\n" .
                "$tabs\t\t\t_qfMsg = _qfMsg + '\\n$this->_jsPostfix';\n" .
                "$tabs\t\t\talert(_qfMsg);\n" .
                "$tabs\t\t\treturn false;\n" .
                "$tabs\t\t}\n" .
                "$tabs\t\treturn true;\n" .
                "$tabs }\n";
        }
        return $html; 
    } // end func _buildRules

    // }}}
    // {{{ isTypeRegistered()

    /**
     * Returns whether or not the form element type is supported
     *
     * @param     string   $type     Form element type
     * @since     1.0
     * @access    public
     * @return    boolean
     * @throws    
     */
    function isTypeRegistered($type)
    {
        return in_array($type, array_keys($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']));
    } // end func isTypeRegistered

    // }}}
    // {{{ getRegisteredTypes()

    /**
     * Returns an array of registered element types
     *
     * @since     1.0
     * @access    public
     * @return    array
     * @throws    
     */
    function getRegisteredTypes()
    {
        return array_keys($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']);
    } // end func getRegisteredTypes

    // }}}
    // {{{ isRuleRegistered()

    /**
     * Returns whether or not the given rule is supported
     *
     * @param     string   $name    Validation rule name
     * @since     1.0
     * @access    public
     * @return    boolean
     * @throws    
     */
    function isRuleRegistered($name)
    {
        return in_array($name, array_keys($this->_registeredRules));
    } // end func isRuleRegistered

    // }}}
    // {{{ getRegisteredRules()

    /**
     * Returns an array of registered validation rules
     *
     * @since     1.0
     * @access    public
     * @return    array
     * @throws    
     */
    function getRegisteredRules()
    {
        return array_keys($this->_registeredRules);
    } // end func getRegisteredRules

    // }}}
    // {{{ isElementRequired()

    /**
     * Returns whether or not the form element is required
     *
     * @param     string   $element     Form element name
     * @since     1.0
     * @access    public
     * @return    boolean
     * @throws    
     */
    function isElementRequired($element)
    {
        return in_array($element, $this->_required);
    } // end func isElementRequired

    // }}}
    // {{{ isElementFrozen()

    /**
     * Returns whether or not the form element is frozen
     *
     * @param     string   $element     Form element name
     * @since     1.0
     * @access    public
     * @return    boolean
     * @throws    
     */
    function isElementFrozen($element)
    {
         if (isset($this->_elementIndex[$element])) {
             return $this->_elements[$this->_elementIndex[$element]]->isFrozen();
         }
         return false;
    } // end func isElementFrozen

    // }}}
    // {{{ setJsWarnings()

    /**
     * Sets JavaScript warning messages
     *
     * @param     string   $pref        Prefix warning
     * @param     string   $post        Postfix warning
     * @since     1.1
     * @access    public
     * @return    void
     */
    function setJsWarnings($pref, $post)
    {
        $this->_jsPrefix = $pref;
        $this->_jsPostfix = $post;
    } // end func setJsWarnings
    
    // }}}
    // {{{ setElementTemplate()

    /**
     * Sets element template 
     *
     * @param     string   $html        The HTML surrounding an element 
     * @param     string   $element     (optional) Name of the element to apply template for
     * @since     2.0
     * @access    public
     * @return    void
     */
    function setElementTemplate($html, $element = null)
    {
        if (is_null($element)) {
            $this->_elementTemplate = $html;
        } else {
            if (!$this->elementExists($element)) {
                return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::setElementTemplate()", 'HTML_QuickForm_Error', true);
            }
            $this->_templates[$element] = $html;
        }
    } // end func setElementTemplate

    // }}}
    // {{{ setHeaderTemplate()

    /**
     * Sets header template 
     *
     * @param     string   $html    The HTML surrounding the header 
     * @since     2.0
     * @access    public
     * @return    void
     */
    function setHeaderTemplate($html)
    {
        $this->_headerTemplate = $html;
    } // end func setHeaderTemplate

    // }}}
    // {{{ setFormTemplate()

    /**
     * Sets form template 
     *
     * @param     string   $html    The HTML surrounding the form tags 
     * @since     2.0
     * @access    public
     * @return    void
     */
    function setFormTemplate($html)
    {
        $this->_formTemplate = $html;
    } // end func setFormTemplate

    // }}}
    // {{{ setRequiredNoteTemplate()

    /**
     * Sets element template 
     *
     * @param     string   $html    The HTML surrounding the required note 
     * @since     2.0
     * @access    public
     * @return    void
     */
    function setRequiredNoteTemplate($html)
    {
        $this->_requiredNoteTemplate = $html;
    } // end func setElementTemplate

    // }}}
    // {{{ clearAllTemplates()

    /**
     * Clears all the HTML out of the templates that surround notes, elements, etc.
     * Useful when you want to use addData() to create a completely custom form look
     *
     * @since   2.0
     * @access  public
     * @returns void
     */
    function clearAllTemplates()
    {
        $this->setElementTemplate('{element}');
        $this->setFormTemplate("\n\t<form{attributes}>{content}\n\t</form>\n");
        $this->setRequiredNoteTemplate('');
        $this->_templates = array();
    }

    // }}}
    // {{{ setRequiredNote()

    /**
     * Sets required-note
     *
     * @param     string   $note        Message indicating some elements are required
     * @since     1.1
     * @access    public
     * @return    void
     */
    function setRequiredNote($note)
    {
        $this->_requiredNote = $note;
    } // end func setRequiredNote

    // }}}
    // {{{ getRequiredNote()

    /**
     * Returns the required note
     *
     * @since     2.0
     * @access    public
     * @return    string
     * @throws    
     */
    function getRequiredNote()
    {
        return $this->_requiredNote;
    } // end func getRequiredNote

    // }}}
    // {{{ validate()

    /**
     * Performs the server side validation
     * @access    public
     * @since     1.0
     * @return    boolean   true if no error found
     * @throws    
     */
    function validate()
    {
        if (count($this->_rules) == 0 || count($this->_submitValues) == 0) {
            return false;
        }
        foreach ($this->_rules as $elementName => $rules) {
            if (isset($this->_errors[$elementName])) {
                continue;
            }
            if ($this->getElementType($elementName) == 'group') {
                // Grouped elements validation
                $submitValues = (isset($this->_submitValues[$elementName])) ? $this->_submitValues[$elementName] : array();
                $this->_validateGroup($elementName, $rules, $submitValues);
            } else {
                // Standard elements validation
                foreach ($rules as $rule) {
                    $type = $format = $message = null;
                    $type = $rule['type'];
                    // error out if the rule is not registered
                    if (!$this->isRuleRegistered($type)) {
                        return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::validate()", 'HTML_QuickForm_Error', true);
                    }
                    $format = $rule['format'];
                    $message = $rule['message'];
                    $validation = $rule['validation'];
                    $ruleData = $this->_registeredRules[$type];
                    switch ($ruleData[0]) {
                        case 'regex':
                            $regex = str_replace('%data%', $format, $ruleData[1]);
                            if (!preg_match($regex, $this->_submitValues[$elementName])) {
                                if (empty($this->_submitValues[$elementName]) && !$this->isElementRequired($elementName)) {
                                    continue 2;
                                } else {
                                    $this->_errors[$elementName] = $message;
                                    continue 2;
                                }
                            }
                            break;
                        case 'function':
                            if (method_exists($this, $ruleData[1])) {
                                if (isset($this->_submitValues[$elementName])) {
                                    $element = $this->_submitValues[$elementName];
                                } elseif (isset($this->_submitFiles[$elementName])) {
                                    $element = $this->_submitFiles[$elementName];
                                }
                                if (!$this->$ruleData[1]($elementName, $element, $format)) {
                                    $this->_errors[$elementName] = $message;
                                    continue 2;
                                }
                            } else {
                                if (!$ruleData[1]($elementName, $this->_submitValues[$elementName], $format)) {
                                    $this->_errors[$elementName] = $message;
                                    continue 2;
                                }
                            }
                            break;
                    }
                }
            }
        }
        if (count($this->_errors) > 0) {
            $files = $this->_submitFiles;
            for (reset($files); $element=key($files); next($files)) {
                $file = pos($files);
                @unlink($file["tmp_name"]);
            }
            return false;
        }
        return true;
    } // end func validate

    // }}}
    // {{{ _validateGroup()

    /**
     * Performs the server side validation for grouped elements
     * Only regex is supported by now and no javascript yet.
     *
     * @param     string   $groupName   Group name
     * @param     array    $rules       Array of validation rules
     * @param     array    $values      Submitted values to be checked
     * @access    private
     * @since     2.5
     * @return    void
     */
    function _validateGroup($groupName, $rules, $values)
    {
        $count = count($values);
        $group =& $this->getElement($groupName);
        $elements =& $group->getElements(); 
        foreach ($rules as $rule) {
            $type = $format = $message = null;
            $type = $rule['type'];
            // error out if the rule is not registered
            if (!$this->isRuleRegistered($type)) {
                return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::validate()", 'HTML_QuickForm_Error', true);
            }
            $format = $rule['format'];
            $message = $rule['message'];
            $ruleData = $this->_registeredRules[$type];
            if (isset($rule['elementIndex'])) {
                $elementIndex = $rule['elementIndex'];
                // per grouped element validation
                if (isset($elements[$elementIndex])) {
                    $value = (isset($values[$elementIndex])) ? $values[$elementIndex] : '';
                } else {
                    return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '".$elementIndex."' is not registered in HTML_QuickForm::_validateGroup()", 'HTML_QuickForm_Error', true);
                }
                switch ($ruleData[0]) {
                    case 'regex':
                        $regex = str_replace('%data%', $format, $ruleData[1]);
                        if (!preg_match($regex, $value)) {
                            if ($value == '' && !$this->isElementRequired($groupName.'['.$elementIndex.']')) {
                                continue 2;
                            } else {
                                $this->_errors[$groupName] = $message;
                                return;
                            }
                        }
                        break;
                }
            } else {
                // per group validation
                $howmany = $rule['howmany'];
                $total = 0;
                foreach ($values as $value) {
                    switch ($ruleData[0]) {
                        case 'regex':
                            $regex = str_replace('%data%', $format, $ruleData[1]);
                            if (!preg_match($regex, $value)) {
                                if (empty($value) && !$this->isElementRequired($groupName)) {
                                    continue 2;
                                } else {
                                    if ($howmany == 0) {
                                        $this->_errors[$groupName] = $message;
                                        return;
                                    }
                                }
                            } else {
                                $total++;
                            }
                            break;
                    }
                }
                if ($total < $howmany) {
                    $this->_errors[$groupName] = $message;
                    return;
                }
            }
        }
    } // end func _validateGroup

    // }}}
    // {{{ _ruleIsUploadedFile()

    /**
     * Checks if the given element contains an uploaded file
     *
     * @param     string    $element    Element name
     * @since     1.1
     * @access    private
     * @return    bool      true if file has been uploaded, false otherwise
     * @throws    
     */
    function _ruleIsUploadedFile($element)
    {
        return is_uploaded_file($this->_submitFiles[$element]['tmp_name']);
    } // end func _ruleIsUploadedFile
    
    // }}}
    // {{{ _ruleCheckMaxFileSize()

    /**
     * Checks that the file does not exceed the max file size
     *
     * @param     string    $element    Element name
     * @param     mixed     $value      Element value
     * @param     int       $maxSize    Max file size
     * @since     1.1
     * @access    private
     * @return    bool      true if filesize is lower than maxsize, false otherwise
     * @throws    
     */
    function _ruleCheckMaxFileSize($element, $value, $maxSize)
    {
        return ($maxSize >= @filesize($this->_submitFiles[$element]['tmp_name']));
    } // end func _ruleCheckMaxFileSize

    // }}}
    // {{{ _ruleCheckMimeType()

    /**
     * Checks if the given element contains an uploaded file of the right mime type
     *
     * @param     string    $element    Element name
     * @param     mixed     $value      Element value
     * @param     mixed     $mimeType   Mime Type (can be an array of allowed types)
     * @since     1.1
     * @access    private
     * @return    bool      true if mimetype is correct, false otherwise
     * @throws    
     */
    function _ruleCheckMimeType($element, $value, $mimeType)
    {
        if (is_array($mimeType)) {
            return in_array($this->_submitFiles[$element]['type'], $mimeType);
        }
        return $this->_submitFiles[$element]['type'] == $mimeType;
    } // end func _ruleCheckMimeType

    // }}}
    // {{{ _ruleCheckFileName()

    /**
     * Checks if the given element contains an uploaded file of the filename regex
     *
     * @param     string    $element    Element name
     * @param     mixed     $value      Element value
     * @param     string    $regex      Regular expression
     * @since     1.1
     * @access    private
     * @return    bool      true if name matches regex, false otherwise
     * @throws    
     */
    function _ruleCheckFileName($element, $value, $regex)
    {
        return preg_match($regex, $this->_submitFiles[$element]['name']);
    } // end func _ruleCheckFileName
    
    // }}}
    // {{{ isFilterRegistered()

    /**
     * Returns whether or not the filter is supported
     *
     * @param     string   $filterName     Filter
     * @since     1.0
     * @access    public
     * @return    boolean
     * @throws    
     */
    function isFilterRegistered($filterName)
    {
        return in_array($filterName, array_keys($this->_registeredFilters));
    } // end func isFilterRegistered

    // }}}
    // {{{ getRegisteredFilters()

    /**
     * Returns an array of registered filters
     *
     * @since     2.0
     * @access    public
     * @return    array
     * @throws    
     */
    function getRegisteredFilters()
    {
        return array_keys($this->_registeredFilters);
    } // end func getRegisteredFilters

    // }}}
    // {{{ _filterTrim()

    /**
     * Returns the trimmed element value
     *
     * @param     mixed     $value  element value
     * @since     2.0
     * @access    private
     * @return    mixed
     * @throws    
     */
    function _filterTrim($value)
    {
        return trim($value);
    } // end func _filterTrim

    // }}}
    // {{{ _filterIntval()

    /**
     * Returns the intval of the element value
     *
     * @param     mixed     $value  element value
     * @since     2.0
     * @access    private
     * @return    mixed
     * @throws    
     */
    function _filterIntval($value)
    {
        return intval($value);
    } // end func _filterIntval

    // }}}
    // {{{ _filterDoubleval()

    /**
     * Returns the doubleval of the element value
     *
     * @param     mixed     $value  element value
     * @since     2.0
     * @access    private
     * @return    mixed
     * @throws    
     */
    function _filterDoubleval($value)
    {
        return doubleval($value);
    } // end func _filterDoubleval

    // }}}
    // {{{ _filterStrval()

    /**
     * Returns the floatval of the element value
     *
     * @param     mixed     $value  element value
     * @since     2.0
     * @access    private
     * @return    mixed
     * @throws    
     */
    function _filterStrval($value)
    {
        return strval($value);
    } // end func _filterStrval

    // }}}
    // {{{ _filterBoolval()

    /**
     * Returns the boolean value of the element value
     *
     * @param     mixed     $value  element value
     * @since     2.0
     * @access    private
     * @return    mixed
     * @throws    
     */
    function _filterBoolval($value)
    {
        return ($value && true);
    } // end func _filterBoolval

    // }}}
    // {{{ _filterStripslashes()

    /**
     * Returns the value after strislashes
     *
     * @param     mixed     $value  element value
     * @since     2.0
     * @access    private
     * @return    mixed
     * @throws    
     */
    function _filterStripslashes($value)
    {
        return (is_string($value)) ? stripslashes($value) : $value;
    } // end func _filterStripslashes

    // }}}
    // {{{ _filterAddslashes()

    /**
     * Returns the value after addslashes
     *
     * @param     mixed     $value  element value
     * @since     2.0
     * @access    private
     * @return    mixed
     * @throws    
     */
    function _filterAddslashes($value)
    {
        return (is_string($value)) ? addslashes($value) : $value;
    } // end func _filterAddslashes

    // }}}
    // {{{ freeze()

    /**
     * Displays elements without HTML input tags
     *
     * @param    mixed   $elementList       array or string of element(s) to be frozen
     * @since     1.0
     * @access   public
     */
    function freeze($elementList=null)
    {
        $elementFlag = false;
        if (isset($elementList) && !is_array($elementList)) {
            $elementList = split('[ ]*,[ ]*', $elementList);
        } elseif (!isset($elementList)) {
            $this->_freezeAll = true;
        }

        foreach ($this->_elements as $key => $val) {
            // need to get the element by reference
            $element = &$this->_elements[$key];
            if (is_object($element)) {
                $name = $element->getName();
                if ($this->_freezeAll || in_array($name, $elementList)) {
                    $elementFlag = true;
                    $element->freeze();
                }
            }
        }

        if (!$elementFlag) {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::freeze()", 'HTML_QuickForm_Error', true);
        }
        return true;
    } // end func freeze
        
    // }}}
    // {{{ process()

    /**
     * Performs the form data processing
     *
     * @since     1.0
     * @access   public
     */
    function process()
    {
        echo '<pre>';
        var_dump($this->_submitValues);
        echo '</pre>';
        echo '<pre>';
        var_dump($this->_submitFiles);
        echo '</pre>';
        return true;
    } // end func process

    // }}}
    // {{{ toHtml ()

    /**
     * Returns an HTML version of the form
     *
     * @param string $in_data (optional) Any extra data to insert right
     *               before form is rendered.  Useful when using templates.
     *
     * @return   string     Html version of the form
     * @since     1.0
     * @access   public
     */
    function toHtml ($in_data = null)
    {
        if (!is_null($in_data)) {
            $this->addData($in_data);
        }

        $html = '';
        reset($this->_elements);
        foreach ($this->_elements as $element) {
            if (isset($element['header'])) {
                $html .= $this->_buildHeader($element);
            } elseif (isset($element['data'])) {
                $html .= $element['data'];
            } else {
                $html .= $this->_buildElement($element);
            }
        }
        if (!empty($this->_required) && $this->_freezeAll == false) {
            $html .= $this->_wrapRequiredNote($formTable);
        }
        $html = $this->_wrapForm($html);
        if (!empty($this->_rules) && $this->_freezeAll == false) {
            $tabs = $this->_getTabs();
            $html =
                "\n$tabs<script language=\"javascript\">\n" .
                "$tabs<!-- \n" . $html = $this->_buildRules() .
                "$tabs//-->\n" .
                "$tabs</script>" .
                $html;
        }
        return $html;
    } // end func toHtml

    // }}}
    // {{{ display()

    /**
     * Displays an HTML version of the form
     *
     * If the body parameter is used then the default layout is overridden and
     * the contents of $body is used within the form
     * @param    mixed    $body      (optional) Body of form
     * @since     1.0
     * @access    public
     */
    function display()
    {
        print $this->toHtml();
    } //end func display

    // }}}
    // {{{ getValidationScript()

    /**
     * Returns the client side validation script
     *
     * @since     2.0
     * @access    public
     * @return    string
     * @throws
     */
    function getValidationScript()
    {
        if (!empty($this->_rules)) {
            return $this->_buildRules();
        }
    } // end func getValidationScript

    // }}}
    // {{{ getAttributesString()

    /**
     * Returns the HTML attributes of the form
     *
     * @since     2.0
     * @access    public
     * @return    void
     * @throws
     */
    function getAttributesString()
    {
        return $this->_getAttrString($this->_attributes);
    } // end func getAttributesString

    // }}}
    // {{{ getSubmitValues()

    /**
     * Returns the values submitted by the form
     *
     * @since     2.0
     * @access    private
     * @return    void
     * @throws
     */
    function getSubmitValues()
    {
        return $this->_submitValues;
    } // end func getSubmitValues

    // }}}
    // {{{ toArray()

    /**
     * Returns the form's contents in an array
     *
     * Detail description
     * @since     2.0
     * @access    public
     * @return    array of form contents
     * @throws
     */
    function toArray()
    {
        $elementIndex = 1;
        $sectionCount = 0;
        $currentSection = null;
        $returnVal = array();
        $returnVal['validationScript'] = $this->getValidationScript();
        $returnVal['attributes'] = $this->getAttributesString();
        $returnVal['requiredNote'] = $this->getRequiredNote();
        foreach ($this->_elements as $element) {
            if (isset($element["header"])) {
                $returnVal['sections'][$sectionCount] = 
                    array('header'=>$element["header"]);
                $currentSection = $sectionCount++;
            } else {
                $name = $element->getName();
                if (!isset($name) || $name == '') {
                    $name = 'element_' . $elementIndex;
                }
                $elementIndex++;
                if ($this->_freezeAll) {
                    $element->freeze();
                }
                $html = $element->toHtml();
                $error = $this->getElementError($name);
                if (isset($error)) {
                    $returnVal['errors'][$name] = $error;
                }
                if (isset($currentSection)) {
                    $returnVal['sections'][$currentSection]['elements'][$name] = 
                        array_merge(array('required'=>$this->isElementRequired($name)),
                            $element->toArray());
                } else {
                    $returnVal['elements'][$name] = 
                        array_merge(array('required'=>$this->isElementRequired($name)),
                            $element->toArray());
                }
            }
        }
        return $returnVal;
    } // end func toArray

    // }}}
    // {{{ isError()

    /**
     * Tell whether a result code from a QuickForm method is an error
     *
     * @param $value int result code
     *
     * @return bool whether $value is an error
     */
    function isError($value)
    {
        return (is_object($value) && (get_class($value) == 'html_quickform_error' || is_subclass_of($value, 'html_quickform_error')));
    }

    // }}}
    // {{{ errorMessage()

    /**
     * Return a textual error message for an QuickForm error code
     *
     * @param $value int error code
     *
     * @return string error message, or false if the error code was
     * not recognized
     */
    function errorMessage($value)
    {
        // make the variable static so that it only has to do the defining on the first call
        static $errorMessages;

        // define the varies error messages
        if (!isset($errorMessages)) {
            $errorMessages = array(
                QUICKFORM_OK                    => 'no error',
                QUICKFORM_ERROR                 => 'unknown error',
                QUICKFORM_INVALID_RULE          => 'the rule does not exist as a registered rule',
                QUICKFORM_NONEXIST_ELEMENT      => 'nonexistent html element',
                QUICKFORM_INVALID_FILTER        => 'invalid filter',
                QUICKFORM_UNREGISTERED_ELEMENT  => 'unregistered element'
            );
        }

        // If this is an error object, then grab the corresponding error code
        if (HTML_QuickForm::isError($value)) {
            $value = $value->getCode();
        }

        // return the textual error message corresponding to the code
        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[QUICKFORM_ERROR];
    }

    // }}}
} // end class HTML_QuickForm

class HTML_QuickForm_Error extends PEAR_Error {

    // {{{ properties

    /** @var string prefix of all error messages */
    var $error_message_prefix = 'QuickForm Error: ';

    // }}}
    // {{{ constructor

    /**
    * Creates a quickform error object, extending the PEAR_Error class
    *
    * @param int   $code the error code
    * @param int   $mode the reaction to the error, either return, die or trigger/callback
    * @param int   $level intensity of the error (PHP error code)
    * @param mixed $debuginfo any information that can inform user as to nature of the error
    */
    function HTML_QuickForm_Error($code = QUICKFORM_ERROR, $mode = PEAR_ERROR_RETURN,
                         $level = E_USER_NOTICE, $debuginfo = null)
    {
        if (is_int($code)) {
            $this->PEAR_Error(HTML_QuickForm::errorMessage($code), $code, $mode, $level, $debuginfo);
        }
        else {
            $this->PEAR_Error("Invalid error code: $code", QUICKFORM_ERROR, $mode, $level, $debuginfo);
        }
    }

    // }}}
}
?>