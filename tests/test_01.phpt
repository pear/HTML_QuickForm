--TEST--
Test 01 simple initialisation of QuickForm class
--FILE--
<?php
if (defined('E_DEPRECATED')) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}
include_once 'HTML/QuickForm.php';
$TestForm = new HTML_QuickForm();
?>
--EXPECT--