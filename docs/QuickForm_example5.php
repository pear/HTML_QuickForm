<?php
require_once('HTML/QuickForm.php');
/*
* This example shows how to use your own filters
* using the registerFilter method.
*/
// $Id$

function _filterAustin($value) {
	return strtoupper($value).', GROOVY BABY !';
}

$form = new HTML_QuickForm('frmTest', 'GET');

$form->registerFilter('austin', '_filterAustin');
$form->addElement('text', 'itxtTest', 'Test Text:');
$form->addElement('submit', 'submit', 'submit');
$form->addRule('itxtTest', 'Test text is required', 'required');
$form->applyFilter('__ALL__', 'austin');

if ($form->validate()) {
    $form->freeze();
    echo 'Before filter:<pre>';
    echo $form->getElementValue('itxtTest');
    echo '</pre>';
    echo 'After filter:<pre>';
    var_dump($form->_submitValues['itxtTest']);
    echo '</pre>';
}
$form->display();

?>