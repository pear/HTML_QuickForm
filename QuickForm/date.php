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

/**
 * Class to dynamically create HTML Select elements from a date
 *
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @access       public
 */
class HTML_QuickForm_date extends HTML_QuickForm_element
{   
    /**
     * Contains the select objects
     * @var       array
     * @access    private
     */
    var $dateSelect = array();
    
    /**
     * Default values of the SELECTs
     * @var       array
     * @access    private
     */
    var $_selectedDate = array();
    
    /**
     * Options in different languages
     * @var       array
     * @access    private
     */
    var $_options = array(
                        "en"    => array (
                            "weekdays_short"=> array ("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"),
                            "weekdays_long" => array ("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"),
                            "months_short"  => array ("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"),
                            "months_long"   => array ("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December")
                        ),
                        "de"    => array (
                            "weekdays_short"=> array ("So", "Mon", "Di", "Mi", "Do", "Fr", "Sa"),
                            "weekdays_long" => array ("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"),
                            "months_short"  => array ("Jan", "Feb", "Marz", "April", "Mai", "Juni", "Juli", "Aug", "Sept", "Okt", "Nov", "Dez"),
                            "months_long"   => array ("Januar", "Februar", "Marz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember")
                        ),
                        "fr"    => array (
                            "weekdays_short"=> array ("Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"),
                            "weekdays_long" => array ("Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"),
                            "months_short"  => array ("Jan", "Fev", "Mar", "Avr", "Mai", "Jun", "Jul", "Aou", "Sep", "Oct", "Nov", "Dec"),
                            "months_long"   => array ("Janvier", "Fevrier", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Decembre")
                        ),
                        "sl"    => array (
                            "weekdays_short"=> array ("Ned", "Pon", "Tor", "Sre", "Cet", "Pet", "Sob"),
                            "weekdays_long" => array ("Nedelja", "Ponedeljek", "Torek", "Sreda", "Cetrtek", "Petek", "Sobota"),
                            "months_short"  => array ("Jan", "Feb", "Mar", "Apr", "Maj", "Jun", "Jul", "Avg", "Sep", "Okt", "Nov", "Dec"),
                            "months_long"   => array ("Januar", "Februar", "Maj", "April", "Maj", "Juni", "Julij", "Avgust", "September", "Oktober", "November", "December")
                        ),
                        'ru'    => array (
                            'weekdays_short'=> array ('Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'),
                            'weekdays_long' => array ('Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'),
                            'months_short'  => array ('Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'),
                            'months_long'   => array ('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь')
                        )
                    );
                    
    /**
     * Default date language
     * @var       string
     * @access    public
     */
    var $language = 'en';

    /**
     * Default date format
     * @var       string
     * @access    public
     */
    var $format = 'dMY';

    /**
     * Default minimum year Arthur C. Clarke style
     * @var       int
     * @access    public
     */
    var $minYear = 2001;

    /**
     * Default maximum year Arthur C. Clarke style again
     * @var       int
     * @access    public
     */
    var $maxYear = 2010;

    /**
     * Frozen flag tells if element is frozen or not
     * @var       bool
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
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_date($elementName=null, $elementLabel=null, $options=array(), $attributes=null)
    {
        HTML_QuickForm_element::HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'date';
        $this->_setDefaults($options);
    } //end constructor

    /**
     * sets the defaults options
     * Current options are:
     * - format
     * - minYear
     * - maxYear
     * - language
     * 
     * @param     array    $options    array of options
     * @access    private
     * @return    void
     */
    function _setDefaults($options)
    {
        if (isset($options['format'])) {
            $this->setFormat($options['format']);
        }
        if (isset($options['language'])) {
            $this->setLanguage($options['language']);
        }
        if (isset($options['minYear'])) {
            $this->setMinYear($options['minYear']);
        }
        if (isset($options['maxYear'])) {
            $this->setMaxYear($options['maxYear']);
        }
    } // end func _setDefaults

    /**
     * Sets the input field name
     * @param     string    $name   Input field name attribute
     * @access    public
     * @return    void
     */
    function setName($name)
    {
        $this->name = $name;
    } //end func setName

    /**
     * Returns the element name
     * @access    public
     * @return    string
     */
    function getName()
    {
        return $this->name;
    } //end func getName

    /**
     * Sets the date element format
     * @access    public
     * @return    void
     */
    function setFormat($format)
    {
        $this->format = $format;
    } // end func setFormat

    /**
     * Sets the date element minimum year
     * @access    public
     * @return    void
     */
    function setMinYear($year)
    {
        $this->minYear = $year;
    } // end func setMinYear

    /**
     * Sets the date element maximum year
     * @access    public
     * @return    void
     */
    function setMaxYear($year)
    {
        $this->maxYear = $year;
    } // end func setMaxYear

    /**
     * Sets the default language for date
     * @param     string    $language  At the moment only 'en', 'de', 'fr'
     * @access    public
     * @return    void
     */
    function setLanguage($language)
    {
        if (in_array($language, array_keys($this->_options))) {
            $this->language = $language;
        } else {
            // use defaults (send your translation please)
            $this->language = 'en';
        }
    } //end func setLanguage

    /**
     * Creates the select objects
     * @access    public
     * @return    
     */
    function _createSelects()
    {
        $this->dateSelect = array();
        $length = strlen($this->format);
        $elementName = $this->name;
        $minYear = $this->minYear;
        $maxYear = $this->maxYear;
        for ($i = 0; $i < $length; $i++) {
            unset($options);
            unset($j);

            $sign = substr($this->format, $i, 1);

            $selectName = $elementName."[$sign]";
            $selectedValue = (isset($this->_selectedDate[$sign])) ? $this->_selectedDate[$sign] : null;

            switch ($sign) {
                case "D" :
                    // Sunday is 0 like with 'w' in date()
                    $options = $this->_options[$this->language]["weekdays_short"];
                    $this->dateSelect[$sign] = &new HTML_QuickForm_select($selectName, null, null, $this->getAttributes());
                    $this->dateSelect[$sign]->load($options, $selectedValue);
                    break;
                case "l" :
                    $options = $this->_options[$this->language]["weekdays_long"];
                    $this->dateSelect[$sign] = &new HTML_QuickForm_select($selectName, null, null, $this->getAttributes());
                    $this->dateSelect[$sign]->load($options, $selectedValue);
                    break;
                case "d":
                    for ($j = 1; $j <= 31; $j++) 
                        $options[$j] = sprintf("%02d", $j);
                    $this->dateSelect[$sign] = &new HTML_QuickForm_select($selectName, null, null, $this->getAttributes());
                    $this->dateSelect[$sign]->load($options, $selectedValue);
                    break;
                case "M" :
                    $options = $this->_options[$this->language]["months_short"];
                    array_unshift($options , '');
                    unset($options[0]);
                    $this->dateSelect[$sign] = &new HTML_QuickForm_select($selectName, null, null, $this->getAttributes());
                    $this->dateSelect[$sign]->load($options,$selectedValue);
                    break;
                case "m" :
                    for ($j = 1; $j <= 12; $j++)
                        $options[$j] = sprintf("%02d", $j);
                    $this->dateSelect[$sign] =  &new HTML_QuickForm_select($selectName, null, null, $this->getAttributes());
                    $this->dateSelect[$sign]->load($options,$selectedValue);
                    break;
                case "F" :
                    $options = $this->_options[$this->language]["months_long"];
                    array_unshift($options , '');
                    unset($options[0]);
                    $this->dateSelect[$sign] = &new HTML_QuickForm_select($selectName, null, null, $this->getAttributes());
                    $this->dateSelect[$sign]->load($options, $selectedValue);
                    break;
                case "Y" :
                    if ($minYear > $maxYear) {
                        for ($j = $maxYear; $j <= $minYear; $j++)
                            $options[$j] = $j;
                    } else {
                        for ($j = $minYear; $j <= $maxYear; $j++)
                            $options[$j] = $j;
                    }   
                    $this->dateSelect[$sign] = &new HTML_QuickForm_select($selectName, null, null, $this->getAttributes());
                    $this->dateSelect[$sign]->load($options, $selectedValue);
                    break;
                default:
                    $this->dateSelect[] = $sign;
            }
        }
    } // end func _createSelects

    /**
     * Sets the selected date
     * @param     mixed     an associative array corresponding to the format you chose
     *                      for your date or an unix epoch timestamp
     * @access    public
     * @return    void
     */
    function setSelectedDate($date)
    {
        if (is_array($date)) {
            $this->_selectedDate = $date;
        } else {
            // might be a unix epoch, then we fill all possible values
            $arr = explode('-', date('w-d-n-Y', (int)$date));
            $this->_selectedDate = array('D' => $arr[0],
                                         'l' => $arr[0],
                                         'd' => $arr[1],
                                         'M' => $arr[2],
                                         'm' => $arr[2],
                                         'F' => $arr[2],
                                         'Y' => $arr[3]);
        }
    } // end func setSelectedDate

    /**
     * Returns the SELECT in HTML
     * @access    public
     * @return    string
     * @throws    
     */
    function toHtml()
    {
        $this->_createSelects();
        $strHtml = '';
        foreach ($this->dateSelect as $key => $element) {
            if ($this->_flagFrozen) {
                if (is_string($element)) {
                    $strHtml .= str_replace(' ', '&nbsp;', $element);
                } else {
                    $strHtml .= $element->getFrozenHtml();
                }
            } else {
                if (is_string($element)) {
                    $strHtml .= str_replace(' ', '&nbsp;', $element);
                } else {
                    $strHtml .= $element->toHtml();
                }
            }
        }
        return $strHtml;
    } // end func toHtml

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string    $event  Name of event
     * @param     mixed     $arg    event arguments
     * @param     object    $caller calling object
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function onQuickFormEvent($event, $arg, &$callerLocal)
    {
        global $caller;
        // make it global so we can access it in any of the other methods if needed
        $caller =& $callerLocal;
        $className = get_class($this);
        switch ($event) {
            case 'addElement':
            case 'createElement':
                $this->$className($arg[0], $arg[1], $arg[2], $arg[3], $arg[4]);
                // need to set the submit value in case setDefault never gets called
                $elementName = $this->getName();
                if (isset($caller->_submitValues[$elementName])) {
                    $date = $caller->_submitValues[$elementName];
                    $this->setSelectedDate($date);
                }
                break;
            case 'setDefault':
                // In form display, default value is always overidden by submitted value
                $elementName = $this->getName();
                if (isset($caller->_submitValues[$elementName])) {
                    $date = $caller->_submitValues[$elementName];
                } else {
                    $date = $arg;
                }
                $this->setSelectedDate($date);
                break;
            case 'setConstant':
                // In form display, constant value overides submitted value
                // but submitted value is kept in _submitValues array
                $this->setSelectedDate($arg);
                break;
        }
        return true;
    } // end func onQuickFormEvent
} // end class HTML_QuickForm_date
?>