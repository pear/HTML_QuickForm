<?php
/**
* Example of usage for PEAR class HTML_QuickForm
*
* @author      Adam Daniel <adaniel1@eesus.jnj.com>
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @version     1.2
*/

require_once ("HTML/QuickForm.php");

$form = new HTML_QuickForm('frmTest');

if ($REQUEST_METHOD == "POST") {
    // Uses posted values
    $form->loadValues();
} else {
    // Fills with some defaults values
    $defaultValues["itxtTest"]  = "Test Text Box";
    $defaultValues["iradYesNo"] = "Y";
    $defaultValues["ichkABC"]   = "A,B";
    $defaultValues["iselTest"]  = "C";
    $form->loadDefaults($defaultValues);
}

// Elements will be displayed in the order they are declared
// Set form caption

$form->addHeader("Test Form");

// Classic form elements

$form->addElement('hidden', 'ihidTest', 'hiddenField');
$form->addElement('text', 'itxtTest', '', 'Test Text:');
$form->addElement('textarea', 'itxaTest', '', 'Test TextArea:');
$form->addElement('password', 'ipwdTest', '', 'Test Password:');
$form->addElement('file', 'ifilTest', '', 'File:');
$form->addElement('select', 'iselTest', array('A'=>'A', 'B'=>'B','C'=>'C','D'=>'D'), 'Test Select:');

// Creates a group of text inputs

$phone[] = $form->createElement('text', null, null, null, array("size"=>3, "maxlength"=>3));
$phone[] = $form->createElement('text', null, null, null, array("size"=>3, "maxlength"=>3));
$phone[] = $form->createElement('text', null, null, null, array("size"=>4, "maxlength"=>4));
$form->addElementGroup($phone, 'Telephone:', 'phoneNo');

// Creates a radio buttons group

$radio[] = $form->createElement('radio', 'iradYesNo', 'Y', 'Yes');
$radio[] = $form->createElement('radio', 'iradYesNo', 'N', 'No');
$form->addElementGroup($radio, 'Yes/No:', 'iradYesNo');

// Creates a checkboxes group

$checkbox[] = $form->createElement('checkbox', 'ichkABC', 'A', 'A');
$checkbox[] = $form->createElement('checkbox', 'ichkABC', 'B', 'B');
$checkbox[] = $form->createElement('checkbox', 'ichkABC', 'C', 'C');
$form->addElementGroup($checkbox, 'ABC:', 'ichkABC');

// Creates a group of buttons to be displayed at the bottom of the form

$buttons[] = $form->createElement('submit', '', 'Submit');
$buttons[] = $form->createElement('reset', '', 'Reset');
$buttons[] = $form->createElement('image', 'iimgTest', '/images/apache_pb.gif');
$buttons[] = $form->createElement('button', 'ibutTest', 'Test Button');
$form->addElementGroup($buttons);

// Adds some validation rules

$form->addRule('itxtTest', 'Test Text is a required field', 'required');
$form->addRule('itxaTest', 'Test TextArea must be at least 5 characters', 'minlength', '5', 'client');
$form->addRule('ipwdTest', 'Password must be between 8 to 10 characters', 'rangelength', '8,10', 'client');
$form->addRule('itxaTest', 'Test TextArea is a required field', 'required');

// new uploaded file rules
$form->addRule('ifilTest', 'Cannot exceed 1776 bytes', 'maxfilesize', 1776);
$form->addRule('ifilTest', 'Must be XML', 'mimetype', 'text/xml');
$form->addRule('ifilTest', 'Must be *.xml', 'filename', '/^.*\.xml$/');
$form->addRule('ifilTest', 'Required File Upload', 'uploadedfile');

// Tries to validate the form

if ($form->validate()) {
    // Form is validated, then processes the data
    $form->process();
    echo "\n<HR>\n";
}

$form->display();
?>