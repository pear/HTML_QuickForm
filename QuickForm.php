<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
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

require_once("PEAR.php");
require_once("HTML/Common.php");

$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'] = 
        array(
            'group'     =>array('HTML/QuickForm/Elements/group.php','HTML_QuickForm_group'),
            'hidden'    =>array('HTML/QuickForm/Elements/hidden.php','HTML_QuickForm_hidden'),
            'reset'     =>array('HTML/QuickForm/Elements/reset.php','HTML_QuickForm_reset'),
            'checkbox'  =>array('HTML/QuickForm/Elements/checkbox.php','HTML_QuickForm_checkbox'),
            'file'      =>array('HTML/QuickForm/Elements/file.php','HTML_QuickForm_file'),
            'image'     =>array('HTML/QuickForm/Elements/image.php','HTML_QuickForm_image'),
            'password'  =>array('HTML/QuickForm/Elements/password.php','HTML_QuickForm_password'),
            'radio'     =>array('HTML/QuickForm/Elements/radio.php','HTML_QuickForm_radio'),
            'button'    =>array('HTML/QuickForm/Elements/button.php','HTML_QuickForm_button'),
            'submit'    =>array('HTML/QuickForm/Elements/submit.php','HTML_QuickForm_submit'),
            'select'    =>array('HTML/QuickForm/Elements/select.php','HTML_QuickForm_select'),
            'text'      =>array('HTML/QuickForm/Elements/text.php','HTML_QuickForm_text'),
            'textarea'  =>array('HTML/QuickForm/Elements/textarea.php','HTML_QuickForm_textarea')
        );

// {{{ error codes

/*
 * Error codes for the QuickForm interface, which will be mapped to textual messages
 * in the QuickForm::errorMessage() function.  If you are to add a new error code, be
 * sure to add the textual messages to the QuickForm::errorMessage() function as well
 */

define("QUICKFORM_OK",                      1);
define("QUICKFORM_ERROR",                  -1);
define("QUICKFORM_INVALID_RULE",           -2);
define("QUICKFORM_NONEXIST_ELEMENT",       -3);
define("QUICKFORM_EMPTY_ARGUMENT",         -4);
define("QUICKFORM_UNREGISTERED_ELEMENT",   -5);

// }}}

