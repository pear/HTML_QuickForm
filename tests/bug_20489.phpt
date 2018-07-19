--TEST--
Bug 20489 Textareas with umlauts or other special chars are rendered empty
--FILE--
<?php
if (defined('E_DEPRECATED')) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}

require_once "HTML/QuickForm.php";

$form = new HTML_QuickForm('register', 'post'); 
$form->addElement('text', 't1', 'Text'); 
$form->addElement('textarea','ta','Text area');
$form->addElement('submit','sb','Submit form');

$form->setDefaults(array("t1"=>"äöüß", "ta"=>"äöüß"));
$form->display();
?>
--EXPECTF--
<form action="%s" method="post" name="register" id="register">
<div>
<table border="0">

	<tr>
		<td align="right" valign="top"><b>Text</b></td>
		<td valign="top" align="left">	<input name="t1" type="text" value="äöüß" /></td>
	</tr>
	<tr>
		<td align="right" valign="top"><b>Text area</b></td>
		<td valign="top" align="left">	<textarea name="ta">äöüß</textarea></td>
	</tr>
	<tr>
		<td align="right" valign="top"><b></b></td>
		<td valign="top" align="left">	<input name="sb" value="Submit form" type="submit" /></td>
	</tr>
</table>
</div>
</form>
