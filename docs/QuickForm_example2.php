<?php /** $Id$ */ ?>
<html>
  <title>QuickForm Example 2</title>
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

// select list array
$selectListArray = array(
    'windows'   => 'Windows',
    'linux'     => 'Linux',
    'irix'      => 'Irix',
    'mac'       => 'Mac',
);

// Fills with some defaults values
$defaultValues['tmp_textarea']  = '
Test Text Area

With line breaks';
$defaultValues['tmp_text2'][0] = '123';
$defaultValues['tmp_text2'][1] = '456';
$defaultValues['tmp_text2'][2] = '789';
$defaultValues['tmp_checkbox'] = 'checked';
$defaultValues['tmp_select'] = 'linux';
$defaultValues['tmp_multipleSelect'][0] = array('linux', 'mac');
// Fill with some constant values.
// Constant is not overridden by POST, GET, or defaultValues
// when values are being filled in
$constantValues['tmp_radio'] = 'Y';
$constantValues['tmp_text']['ab'] = 'constant text';

$form->setDefaults($defaultValues);
$form->setConstants($constantValues);

$form->addElement('hidden','tmp_hidden', 'value');
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
$form->addElement('text','tmp_text[ab]',null,'size="10"');
$form->addElement('text','tmp_text[bc]',null,'size="10"');
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
    <td align="left" bgcolor="#cccccc">
      Array of Text Boxes 
    </td>
    <td align="right" bgcolor="#cccccc">
      Advanced Check Box 
    </td>
  </tr>
  <tr>
    <td align="left">');
// a group of text boxes in an array
$form->addElement('text','tmp_text2[0]',null,'size="3"');
$form->addElement('text','tmp_text2[1]',null,'size="3"');
$form->addElement('text','tmp_text2[2]',null,'size="3"');
$form->addData('
    </td>
    <td align="right">');
// advanced checkbox will always return a value, even when not checked
$form->addElement('advcheckbox','tmp_checkbox',null,'Please Check',null,array('not checked', 'checked'));
$form->addData('
    </td>
  </tr>
  <tr>
    <td align="left" bgcolor="#cccccc">
      Select List 
    </td>
    <td align="right" bgcolor="#cccccc">
      Multiple Select List 
    </td>
  </tr>
  <tr>
    <td align="left" valign="top">');
$form->addElement('select', 'tmp_select', null, $selectListArray);
$form->addData('
    </td>
    <td align="right">');
$form->addElement('select', 'tmp_multipleSelect[0]', null, $selectListArray, array('multiple' => 'multiple', 'size' => 4));
$form->addData('
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">');
$form->addElement('reset','tmp_reset','Reset Form');
$form->addElement('submit','tmp_submit','Submit Form');
$form->addData('
    </td>
  </tr>
</table>');
$form->addRule('tmp_text','Must enter text value','required',null,'client');

$form->display();
echo "\n<HR> <b>Submitted Values: </b><br />\n";
echo "<pre>";
print_r($_POST);
?>
</body>
</html>
