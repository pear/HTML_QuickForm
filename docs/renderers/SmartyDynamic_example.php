<?php
/**
 * Example of usage for Array renderer and Smarty template engine
 *
 * @author Alexey Borzov <borz_off@cs.msu.su>
 * @author Thomas Schulz <ths@4bconsult.de>
 *
 * $Id$ 
 */

require_once 'HTML/QuickForm.php';
// fix this if your Smarty is somewhere else
require_once 'Smarty.class.php';

$form = new HTML_QuickForm('frmTest', 'post');

$form->setDefaults(array(
    'itxtTest'  => 'Test Text Box',
    'itxaTest'  => 'Hello World',
    'iselTest'  => array('B', 'C'),
    'name'      => array('first' => 'Thomas', 'last' => 'Schulz'),
    'iradYesNo' => 'Y',
    'ichkABCD'  => array('A'=>true,'D'=>true)
));

$form->addElement('header', '', 'Normal Elements');

$form->addElement('hidden', 'ihidTest', 'hiddenField');
$form->addElement('text', 'itxtTest', 'Test Text');
$form->addElement('textarea', 'itxaTest', 'Test TextArea');
$form->addElement('password', 'ipwdTest', 'Test Password');
$select =& $form->addElement('select', 'iselTest', 'Test Select', array('A'=>'A', 'B'=>'B','C'=>'C','D'=>'D'));
$select->setSize(5);
$select->setMultiple(true);
$form->addElement('submit', 'isubTest', 'Test Submit');

$form->addElement('header', '', 'Grouped Elements');

$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'A', null, 'A');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'B', null, 'B');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'C', null, 'C');
$checkbox[] = &HTML_QuickForm::createElement('checkbox', 'D', null, 'D');
$form->addGroup($checkbox, 'ichkABCD', 'ABCD', array('&nbsp;', '<br />'));

$radio[] = &HTML_QuickForm::createElement('radio', null, null, 'Yes', 'Y');
$radio[] = &HTML_QuickForm::createElement('radio', null, null, 'No', 'N');
$form->addGroup($radio, 'iradYesNo', 'Yes/No', '<br />');

$name['first'] = &HTML_QuickForm::createElement('text', 'first', 'First:');
$name['first']->setSize(20);
$name['last'] = &HTML_QuickForm::createElement('text', 'last', 'Last:');
$name['last']->setSize(30);
$form->addGroup($name, 'name', 'Name', '<br />');

// add some 'required' rules to show "stars" and (possible) errors...
$form->addRule('itxtTest', 'Test Text is a required field', 'required');
$form->addRule('itxaTest', 'Test TextArea is a required field', 'required');
// $form->addRule('iradYesNo', 'Check Yes or No', 'required');
$form->addGroupRule('name', array('last' => array(array('Last name is required', 'required'))));

// try to validate the form
if ($form->validate()) {
    $form->freeze();
}

// create a template object
$tpl =& new Smarty;
$tpl->template_dir  = './';

// assign array with form data
$tpl->assign('frm', $form->toArray());

// render and display the template
$tpl->display('smarty-dynamic.html');

?>
