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

require_once("PEAR.php");
require_once("HTML/Common.php");

/**
* Create, validate and process HTML forms
*
* @author      Adam Daniel <adaniel1@eesus.jnj.com>
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @version     1.0
* @since       PHP 4.0.3pl1
*/

class HTML_QuickForm extends HTML_Common {

    /**
     * Array containing the form fields
     * @since     1.0
     * @var  array
     * @access   private
     */
    var $_elements = array();

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
    var $_requiredNote = "<FONT size=\"1\" color=\"#FF0000\">*</FONT><FONT size=\"1\"> denotes required field</FONT>";
    
    /**
     * Array of registered element types
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_registeredTypes = 
        array(
            'group'     =>array('HTML_QuickForm/Elements/group.php','HTML_QuickForm_group'),
            'hidden'    =>array('HTML_QuickForm/Elements/hidden.php','HTML_QuickForm_hidden'),
            'reset'     =>array('HTML_QuickForm/Elements/reset.php','HTML_QuickForm_reset'),
            'checkbox'  =>array('HTML_QuickForm/Elements/checkbox.php','HTML_QuickForm_checkbox'),
            'file'      =>array('HTML_QuickForm/Elements/file.php','HTML_QuickForm_file'),
            'image'     =>array('HTML_QuickForm/Elements/image.php','HTML_QuickForm_image'),
            'password'  =>array('HTML_QuickForm/Elements/password.php','HTML_QuickForm_password'),
            'radio'     =>array('HTML_QuickForm/Elements/radio.php','HTML_QuickForm_radio'),
            'button'    =>array('HTML_QuickForm/Elements/button.php','HTML_QuickForm_button'),
            'submit'    =>array('HTML_QuickForm/Elements/submit.php','HTML_QuickForm_submit'),
            'select'    =>array('HTML_QuickForm/Elements/select.php','HTML_QuickForm_select'),
            'text'      =>array('HTML_QuickForm/Elements/text.php','HTML_QuickForm_text'),
            'textarea'  =>array('HTML_QuickForm/Elements/textarea.php','HTML_QuickForm_textarea')
        );

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
            'lettersonly'   =>array('regex', '^[a-zA-Z]*$'),
            'alphanumeric'  =>array('regex', '^[a-zA-Z0-9]*$')
        );

    /**
     * Class constructor
     * @param    string      $formName          Form's name.
     * @param    string      $method            (optional)Form's method defaults to 'POST'
     * @param    string      $action            (optional)Form's action
     * @param    string      $target            (optional)Form's target defaults to '_self'
     * @param    array       $attributes        (optional)Associative array of form tag extra attributes
     * @access   public
     */
    function HTML_QuickForm($formName="", $method="POST", $action="", $target="_self", $attributes=null)
    {
        $method = (strtoupper($method) == "GET") ? "GET" : "POST";
        $action = ($action == "") ? $GLOBALS["PHP_SELF"] : $action;
        HTML_Common::HTML_Common($attributes);
        $this->updateAttributes(array("action"=>$action, "method"=>$method, "name"=>$formName, "target"=>$target)); 
    } // end constructor
    
