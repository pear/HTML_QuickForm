<?php
/**
* Example of usage for PEAR class HTML_QuickForm
* This example shows a way to compare 2 values using a custom
* validation rule in QuickForm.
* 
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @version     2.0
*
* $Id$ 
*/

require_once ('HTML/QuickForm.php');

$form = new HTML_QuickForm();

function cmpPass($element, $pass2val)
{
    global $form;
    $pass1val = $form->getElementValue('passwd1');
    return ($pass1val == $pass2val);
}
$form->registerRule('compare', 'function', 'cmpPass');
$form->addElement('password', 'passwd1', 'Enter password');
$form->addElement('password', 'passwd2', 'Confirm password');
$form->addElement('submit', 'submit', 'submit');
$form->addRule('passwd1', 'Please enter password', 'required');
$form->addRule('passwd2', 'Please confirm password', 'required');
$form->addRule('passwd2', 'Passwords are not the same', 'compare', 'function');
$form->validate();
$form->display();

?>