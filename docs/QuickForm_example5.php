<?php
require_once('HTML/QuickForm.php');
/*
* This example shows how to use your own functions as filters
*/
// $Id$

function _filterAustin($value) {
    return strtoupper($value).', GROOVY BABY !';
}

$form = new HTML_QuickForm('frmTest', 'GET');

$form->addElement('text', 'itxtTest', 'Test Text:');
$form->addElement('submit', 'submit', 'submit');
$form->addRule('itxtTest', 'Test text is required', 'required');

if ($form->validate()) {
    $form->applyFilter('__ALL__', '_filterAustin');
    $form->freeze();
    echo 'Before filter:<pre>';
    var_dump($form->getElementValue('itxtTest'));
    echo '</pre>';
    echo 'After filter:<pre>';
    var_dump($form->exportValue('itxtTest'));
    echo '</pre>';
}
$form->display();

?>