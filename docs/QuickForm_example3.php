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

function cmpPass($fields)
{
    if (strlen($fields['passwd1']) && strlen($fields['passwd2']) && 
        $fields['passwd1'] != $fields['passwd2']) {
        return array('passwd1' => 'Passwords are not the same');
    }
    return true;
}
$form->addElement('password', 'passwd1', 'Enter password');
$form->addElement('password', 'passwd2', 'Confirm password');
$form->addElement('submit', 'submit', 'submit');
$form->addRule('passwd1', 'Please enter password', 'required');
$form->addRule('passwd2', 'Please confirm password', 'required');
$form->addFormRule('cmpPass');
$form->validate();
$form->display();

?>