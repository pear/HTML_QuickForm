<html>
<body>
<?php
/**
* Another example of usage for PEAR class HTML_QuickForm
*
* @author      Jason Rust <jrust@rustyparts.com> 
* @version     2.0
*/

require_once ("HTML/QuickForm.php");
// example of creating a completely custom look for the form
$form =& new HTML_QuickForm('tmp_form','POST');

// clear all templates so we can give it a custom look
$form->clearAllTemplates();

// Fills with some defaults values
$defaultValues['tmp_textarea']  = '
Test Text Area

With line breaks';
$defaultValues['tmp_radio'] = 'Y';
$defaultValues['tmp_text2[0]'] = '123';
$defaultValues['tmp_text2[1]'] = '456';
$defaultValues['tmp_text2[2]'] = '789';
// Fill with some constant values.
// Constant is not overridden by POST, GET, or defaultValues
// when values are being filled in
$constantValues['tmp_hidden'] = 25;
$constantValues['tmp_text'] = 'constant text';

$form->setDefaults($defaultValues);
$form->setConstants($constantValues);


$form->addElement('hidden','tmp_hidden');
$form->addData('
<table border="0" cellpadding="0" cellspacing="2" bgcolor="#eeeeee">
  <tr>
    <td bgcolor="#cccccc" colspan="2" align="center">
      Our custom QuickForm...
    </td>
  </tr>
  <tr>
    <td align="center" colspan="2">');
$form->addElement('textarea','tmp_textarea',null,'cols="50" rows="10" wrap="virtual"');
$form->addData('
    </td>
  </tr>
  <tr>
    <td align="left" bgcolor="#cccccc" width="50%">
      Text box
    </td>
    <td align="right" bgcolor="#cccccc" width="50%">
      Yes or No? 
    </td>
  </tr>
    <td align="left">');
$form->addElement('text','tmp_text',null,'size="30"');
$form->addData('
    </td>
    <td align="right">
      Yes: ');
$form->addElement('radio','tmp_radio',null,null,'Y');
$form->addData('No: ');
$form->addElement('radio','tmp_radio',null,null,'N');
$form->addData('
    </td>
  </tr>
  <tr>
    <td align="center" bgcolor="#cccccc" colspan="2">
      Group of Text Boxes 
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center"');
// a group of text boxes in an array
$form->addElement('text','tmp_text2[0]',null,'size="3"');
$form->addElement('text','tmp_text2[1]',null,'size="3"');
$form->addElement('text','tmp_text2[2]',null,'size="3"');
$form->addData('
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center"');
$form->addElement('submit','tmp_submit','Submit Form');
$form->addData('
    </td>
  </tr>
</table>');

$form->display();
echo "\n<HR> <b>Submitted Values: </b><br />\n";
echo "<pre>";
print_r($_POST);
?>
</body>
</html>

