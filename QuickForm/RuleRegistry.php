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
// |          Alexey Borzov <borz_off@cs.msu.su>                          |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id$

$GLOBALS['_HTML_QuickForm_registered_rules'] = array(
    'required'      => array('html_quickform_rule_required', 'HTML/QuickForm/Rule/Required.php'),
    'maxlength'     => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
    'minlength'     => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
    'rangelength'   => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
    'email'         => array('html_quickform_rule_email',    'HTML/QuickForm/Rule/Email.php'),
    'regex'         => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'lettersonly'   => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'alphanumeric'  => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'numeric'       => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'nopunctuation' => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'nonzero'       => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'callback'      => array('html_quickform_rule_callback', 'HTML/QuickForm/Rule/Callback.php'),
    'compare'       => array('html_quickform_rule_compare',  'HTML/QuickForm/Rule/Compare.php')
);

/**
* Registers rule objects and uses them for validation
*
*/
class HTML_QuickForm_RuleRegistry
{
    /**
     * Array containing references to used rules
     * @var     array
     * @access  private
     */
    var $_rules = array();


    /**
     * Returns a singleton of HTML_QuickForm_RuleRegistry
     *
     * Usually, only one RuleRegistry object is needed, this is the reason
     * why it is recommended to use this method to get the validation object. 
     *
     * @access    public
     * @static
     * @return    object    Reference to the HTML_QuickForm_RuleRegistry singleton
     */
    function &getInstance()
    {
        static $obj;
        if (!isset($obj)) {
            $obj = new HTML_QuickForm_RuleRegistry();
        }
        return $obj;
    } // end func getInstance

    /**
     * Registers a new validation rule
     *
     * In order to use a custom rule in your form, you need to register it
     * first. For regular expressions, one can directly use the 'regex' type
     * rule in addRule(), this is faster than registering the rule.
     *
     * Functions and methods can be registered. Use the 'function' type.
     * When registering a method, specify the class name as second parameter.
     *
     * You can also register an HTML_QuickForm_Rule subclass with its own
     * validate() method.
     *
     * @param     string    $ruleName   Name of validation rule
     * @param     string    $type       Either: 'regex', 'function' or null
     * @param     string    $data1      Name of function, regular expression or
     *                                  HTML_QuickForm_Rule object class name
     * @param     string    $data2      Object parent of above function or HTML_QuickForm_Rule file path
     * @access    public
     * @return    void
     */
    function registerRule($ruleName, $type, $data1, $data2 = null)
    {
        $type = strtolower($type);
        if ($type == 'regex') {
            // Regular expression
            $rule =& $this->getRule('regex');
            $rule->addData($ruleName, $data1);
            $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName] = $GLOBALS['_HTML_QuickForm_registered_rules']['regex'];

        } elseif ($type == 'function' || $type == 'callback') {
            // Callback function
            $rule =& $this->getRule('callback');
            $rule->addData($ruleName, $data1, $data2);
            $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName] = $GLOBALS['_HTML_QuickForm_registered_rules']['callback'];

        } elseif (is_object($data1)) {
            // An instance of HTML_QuickForm_Rule
            $this->_rules[get_class($data1)] = $data1;
            $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName] = array(get_class($data1), null);

        } else {
            // Rule class name
            $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName] = array(strtolower($data1), $data2);
        }
    } // end func registerRule

    /**
     * Returns a reference to the requested rule object
     *
     * @param     string   $ruleName        Name of the requested rule
     * @access    public
     * @return    object
     */
    function &getRule($ruleName)
    {
        list($class, $path) = $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName];

        if (!isset($this->_rules[$class])) {
            if (!empty($path)) {
                include_once($path);
            }
            $this->_rules[$class] =& new $class();
        }
        $this->_rules[$class]->setName($ruleName);
        return $this->_rules[$class];
    } // end func getRule

    /**
     * Performs validation on the given values
     *
     * @param     string   $ruleName        Name of the rule to be used
     * @param     mixed    $values          Can be a scalar or an array of values 
     *                                      to be validated
     * @param     mixed    $options         Options used by the rule
     * @param     mixed    $multiple        Whether to validate an array of values altogether
     * @access    public
     * @return    mixed    true if no error found, int of valid values (when an array of values is given) or false if error
     */
    function validate($ruleName, $values, $options = null, $multiple = false)
    {
        $rule =& $this->getRule($ruleName);

        if (is_array($values) && !$multiple) {
            $result = 0;
            foreach ($values as $value) {
                if ($rule->validate($value, $options) === true) {
                    $result++;
                }
            }
            return ($result == 0) ? false : $result;
        } else {
            return $rule->validate($values, $options);
        }
    } // end func validate

    /**
     * Returns the validation test in javascript code
     *
     * @param     string    $ruleName   Name of rule to be used for validation
     * @param     string    $jsValue    JS code to find the element value
     * @param     string    $jsField    Element name in the form
     * @param     string    $jsMessage  Error message encoded for javascript
     * @param     string    $jsReset    JS code to revert the value back to default if error
     * @param     mixed     $options    Options for this rule, not used yet
     * @access    public
     * @return    mixed   false if error or the number of valid elements
     */
    function getValidationScript($ruleName, $jsValue, $jsField, $jsMessage, $jsReset = '', $options = null)
    {
        $rule =& $this->getRule($ruleName);
        return $rule->getValidationScript($jsValue, $jsField, $jsMessage, $jsReset, $options);
    } // end func getValidationScript

} // end class HTML_QuickForm_RuleRegistry
?>