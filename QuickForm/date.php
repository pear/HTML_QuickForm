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

require_once('HTML/QuickForm/element.php');
require_once('HTML/QuickForm/select.php');

/**
 * Class to dynamically create HTML Select elements from a date
 *
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @access       public
 * @deprecated   Use dategroup element instead
 */
class HTML_QuickForm_date extends HTML_QuickForm_element
{   
    // {{{ properties

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
                        'en'    => array (
                            'weekdays_short'=> array ('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'),
                            'weekdays_long' => array ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
                            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
                            'months_long'   => array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')
                        ),
                        'de'    => array (
                            'weekdays_short'=> array ('So', 'Mon', 'Di', 'Mi', 'Do', 'Fr', 'Sa'),
                            'weekdays_long' => array ('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'),
                            'months_short'  => array ('Jan', 'Feb', 'Marz', 'April', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez'),
                            'months_long'   => array ('Januar', 'Februar', 'Marz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember')
                        ),
                        'fr'    => array (
                            'weekdays_short'=> array ('Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'),
                            'weekdays_long' => array ('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'),
                            'months_short'  => array ('Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'),
                            'months_long'   => array ('Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre')
                        ),
                        'hu'    => array (
                            'weekdays_short'=> array ('V', 'H', 'K', 'Sze', 'Cs', 'P', 'Szo'),
                            'weekdays_long' => array ('vasárnap', 'hétfõ', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat'),
                            'months_short'  => array ('jan', 'feb', 'márc', 'ápr', 'máj', 'jún', 'júl', 'aug', 'szept', 'okt', 'nov', 'dec'),
                            'months_long'   => array ('január', 'február', 'március', 'április', 'május', 'június', 'július', 'augusztus', 'szeptember', 'október', 'november', 'december')
                        ),
                        'pl'    => array (
                            'weekdays_short'=> array ('Nie', 'Pn', 'Wt', '¦r', 'Czw', 'Pt', 'Sob'),
                            'weekdays_long' => array ('Niedziela', 'Poniedzia³ek', 'Wtorek', '¦roda', 'Czwartek', 'Pi±tek', 'Sobota'),
                            'months_short'  => array ('Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Pa¼', 'Lis', 'Gru'),
                            'months_long'   => array ('Styczeñ', 'Luty', 'Marzec', 'Kwiecieñ', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpieñ', 'Wrzesieñ', 'Pa¼dziernik', 'Listopad', 'Grudzieñ')
                        ),
                        'sl'    => array (
                            'weekdays_short'=> array ('Ned', 'Pon', 'Tor', 'Sre', 'Cet', 'Pet', 'Sob'),
                            'weekdays_long' => array ('Nedelja', 'Ponedeljek', 'Torek', 'Sreda', 'Cetrtek', 'Petek', 'Sobota'),
                            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Avg', 'Sep', 'Okt', 'Nov', 'Dec'),
                            'months_long'   => array ('Januar', 'Februar', 'Marec', 'April', 'Maj', 'Junij', 'Julij', 'Avgust', 'September', 'Oktober', 'November', 'December')
                        ),
                        'ru'    => array (
                            'weekdays_short'=> array ('Âñ', 'Ïí', 'Âò', 'Ñð', '×ò', 'Ïò', 'Ñá'),
                            'weekdays_long' => array ('Âîñêðåñåíüå', 'Ïîíåäåëüíèê', 'Âòîðíèê', 'Ñðåäà', '×åòâåðã', 'Ïÿòíèöà', 'Ñóááîòà'),
                            'months_short'  => array ('ßíâ', 'Ôåâ', 'Ìàð', 'Àïð', 'Ìàé', 'Èþí', 'Èþë', 'Àâã', 'Ñåí', 'Îêò', 'Íîÿ', 'Äåê'),
                            'months_long'   => array ('ßíâàðü', 'Ôåâðàëü', 'Ìàðò', 'Àïðåëü', 'Ìàé', 'Èþíü', 'Èþëü', 'Àâãóñò', 'Ñåíòÿáðü', 'Îêòÿáðü', 'Íîÿáðü', 'Äåêàáðü')
                        ),
                        'es'    => array (
                            'weekdays_short'=> array ('Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'),
                            'weekdays_long' => array ('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
                            'months_short'  => array ('Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'),
                            'months_long'   => array ('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septimbre', 'Octubre', 'Noviembre', 'Diciembre')
                        ),
                        'da'    => array (
                            'weekdays_short'=> array ('Søn', 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'Lør'),
                            'weekdays_long' => array ('Søndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag'),
                            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
                            'months_long'   => array ('Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'December')
                        ),
                        'is'    => array (
                            'weekdays_short'=> array ('Sun', 'Mán', 'Þri', 'Mið', 'Fim', 'Fös', 'Lau'),
                            'weekdays_long' => array ('Sunnudagur', 'Mánudagur', 'Þriðjudagur', 'Miðvikudagur', 'Fimmtudagur', 'Föstudagur', 'Laugardagur'),
                            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Maí', 'Jún', 'Júl', 'Ágú', 'Sep', 'Okt', 'Nóv', 'Des'),
                            'months_long'   => array ('Janúar', 'Febrúar', 'Mars', 'Apríl', 'Maí', 'Júní', 'Júlí', 'Ágúst', 'September', 'Október', 'Nóvember', 'Desember')
                        ),
                        'it'    => array (
                            'weekdays_short'=> array ('Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'),
                            'weekdays_long' => array ('Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'),
                            'months_short'  => array ('Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'),
                            'months_long'   => array ('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre')
                        ),
                        'sk'    => array (
                            'weekdays_short'=> array ('Ned', 'Pon', 'Uto', 'Str', 'Štv', 'Pia', 'Sob'),
                            'weekdays_long' => array ('Nede¾a', 'Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota'),
                            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Máj', 'Jún', 'Júl', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
                            'months_long'   => array ('Január', 'Ferbruár', 'Marec', 'Apríl', 'Máj', 'Jún', 'Júl', 'August', 'September', 'Október', 'November', 'December')
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

    // }}}
    // {{{ constructor

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

    // }}}
    // {{{ _setDefaults()

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

    // }}}
    // {{{ setName()

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

    // }}}
    // {{{ getName()

    /**
     * Returns the element name
     * @access    public
     * @return    string
     */
    function getName()
    {
        return $this->name;
    } //end func getName

    // }}}
    // {{{ setFormat()

    /**
     * Sets the date element format.  The available formats are:
     * D => Short names of days
     * l => Long names of days
     * d => Day numbers
     * M => Short names of months
     * F => Long names of months
     * m => Month numbers
     * Y => Four digit year
     * h => 12 hour format
     * h => 23 hour  format
     * i => Minutes
     * s => Seconds
     * a => am/pm
     * A => AM/PM
     *
     * @access    public
     * @return    void
     */
    function setFormat($format)
    {
        $this->format = $format;
    } // end func setFormat

    // }}}
    // {{{ setMinYear()

    /**
     * Sets the date element minimum year
     * @access    public
     * @return    void
     */
    function setMinYear($year)
    {
        $this->minYear = $year;
    } // end func setMinYear

    // }}}
    // {{{ setMaxYear()

    /**
     * Sets the date element maximum year
     * @access    public
     * @return    void
     */
    function setMaxYear($year)
    {
        $this->maxYear = $year;
    } // end func setMaxYear

    // }}}
    // {{{ setLanguage()

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

    // }}}
    // {{{ _createSelects()

    /**
     * Creates the select objects
     * @access    public
     * @return    void
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
            $loadSelect = true;

            switch ($sign) {
                case 'D':
                    // Sunday is 0 like with 'w' in date()
                    $options = $this->_options[$this->language]['weekdays_short'];
                    break;
                case 'l':
                    $options = $this->_options[$this->language]['weekdays_long'];
                    break;
                case 'd':
                    $options = $this->_createNumericOptionList(1, 31);
                    break;
                case 'M':
                    $options = $this->_options[$this->language]['months_short'];
                    array_unshift($options , '');
                    unset($options[0]);
                    break;
                case 'm':
                    $options = $this->_createNumericOptionList(1, 12);
                    break;
                case 'F':
                    $options = $this->_options[$this->language]['months_long'];
                    array_unshift($options , '');
                    unset($options[0]);
                    break;
                case 'Y':
                    if ($minYear > $maxYear) {
                        $options = $this->_createNumericOptionList($maxYear, $minYear);
                    } else {
                        $options = $this->_createNumericOptionList($minYear, $maxYear);
                    }
                    break;
                case 'h':
                    $options = $this->_createNumericOptionList(1, 12);
                    break;
                case 'H':
                    $options = $this->_createNumericOptionList(0, 23);
                    break;
                case 'i':
                    $options = $this->_createNumericOptionList(0, 59);
                    break;
                case 's':
                    $options = $this->_createNumericOptionList(0, 59);
                    break;
                case 'a':
                    $options = array('am' => 'am', 'pm' => 'pm');
                    break;
                case 'A':
                    $options = array('AM' => 'AM', 'PM' => 'PM');
                    break;
                default:
                    $this->dateSelect[] = $sign;
                    $loadSelect = false;
            }

            if ($loadSelect) {
                $this->dateSelect[$sign] = &new HTML_QuickForm_select($selectName, null, null, $this->getAttributes());
                $this->dateSelect[$sign]->load($options,$selectedValue);
            }
        }
    } // end func _createSelects

    // }}}
    // {{{ _createNumericOptionList()

    /**
     * Creates a numeric option list based on a start number and end number
     *
     * @param int $start The start number
     * @param int $end The end number
     *
     * @access public
     * @return array An array of numeric options.
     */
    function _createNumericOptionList($start, $end)
    {
        $options = array();
        for ($i = $start; $i <= $end; $i++) {
            $options[$i] = sprintf('%02d', $i);
        }
        return $options;
        
    } // end func _createNumericOptionList

    // }}}
    // {{{ setSelectedDate()

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
            $arr = explode('-', date('w-d-n-Y-h-H-i-s-a-A', (int)$date));
            $this->_selectedDate = array('D' => $arr[0],
                                         'l' => $arr[0],
                                         'd' => $arr[1],
                                         'M' => $arr[2],
                                         'm' => $arr[2],
                                         'F' => $arr[2],
                                         'Y' => $arr[3],
                                         'h' => $arr[4],
                                         'H' => $arr[5],
                                         'i' => $arr[6],
                                         's' => $arr[7],
                                         'a' => $arr[8],
                                         'A' => $arr[9]);
        }
    } // end func setSelectedDate

    // }}}
    // {{{ toHtml()

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

    // }}}
    // {{{ onQuickFormEvent()

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
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                // constant values override both default and submitted ones
                // default values are overriden by submitted
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_submitValues);
                    if (null === $value) {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (null !== $value) {
                    $this->setSelectedDate($value);
                }
                break;
            case 'setGroupValue':
                $this->setSelectedDate($arg);
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    } // end func onQuickFormEvent

    // }}}
    // {{{ setValue()

    function setValue($value)
    {
        $this->setSelectedDate($value);
    }

    // }}}
    // {{{ getValue()

    function getValue()
    {
        return $this->_selectedDate;
    }

    // }}}
    // {{{ exportValue()

    function exportValue(&$submitValues, $assoc = false)
    {
        $this->_createSelects();
        $value = null;
        foreach (array_keys($this->dateSelect) as $key) {
            // selects have string keys, separators have numeric ones
            if (is_string($key)) {
                $v = $this->dateSelect[$key]->exportValue($submitValues, $assoc);
                if (null !== $v) {
                    if (null === $value) {
                        $value = array();
                    }
                    if ($assoc) {
                        $value = @array_merge_recursive($value, $v);
                    } else {
                        $value[$key] = $v;
                    }
                }
            }
        }
        return $value;
    }

    // }}}
} // end class HTML_QuickForm_date
?>
