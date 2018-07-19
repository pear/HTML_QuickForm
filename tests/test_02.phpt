--TEST--
Test 02 Initialisation of QuickForm class and call of addElement() and toHtml()
--FILE--
<?php
if (defined('E_DEPRECATED')) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}
include_once 'HTML/QuickForm.php';
$TestForm = new HTML_QuickForm();
$TestForm->addElement('header', null, 'QuickForm tutorial example');
$TestForm->toHtml(); // Without echo!
?>
--EXPECT--