<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
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

require_once("HTML/QuickForm/element.php");
require_once("HTML/QuickForm/select.php");

// Still some features to be implemented and commented.
// Use with care. This is only EXPERIMENTAL.
// API might change.

/**
 * Class to dynamically create an dates in HTML SELECT fields
 *
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      2.3
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
     * Default values of the SELECT
     * 
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_values = array();
    
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
                            "months_short"  => array ("Jan", "Feb", "M0/00rz", "April", "Mai", "Juni", "Juli", "Aug", "Sept", "Okt", "Nov", "Dez"),
                            "months_long"   => array ("Januar", "Februar", "M0/00rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember")
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
    var $language = "en";
    
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
     * @since     2.3
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_date ($elementName, $value=null, $attributes = array('format'=>'dMY','minyear'=>'1990','maxyear'=>'2020'))
    {
        HTML_QuickForm_element::HTML_QuickForm_element($elementName, $value, $attributes);
        $elementData = array("type"=>$this->_type, "name"=>$elementName);
        $this->updateAttributes($elementData);
        if (is_string($value)) {
            $this->_values = explode("-",$value);
        } else {
            $this->_values = $value;
        }
    } //end constructor
    
    /**
     * Date format parser
     *
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
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
            $selectName = $elementName."[$sign]";
            $selectedValue = $this->_values[$i];
            switch ($sign) {
                case "D" :
                    $options = $this->options[$this->language]["weekdays_short"];
                    array_unshift($options , '--');
                    unset($options[0]);
                    $this->dateSelect[$i] = &new HTML_QuickForm_select($selectName);
                    $this->dateSelect[$i]->load($options,$selectedValue);
                    break;
                case "d":
                    for ($j = 1; $j <= 31; $j++) 
                        $options[$j] = sprintf("%02d", $j);
                    $this->dateSelect[$i] = &new HTML_QuickForm_select($selectName);
                    $this->dateSelect[$i]->load($options,$selectedValue);
                    break;
                case "M" :
                    $options = $this->options[$this->language]["months_short"];
                    array_unshift($options , '--');
                    unset($options[0]);
                    $this->dateSelect[$i] = &new HTML_QuickForm_select($selectName);
                    $this->dateSelect[$i]->load($options,$selectedValue);
                    break;
                case "m" :
                    for ($j = 1; $j <= 12; $j++)
                        $options[$j] = sprintf("%02d", $j);
                    $this->dateSelect[$i] =  &new HTML_QuickForm_select($selectName);
                    $this->dateSelect[$i]->load($options,$selectedValue);
                    break;
                case "F" :
                    $options = $this->options[$this->language]["months_long"];
                    array_unshift($options ,'--');
                    unset($options[0]);
                    $this->dateSelect[$i] = &new HTML_QuickForm_select($selectName);
                    $this->dateSelect[$i]->load($options,$selectedValue);
                    break;
                case "Y" :
                    if ($minyear > $maxyear) {
                        for ($j = $maxyear; $j <= $minyear; $j++)
                            $options[$j] = $j;
                    } else {
                        for ($j = $minyear; $j <= $maxyear; $j++)
                            $options[$j] = $j;
                    }   
                    $this->dateSelect[$i] = &new HTML_QuickForm_select($selectName);
                    $this->dateSelect[$i]->load($options,$selectedValue);
                    break;
            }
        }

    }

    /**
     * Sets the default language for date
     * 
     * @param     string    $language  At the moment only 'en', 'de', 'fr'
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setLanguage($language)
    {
        if (!empty($language)) {
            $this->language = $language;
        } 
    } //end func setLanguage
    
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
        $this->_parseFormat();
        $tabs = $this->_getTabs();
        $strHtml='';
        foreach ($this->dateSelect as $key => $val) {
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