    /**
     * Returns the current API version
     *
     * @since     1.0
     * @access    public
     * @return    float
     */
    function apiVersion()
    {
        return 1.0;
    } // end func apiVersion

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
        $this->_registeredTypes[$typeName] = array($include, $className);
    } // end func registerElementType

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
            return new PEAR_ERROR("Argument to HTML_QuickForm::elementExists is empty");
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
        
        // Uploads management FIX ME
        /*
        $this->_submitFiles = $GLOBALS["HTTP_POST_FILES"];
        for (reset($this->_submitFiles); $key = key($this->_submitFiles); next($this->_submitFiles)) {
            $value = pos($this->_submitFiles);
            $value = is_string($value) ? stripslashes($value) : $value;
            $this->_submitFiles[$key] = $value;
            $this->$key = $value;
        }*/
    } // end func loadValues
    
    /**
     * Moves an uploaded file into the destination 
     * @param    string  $element  
     * @since     1.0
     * @param    string  $dest
     * @access   public
     */
    function moveUploadedFile($element, $dest)
    {
        $file = $this->_formFiles[$element];
        if (copy($file["tmp_name"], $dest . $file["name"])) {
            @unlink($file["tmp_name"]);
            return true;
        } else {
            return false;
        }
    } // end func moveUploadedFile
    
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
        if (!$this->isTypeRegistered($elementType)) {
            return new PEAR_Error("HTML_QuickForm element type '$elementType' is not registered");
        }
        $objectName = $this->_registeredTypes[$elementType][1];
        $includeFile = $this->_registeredTypes[$elementType][0];
        !include_once $includeFile;
        $elementObject = new $objectName($elementName, $mixed, $attributes);
        if (($elementType == "checkbox" OR $elementType == "radio") AND isset($elementLabel)) {
            $elementObject->setLabel($elementLabel);
        }
        return $elementObject;
    } // end func createElement

    /**
     * Adds an element into the form
     *
     * @param    string     $elementType    type of element to add (text, textarea, file...)
     * @param    string     $elementName    form name of this element
     * @param    string     $value          (optional)value of this element
     * @param    string     $elementLabel   (optional)label of this element
     * @param    array      $attributes     (optional)associative array with extra attributes (can be html or custom)
     * @since     1.0
     * @return   void
     * @access   public
     */
    function addElement($elementType, $elementName, $value=null, $elementLabel=null, $attributes=null)
    {
        if ($elementType == "file" AND $this->_fileFlag == false) {
            $this->_fileFlag = true;
            $this->updateAttributes(array("method"=>"POST", "enctype"=>"multipart/form-data"));
            $hiddenObject = new HTML_QuickForm_hidden("MAX_FILE_SIZE",$this->_maxFileSize,"");
            $this->_hidden[] = $hiddenObject;
        }
        if (isset($this->_elementValues[$elementName]) && $elementType != "select") {
            $value = $this->_elementValues[$elementName];
        }
        $elementObject = &HTML_QuickForm::createElement($elementType, $elementName, $value, $elementLabel, $attributes);
        if (PEAR::isError($elementObject)) {
            return $elementObject;
        }
        switch ($elementType) {
            case "hidden" :
                $this->_hidden[] = $elementObject;
                break;
            case "file" :
                $this->_fileFlag = true;
            default :
                $this->_elements[] = array("label"=>$elementLabel,"object"=>$elementObject);
                break;
        }
    } // end func addElement
    
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
        $elementObject = &HTML_QuickForm::createElement('group', $name, $elements, null, $groupLayout);
        if (PEAR::isError($elementObject)) {
            return $elementObject;
        }
        $this->_elements[] = array("label"=>$label,"object"=>$elementObject);
    } // end func addElementGroup
        
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
            return new PEAR_ERROR("Element does not exist in HTML_QuickForm::addRule");
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
            $label = "<FONT color=\"#FF0000\">*</FONT>$label";
        }
        $html .= 
            "\n$tabs\t<TR>\n" .
            "$tabs\t\t<TD align=\"right\" valign=\"top\"><B>$label&nbsp;</B></TD>\n" .
            "$tabs\t\t<TD NOWRAP valign=\"top\" align=\"left\">";
        if ($error != null) {
            $html .= 
                "<FONT color=\"#FF0000\">$error</FONT><BR>";
        }
        $html .= 
            $element->toHtml() .
            "</TD>\n" .
            "$tabs\t</TR>";
        return $html;
    } // end func _wrapElement
    
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
            "\n$tabs\t<TR>\n" .
            "$tabs\t\t<TD NOWRAP align=\"left\" valign='top' " .
            "colspan=\"2\" bgcolor=\"#CCCCCC\"><B>$header</B></TD>\n" .
            "$tabs\t</TR>";
        return $html;
    } // end func _wrapHeader
        
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
            "\n$tabs<TABLE border=\"0\">\n" .
            "$tabs\t<FORM".$this->_getAttrString($this->_attributes).">" .
            $content .
            "\n$tabs\t</FORM>\n" .
            "$tabs</TABLE>";
        return $html;
    } // end func _wrapForm

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
            "\n$tabs\t<TR>\n" .
            "$tabs\t\t<TD>&nbsp;</TD>\n" .
            "$tabs\t\t<TD align='left' valign='top'>$this->_requiredNote</TD>\n" .
            "$tabs\t</TR>";
        return $html;
    } // end func setCaption

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
        if ($this->isElementFrozen($elementName) || $this->_freezeAll == true) {
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
        $object->setTabOffset($this->getTabOffset() + 3);
        $html .= $this->_wrapElement($object, $label, $required, $error);
        return $html;
    } // end func _buildElement
    
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

    /**
     * Returns an HTML string of the hidden form elements
     *
     * @since     1.0
     * @access    private
     * @return    string    html of hidden elements
     * @throws    
     */
    function _buildHiddenElements()
    {
        $strHidden = "";
        for ($i=0; $i < count($this->_hidden); $i++) {
            $element = $this->_hidden[$i];
            $element->setTabOffset($this->getTabOffset()+1);
            $strHidden .= $element->toHtml();
        }
        return $strHidden;
    } // end func _buildHiddenElements
    
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
            "\n$tabs<SCRIPT language=\"javascript\">\n" .
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
            "$tabs</SCRIPT>";
        return $html; 
    } // end func _buildRules

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
        return in_array($type, array_keys($this->_registeredTypes));
    } // end func isTypeRegistered

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
        return array_keys($this->_registeredTypes);
    } // end func getRegisteredTypes

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
        $files = $this->_submitFiles;
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
                            if (!$this->$ruleData[1]($elementName, $this->_submitValues[$elementName])) {
                                $this->_errors[$elementName] = $message;
                                continue 2;
                            }
                        } else {
                            if (!$ruleData[1]($elementName, $this->_submitValues[$elementName])) {
                                $this->_errors[$elementName] = $message;
                                continue 2;
                            }
                        }
                        break;
                }
                /*
                if (method_exists($formValidationObject,$methodName)) {
                    if (!$formValidationObject->$methodName($elementName,$this->_submitValues[$elementName])) {
                        $this->_errors[$elementName] = $message;
                    }
                } else {
                        return new PEAR_ERROR("Validation rule does not exist in HTML_QuickForm::validate");
                }
                */
            }
        }

        if (count($this->_errors) > 0) {
            for (reset($files); $element=key($files); next($files)) {
                $file = pos($files);
                @unlink($file["tmp_name"]);
            }
            return false;
        }
        return true;
    } // end func validate
    
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
            while(list($key,$val)=each($elementList)) {
                if (!$this->elementExists($val)) {
                    return new PEAR_ERROR("Element does not exist in HTML_QuickForm::freeze");
                }
                $this->_frozen[] = $val;
            }
        } elseif ($elementList == null) {
            $this->_freezeAll = true;
        } else {
            if (!$this->elementExists($elementList)) {
                return new PEAR_ERROR("Element does not exist in HTML_QuickForm::freeze");
            }
            $this->_frozen[] = $elementList;
        }
    } // end func freeze
        
    /**
     * Performs the form data processing
     *
     * @since     1.0
     * @access   public
     */
    function process()
    {
        echo "<PRE>";
        print_r($this->_submitValues);
        echo "</PRE>";
        echo "<PRE>";
        print_r($this->_submitFiles);
        echo "</PRE>";
    } // end func process
        
    /**
     * Returns an HTML version of the form
     *
     * @return   string     Html version of the form
     * @since     1.0
     * @access   public
     */
    function toHtml ()
    {
        $html = "";
        if (!empty($this->_formCaption)) {
            $caption = $this->_wrapCaption($this->_formCaption);
        }
        for ($i=0; $i < count($this->_elements); $i++) {
            if (isset($this->_elements[$i]["elements"])) {
                $html .= $this->_buildGroup($this->_elements[$i]);
            } elseif (isset($this->_elements[$i]["header"])) {
                $html .= $this->_buildHeader($this->_elements[$i]);
            } else {
                $html .= $this->_buildElement($this->_elements[$i]);
            }
        }
        if (!empty($this->_required) && $this->_freezeAll == false) {
            $html .= $this->_wrapRequiredNote($formTable);
        }
        if (!empty($this->_hidden)) {
            $html .= $this->_buildHiddenElements();
        }
        $html = $this->_wrapForm($html);
        if (!empty($this->_rules)) {
            $html = $this->_buildRules() . $html;
        }
        return $html;
    } // end func toHtml
    
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

} // end class HTML_QuickForm
?>