<?php
require_once ("HTML_QuickForm/QuickForm.php");

$form = new HTML_QuickForm('frmTest');
$form->addHeader("Test Form");
$form->addElement('hidden', 'ihidTest', 'hiddenField');
$form->addElement('text', 'itxtTest', '', 'Test Text:');
$form->addElement('textarea', 'itxaTest', '', 'Test TextArea:');
$form->addElement('password', 'ipwdTest', '', 'Test Password:');
$form->addElement('file', 'ifilTest', '', 'File:');
$form->addElement('select', 'iselTest', array('A'=>'A', 'B'=>'B'), 'Test Select:');

$phone[] = $form->createElement('text', null, null, null, array("size"=>3, "maxlength"=>3));
$phone[] = $form->createElement('text', null, null, null, array("size"=>3, "maxlength"=>3));
$phone[] = $form->createElement('text', null, null, null, array("size"=>4, "maxlength"=>4));
$form->addElementGroup($phone, 'Telephone:', 'phoneNo');

$radio[] = $form->createElement('radio', '', 'Y', 'Yes');
$radio[] = $form->createElement('radio', '', 'N', 'No');
$form->addElementGroup($radio, 'Yes/No:', 'iradYesNo');

$checkbox[] = $form->createElement('checkbox', '', 'A', 'A');
$checkbox[] = $form->createElement('checkbox', '', 'B', 'B');
$checkbox[] = $form->createElement('checkbox', '', 'C', 'C');
$form->addElementGroup($checkbox, 'ABC:', 'ichkABC');

$buttons[] = $form->createElement('submit', '', 'Submit');
$buttons[] = $form->createElement('reset', '', 'Reset');
$buttons[] = $form->createElement('image', 'iimgTest', '/images/apache_pb.gif');
$buttons[] = $form->createElement('button', 'ibutTest', 'Test Button');
$form->addElementGroup($buttons);

$form->addRule('itxtTest', 'Test Text is a required field', 'required');
$form->addRule('itxaTest', 'Test TextArea is a required field', 'required');
$form->addRule('itxaTest', 'Test TextArea must be at least 5 characters', 'minlength', '5', 'client');
$form->addRule('ipwdTest', 'Password must be between 8 to 10 characters', 'rangelength', '8,10', 'client');

$defaultValues = array('itxtTest'=>'Test Text Box', 'iradYesNo'=>'N', 'ichkABC'=>'A,B', 'iselTest'=>'DB');
$form->loadDefaults($defaultValues);
$form->loadValues();

if ($form->validate()) {
    $form->process();
    echo "\n<HR>\n";
}
$form->display();
?>