<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once("HTML_QuickForm/Elements/element.php");
require_once("HTML_QuickForm/Elements/select.php");

// Still some features to be implemented and commented.
// Use with care.

/**
 * Class to dynamically create an dates in HTML SELECT fields
 *
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.1
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_date extends HTML_QuickForm_element
{
    /**
     * Type of the field
     * @var       
     * @since     1.3
     * @access    public
     */
    var $type = "date";
    
    /**
     * Contains the date values
     *
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $dateSelect = array();
    
    /**
     * Contains the select options
     *
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_options = array();
    
    /**
     * Default values of the SELECT
     * 
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_values = array();
    
    /**
     * Default date format
     * 
     * @var       string
     * @since     1.0
     * @access    public
     */
    var $format = "Ymd";
    
    /**
     * Options in different languages
     * 
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $options = array(
                        "en"    => array (
                            "weekdays_short"=> array ("Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"),
                            "weekdays_long" => array ("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"),
                            "months_short"  => array ("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"),
                            "months_long"   => array ("January", "February", "March", "April", "May", "June", "Juli", "August", "September", "October", "November", "December")
                        ),
                        "de"    => array (
                            "weekdays_short"=> array ("Mon", "Di", "Mi", "Do", "Fr", "Sa", "So"),
                            "weekdays_long" => array ("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"),
                            "months_short"  => array ("Jan", "Feb", "März", "April", "Mai", "Juni", "Juli", "Aug", "Sept", "Okt", "Nov", "Dez"),
                            "months_long"   => array ("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember")
                        ),
                        "fr"    => array (
                            "weekdays_short"=> array ("Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"),
                            "weekdays_long" => array ("Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"),
                            "months_short"  => array ("Jan"=>"01", "Fev"=>"02", "Mar"=>"03", "Avr"=>"04", "Mai"=>"05", "Jun"=>"06", "Jul"=>"07", "Aou"=>"08", "Sep"=>"09", "Oct"=>"10", "Nov"=>"11", "Dec"=>"12"),
                            "months_long"   => array ("Janvier"=>"01", "Fevrier"=>"02", "Mars"=>"03", "Avril"=>"04", "Mai"=>"05", "Juin"=>"06", "Juillet"=>"07", "Aout"=>"08", "Septembre"=>"09", "Octobre"=>"10", "Novembre"=>"11", "Decembre"=>"12")
                        ),              
                    );
                    
    /**
     * Default date language
     * 
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $language = "fr";
    
    /**
     * Frozen flag tells if element is frozen or not
     * @var       
     * @since     1.3
     * @access    private
     */
    var $_flagFrozen = false;

    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $value          (optional)Input field value
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array. Date format is passed along the attributes.
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_date ($elementName, $value=null, $attributes)
    {
        HTML_QuickForm_element::HTML_QuickForm_element($attributes);
        $elementData = array("type"=>$this->_type, "name"=>$elementName);
        $this->updateAttributes($elementData);
        if (is_string($value)) {
            $this->_values = explode("-",$value);
        } else {
            $this->_values = $value;
        }
        $this->_parseFormat();
    } //end constructor

    function _parseFormat ()
    {
        $format = $this->_attributes["format"];
        $length = strlen($format);
        $minyear = $this->_attributes["minyear"];
        $maxyear = $this->_attributes["maxyear"];
        $elementName = $this->_attributes["name"];
        for ($i=0;$i<$length;$i++) {
            unset($options);
            unset($j);
            $sign = substr($format,$i,1);
            $selectName = $elementName."[]";
            $selectedValue = $this->_values[$i];
            switch ($sign) {
                case "D" :
                    $this->dateSelect[$i] = &HTML_Form::createElement("select",$selectName);
                    $this->dateSelect[$i]->load($this->options[$this->language]["weekdays_short"],$selectedValue);
                    break;
                case "d":
                    for ($j = 1; $j <= 31; $j++) 
                        $options[] = sprintf("%02d", $j);
                    $this->dateSelect[$i] = &HTML_Form::createElement("select",$selectName);
                    $this->dateSelect[$i]->load($options,$selectedValue);
                    break;
                case "M" :
                    $this->dateSelect[$i] = &HTML_Form::createElement("select",$selectName);
                    $this->dateSelect[$i]->load($this->options[$this->language]["months_short"],$selectedValue);
                    break;
                case "m" :
                    for ($j = 1; $j <= 12; $j++)
                        $options[] = sprintf("%02d", $j);
                    $this->dateSelect[$i] = &HTML_Form::createElement("select",$selectName);
                    $this->dateSelect[$i]->load($options,$selectedValue);
                    break;
                case "F" :
                    $this->dateSelect[$i] = &HTML_Form::createElement("select",$selectName);
                    $this->dateSelect[$i]->load($this->options[$this->language]["months_long"],$selectedValue);
                    break;
                case "Y" :
                    if ($minyear > $maxyear) {
                        for ($j = $maxyear; $j <= $minyear; $j++)
                            $options[] = $j;
                    } else {
                        for ($j = $minyear; $j <= $maxyear; $j++)
                            $options[] = $j;
                    }   
                    $this->dateSelect[$i] = &HTML_Form::createElement("select",$selectName);
                    $this->dateSelect[$i]->load($options,$selectedValue);
                    break;
            }
        }

    }

    /**
     * Sets the default values of the select box
     * 
     * @param     mixed    $values  Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setSelected($values)
    {
        if (is_string($values)) {
            $values = split("[ ]?,[ ]?", $values);
        }
        $this->_values = $values;  
    } //end func setValue
    
    /**
     * Returns an array of the selected values
     * 
     * @since     1.0
     * @access    public
     * @return    array of selected values
     * @throws    
     */
    function getSelected()
    {
        return $this->_values;
    } // end func getSelectedValues

    /**
     * Sets the select field size, only applies
     * 
     * @param     int    $size  Size of select  field
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setSize($size)
    {
        $this->updateAttributes(array("size"=>$size));
    } //end func setSize
    
    /**
     * Returns the select field size
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function getSize()
    {
        return $this->getAttribute("size");
    } //end func getSize

    /**
     * Adds a new OPTION to the SELECT
     *
     * @param     string    $text       Display text for the OPTION
     * @param     string    $value      Value for the OPTION
     * @param     mixed     $attributes Either a typical HTML attribute string 
     *                                  or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function addOption($text, $value, $attributes=null)
    {
        $attributes = $this->_parseAttributes($attributes);
        if ($this->getAttribute("selected") && !in_array($value, $this->_values)) {
            $this->_values[] = $value;
            array_unique($this->_values);
        }
        $attr = array("value"=>$value);
        $this->_updateAttrArray($attributes, $attr);
        $this->_options[] = array("text"=>$text, "attr"=>$attributes);
    } // end func addOption
    
    /**
     * Loads the options from an associative array
     * 
     * @param     array    $arr     Associative array of options
     * @param     mixed    $values  (optional) Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    PEAR_Error on error or true
     * @throws    PEAR_Error
     */
    function loadArray($arr, $values=null)
    {
        if (!is_array($arr)) {
            return new PEAR_ERROR("First argument to HTML_Select::loadArray is not a valid array");
        }
        if (isset($values)) {
            $this->setSelected($values);
        }
        while (list($key, $val) = each($arr)) {
            if (is_int($key)) {
                $key = $val;
            }
            $this->addOption($key, $val);
        }
        return true;
    } // end func loadArray

    /**
     * Loads options from different types of data sources
     *
     * @param     mixed     $options     Options source currently supports assoc array or DB_result
     * @param     mixed     $param1     (optional) See function detail
     * @param     mixed     $param2     (optional) See function detail
     * @param     mixed     $param3     (optional) See function detail
     * @param     mixed     $param4     (optional) See function detail
     * @since     1.1
     * @access    public
     * @return    PEAR_Error on error or true
     * @throws    PEAR_Error
     */
    function load(&$options, $values)
    {
        return $this->loadArray($options, $values);
    } // end func load
    
    /**
     * Returns the SELECT in HTML
     *
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function toHtml()
    {
        $tabs = $this->_getTabs();
        while(list($key,$val)=each($this->dateSelect)) {
            if ($this->_flagFrozen) {
                $strHtml .= $this->dateSelect[$key]->getFrozen();
            } else {
                $strHtml .= $this->dateSelect[$key]->toHtml();
            }
        }
        return $strHtml;
    } // end func toHtml
}
?>