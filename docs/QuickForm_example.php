<?php
/**
* Example of usage for PEAR class HTML_QuickForm
*
* @author      Adam Daniel <adaniel1@eesus.jnj.com>
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @version     2.0
*
* $Id$ 
*/

require_once ("HTML/QuickForm.php");
$form = new HTML_QuickForm('frmTest', 'get');
// Fills with some defaults values
$defaultValues['itxtTest']  = 'Test Text Box';
$defaultValues['itxaTest']  = 'Hello World';
$defaultValues['ichkTest']  = true;
$defaultValues['iradTest']  = 1;
$defaultValues['iselTest']  = array('B', 'C');
$defaultValues['name']      = array('first'=>'Adam', 'last'=>'Daniel');
$defaultValues['phoneNo']   = array('513', '123', '3456');
$defaultValues['iradYesNo'] = 'Y';
$defaultValues['ichkABC']   = array('A'=>true,'B'=>true);
$defaultValues['QFText']    = '153.5';
$defaultValues['dateTest1']    = array('d'=>11, 'm'=>1, 'Y'=>2003);
$form->setDefaults($defaultValues);

$constantValues['dateTest3']    = time();
$form->setConstants($constantValues);

// Elements will be displayed in the order they are declared
$form->addHeader('Normal Elements');
// Classic form elements
$form->addElement('hidden', 'ihidTest', 'hiddenField');
$form->addElement('text', 'itxtTest', 'Test Text:');
$form->addElement('textarea', 'itxaTest', 'Test TextArea:');
$form->addElement('password', 'ipwdTest', 'Test Password:');
//$form->addElement('file', 'ifilTest', 'File:');
$form->addElement('checkbox', 'ichkTest', 'Test CheckBox:', 'Check the box');
$form->addElement('radio', 'iradTest', 'Test Radio Buttons:', 'Check the radio button #1', 1);
$form->addElement('radio', 'iradTest', '(Not a group)', 'Check the radio button #2', 2);
$form->addElement('button', 'ibtnTest', 'Test Button');
$form->addElement('reset', 'iresTest', 'Test Reset');
$form->addElement('submit', 'isubTest', 'Test Submit');
$form->addElement('image', 'iimgTest', 'http://www.php.net/gifs/php_logo.gif');
$form->addElement('select', 'iselTest', 'Test Select:', array('A'=>'A', 'B'=>'B','C'=>'C','D'=>'D'));
$select = &$form->getElement('iselTest');
$select->setSize(5);
$select->setMultiple(true);

$form->addHeader('Date Elements');
// Date elements
$form->addElement('date', 'dateTest1', 'Date1:', array('format'=>'dmY', 'minYear'=>2000, 'maxYear'=>2004));
$form->addElement('date', 'dateTest2', 'Date2:', array('format'=>'d-F-Y', 'language'=>'de'));
$form->addElement('date', 'dateTest3', 'Today is:', array('format'=>'l D d M Y'));


$form->addHeader('Grouped Elements');
// Grouped elements
$name['last'] = &HTML_QuickForm::createElement('text', 'last');
$name['last']->setSize(30);
$name['first'] = &HTML_QuickForm::createElement('text', 'first');
$name['first']->setSize(20);
$form->addGroup($name, 'name', 'Name (last, first):', ',&nbsp;');
// Creates a group of text inputs
$areaCode = &HTML_QuickForm::createElement('text', '');
$areaCode->setSize(3);
$areaCode->setMaxLength(3);
$phoneNo1 = &HTML_QuickForm::createElement('text', '');
$phoneNo1->setSize(3);
$phoneNo1->setMaxLength(3);
$phoneNo2 = &HTML_QuickForm::createElement('text', '');
$phoneNo2->setSize(4);
$phoneNo2->setMaxLength(4);
$form->addGroup(array($areaCode, $phoneNo1, $phoneNo2), 'phoneNo', 'Telephone:', '-');
// Creates a radio buttons group
$radio[] = &HTML_QuickForm::createElement('radio', null, null, 'Yes', 'Y');
$radio[] = &HTML_QuickForm::createElement('radio', null, null, 'No', 'N');
$form->addGroup($radio, 'iradYesNo', 'Yes/No:');
// Creates a checkboxes group
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'A', null, 'A');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'B', null, 'B');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'C', null, 'C');
$form->addGroup($checkbox, 'ichkABC', 'ABC:', '<br />');
// Creates a group of buttons to be displayed at the bottom of the form
$buttons[] = &HTML_QuickForm::createElement('submit', null, 'Submit');
$buttons[] = &HTML_QuickForm::createElement('reset', null, 'Reset');
$buttons[] = &HTML_QuickForm::createElement('image', 'iimgTest', '/images/apache_pb.gif');
$buttons[] = &HTML_QuickForm::createElement('button', 'ibutTest', 'Test Button');
$form->addGroup($buttons);
$form->addHeader('Using the form element classes directly');
$text = new HTML_QuickForm_text('QFText', 'QuickForm Text:');
$form->addElement($text);
// applies new filters to the element values
$form->applyFilter('__ALL__', 'trim');
$form->applyFilter('QFText', 'doubleval');
// Adds some validation rules
$form->addRule('itxtTest', 'Test Text is a required field', 'required');
$form->addRule('itxaTest', 'Test TextArea is a required field', 'required');
$form->addRule('itxaTest', 'Test TextArea must be at least 5 characters', 'minlength', '5');
$form->addRule('ipwdTest', 'Password must be between 8 to 10 characters', 'rangelength', '8,10');
$form->addRule('QFText', 'Value must be numeric', 'numeric', '', 'client');

// FILE RULES
/*
$form->addRule('ifilTest', 'Cannot exceed 1776 bytes', 'maxfilesize', 1776);
$form->addRule('ifilTest', 'Must be XML', 'mimetype', 'text/xml');
$form->addRule('ifilTest', 'Must be *.xml', 'filename', '/^.*\.xml$/');
$form->addRule('ifilTest', 'Required File Upload', 'uploadedfile');
*/


// Tries to validate the form
if ($form->validate()) {
    // Form is validated, then processes the data
    $form->freeze();
    $form->process();
    echo "\n<HR>\n";
}
$form->display();
/*echo '<pre>';
print_r($form->toArray());
echo '</pre>';
*/
?>