/**
 * Create, validate and process HTML forms
 *
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @version     1.1
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
     * Array containing element name and type relationship
     * @since     1.1
     * @var  array
     * @access   private
     */
    var $_elementTypes = array();

    /**
     * Array containing required field IDs
     * @since     1.0
     * @var  array
     * @access   private
     */ 
    var $_required = array();
    
    /**
     * Flag to know if form contains a file input
     * @since     1.0
     * @var  boolean
     * @access   private
     */
    var $_fileFlag = false;
    
    /**
     * Prefix message in javascript alert if error
     * @since     1.0
     * @var  string
     * @access   public
     */ 
    var $_jsPrefix = "Invalid information entered.";    
    
    /**
     * Postfix message in javascript alert if error
     * @since     1.0
     * @var  string
     * @access   public
     */ 
    var $_jsPostfix = "Please correct these fields.";   

    /**
     * Array of form values
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_elementValues = array();
    
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
     * Array containing the hidden fields
     * @var  array
     * @access   private
     */
    var $_hidden = array();

    /**
     * Array containing the frozen fields
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_frozen = array();
    
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
    var $_requiredNote = "<font size=\"1\" color=\"#FF0000\">*</font><font size=\"1\"> denotes required field</font>";
    
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
            'lettersonly'   =>array('regex', '/^[a-zA-Z]*$/'),
            'alphanumeric'  =>array('regex', '/^[a-zA-Z0-9]*$/'),
            'numbersonly'   =>array('regex', '/^[0-9]*$/'),
            'uploadedfile'  =>array('function', '_isUploadedFile'),
            'maxfilesize'   =>array('function', '_checkMaxFileSize'),
            'mimetype'      =>array('function', '_checkMimeType'),
            'filename'      =>array('function', '_checkFileName')
        );

    // }}}
    // {{{ HTML_QuickForm()

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
        $method = (strtoupper($method) == 'GET') ? 'GET' : 'POST';
        $action = ($action == "") ? $GLOBALS['PHP_SELF'] : $action;
        HTML_Common::HTML_Common($attributes);
        $this->updateAttributes(array('action'=>$action, 'method'=>$method, 'name'=>$formName, 'target'=>$target));
        $this->_registeredTypes = &$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'];
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
        return 1.1;
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
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$typeName] = array($include, $className);
    } // end func registerElementType

    // }}}
    // {{{ registerRule()

    /**
     * Registers a new validation rule
     *
     * @param     string    $ruleName   Name of validation rule
     * @param     string    $type       Either: 'regex' or 'function'
     * @param     string    $data1       Name of function or regular expression
     * @param     string    $data2       Object parent of above function
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
        if (!isset($element)) {
            return PEAR::raiseError(null, QUICKFORM_EMPTY_ARGUMENT, null, E_USER_WARNING, "Argument to HTML_QuickForm::elementExists is empty", 'HTML_QuickForm_Error', true);
        } else {
            for ($i=0;$i<count($this->_elements);$i++){
                if (isset($this->_elements[$i]["object"])) {
                    unset ($elementName);
                    $elementName = $this->_elements[$i]["object"]->getName();
                    if ($elementName == $element) {
                        return true;
                    }
                }
            }
            return false;
        }
    } // end func elementExists
    
    // }}}
    // {{{ loadDefaults()

    /**
     * Initializes default form values
     *
     * @param     array   $defaultValues        values used to fill the form    
     * @since     1.0
     * @access    public
     * @return    void
     */
    function loadDefaults($defaultValues=null)
    {
        if (is_array($defaultValues)) {
            while(list($key,$value)=each($defaultValues)) {
                $value = is_string($value) ? stripslashes($value) : $value;             
                $this->_elementValues[$key] = $value;
            }
        }
    } // end func loadDefaults

    // }}}
    // {{{ loadValues()

    /**
     * Load form elements with submitted values
     *
     * @param     array   $elementList      list of elements to be loaded with their values 
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function loadValues($elementList=null)
    {
        if (count($GLOBALS["HTTP_" . $this->_attributes["method"] . "_VARS"]) == 0) {
            return;
        }
        $this->_elementValues = array();
        $this->_submitValues = $GLOBALS["HTTP_" . $this->_attributes["method"] . "_VARS"];
        if (is_array($elementList)) {
            while(list($key,$val)=each($elementList)) {
                $value = $this->_submitValues[$val];
                $value = is_string($value) ? stripslashes($value) : $value;             
                $this->_elementValues[$val] = $value;
            }
        } elseif ($elementList == null) {
            for (reset($this->_submitValues); $key = key($this->_submitValues); next($this->_submitValues)) {
                $value = pos($this->_submitValues);
                $value =  is_string($value) ? stripslashes($value) : $value;
                $this->_elementValues[$key] = $value;
            }
        } else {
            $this->_elementValues[$elementList] = $this->_submitValues[$elementList];       
        }

        $this->_submitFiles = $GLOBALS["HTTP_POST_FILES"];
        for (reset($this->_submitFiles); $key = key($this->_submitFiles); next($this->_submitFiles)) {
            $value = pos($this->_submitFiles);
            $value = is_string($value) ? stripslashes($value) : $value;
            $this->_submitFiles[$key] = $value;
            $this->$key = $value;
        }
    } // end func loadValues
    
    // }}}
    // {{{ moveUploadedFile()

    /**
     * Moves an uploaded file into the destination 
     * @param    string  $element  
     * @param    string  $dest
     * @param    string  $fileName	(optional)destination name for uploaded file
     * @since     1.0
     * @access   public
     */
    function moveUploadedFile($element, $dest, $fileName='')
    {
        $file = $this->_submitFiles[$element];
        if ($dest != ""  && substr($dest, -1) != "/")
            $dest .= "/";
        $fileName = ($fileName != '') ? $fileName : $file['name'];
        if (copy($file["tmp_name"], $dest . $fileName)) {
            @unlink($file["tmp_name"]);
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
     * @return    object extended class of HTML_QuickForm_element
     * @throws    
     */
    function &createElement($elementType, $elementName, $mixed=null, $elementLabel=null, $attributes=null)
    {
        $elementType = strtolower($elementType);
        if (!HTML_QuickForm::isTypeRegistered($elementType)) {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "HTML_QuickForm element type '$elementType' is not registered", 'HTML_QuickForm_Error', true);
        }
        $objectName = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$elementType][1];
        $includeFile = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$elementType][0];
        !include_once $includeFile;
        $elementObject = new $objectName($elementName, $mixed, $attributes);
        if (($elementType == 'checkbox' OR $elementType == 'radio') AND isset($elementLabel)) {
            $elementObject->setLabel($elementLabel);
        }
        return $elementObject;
    } // end func createElement

    // }}}
    // {{{ addElement()

    /**
     * Adds an element into the form
     *
     * @param    string     $elementType    type of element to add (text, textarea, file...)
     * @param    string     $elementName    form name of this element
     * @param    string     $value          (optional)value of this element
     * @param    array      $attributes     (optional)associative array with extra attributes (can be html or custom)
     * @param    string     $elementLabel   (optional)label of this element
     * @since     1.0
     * @return   void
     * @access   public
     */
    function addElement($elementType, $elementName, $value=null, $attributes=null, $elementLabel=null)
    {
        if ($elementType == 'file' AND $this->_fileFlag == false) {
            $this->updateAttributes(array('method'=>'POST', 'enctype'=>'multipart/form-data'));
            $err = &$this->addElement('hidden', 'MAX_FILE_SIZE', $this->_maxFileSize, '');
            if (PEAR::isError($err)) {
                return $err;
            }
            $this->_fileFlag = true;
        }
        if (isset($this->_elementValues[$elementName]) && $elementType != 'select') {
            $value = $this->_elementValues[$elementName];
        }
        $elementObject = &$this->createElement($elementType, $elementName, $value, $elementLabel, $attributes);
        if (PEAR::isError($elementObject)) {
            return $elementObject;
        }
        $this->_elements[] = array('label'=>$elementLabel,'object'=>$elementObject);
        $this->_elementTypes[$elementName] = $elementType;
    } // end func addElement

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
    // {{{ addElementGroup()

    /**
     * Adds an element group
     * @param    array      $elements       array of elements composing the group
     * @param    string     $label          (optional)group label
     * @param    string     $name           (optional)group name
     * @param    string     $layout         (optional)defaults to row, can be cols
     * @return   void
     * @since     1.0
     * @access   public
     * @throws   PEAR_Error
     */
    function addElementGroup($elements, $label="", $name=null, $groupLayout="rows")
    {
        // Update form attributes if there is a file input in the elements array
        foreach ($elements as $el) {
            if ($this->_fileFlag == true) {
                break;
            }
            if ($el->getType() == 'file') {
                $this->updateAttributes(array("method"=>"POST", "enctype"=>"multipart/form-data"));
                $err = &$this->addElement('hidden', 'MAX_FILE_SIZE', $this->_maxFileSize, '');
                if (PEAR::isError($err)) {
                    return $err;
                }
                $this->_fileFlag = true;
            }
        }
        $elementObject = &HTML_QuickForm::createElement('group', $name, $elements, null, $groupLayout);
        if (PEAR::isError($elementObject)) {
            return $elementObject;
        }
        $this->_elements[] = array("label"=>$label,"object"=>$elementObject);
    } // end func addElementGroup
        
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
         if (isset($this->_elementTypes[$element])) {
             return $this->_elementTypes[$element];
         }
         return false;
     } // end func getElementType

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
        $this->_elements[] = array("header"=>$label);
    } // end func addHeader

    // }}}
    // {{{ addRule()

    /**
     * Adds a validation rule for the given field
     *
     * @param    string  $element       Form element name
     * @param    string  $message       Message to display for invalid data
     * @param    int     $type          Rule type use getRegisteredType to get types
     * @param    string  $format        (optional)Required for extra rule data
     * @param    int     $validation    (optional)Where to perform validation: "server", "client"
     * @since     1.0
     * @access   public
     */
    function addRule($element, $message="", $type="", $format="", $validation="server")
    {
        if (!$this->elementExists($element)) {
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::addRule", 'HTML_QuickForm_Error', true);
        }
        if ($type == "required") {
            $this->_required[] = $element;
        }
        if (!isset($this->_rules[$element])) {
            $this->_rules[$element] = array();
        }
        if ($validation == 'client') {
            $this->updateAttributes(array('onsubmit'=>'return validate_' . $this->_attributes['name'] . '();'));
        }
        $this->_rules[$element][] = array("type"=>$type, 
            "format"=>$format, "message"=>$message, "validation"=>$validation);
    } // end func addRule

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
        $html = "";
        if ($required) {
            $label = "<font color=\"#FF0000\">*</font>$label";
        }
        $html .= 
            "\n$tabs\t<tr>\n" .
            "$tabs\t\t<td align=\"right\" valign=\"top\"><b>$label&nbsp;</b></td>\n" .
            "$tabs\t\t<td nowrap=\"nowrap\" valign=\"top\" align=\"left\">";
        if ($error != null) {
            $html .= 
                "<font color=\"#FF0000\">$error</font><br>";
        }
        $html .= 
            $element->toHtml() .
            "</td>\n" .
            "$tabs\t</tr>";
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
        $html .= 
            "\n$tabs\t<tr>\n" .
            "$tabs\t\t<td nowrap=\"nowrap\" align=\"left\" valign='top' " .
            "colspan=\"2\" bgcolor=\"#CCCCCC\"><b>$header</b></td>\n" .
            "$tabs\t</tr>";
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
        $html = "";
        $tabs = $this->_getTabs();
        $html .= 
            "\n$tabs<table border=\"0\">\n" .
            "$tabs\t<form".$this->_getAttrString($this->_attributes).">" .
            $content .
            "\n$tabs\t</form>\n" .
            "$tabs</table>";
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
        $html = "";
        $tabs = $this->_getTabs();
        $html .= 
            "\n$tabs\t<tr>\n" .
            "$tabs\t\t<td>&nbsp;</td>\n" .
            "$tabs\t\t<td align=\"left\" valign=\"top\">$this->_requiredNote</td>\n" .
            "$tabs\t</tr>";
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
        $html        = "";
        $label       = $element["label"];
        $object      = $element["object"];
        $elementName = $object->getName();
        $elementType = $object->getType();
        $required    = ($this->isElementRequired($elementName) && $this->_freezeAll == false);
        $error       = $this->getElementError($elementName);
        if (isset($this->_elementValues[$elementName])) {
            $object->setValue($this->_elementValues[$elementName]);
        }
        if (($this->isElementFrozen($elementName) || $this->_freezeAll == true) && $elementType != 'hidden') {
            $object->freeze();
            if ($object->persistantFreeze()) {
                $elementValue = $object->getValue();
                if (is_array($elementValue)) {
                    while (list($key, $value) = each($elementValue)) {
                        $this->addElement('hidden', $elementName . "[$key]", $value);
                    }
                } else {
                    $this->addElement('hidden', $elementName, $elementValue);
                }
            }
        }
        if ($object->getType() != 'hidden') {
            $object->setTabOffset($this->getTabOffset() + 3);
            $html = $this->_wrapElement($object, $label, $required, $error);
        } else {
            $object->setTabOffset($this->getTabOffset() + 1);
            $html = $object->toHtml();
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
        $header = $element["header"];
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
        $html = "";
        $tabs = $this->_getTabs();
        $html .=
            "\n$tabs<script language=\"javascript\">\n" .
            "$tabs<!-- \n" .
            "$tabs\tfunction validate_" . $this->_attributes['name'] . "() {\n" .
            "$tabs\t\terrFlag = new Array();\n" .
            "$tabs\t\tmsg = '';\n" .
            "$tabs\t\tfrm = document.forms['" . $this->_attributes['name'] . "'];\n";
        for (reset($this->_rules); $elementName=key($this->_rules); next($this->_rules)) {
            $rules = pos($this->_rules);
            for ($i=0; $i < count($rules); $i++) {
                $type       = $rules[$i]["type"];
                $validation = $rules[$i]["validation"];
                $message    = $rules[$i]["message"];
                $format     = $rules[$i]["format"];
                $ruleData = $this->_registeredRules[$type];
                // error out if the rule does not exist
                if (empty($ruleData)) {
                    return PEAR::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Tried to register rule of type '$type'", 'HTML_QuickForm_Error', true);
                }
                if ($validation == "client") {
                    switch ($ruleData[0]) {
                        case 'regex':
                            $regex = str_replace('%data%', $format, $ruleData[1]);
                            $html .=
                                "$tabs\t\tvar field = frm.elements['$elementName'];\n"  .
                                "$tabs\t\tvar regex = $regex;\n"  .
                                "$tabs\t\tif (!regex.test(field.value) && !errFlag['$elementName']) {\n" .
                                "$tabs\t\t\terrFlag['$elementName'] = true;\n" .
                                "$tabs\t\t\tmsg = msg + '\\n - $message';\n" .
                                "$tabs\t\t}\n";
                            break;
                        case 'function':
                            $html .=
                                "$tabs\t\tvar field = frm.elements['$elementName'];\n"  .
                                "$tabs\t\tif (!" . $ruleData[1] . "('$elementName', field.value) && !errFlag['$elementName']) {\n" .
                                "$tabs\t\t\terrFlag['$elementName'] = true;\n" .
                                "$tabs\t\t\tmsg = msg + '\\n - $message';\n" .
                                "$tabs\t\t}\n";
                            break;
                    }
                }
            }
        }
        $html .=
            "$tabs\t\tif (msg != '') {\n" .
            "$tabs\t\t\tmsg = '$this->_jsPrefix' + msg;\n" .
            "$tabs\t\t\tmsg = msg + '\\n$this->_jsPostfix';\n" .
            "$tabs\t\t\talert(msg);\n" .
            "$tabs\t\t\treturn false;\n" .
            "$tabs\t\t}\n" .
            "$tabs\t\treturn true;\n" .
            "$tabs }\n" .
            "$tabs//-->\n" .
            "$tabs</script>\n";
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
        return in_array($element, $this->_frozen);
    } // end func isElementFrozen

    // }}}
    // {{{ setJsWarnings()

    /**
     * Sets JavaScript warning messages
     *
     * @param     string   $pref        Prefix warning
     * @param     string   $post        Postfix warning
     * @since     1.5
     * @access    public
     * @return    void
     */
    function setJsWarnings($pref, $post)
    {
        $this->_jsPrefix = $pre;
        $this->_jsPostfix = $post;
    } // end func setJsWarnings
    
    // }}}
    // {{{ setRequiredNote()

    /**
     * Sets required-note
     *
     * @param     string   $note        Message indicating some elements are required
     * @since     1.5
     * @access    public
     * @return    void
     */
    function setRequiredNote($note)
    {
        $this->_requiredNote = $note;
    } // end func setRequiredNote

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
        for (reset($this->_rules); $elementName = key($this->_rules); next($this->_rules)) {
            if (isset($this->_errors[$elementName])) {
                continue;
            }
            $rules = pos($this->_rules);
            for ($i=0; $i < count($rules); $i++) {
                $type = $format = $message = null;
                $type = $rules[$i]["type"];
                $format = $rules[$i]["format"];
                $message = $rules[$i]["message"];
                $validation = $rules[$i]["validation"];
                $ruleData = $this->_registeredRules[$type];
                switch ($ruleData[0]) {
                    case 'regex':
                        $regex = str_replace('%data%', $format, $ruleData[1]);
                        if (!preg_match($regex, $this->_submitValues[$elementName])) {
                            $this->_errors[$elementName] = $message;
                            continue 2;
                        }
                        break;
                    case 'function':
                        if (method_exists($this, $ruleData[1])) {
                            if (!$this->$ruleData[1]($elementName, $this->_submitValues[$elementName], $format)) {
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
    // {{{ _isUploadedFile()

    /**
     * Checks if the given element contains an uploaded file
     *
     * @param     string    $element    Element name
     * @since     1.1
     * @access    private
     * @return    bool      true if file has been uploaded, false otherwise
     * @throws    
     */
    function _isUploadedFile($element)
    {
        return is_uploaded_file($this->_submitFiles[$element]['tmp_name']);
    } // end func _isUploadedFile
    
    // }}}
    // {{{ _checkMaxFileSize()

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
    function _checkMaxFileSize($element, $value, $maxSize)
    {
        return ($maxSize >= filesize($this->_submitFiles[$element]['tmp_name']));
    } // end func _checkMaxFileSize

    // }}}
    // {{{ _checkMimeType()

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
    function _checkMimeType($element, $value, $mimeType)
    {
        if (is_array($mimeType)) {
            return in_array($this->_submitFiles[$element]['type'],$mimeType);
        }
        return $this->_submitFiles[$element]['type'] == $mimeType;
    } // end func _checkMimeType

    // }}}
    // {{{ _checkFileName()

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
    function _checkFileName($element, $value, $regex)
    {
        return preg_match($regex, $this->_submitFiles[$element]['name']);
    } // end func _checkFileName

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
        if (is_array($elementList)) {
            foreach($elementList as $val) {
                if (!$this->elementExists($val)) {
                    return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Tried to freeze $val in HTML_QuickForm::freeze", 'HTML_QuickForm_Error', true);
                }
                $this->_frozen[] = $val;
            }
        } elseif ($elementList == null) {
            $this->_freezeAll = true;
        } else {
            if (!$this->elementExists($elementList)) {
                return PEAR::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Tried to freeze $elementList in HTML_QuickForm::freeze", 'HTML_QuickForm_Error', true);
            }
            $this->_frozen[] = $elementList;
        }
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
        echo "<pre>";
        print_r($this->_submitValues);
        echo "</pre>";
        echo "<pre>";
        print_r($this->_submitFiles);
        echo "</pre>";
    } // end func process
        
    // }}}
    // {{{ toHtml ()

    /**
     * Returns an HTML version of the form
     *
     * @return   string     Html version of the form
     * @since     1.0
     * @access   public
     */
    function toHtml ()
    {
        $html = '';
        if (!empty($this->_formCaption)) {
            $caption = $this->_wrapCaption($this->_formCaption);
        }
        reset($this->_elements);
        while (list(, $element) = each($this->_elements)) {
            if (isset($element['elements'])) {
                $html .= $this->_buildGroup($element);
            } elseif (isset($element['header'])) {
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
        if (!empty($this->_rules)) {
            $html = $this->_buildRules() . $html;
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
                QUICKFORM_EMPTY_ARGUMENT        => 'empty argument passed',
                QUICKFORM_UNREGISTERED_ELEMENT  => 'unregistered element',
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
        } else {
            $this->PEAR_Error("Invalid error code: $code", QUICKFORM_ERROR, $mode, $level, $debuginfo);
        }
    }

    // }}}
}
?>