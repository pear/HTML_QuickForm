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
            'advcheckbox'   =>array('HTML/QuickForm/advcheckbox.php','HTML_QuickForm_advcheckbox'),
            'date'          =>array('HTML/QuickForm/date.php','HTML_QuickForm_date'),
            'static'        =>array('HTML/QuickForm/static.php','HTML_QuickForm_static'),
            'header'        =>array('HTML/QuickForm/header.php', 'HTML_QuickForm_header'),
            'html'          =>array('HTML/QuickForm/html.php', 'HTML_QuickForm_html'),
            'hierselect'    =>array('HTML/QuickForm/hierselect.php', 'HTML_QuickForm_hierselect'),
            'dategroup'     =>array('HTML/QuickForm/dategroup.php', 'HTML_QuickForm_dategroup')
        );

$GLOBALS['_HTML_QuickForm_registered_rules'] = array(
    'required'      =>array('regex', '/(\s|\S)/'),
    'maxlength'     =>array('regex', '/^(\s|\S){0,%data%}$/'),
    'minlength'     =>array('regex', '/^(\s|\S){%data%,}$/'),
    'rangelength'   =>array('regex', '/^(\s|\S){%data%}$/'),
    'regex'         =>array('regex', '%data%'),
    'email'         =>array('regex', '/^[a-zA-Z0-9\._-]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/'),
    'emailorblank'  =>array('regex', '/(^$)|(^[a-zA-Z0-9\._-]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$)/'),
    'lettersonly'   =>array('regex', '/^[a-zA-Z]+$/'),
    'alphanumeric'  =>array('regex', '/^[a-zA-Z0-9]+$/'),
    'numeric'       =>array('regex', '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/'),
    'nopunctuation' =>array('regex', '/^[^().\/\*\^\?#!@$%+=,\"\'><~\[\]{}]+$/'),
    'nonzero'       =>array('regex', '/^[1-9][0-9]+/')
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
define('QUICKFORM_INVALID_ELEMENT_NAME',   -6);
define('QUICKFORM_INVALID_PROCESS',        -7);

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
     * Array containing indexes of duplicate elements
     * @since     2.10
     * @var  array
     * @access   private
     */
    var $_duplicateIndex = array();

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
     * Form rules, global variety
     * @var     array
     * @access  private
     */
    var $_formRules = array();

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
    var $_requiredNote = '<span style="font-size:80%; color:#ff0000;">*</span><span style="font-size:80%;"> denotes required field</span>';

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * @param    string      $formName          Form's name.
     * @param    string      $method            (optional)Form's method defaults to 'POST'
     * @param    string      $action            (optional)Form's action
     * @param    string      $target            (optional)Form's target defaults to '_self'
     * @param    mixed       $attributes        (optional)Extra attributes for <form> tag
     * @access   public
     */
    function HTML_QuickForm($formName='', $method='post', $action='', $target='_self', $attributes=null)
    {
        HTML_Common::HTML_Common($attributes);
        $method = (strtoupper($method) == 'GET') ? 'get' : 'post';
        $action = ($action == '') ? $_SERVER['PHP_SELF'] : $action;
        $target = (empty($target) || $target == '_self') ? array() : array('target' => $target);
        $attributes = array('action'=>$action, 'method'=>$method, 'name'=>$formName, 'id'=>$formName) + $target;
        $this->updateAttributes($attributes);
        if (1 == get_magic_quotes_gpc()) {
            $this->_submitValues = $this->_recursiveFilter('stripslashes', 'get' == $method? $_GET: $_POST);
        } else {
            $this->_submitValues = 'get' == $method? $_GET: $_POST;
        }
        $this->_submitFiles =& $_FILES;
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
        return 3.0;
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
     */
    function registerRule($ruleName, $type, $data1, $data2=null)
    {
        $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName] = array($type, $data1, $data2);
    } // end func registerRule

    // }}}
    // {{{ elementExists()

    /**
     * Returns true if element is in the form
     *
     * @param     string   $element         form name of element to check
     * @since     1.0
     * @access    public
     * @return    boolean
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
                if (is_array($filter) && !is_object($filter[0])) {
                    foreach ($filter as $val) {
                        if (!$this->_callbackExists($val)) {
                            return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setDefaults()", 'HTML_QuickForm_Error', true);
                        } else {
                            $defaultValues = $this->_recursiveFilter($val, $defaultValues);
                        }
                    }
                } elseif (!$this->_callbackExists($filter)) {
                    return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setDefaults()", 'HTML_QuickForm_Error', true);
                } else {
                    $defaultValues = $this->_recursiveFilter($filter, $defaultValues);
                }
            }
            $this->_defaultValues = array_merge($this->_defaultValues, $defaultValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
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
                if (is_array($filter) && !is_object($filter[0])) {
                    foreach ($filter as $val) {
                        if (!$this->_callbackExists($val)) {
                            return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setConstants()", 'HTML_QuickForm_Error', true);
                        } else {
                            $constantValues = $this->_recursiveFilter($val, $constantValues);
                        }
                    }
                } elseif (!$this->_callbackExists($filter)) {
                    return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setConstants()", 'HTML_QuickForm_Error', true);
                } else {
                    $constantValues = $this->_recursiveFilter($filter, $constantValues);
                }
            }
            $this->_constantValues = array_merge($this->_constantValues, $constantValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
            }
        }
    } // end func setConstants

    // }}}
    // {{{ moveUploadedFile()

    /**
     * Moves an uploaded file into the destination (DEPRECATED)
     * @param    string  $element       Element name
     * @param    string  $dest          Destination directory path
     * @param    string  $fileName      (optional) New file name
     * @since    1.0
     * @access   public
     * @deprecated  Use HTML_QuickForm_file::moveUploadedFile() method
     */
    function moveUploadedFile($element, $dest, $fileName='')
    {
        $file =& $this->_submitFiles[$element];
        if ($dest != ''  && substr($dest, -1) != '/')
            $dest .= '/';
        $fileName = ($fileName != '') ? $fileName : $file['name'];
        if (move_uploaded_file($file['tmp_name'], $dest . $fileName)) {
            return true;
        } else {
            return false;
        }
    } // end func moveUploadedFile
    
    // }}}
    // {{{ setMaxFileSize()

    /**
     * Sets the value of MAX_FILE_SIZE hidden element
     *
     * @param     int    $bytes    Size in bytes
     * @since     3.0
     * @access    public
     * @return    void
     */
    function setMaxFileSize($bytes = 0)
    {
        if ($bytes > 0) {
            $this->_maxFileSize = $bytes;
        }
        if (!$this->elementExists('MAX_FILE_SIZE')) {
            $this->addElement('hidden', 'MAX_FILE_SIZE', $this->_maxFileSize);
        } else {
            $el =& $this->getElement('MAX_FILE_SIZE');
            $el->updateAttributes(array('value' => $this->_maxFileSize));
        }
    } // end func setMaxFileSize

    // }}}
    // {{{ getMaxFileSize()

    /**
     * Returns the value of MAX_FILE_SIZE hidden element
     *
     * @since     3.0
     * @access    public
     * @return    int   max file size in bytes
     */
    function getMaxFileSize()
    {
        return $this->_maxFileSize;
    } // end func getMaxFileSize

    // }}}
    // {{{ isUploadedFile()

    /**
     * Checks if the given element contains an uploaded file (DEPRECATED)
     *
     * @param     string    $element    Element name
     * @since     2.10
     * @access    public
     * @return    bool      true if file has been uploaded, false otherwise
     * @deprecated  Use HTML_QuickForm_file::isUploadedFile() method
     */
    function isUploadedFile($element)
    {
        if (!$this->elementExists($element) || 'file' != $this->getElementType($element)) {
            return false;
        } else {
            $elementObject =& $this->getElement($element);
            return $elementObject->isUploadedFile();
        }
    } // end func isUploadedFile

    // }}}
    // {{{ getUploadedFile()

    /**
     * Returns temporary filename of uploaded file (DEPRECATED)
     * @param    string  $element  
     * @since    2.10
     * @access   public
     * @deprecated  Use either of HTML_QuickForm_file::getValue(), HTML_QuickForm::getElementValue(), HTML_QuickForm::getSubmitValue() methods to access this information
     */
    function getUploadedFile($element)
    {
        return isset($this->_submitFiles[$element])? $this->_submitFiles[$element]['tmp_name']: null;
    } // end func getUploadedFile

    // }}}
    // {{{ &createElement()

    /**
     * Creates a new form element of the given type.
     * 
     * This method accepts variable number of parameters, their 
     * meaning and count depending on $elementType
     *
     * @param     string     $elementType    type of element to add (text, textarea, file...)
     * @since     1.0
     * @access    public
     * @return    object extended class of HTML_element
     * @throws    HTML_QuickForm_Error
     */
    function &createElement($elementType)
    {
        $args = func_get_args();
        return HTML_QuickForm::_loadElement('createElement', $elementType, array_slice($args, 1));
    } // end func createElement
    
    // }}}
    // {{{ _loadElement()

    /**
     * Returns a form element of the given type
     *
     * @param     string   $event   event to send to newly created element ('createElement' or 'addElement')
     * @param     string   $type    element type
     * @param     array    $args    arguments for event
     * @since     2.0
     * @access    private
     * @return    object    a new element
     * @throws    HTML_QuickForm_Error
     */
    function &_loadElement($event, $type, $args)
    {
        $type = strtolower($type);
        if (!HTML_QuickForm::isTypeRegistered($type)) {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$type' does not exist in HTML_QuickForm::_loadElement()", 'HTML_QuickForm_Error', true);
        }
        $className = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][1];
        $includeFile = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][0];
        include_once($includeFile);
        $elementObject =& new $className();
        for ($i = 0; $i < 5; $i++) {
            if (!isset($args[$i])) {
                $args[$i] = null;
            }
        }
        $err = $elementObject->onQuickFormEvent($event, $args, $this);
        if ($err !== true) {
            return $err;
        }
        return $elementObject;
    } // end func _loadElement

    // }}}
    // {{{ addElement()

    /**
     * Adds an element into the form
     * 
     * If $element is a string representing element type, then this 
     * method accepts variable number of parameters, their meaning 
     * and count depending on $element
     *
     * @param    mixed      $element        element object or type of element to add (text, textarea, file...)
     * @since    1.0
     * @return   object     reference to element
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function &addElement($element)
    {
        if (is_object($element) && is_subclass_of($element, 'html_quickform_element')) {
           $elementObject = &$element;
           $elementObject->onQuickFormEvent('updateValue', null, $this);
        } else {
            $args = func_get_args();
            $elementObject =& $this->_loadElement('addElement', $element, array_slice($args, 1));
            if (PEAR::isError($elementObject)) {
                return $elementObject;
            }
        }
        $elementName = $elementObject->getName();

        // Add the element if it is not an incompatible duplicate
        if (!empty($elementName) && isset($this->_elementIndex[$elementName])) {
            if ($this->_elements[$this->_elementIndex[$elementName]]->getType() ==
                $elementObject->getType()) {
                $this->_elements[] =& $elementObject;
                $this->_duplicateIndex[$elementName][] = count($this->_elements) - 1;
            } else {
                return PEAR::raiseError(null, QUICKFORM_INVALID_ELEMENT_NAME, null, E_USER_WARNING, "Element '$elementName' already exists in HTML_QuickForm::addElement()", 'HTML_QuickForm_Error', true);
            }
        } else {
            $this->_elements[] =& $elementObject;
            $this->_elementIndex[$elementName] = count($this->_elements) - 1;
        }

        return $elementObject;
    } // end func addElement
    
    // }}}
    // {{{ addGroup()

    /**
     * Adds an element group
     * @param    array      $elements       array of elements composing the group
     * @param    string     $name           (optional)group name
     * @param    string     $groupLabel     (optional)group label
     * @param    string     $separator      (optional)string to separate elements
     * @param    string     $appendName     (optional)specify whether the group name should be
     *                                      used in the form element name ex: group[element]
     * @return   object     reference to added group of elements
     * @since    2.8
     * @access   public
     * @throws   PEAR_Error
     */
    function &addGroup($elements, $name=null, $groupLabel='', $separator=null, $appendName = true)
    {
        static $anonGroups = 1;

        if (empty($name)) {
            $name       = 'qf_group_' . $anonGroups++;
            $appendName = false;
        }
        return $this->addElement('group', $name, $groupLabel, $elements, $separator, $appendName);
    } // end func addGroup
    
    // }}}
    // {{{ addElementGroup()

    /**
     * Adds an element group (DEPRECATED, use addGroup instead)
     * @param    array      $elements       array of elements composing the group
     * @param    string     $groupLabel     (optional)group label
     * @param    string     $name           (optional)group name
     * @param    string     $separator      (optional)string to seperate elements
     * @return   object     reference to added group of elements
     * @deprecated deprecated since 2.10, use addGroup() instead
     * @since    1.0
     * @access   public
     * @throws   PEAR_Error
     */
    function &addElementGroup($elements, $groupLabel='', $name=null, $separator=null)
    {
        return $this->addGroup($elements, $name, $groupLabel, $separator);
    } // end func addElementGroup
    
    // }}}
    // {{{ &getElement()

    /**
     * Returns a reference to the element
     *
     * @param     string     $element    Element name
     * @since     2.0
     * @access    public
     * @return    object     reference to element
     * @throws    HTML_QuickForm_Error
     */
    function &getElement($element)
    {
        if (isset($this->_elementIndex[$element])) {
            return $this->_elements[$this->_elementIndex[$element]];
        } else {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElement()", 'HTML_QuickForm_Error', true);
        }
    } // end func getElement

    // }}}
    // {{{ &getElementValue()

    /**
     * Returns the element's raw value
     * 
     * This returns the value as submitted by the form (not filtered) 
     * or set via setDefaults() or setConstants()
     *
     * @param     string     $element    Element name
     * @since     2.0
     * @access    public
     * @return    mixed     element value
     * @throws    HTML_QuickForm_Error
     */
    function &getElementValue($element)
    {
        if (isset($this->_elementIndex[$element])) {
            $value = $this->_elements[$this->_elementIndex[$element]]->getValue();
            if (isset($this->_duplicateIndex[$element])) {
                foreach ($this->_duplicateIndex[$element] as $index) {
                    $v = $this->_elements[$index]->getValue();
                    if (null !== $v) {
                        if (is_null($value)) {
                            $value = $v;
                        } elseif(!is_array($value)) {
                            $value = array($value, $v);
                        } else {
                            $value[] = $v;
                        }
                    }
                }
            }
            return $value;
        } else {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElementValue()", 'HTML_QuickForm_Error', true);
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
     * @return    mixed     submitted element value or null if not set
     */    
    function getSubmitValue($element)
    {
        return $this->_findElementValue($element);
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
      * @return     string    Type of the element, false if the element is not found
      */
     function getElementType($element)
     {
         if (isset($this->_elementIndex[$element])) {
             return $this->_elements[$this->_elementIndex[$element]]->getType();
         }
         return false;
     } // end func getElementType

     // }}}
     // {{{ updateElementAttr()

    /**
     * Updates Attributes for one or more elements
     *
     * @param      mixed    $elements   Array of element names/objects or string of elements to be updated
     * @param      mixed    $attrs      Array or sting of html attributes
     * @since      2.10
     * @access     public
     * @return     void
     */
    function updateElementAttr($elements, $attrs)
    {
        if (is_string($elements)) {
            $elements = split('[ ]?,[ ]?', $elements);
        }
        foreach ($elements as $element) {
            if (is_object($element) && is_subclass_of($element, 'HTML_QuickForm_element')) {
                $element->updateAttributes($attrs);
            } elseif (isset($this->_elementIndex[$element])) {
                $this->_elements[$this->_elementIndex[$element]]->updateAttributes($attrs);
            }
        }
    } // end func updateElementAttr

    // }}}
    // {{{ removeElement()

    /**
     * Removes an element
     *
     * @param string    $elementName The element name
     * @param boolean   $removeRules True if rules for this element are to be removed too                     
     *
     * @access public
     * @since 2.0
     * @return void
     * @throws HTML_QuickForm_Error
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
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$elementName' does not exist in HTML_QuickForm::removeElement()", 'HTML_QuickForm_Error', true);
        }
    } // end func removeElement

    // }}}
    // {{{ addHeader()

    /**
     * Adds a header element to the form (DEPRECATED)
     *
     * @param     string    $label      label of header
     * @since     1.0   
     * @access    public
     * @deprecated deprecated since 3.0, use addElement('header', ...) instead
     * @return    object A reference to a header element
     * @throws    PEAR_Error
     */
    function &addHeader($label)
    {
        return $this->addElement('header', null, $label);
    } // end func addHeader

    // }}}
    // {{{ addRule()

    /**
     * Adds a validation rule for the given field
     *
     * If the element is in fact a group, it will be considered as a whole.
     * To validate grouped elements as separated entities, 
     * use addGroupRule instead of addRule.
     *
     * @param    string     $element       Form element name
     * @param    string     $message       Message to display for invalid data
     * @param    string     $type          Rule type, use getRegisteredRules() to get types
     * @param    string     $format        (optional)Required for extra rule data
     * @param    string     $validation    (optional)Where to perform validation: "server", "client"
     * @param    boolean    $reset         Client-side validation: reset the form element to its original value if there is an error?
     * @param    boolean    $force         Force the rule to be applied, even if the target form element does not exist
     * @since    1.0
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function addRule($element, $message, $type, $format='', $validation='server', $reset = false, $force = false)
    {
        if (!$force) {
            if (!$this->elementExists($element)) {
                return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
            }
        }
        if (!$this->isRuleRegistered($type)) {
            return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
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
        $rule = array('type'        => $type,
                      'format'      => $format,
                      'message'     => $message,
                      'validation'  => $validation,
                      'reset'       => $reset);

        if ($type != 'function' && $this->getElementType($element) == 'group') {
            $rule['howmany'] = 1;
        }
        $this->_rules[$element][] = $rule;
    } // end func addRule

    // }}}
    // {{{ addGroupRule()

    /**
     * Adds a validation rule for the given group of elements
     *
     * Only groups with a name can be assigned a validation rule
     * Use addGroupRule when you need to validate elements inside the group.
     * Use addRule if you need to validate the group as a whole. In this case,
     * the same rule will be applied to all elements in the group.
     * Use addRule if you need to validate the group against a function.
     *
     * @param    string     $group         Form group name
     * @param    mixed      $arg1          Array for multiple elements or error message string for one element
     * @param    string     $type          (optional)Rule type use getRegisteredRules() to get types
     * @param    string     $format        (optional)Required for extra rule data
     * @param    int        $howmany       (optional)How many valid elements should be in the group
     * @param    string     $validation    (optional)Where to perform validation: "server", "client"
     * @since    2.5
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function addGroupRule($group, $arg1, $type='', $format='', $howmany=0, $validation = 'server')
    {
        if (!$this->elementExists($group)) {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Group '$group' does not exist in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
        }
        if (!isset($this->_rules[$group])) {
            $this->_rules[$group] = array();
        }
        $groupObj =& $this->getElement($group);
        if (is_array($arg1)) {
            $required = 0;
            foreach ($arg1 as $elementIndex => $rules) {
                $elementName = $groupObj->getElementName($elementIndex);
                if ($elementName !== false) {
                    foreach ($rules as $rule) {
                        $format = (isset($rule[2])) ? $rule[2] : '';
                        $type = $rule[1];
                        if (!$this->isRuleRegistered($type)) {
                            return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
                        }
                        $this->_rules[$group][] = array('type'        => $type,
                                                        'format'      => $format, 
                                                        'message'     => $rule[0],
                                                        'validation'  => 'server',
                                                        'elementName' => $elementName);
                        if ($type == 'required') {
                            $this->_required[] = $elementName;
                            $groupObj->_required[] = $elementName;
                            $required++;
                        }
                    }
                } else {
                    return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$elementIndex' not found in group '$group' in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
                }
            }
            if ($required > 0 && count($groupObj->getElements()) == $required) {
                $this->_required[] = $group;
            }
        } elseif (is_string($arg1)) {
            if (!$this->isRuleRegistered($type)) {
                return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
            }

            // Radios need to be handled differently when required
            if ($type == 'required' && $groupObj->getGroupType() == 'radio') {
                $howmany = ($howmany == 0) ? 1 : $howmany;
            }

            $this->_rules[$group][] = array('type'       => $type,
                                            'format'     => $format, 
                                            'message'    => $arg1,
                                            'validation' => $validation,
                                            'howmany'    => $howmany);
            if ($type == 'required') {
                $this->_required[] = $group;
            }
            if ($validation == 'client') {
                $this->updateAttributes(array('onsubmit'=>'return validate_'.$this->_attributes['name'] . '();'));
            }
        }
    } // end func addGroupRule

    // }}}
    // {{{ addFormRule()

   /**
    * Adds a global validation rule 
    * 
    * This should be used when for a rule involving several fields or if
    * you want to use some completely custom validation for your form.
    * The rule function/method should return true in case of successful 
    * validation and array('element name' => 'error') when there were errors.
    * 
    * @access   public
    * @param    mixed   Callback, either function name or array(&$object, 'method')
    * @throws   HTML_QuickForm_Error
    */
    function addFormRule($rule)
    {
        if (!$this->_callbackExists($rule)) {
            return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, 'Callback function does not exist in HTML_QuickForm::addFormRule()', 'HTML_QuickForm_Error', true);
        }
        $this->_formRules[] = $rule;
    }
    
    // }}}
    // {{{ addData()

    /**
     * Adds raw HTML (or text) data element to the form (DEPRECATED)
     *
     * @param string $data The data to add to the form object
     * @access public
     * @deprecated deprecated since 3.0, use addElement('html', ...) instead
     * @return object reference to a new element
     * @throws PEAR_Error
     */
    function &addData($data)
    {
        return $this->addElement('html', $data);
    }

    // }}}
    // {{{ applyFilter()

    /**
     * Applies a data filter for the given field(s)
     *
     * @param    mixed     $element       Form element name or array of such names
     * @param    mixed     $filter        Callback, either function name or array(&$object, 'method')
     * @since    2.0
     * @access   public
     */
    function applyFilter($element, $filter)
    {
        if (!$this->_callbackExists($filter)) {
            return PEAR::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::applyFilter()", 'HTML_QuickForm_Error', true);
        }
        if ($element == '__ALL__') {
            $this->_submitValues = $this->_recursiveFilter($filter, $this->_submitValues);
        } else {
            if (!is_array($element)) {
                $element = array($element);
            }
            foreach ($element as $elName) {
                if ($this->elementExists($elName)) {
                    if (isset($this->_submitValues[$elName])) {
                        $this->_submitValues[$elName] = $this->_recursiveFilter($filter, $this->_submitValues[$elName]);
                    }
                } else {
                    return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$elName' does not exist in HTML_QuickForm::applyFilter()", 'HTML_QuickForm_Error', true);
                }
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
     */
    function _recursiveFilter($filter, $value)
    {
        if (is_array($value)) {
            $cleanValues = array();
            foreach ($value as $k => $v) {
                $cleanValues[$k] = $this->_recursiveFilter($filter, $value[$k]);
            }
            return $cleanValues;
        } else {
            return call_user_func($filter, $value);
        }
    } // end func _recursiveFilter

    // }}}
    // {{{ _callbackExists()

   /**
    * Checks for callback function existance
    *
    * @param  mixed     a callback, like one used by call_user_func()
    * @access private
    * @return bool
    */
    function _callbackExists($callback)
    {
        if (is_string($callback)) {
            return function_exists($callback);
        } elseif (is_array($callback) && is_object($callback[0])) {
            return method_exists($callback[0], $callback[1]);
        } else {
            return false;
        }
    } // end func _callbackExists
    
    // }}}
    // {{{ isTypeRegistered()

    /**
     * Returns whether or not the form element type is supported
     *
     * @param     string   $type     Form element type
     * @since     1.0
     * @access    public
     * @return    boolean
     */
    function isTypeRegistered($type)
    {
        return isset($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type]);
    } // end func isTypeRegistered

    // }}}
    // {{{ getRegisteredTypes()

    /**
     * Returns an array of registered element types
     *
     * @since     1.0
     * @access    public
     * @return    array
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
     */
    function isRuleRegistered($name)
    {
        return isset($GLOBALS['_HTML_QuickForm_registered_rules'][$name]);
    } // end func isRuleRegistered

    // }}}
    // {{{ getRegisteredRules()

    /**
     * Returns an array of registered validation rules
     *
     * @since     1.0
     * @access    public
     * @return    array
     */
    function getRegisteredRules()
    {
        return array_keys($GLOBALS['_HTML_QuickForm_registered_rules']);
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
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setElementTemplate($html, $element = null)
    {
        $renderer =& $this->defaultRenderer();
        return $renderer->setElementTemplate($html, $element);
    } // end func setElementTemplate

    // }}}
    // {{{ setHeaderTemplate()

    /**
     * Sets header template 
     *
     * @param     string   $html    The HTML surrounding the header 
     * @since     2.0
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setHeaderTemplate($html)
    {
        $renderer =& $this->defaultRenderer();
        return $renderer->setHeaderTemplate($html);
    } // end func setHeaderTemplate

    // }}}
    // {{{ setFormTemplate()

    /**
     * Sets form template 
     *
     * @param     string   $html    The HTML surrounding the form tags 
     * @since     2.0
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setFormTemplate($html)
    {
        $renderer =& $this->defaultRenderer();
        return $renderer->setFormTemplate($html);
    } // end func setFormTemplate

    // }}}
    // {{{ setRequiredNoteTemplate()

    /**
     * Sets element template 
     *
     * @param     string   $html    The HTML surrounding the required note 
     * @since     2.0
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access    public
     * @return    void
     */
    function setRequiredNoteTemplate($html)
    {
        $renderer =& $this->defaultRenderer();
        return $renderer->setRequiredNoteTemplate($html);
    } // end func setElementTemplate

    // }}}
    // {{{ clearAllTemplates()

    /**
     * Clears all the HTML out of the templates that surround notes, elements, etc.
     * Useful when you want to use addData() to create a completely custom form look
     *
     * @since   2.0
     * @deprecated deprecated since 3.0, use renderers for controlling the presentation
     * @access  public
     * @return void
     */
    function clearAllTemplates()
    {
        $renderer =& $this->defaultRenderer();
        return $renderer->clearAllTemplates();
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
     */
    function getRequiredNote()
    {
        return $this->_requiredNote;
    } // end func getRequiredNote

    // }}}
    // {{{ _findElementValue()

    /**
     * Tries to find the element value from the submitted values array
     * 
     * @since     2.7
     * @access    private
     * @return    mixed     value if found or null
     */
    function _findElementValue($elementName)
    {
        if (isset($this->_submitValues[$elementName])) {
            return $this->_submitValues[$elementName];
        }
        $elementType = $this->getElementType($elementName);
        if ($elementType == 'file') {
            return $this->_submitFiles[$elementName];
        } elseif ($elementType == 'group') {
            $group =& $this->getElement($elementName);
            $values = null;
            $elements =& $group->getElements();
            foreach (array_keys($elements) as $key) {
                $name = $group->getElementName($key);
                if ($name != $elementName) {
                    // filter out radios
                    $values[$name] = $this->_findElementValue($name);
                }
            }
            return $values;
        }
        $myVar = str_replace(array(']', '['), array('', "']['"), $elementName);
        $myVar = "['".$myVar."']";
        return eval("return (isset(\$this->_submitValues$myVar)) ? \$this->_submitValues$myVar : null;");
    } //end func _findElementValue

    // }}}
    // {{{ validate()

    /**
     * Performs the server side validation
     * @access    public
     * @since     1.0
     * @return    boolean   true if no error found
     */
    function validate()
    {
        if (count($this->_rules) == 0 && count($this->_submitValues) > 0) {
            return true;
        } elseif (count($this->_rules) == 0 || count($this->_submitValues) == 0) {
            return false;
        }

        foreach ($this->_rules as $target => $rules) {
            $elementType = $this->getElementType($target);
            $submitValue = $this->_findElementValue($target);

            foreach ($rules as $rule) {
                if (isset($this->_errors[$target])) {
                    continue 2;                
                }
                $type     = $rule['type'];
                $message  = $rule['message'];
                $format   = $rule['format'];
                $ruleData = $GLOBALS['_HTML_QuickForm_registered_rules'][$type];

                if ($elementType != 'group') {
                    if (!$this->_validateElement($target, $submitValue, $format, $ruleData)) {
                        $this->_errors[$target] = $message;
                    }
                } else {
                    // Element is group
                    $elementName = isset($rule['elementName']) ? $rule['elementName'] : null;
                    $howmany     = isset($rule['howmany']) ? $rule['howmany'] : null;
                    if (!$this->_validateGroup($target, $submitValue, $format, $ruleData, $elementName, $howmany)) {
                        $this->_errors[$target] = $message;
                    }
                }
            }
        }

        // process the global rules now
        foreach ($this->_formRules as $rule) {
            if (true !== ($res = call_user_func($rule, $this->_submitValues, $this->_submitFiles))) {
                $this->_errors += $res;
            }
        }

        return (0 == count($this->_errors));
    } // end func validate

    // }}}
    // {{{ _validateElement()

    /**
     * Performs the server side validation for an element
     *
     * If the element is in fact a group, it will be considered as a whole.
     * To validate grouped elements as separated entities, 
     * use addGroupRule instead of addRule.
     *
     * @param     string   $groupName   Group name
     * @param     mixed    $submitValue Submitted values to be checked
     * @param     string   $format      Optional rule parameter
     * @param     array    $ruleData    Rule data found in _registeredRules
     * @access    private
     * @since     2.7
     * @return    bool     True on success, false if error found
     */
    function _validateElement($elementName, $submitValue, $format, $ruleData)
    {
        if (is_array($submitValue)) {
            if (count($submitValue) == 0) {
                $submitValue = '';
            } elseif ($ruleData[0] != 'function') {
                // used when values are in an array, ex. select multiple
                return $this->_validateGroup($elementName, $submitValue, $format, $ruleData, null, 1);
            }
        }
        switch ($ruleData[0]) {
            case 'regex':
                if ((!isset($submitValue) || $submitValue == '') && 
                    !$this->isElementRequired($elementName)) {
                    // Element is not required
                    return true;
                }
                $regex = str_replace('%data%', $format, $ruleData[1]);
                if (!preg_match($regex, $submitValue)) {
                    return false;
                }
                break;
            case 'function':
                if (isset($ruleData[2])) {
                    return call_user_func(array($ruleData[2], $ruleData[1]), $submitValue, $format);
                } elseif (method_exists($this, $ruleData[1])) {
                    return $this->$ruleData[1]($elementName, $submitValue, $format);
                } else {
                    return $ruleData[1]($elementName, $submitValue, $format);
                }
                break;
        }
        return true;
    } // end func _validateElement

    // }}}
    // {{{ _validateGroup()

    /**
     * Performs the server side validation for grouped elements
     *
     * @param     string   $groupName   Group name
     * @param     mixed    $values      Submitted values to be checked
     * @param     string   $format      Optional rule parameter
     * @param     array    $ruleData    Rule data found in _registeredRules
     * @param     string   $elementName Name of the element to validate in group
     * @param     int      $howmany     How many valid elements should be in the group
     * @access    private
     * @since     2.7
     * @return    bool     True on success, false if error found
     */
    function _validateGroup($groupName, $values, $format, $ruleData, $elementName = null, $howmany = null)
    {
        if (!is_null($elementName)) {
            if (is_array($values)) {
                $submitValue = $this->_findElementValue($elementName);
            }
            return $this->_validateElement($elementName, $submitValue, $format, $ruleData);

        } elseif (!is_null($howmany) && $ruleData[0] != 'function') {
            // the same rule is applied to every elements in the group
            $total = 0;
            if (!is_array($values)) {
                $values = array($values);
            }
            foreach ( $values as $value ) {
                if ($this->_validateElement($groupName, $value, $format, $ruleData)) {
                    $total++;
                }
            }
            if ($howmany == 0) {
                 $group =& $this->getElement($groupName);
                 $howmany = count($group->getElements());
            }
            if ($total < $howmany) {
                return false;
            } else {
                return true;
            }
        } else {
            // treat as a standard element (function validation set with addRule())
            return $this->_validateElement($groupName, $values, $format, $ruleData);
        }
    } // end func _validateGroup

    // }}}
    // {{{ freeze()

    /**
     * Displays elements without HTML input tags
     *
     * @param    mixed   $elementList       array or string of element(s) to be frozen
     * @since     1.0
     * @access   public
     * @throws   HTML_QuickForm_Error
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
    // {{{ isFrozen()

    /**
     * Returns whether or not the whole form is frozen
     *
     * @since     3.0
     * @access    public
     * @return    boolean
     */
    function isFrozen()
    {
         return $this->_freezeAll;
    } // end func isFrozen

    // }}}
    // {{{ process()

    /**
     * Performs the form data processing
     *
     * @param    mixed     $callback        Callback, either function name or array(&$object, 'method')
     * @param    bool      $mergeFiles      Whether uploaded files should be processed too
     * @since    1.0
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    function process($callback, $mergeFiles = true)
    {
        if (!$this->_callbackExists($callback)) {
            return PEAR::raiseError(null, QUICKFORM_INVALID_PROCESS, null, E_USER_WARNING, "Callback function does not exist in QuickForm::process()", 'HTML_QuickForm_Error', true);
        }
        $values = ($mergeFiles === true) ? array_merge($this->_submitValues, $this->_submitFiles) : $this->_submitValues;
        return call_user_func($callback, $values);
    } // end func process

    // }}}
    // {{{ accept()

   /**
    * Accepts a renderer
    *
    * @param object     An HTML_QuickForm_Renderer object
    * @since 3.0
    * @access public
    * @return void
    */
    function accept(&$renderer)
    {
        $renderer->startForm($this);
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            if ($this->_freezeAll) {
                $element->freeze();
            }
            $elementName = $element->getName();
            $required    = ($this->isElementRequired($elementName) && $this->_freezeAll == false);
            $error       = $this->getElementError($elementName);
            $element->accept($renderer, $required, $error);
        }
        $renderer->finishForm($this);
    } // end func accept

    // }}}
    // {{{ defaultRenderer()

   /**
    * Returns a reference to default renderer object
    *
    * @access public
    * @since 3.0
    * @return object a default renderer object
    */
    function &defaultRenderer()
    {
        if (!isset($GLOBALS['_HTML_QuickForm_default_renderer'])) {
            include_once('HTML/QuickForm/Renderer/Default.php');
            $GLOBALS['_HTML_QuickForm_default_renderer'] =& new HTML_QuickForm_Renderer_Default();
        }
        return $GLOBALS['_HTML_QuickForm_default_renderer'];
    } // end func defaultRenderer

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
            $this->addElement('html', $in_data);
        }
        $renderer =& $this->defaultRenderer();
        $this->accept($renderer);
        return $renderer->toHtml();
    } // end func toHtml

    // }}}
    // {{{ getValidationScript()

    /**
     * Returns the client side validation script
     *
     * @since     2.0
     * @access    public
     * @return    string    Javascript to perform validation, empty string if no 'client' rules were added
     */
    function getValidationScript()
    {
        if (empty($this->_rules) || $this->_freezeAll) {
            return '';
        }
        $html = '';
        $tabs = $this->_getTabs();
        $test = array();
        for (reset($this->_rules); $elementName = key($this->_rules); next($this->_rules)) {
            $rules = pos($this->_rules);
            foreach ($rules as $rule) {
                $type       = $rule['type'];
                $validation = $rule['validation'];
                $message    = $rule['message'];
                $format     = $rule['format'];
                $reset      = (isset($rule['reset'])) ? $rule['reset'] : false;
                $ruleData   = $GLOBALS['_HTML_QuickForm_registered_rules'][$type];
                if ($validation == 'client') {
                    $index = $this->_elementIndex[$elementName];
                    if ($this->_elements[$index]->getType() == 'group' ||
                        ($this->_elements[$index]->getType() == 'select' && $this->_elements[$index]->getMultiple())) {
                        $value =
                            "$tabs\t\tvar value = '';\n" .
                            "$tabs\t\tfor (var i = 0; i < frm.elements.length; i++) {\n" .
                            "$tabs\t\t\tif (frm.elements[i].name.indexOf('$elementName') == 0) {\n" .
                            "$tabs\t\t\t\tvalue += frm.elements[i].value;\n" .
                            "$tabs\t\t\t}\n" .
                            "$tabs\t\t}";
                        if ($reset) {
                            $tmp_reset =
                                "$tabs\t\t\tfor (var i = 0; i < frm.elements.length; i++) {\n" .
                                "$tabs\t\t\t\tif (frm.elements[i].name.indexOf('$elementName') == 0) {\n" .
                                "$tabs\t\t\t\t\tfrm.elements[i].value = frm.elements[i].defaultValue;\n" .
                                "$tabs\t\t\t\t}\n" .
                                "$tabs\t\t\t}\n";
                        } else {
                            $tmp_reset = '';
                        }
                    } elseif ($this->_elements[$index]->getType() == 'checkbox') {
                        $value = "$tabs\t\tif (frm.elements['$elementName'].checked) {\n" .
                                 "$tabs\t\t\tvar value = 1;\n" .
                                 "$tabs\t\t} else {\n" .
                                 "$tabs\t\t\tvar value = '';\n" .
                                 "$tabs\t\t}";
                        $tmp_reset = ($reset) ? "$tabs\t\tfield.checked = field.defaultChecked;\n" : '';
                    } else {
                        $value = "$tabs\t\tvar value = frm.elements['$elementName'].value;";
                        $tmp_reset = ($reset) ? "$tabs\t\tfield.value = field.defaultValue;\n" : '';
                    }
                    switch ($ruleData[0]) {
                        case 'regex':
                            $regex = str_replace('%data%', $format, $ruleData[1]);
                            if (!$this->isElementRequired($elementName)) {
                                // This regex will make the rule optional and preserve your delimiters
                                $regex = preg_replace('/^(\/|.*[^\^])(.*)\1$/', '$1^$|$2$1', $regex);
                            }
                            $test[] =
                                "$value\n" .
                                "$tabs\t\tvar field = frm.elements['$elementName'];\n"  .
                                "$tabs\t\tvar regex = $regex;\n"  .
                                "$tabs\t\tif (!regex.test(value) && !errFlag['$elementName']) {\n" .
                                "$tabs\t\t\terrFlag['$elementName'] = true;\n" .
                                "$tabs\t\t\t_qfMsg = unescape(_qfMsg + '\\n - ".rawurlencode($message)."');\n".
                                $tmp_reset.
                                "$tabs\t\t}";
                            break;
                        case 'function':
                            $test[] =
                                "$value\n" .
                                "$tabs\t\tvar field = frm.elements['$elementName'];\n"  .
                                "$tabs\t\tif (!" . $ruleData[1] . "('$elementName', value) && !errFlag['$elementName']) {\n" .
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
                "$tabs\n\t\tif (_qfMsg != '') {\n" .
                "$tabs\t\t\t_qfMsg = '$this->_jsPrefix' + _qfMsg;\n" .
                "$tabs\t\t\t_qfMsg = _qfMsg + '\\n$this->_jsPostfix';\n" .
                "$tabs\t\t\talert(_qfMsg);\n" .
                "$tabs\t\t\treturn false;\n" .
                "$tabs\t\t}\n" .
                "$tabs\t\treturn true;\n" .
                "$tabs }\n";
            $html = "$tabs\n<script type=\"text/javascript\">\n" .
                    "$tabs<!-- \n" . $html . "$tabs//-->\n" .
                    "$tabs</script>";
        }
        return $html;
    } // end func getValidationScript

    // }}}
    // {{{ getAttributesString()

    /**
     * Returns the HTML attributes of the form (DEPRECATED)
     *
     * @since     2.0
     * @access    public
     * @return    string
     * @deprecated  Use HTML_Common::getAttributes(true)
     */
    function getAttributesString()
    {
        return $this->getAttributes(true);
    } // end func getAttributesString

    // }}}
    // {{{ getSubmitValues()

    /**
     * Returns the values submitted by the form
     *
     * @since     2.0
     * @access    public
     * @param     bool      Whether uploaded files should be returned too
     * @return    array
     */
    function getSubmitValues($mergeFiles = false)
    {
        return $mergeFiles? array_merge($this->_submitValues, $this->_submitFiles): $this->_submitValues;
    } // end func getSubmitValues

    // }}}
    // {{{ toArray()

    /**
     * Returns the form's contents in an array.
     *
     * The description of the array structure is in HTML_QuickForm_Renderer_Array docs
     * 
     * @since     2.0
     * @access    public
     * @return    array of form contents
     */
    function toArray()
    {
        include_once 'HTML/QuickForm/Renderer/Array.php';
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $this->accept($renderer);
        return $renderer->toArray();
     } // end func toArray

    // }}}
    // {{{ exportValue()

    /**
     * Returns a 'safe' element's value
     * 
     * This method first tries to find a cleaned-up submitted value,
     * it will return a value set by setValue()/setDefaults()/setConstants()
     * if submitted value does not exist for the given element.
     *
     * @param  string   Name of an element
     * @access public
     * @return mixed
     */
    function exportValue($element)
    {
        if (isset($this->_elementIndex[$element])) {
            $value = $this->_elements[$this->_elementIndex[$element]]->exportValue($this->_submitValues, false);
            if (isset($this->_duplicateIndex[$element])) {
                foreach ($this->_duplicateIndex[$element] as $index) {
                    $v = $this->_elements[$index]->exportValue($this->_submitValues, false);
                    if (null !== $v) {
                        if (is_null($value)) {
                            $value = $v;
                        } elseif(!is_array($value)) {
                            $value = array($value, $v);
                        } else {
                            $value[] = $v;
                        }
                    }
                }
            }
            return $value;
        } else {
            return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElementValue()", 'HTML_QuickForm_Error', true);
        }
    }

    // }}}
    // {{{ exportValues()

    /**
     * Returns 'safe' elements' values
     *
     * Unlike getSubmitValues(), this will return only the values 
     * corresponding to the elements present in the form.
     * 
     * @param   mixed   Array/string of element names, whose values we want. If not set then return all elements.
     * @access  public
     * @return  array   An assoc array of elements' values
     * @throws  HTML_QuickForm_Error
     */
    function exportValues($elementList = null)
    {
        $values = array();
        if (null === $elementList) {
            // iterate over all elements, calling their exportValue() methods
            foreach (array_keys($this->_elements) as $key) {
                $value = $this->_elements[$key]->exportValue($this->_submitValues, true);
                if (is_array($value)) {
                    // This shit throws a bogus warning in PHP 4.3.x
                    $values = @array_merge_recursive($values, $value);
                }
            }
        } else {
            if (!is_array($elementList)) {
                $elementList = array_map('trim', explode(',', $elementList));
            }
            foreach ($elementList as $elementName) {
                $value = $this->exportValue($elementName);
                if (PEAR::isError($value)) {
                    return $value;
                }
                $values[$elementName] = $value;
            }
        }
        return $values;
    }

    // }}}
    // {{{ isError()

    /**
     * Tell whether a result from a QuickForm method is an error (an instance of HTML_QuickForm_Error)
     *
     * @access public
     * @param mixed     result code
     * @return bool     whether $value is an error
     */
    function isError($value)
    {
        return (is_object($value) && (get_class($value) == 'html_quickform_error' || is_subclass_of($value, 'html_quickform_error')));
    } // end func isError

    // }}}
    // {{{ errorMessage()

    /**
     * Return a textual error message for an QuickForm error code
     *
     * @access  public
     * @param   int     error code
     * @return  string  error message
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
                QUICKFORM_UNREGISTERED_ELEMENT  => 'unregistered element',
                QUICKFORM_INVALID_ELEMENT_NAME  => 'element already exists',
                QUICKFORM_INVALID_PROCESS       => 'process callback does not exist'
            );
        }

        // If this is an error object, then grab the corresponding error code
        if (HTML_QuickForm::isError($value)) {
            $value = $value->getCode();
        }

        // return the textual error message corresponding to the code
        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[QUICKFORM_ERROR];
    } // end func errorMessage

    // }}}
} // end class HTML_QuickForm

class HTML_QuickForm_Error extends PEAR_Error {

    // {{{ properties

    /**
    * Prefix for all error messages
    * @var string
    */
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
        } else {
            $this->PEAR_Error("Invalid error code: $code", QUICKFORM_ERROR, $mode, $level, $debuginfo);
        }
    }

    // }}}
} // end class HTML_QuickForm_Error
?>