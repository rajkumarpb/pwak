<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PWAK (PHP Web Application Kit) framework.
 *
 * PWAK is a php framework initially developed for the
 * {@link http://onlogistics.googlecode.com Onlogistics} ERP/Supply Chain
 * management web application.
 * It provides components and tools for developers to build complex web
 * applications faster and in a more reliable way.
 *
 * PHP version 5.1.0+
 * 
 * LICENSE: This source file is subject to the MIT license that is available
 * through the world-wide-web at the following URI:
 * http://opensource.org/licenses/mit-license.php
 *
 * @package   PWAK
 * @author    ATEOR dev team <dev@ateor.com>
 * @copyright 2003-2008 ATEOR <contact@ateor.com> 
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   SVN: $Id: I18n.php,v 1.15 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

if (!defined('DOMAIN')) {
    define('DOMAIN', 'messages');
}

/**
 * Répertoire vers les fichiers catalogues
 */
if (!defined('LOCALE_DIR')) {
    define('LOCALE_DIR', PROJECT_ROOT . '/locale/');
}

/**
 * Locale par défaut si aucune n'est définie
 */
if (!defined('LOCALE_DEFAULT')) {
    define('LOCALE_DEFAULT', 'fr_FR');
}

// }}}

/**
 * Classe de gestion de l'internationalisation (i18n).
 *
 * Cette classe permet de d'initialiser le moteur gettext avec une locale
 * donnée, et de récupérer un certain nombre d'informations spécifiques à la
 * langue en cours.
 *
 * Voici un example complet d'utilisation:
 * <code>
 *
 * // quelles locales sont supportées ?
 * echo 'Les locales supportées sont: <ul>';
 * foreach(I18N::getSupportedLocales() as $name) {
 *     echo '<li>' . $name . '</li>';
 * }
 * echo '</ul>';
 *
 * // supposons qu'on ait stocké la chaine correspondant à locale de
 * // prédilection de l'utilisateur dans un cookie.
 * I18N::setLocale($_COOKIE['user_locale']);
 *
 * // la langue est maintenant définie
 * echo 'La langue courante est: ' . I18N::getLocaleName() . '<br/>';
 *
 * // on peut aussi récupérer d'autres informations comme le code, la date ou
 * // encore l'encoding en vigueur pour la locale définie
 * echo 'Son code est: ' . I18N::getLocaleCode() . '<br/>';
 * echo 'Son encoding est défini sur: ' . I18N::getLocaleEncoding() . '<br/>';
 *
 * // enfin on peut se servir des fonctions de formattage:
 * echo I18N::formatDate(time(), I18N::DATETIME_LONG_TEXTUAL) . '<br/>';
 * echo I18N::formatDuration(3600) . '<br/>';
 * echo I18N::formatNumber(12345.678, 2) . '<br/>';
 * echo I18N::formatCurrency('$', 12.34) . '<br/>';
 * </code>
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License 
 * @package    Framework
 * @copyright  2002-2006 ATEOR - All rights reserved
 * @since      version 2.0.1
 */
class I18N
{
    // Constantes {{{

    /**
     * Constantes pour les formats de date prédéfinis.
     */
    const TIME_SHORT             =  1; // ex: HH:MM
    const TIME_LONG              =  2; // ex: HH:MM:SS
    const DATE_SHORT             =  3; // DD/MM/YY
    const DATE_LONG              =  4; // DD/MM/YYYY
    const DATE_SHORT_TEXTUAL     =  5; // jeu. 11 jan. 2007
    const DATE_LONG_TEXTUAL      =  6; // jeudi 11 janvier 2007
    const DATETIME_SHORT         =  7; // DATE_SHORT + TIME_SHORT
    const DATETIME_LONG          =  8; // DATE_LONG  + TIME_SHORT
    const DATETIME_FULL          =  9; // DATE_LONG  + TIME_LONG
    const DATETIME_SHORT_TEXTUAL = 10; // DATE_SHORT_TEXTUAL + TIME_SHORT
    const DATETIME_LONG_TEXTUAL  = 11; // DATE_LONG_TEXTUAL + TIME_SHORT
    const DATETIME_FULL_TEXTUAL  = 12; // DATE_LONG_TEXTUAL + TIME_LONG

    // }}}
    // properties {{{

    /**
     * Tableau des locales supportées.
     * Chaque locale supportée doit être définie ici.
     *
     * @var    array $locales
     * @static
     * @access protected
     * @todo Externaliser le tableau I18N::$locales dans des fichiers pour 
     * chaque langue, car ça risque de devenir un tableau énorme sinon...
     */
    protected static $locales = array(
        // fr_FR {{{
        'fr_FR' => array(
            'name'            => 'Français',
            'code'            => 'fr_FR',
            'shortcode'       => 'fr',
            'encoding'        => 'ISO-8859-15',
            'currency_format' => '%s {$CURRENCY}',
            'percent_format'  => '%s %%',
            'thousand_sep'    => ' ',
            'decimal_sep'     => ',',
            'date_format'     => array(
                I18N::TIME_SHORT             => '%H:%M',
                I18N::TIME_LONG              => '%H:%M:%S',
                I18N::DATE_SHORT             => '%d/%m/%y',
                I18N::DATE_LONG              => '%d/%m/%Y',
                I18N::DATE_SHORT_TEXTUAL     => '%a %d %b %Y',
                I18N::DATE_LONG_TEXTUAL      => '%A %d %B %Y',
                I18N::DATETIME_SHORT         => '%d/%m/%y %H:%M',
                I18N::DATETIME_LONG          => '%d/%m/%Y %H:%M',
                I18N::DATETIME_FULL          => '%d/%m/%Y %H:%M:%S',
                I18N::DATETIME_SHORT_TEXTUAL => '%a %d %b %Y %H:%M',
                I18N::DATETIME_LONG_TEXTUAL  => '%A %d %B %Y %H:%M',
                I18N::DATETIME_FULL_TEXTUAL  => '%A %d %B %Y %H:%M:%S'
            ),
            'days' => array(
                array('Dimanche', 'Dim'),
                array('Lundi', 'Lun'),
                array('Mardi', 'Mar'),
                array('Mercredi', 'Mer'),
                array('Jeudi', 'Jeu'), 
                array('Vendredi', 'Ven'),
                array('Samedi', 'Sam')
            ),
            'monthes' => array(
                array('Janvier', 'Jan'),
                array('Février', 'Fév'),
                array('Mars', 'Mar'),
                array('Avril', 'Avr'),
                array('Mai', 'Mai'), 
                array('Juin', 'Juin'),
                array('Juillet', 'Jui'),
                array('Août', 'Août'),
                array('Septembre', 'Sep'),
                array('Octobre', 'Oct'),
                array('Novembre', 'Nov'),
                array('Décembre', 'Déc')
            ),
            'html_select_date_format' => 'dmY',
            'duration_format' => array(
                'day'    => '%s j.',
                'hour'   => '%s h.',
                'minute' => '%s min.'
            ),
            // en français on écrit: 100 000,45
            'extract_pattern' => array(' ', ','),
            'extract_replacement' => array('', '.')
        ),
        // }}}
        // en_GB {{{
        'en_GB' => array(
            'name'            => 'English (GB)',
            'code'            => 'en_GB',
            'shortcode'       => 'en',
            'encoding'        => 'ISO-8859-1',
            'currency_format' => '{$CURRENCY}%s',
            'percent_format'  => '%% %s',
            'thousand_sep'    => ',',
            'decimal_sep'     => '.',
            'date_format'     => array(
                I18N::TIME_SHORT             => '%H:%M',
                I18N::TIME_LONG              => '%H:%M:%S',
                I18N::DATE_SHORT             => '%y/%m/%d',
                I18N::DATE_LONG              => '%Y/%m/%d',
                I18N::DATE_SHORT_TEXTUAL     => '%a, %d %b %Y',
                I18N::DATE_LONG_TEXTUAL      => '%A, %d %B %Y',
                I18N::DATETIME_SHORT         => '%Y/%m/%d %H:%M',
                I18N::DATETIME_LONG          => '%Y/%m/%d %H:%M',
                I18N::DATETIME_FULL          => '%Y/%m/%d %H:%M:%S',
                I18N::DATETIME_SHORT_TEXTUAL => '%a, %d %b %Y %H:%M',
                I18N::DATETIME_LONG_TEXTUAL  => '%A, %d %B %Y %H:%M',
                I18N::DATETIME_FULL_TEXTUAL  => '%A, %d %B %Y %H:%M:%S'
            ),
            'days' => array(
                array('Sunday', 'Sun'),
                array('Monday', 'Mon'),
                array('Tuesday', 'Tue'),
                array('Wednesday', 'Wed'),
                array('Thursday', 'Thu'), 
                array('Friday', 'Fri'),
                array('Saturday', 'Sat')
            ),
            'monthes' => array(
                array('January', 'Jan'),
                array('February', 'Feb'),
                array('March', 'Mar'),
                array('April', 'Apr'),
                array('May', 'May'), 
                array('June', 'Jun'),
                array('July', 'Jul'),
                array('August', 'Aug'),
                array('September', 'Sep'),
                array('October', 'Oct'),
                array('November', 'Nov'),
                array('December', 'Dec')
            ),
            'html_select_date_format' => 'Ymd',
            'duration_format' => array(
                'day'    => '%s d.',
                'hour'   => '%s h.',
                'minute' => '%s min.'
            ),
            // en anglais on écrit: 100,000.45
            'extract_pattern' => array(','),
            'extract_replacement' => array('')
        ),
        // }}}
        // nl_NL {{{
        'nl_NL' => array(
            'name'            => 'Nederlands',
            'code'            => 'nl_NL',
            'shortcode'       => 'nl',
            'encoding'        => 'ISO-8859-1',
            'currency_format' => '{$CURRENCY} %s',
            'percent_format'  => '%% %s',
            'thousand_sep'    => '.',
            'decimal_sep'     => ',',
            'date_format'     => array(
                I18N::TIME_SHORT             => '%H:%M',
                I18N::TIME_LONG              => '%H:%M:%S',
                I18N::DATE_SHORT             => '%d/%m/%y',
                I18N::DATE_LONG              => '%d/%m/%Y',
                I18N::DATE_SHORT_TEXTUAL     => '%a %d %b %Y',
                I18N::DATE_LONG_TEXTUAL      => '%A %d %B %Y',
                I18N::DATETIME_SHORT         => '%d/%m/%y %H:%M',
                I18N::DATETIME_LONG          => '%d/%m/%Y %H:%M',
                I18N::DATETIME_FULL          => '%d/%m/%Y %H:%M:%S',
                I18N::DATETIME_SHORT_TEXTUAL => '%a %d %b %Y %H:%M',
                I18N::DATETIME_LONG_TEXTUAL  => '%A %d %B %Y %H:%M',
                I18N::DATETIME_FULL_TEXTUAL  => '%A %d %B %Y %H:%M:%S'
            ),
            'days' => array(
                array('zondag', 'zon'),
                array('maandag', 'maa'),
                array('dinsdag', 'din'),
                array('woensdag', 'woe'),
                array('donderdag', 'don'), 
                array('vrijdag', 'vrij'),
                array('zaterdag', 'zat')
            ),
            'monthes' => array(
                array('januari', 'jan'),
                array('februari', 'feb'),
                array('maart', 'maa'),
                array('april', 'apr'),
                array('mei', 'mei'), 
                array('juni', 'jun'),
                array('juli', 'jul'),
                array('augustus', 'aug'),
                array('september', 'sep'),
                array('oktober', 'okt'),
                array('november', 'nov'),
                array('december', 'dec')
            ),
            'html_select_date_format' => 'dmY',
            'duration_format' => array(
                'day'    => '%s d.', // dagen
                'hour'   => '%s u.', // uren
                'minute' => '%s min.'// minuten
            ),
            // en dutch, on écrit: 100.000,45
            'extract_pattern' => array('.', ','),
            'extract_replacement' => array('', '.')
        ),
        // }}}
        // de_DE {{{
        'de_DE' => array(
            'name'            => 'Deutsch',
            'code'            => 'de_DE',
            'shortcode'       => 'de',
            'encoding'        => 'ISO-8859-1',
            'currency_format' => '%s {$CURRENCY}',
            'percent_format'  => '%s %%',
            'thousand_sep'    => '.',
            'decimal_sep'     => ',',
            'date_format'     => array(
                I18N::TIME_SHORT             => '%H:%M',
                I18N::TIME_LONG              => '%H:%M:%S',
                I18N::DATE_SHORT             => '%d.%m.%y',
                I18N::DATE_LONG              => '%d.%m.%Y',
                I18N::DATE_SHORT_TEXTUAL     => '%A, %d. %B %Y',
                I18N::DATE_LONG_TEXTUAL      => '%A, %d. %B %Y',
                I18N::DATETIME_SHORT         => '%d.%m.%y %H:%M',
                I18N::DATETIME_LONG          => '%d.%m.%Y %H:%M',
                I18N::DATETIME_FULL          => '%d.%m.%Y %H:%M:%S',
                I18N::DATETIME_SHORT_TEXTUAL => '%A, %d. %B %Y %H:%M',
                I18N::DATETIME_LONG_TEXTUAL  => '%A, %d. %B %Y %H:%M',
                I18N::DATETIME_FULL_TEXTUAL  => '%A, %d. %B %Y %H:%M:%S'
            ),
            'days' => array(
                array('Sonntag', 'Son'),
                array('Montag', 'Mon'),
                array('Dienstag', 'Die'),
                array('Mittwoch', 'Mit'),
                array('Donnerstag', 'Don'), 
                array('Freitag', 'Fre'),
                array('Samstag', 'Sam')
            ),
            'monthes' => array(
                array('Januar', 'Jan'),
                array('Februar', 'Feb'),
                array('März', 'Mär'),
                array('April', 'Apr'),
                array('Mai', 'Mai'), 
                array('Juni', 'Jun'),
                array('Juli', 'Jul'),
                array('August', 'Aug'),
                array('September', 'Sep'),
                array('Oktober', 'Okt'),
                array('November', 'Nov'),
                array('Dezember', 'Dez')
            ),
            'html_select_date_format' => 'dmY',
            'duration_format' => array(
                'day'    => '%s T.',
                'hour'   => '%s Std.',
                'minute' => '%s Min.'
            ),
            // en deutsch, on écrit: 100.000,45
            'extract_pattern' => array('.', ','),
            'extract_replacement' => array('', '.')
        ),
        // }}}
        // tr_TR {{{
        'tr_TR' => array(
            'name'            => 'Türkçe',
            'code'            => 'tr_TR',
            'shortcode'       => 'tr',
            'encoding'        => 'ISO-8859-9',
            'currency_format' => '%s {$CURRENCY}',
            'percent_format'  => '%% %s',
            'thousand_sep'    => '.',
            'decimal_sep'     => ',',
            'date_format'     => array(
                I18N::TIME_SHORT             => '%H:%M',
                I18N::TIME_LONG              => '%H:%M:%S',
                I18N::DATE_SHORT             => '%d/%m/%y',
                I18N::DATE_LONG              => '%d/%m/%Y',
                I18N::DATE_SHORT_TEXTUAL     => '%d %b %Y %a',
                I18N::DATE_LONG_TEXTUAL      => '%d %B %Y %A',
                I18N::DATETIME_SHORT         => '%d/%m/%Y %H:%M',
                I18N::DATETIME_LONG          => '%d/%m/%Y %H:%M',
                I18N::DATETIME_FULL          => '%d/%m/%Y %H:%M:%S',
                I18N::DATETIME_SHORT_TEXTUAL => '%d %b %Y %a %H:%M',
                I18N::DATETIME_LONG_TEXTUAL  => '%d %B %Y %A %H:%M',
                I18N::DATETIME_FULL_TEXTUAL  => '%d %B %Y %A %H:%M:%S'
            ),
            'days' => array(
                array('Pazar', 'Paz'),
                array('Pazartesi', 'Pzt'),
                array('Sali', 'Sal'),
                array('Çarsamba', 'Çar'),
                array('Persembe', 'Per'), 
                array('Cuma', 'Cum'),
                array('Cumartesi', 'Cmt')
            ),
            'monthes' => array(
                array('Ocak', 'Oca'),
                array('Subat', 'Sub'),
                array('Mart', 'Mar'),
                array('Nisan', 'Nis'),
                array('Mayis', 'May'), 
                array('Haziran', 'Haz'),
                array('Temmuz', 'Tem'),
                array('Agustos', 'Agu'),
                array('Eylül', 'Eyl'),
                array('Ekim', 'Eki'),
                array('Kasim', 'Kas'),
                array('Aralik', 'Ara')
            ),
            'html_select_date_format' => 'dmY',
            'duration_format' => array(
                'day'    => '%s g.',
                'hour'   => '%s s.',
                'minute' => '%s d.'
            ),
            // en turc on écrit: 100.000,45
            'extract_pattern' => array('.', ','),
            'extract_replacement' => array('', '.')
        ),
        // }}}
    );

    /**
     * Tableau des données de la locale courante.
     *
     * @var    array $data
     * @static
     * @access protected
     */
    protected static $data = array();

    /**
     * Tableau des messages d'erreurs utilisés par cette classe.
     *
     * @var    array $messages
     * @static
     * @access protected
     */
    protected static $messages = array(
        'unset'=>'You must call I18N::setLocale() first.',
        'unsupported'=>'Unsupported locale "%s", locale set to "%s" (default)'
    );

    // }}}
    // I18N::setLocale() {{{

    /**
     * Initialize gettext avec la locale passée en paramètre, si aucune locale
     * n'est passée, on prend la valeur de la constante LOCALE_DEFAULT.
     *
     * Pour des raisons de compatibilité, on ne se base pas sur la fonction C 
     * système setlocale() (hormis pour LC_MESSAGES).
     *
     * @static
     * @param  string $locale le code de la langue (ex: fr_FR)
     * @return void
     * @access public
     */
    public static function setLocale($locale=false) {
        // reset locale
        $result = setlocale(LC_MESSAGES, 'C');
        if (!$locale) {
            $locale = defined('LOCALE')?LOCALE:LOCALE_DEFAULT;
        }
        if (!isset(self::$locales[$locale])) {
            trigger_error(
                sprintf(self::$messages['unsupported'], $locale, LOCALE_DEFAULT),
                E_USER_WARNING
            );
            $locale = LOCALE_DEFAULT;
        }
        self::$data = self::$locales[$locale];
        // avec certaines versions de gettext (ex. 0.14.4) il faut ce putenv(), 
        // sinon ça marche mal...
        putenv('LC_MESSAGES=' . $locale);
        setlocale(LC_MESSAGES, $locale);
        // initialise gettext
        textdomain(DOMAIN);
        bindtextdomain(DOMAIN, LOCALE_DIR);
        bind_textdomain_codeset(DOMAIN, self::$data['encoding']);
    }

    // }}}
    // I18N::getLocaleName() {{{

    /**
     * Retourne le nom de la langue courante dans la langue (ex: Français).
     *
     * @static
     * @access public
     * @return string
     */
    public static function getLocaleName()
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        return self::$data['name'];
    }

    // }}}
    // I18N::getLocaleCode() {{{

    /**
     * Retourne le code de la langue courant (ex: fr_FR).
     *
     * Si short vaut true, c'est le code à 2 caractères (ex: fr) qui est
     * retourné.
     *
     * @static
     * @access public
     * @param  bool $short determine si on doit retourner le code long ou court
     * @return string
     */
    public static function getLocaleCode($short=false)
    {
        if (!isset(self::$data['code'])) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        if ($short) {
            return self::$data['shortcode'];
        }
        return self::$data['code'];
    }

    // }}}
    // I18N::getLocaleEncoding() {{{

    /**
     * Retourne l'encoding de la locale courante (ex: ISO-8859-15).
     *
     * @static
     * @access public
     * @return string
     */
    public static function getLocaleEncoding()
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        return self::$data['encoding'];
    }

    // }}}
    // I18N::getHTMLSelectDateFormat() {{{

    /**
     * Retourne le format de date à passer à la fonction php date() de la
     * locale courante pour les select HTML (de quickform et smarty notamment).
     *
     * @static
     * @access public
     * @return string
     */
    public static function getHTMLSelectDateFormat()
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        return self::$data['html_select_date_format'];
    }

    // }}}
    // I18N::getSupportedLocales() {{{

    /**
     * Retourne un tableau associatif des locales supportées par le framework.
     * 
     * Exemple de tableau retourné:
     * <code>
     * array(
     *     'fr_FR' => 'Français',
     *     'en_GB' => 'English',
     *     'de_DE' => 'Deutch'
     * );
     * </code>
     *
     * @static
     * @access public
     * @return array
     */
    public static function getSupportedLocales()
    {
        $return = array();
        foreach (self::$locales as $code=>$data) {
            $return[$code] = $data['name'];
        }
        return $return;
    }

    // }}}
    // I18N::formatDate() {{{

    /**
     * Formatte une date conformément aux usages dans la langue courante.
     *
     * Le premier paramètre doit être soit un timestamp, soit une date mysql. 
     * Et le second soit une des constantes de dates définies en en-tête de ce 
     * fichier, soit une chaîne de caractères conforme au paramètre format de 
     * la fonction native date() (ex: "d/m/Y") ou la fonction native
     * strftime() (ex: %d/%m/%Y).
     *
     * Examples:
     * <code>
     * // affiche lun 15 jan 2007
     * I18N::setLocale('fr_FR');
     * echo I18N::formatDate('2007-01-15 12:00:00', I18N::DATE_SHORT_TEXTUAL);
     *
     * // affiche mon 15 jan 2007 12:00 (la date du jour)
     * I18N::setLocale('en_GB');
     * echo I18N::formatDate(time(), I18N::DATETIME_SHORT_TEXTUAL);
     *
     * // affiche 01/2007
     * echo I18N::formatDate('2007-01-15 12:00:00', 'm/Y');
     * echo I18N::formatDate('2007-01-15 12:00:00', '%m/%Y'); // équivalent
     * </code>
     *
     * @static
     * @param  mixed int or string $date timestamp ou date (YYYY-MM-DD HH:MM:SS)
     * @param  mixed const or string $format une constante ou une chaine format
     * @return string la date formatée
     * @access public
     * @see    strftime
     * @see    date
     */
    public static function formatDate($date=false, $format=I18N::DATETIME_LONG)
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        if ($date == 0) {
            return '';
        } else if (!is_numeric($date)) {
            // on a pas un timestamp, il faut convertir la date
            if(false === strpos($date, ' ')) {
                // ajoute les heures si nécéssaire
                $date .= ' 00:00:00';
            }
            $ts = DateTimeTools::mysqlDateToTimeStamp($date);
            if (!$ts) return '';
        } else {
            // on a un timestamp
            $ts = (int)$date;
        }
        if (is_int($format)) {
            if (!isset(self::$data['date_format'][$format])) {
                // format inexistant, on issue un warning
                trigger_error(self::$messages['unknown_date_format'],
                    E_USER_WARNING);
                $format = I18N::DATE_SHORT;
            }
            $fmt  = self::$data['date_format'][$format];
            $func = 'strftime';
        } else {
            $fmt = $format;
            $func = (strpos($fmt, '%') !== false)?'strftime':'date';
        }
        // assigne les noms de jours/mois textuels
        if (in_array($format, array(I18N::DATE_LONG_TEXTUAL, I18N::DATETIME_LONG_TEXTUAL,
                                    I18N::DATETIME_FULL_TEXTUAL))
          || strpos($format, '%A') !== false || strpos($format, '%B') !== false)
        {
            $day = self::$data['days'][(int)date('w', $ts)];
            $fmt = str_replace('%A', $day[0], $fmt);
            $month = self::$data['monthes'][(int)date('m', $ts)-1];
            $fmt   = str_replace('%B', $month[0], $fmt);
        }
        // assigne les noms de jours/mois textuels abrégés
        if (in_array($format, array(I18N::DATE_SHORT_TEXTUAL, I18N::DATETIME_SHORT_TEXTUAL))
          || strpos($format, '%a') !== false || strpos($format, '%b') !== false)
        {
            $day = self::$data['days'][(int)date('w', $ts)];
            $fmt = str_replace('%a', $day[1], $fmt);
            $month = self::$data['monthes'][(int)date('m', $ts)-1];
            $fmt   = str_replace('%b', $month[1], $fmt);
        }
        return $func($fmt, $ts);
    }

    // }}}
    // I18N::formatDuration() {{{

    /**
     * Formatte une durée conformément aux usages dans la langue courante.
     *
     * Le paramètre doit être soit une durée en secondes (int et string sont
     * acceptés) soit une chaine de type "HH:MM" ou "HH:MM:SS" (time mysql). 
     *
     * Examples:
     * <code>
     * echo I18N::formatDuration(9345);       // affiche: 2 h. 36 min.
     * echo I18N::formatDuration("30000");    // affiche: 8 h. 20 min.
     * echo I18N::formatDuration("12:34");    // affiche: 12 h. 34 min.
     * echo I18N::formatDuration("12:34:10"); // affiche: 12 h. 35 min.
     * </code>
     *
     * @static
     * @param  mixed int or string $duration la durée
     * @return string
     * @access public
     */
    public static function formatDuration($duration)
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        if (!is_int($duration)) {
            if (preg_match('/[0-9]{2}:[0-9]{2}(:[0-9]{2})?/', $duration)) {
                $duration = DateTimeTools::mysqlDateToTimeStamp($duration);
            } else {
                $duration = (int)$duration;
            }
        }
        $sign     = ($duration < 0)?'-':'';
        $duration = abs($duration / 60);
        $days     = floor($duration / (24 * 60));
        $hours    = floor(($duration - 24 * 60 * $days) / 60);
        $mins     = ceil($duration - 24 * 60 * $days - 60 * $hours);
        $result   = '';
        if ($days != 0) {
            $result .= sprintf(self::$data['duration_format']['day'], $days);
        } 
        if ($hours != 0) {
            if (!empty($result)) {
                $result .= ' ';
            } 
            $result .= sprintf(self::$data['duration_format']['hour'], $hours);
        } 
        if ($mins != 0) {
            if (!empty($result)) {
                $result .= ' ';
            } 
            $result .= sprintf(self::$data['duration_format']['minute'], $mins);
        } 
        if (empty($result)) {
            $result = sprintf(self::$data['duration_format']['minute'], 0);
        } 
        return $sign . $result;
    }

    // }}}
    // I18N::formatNumber() {{{

    /**
     * Formatte un nombre conformément aux usages dans la langue courante.
     *
     * Le premier paramètre doit être soit un entier, soit un nombre décimal,
     * soit une chaîne représentant le nombre. 
     * Le deuxième paramètre permet de spécifier le nombre de décimales à
     * afficher, pour un entier ce sera 0 * $dec_num à moins que le paramètre 
     * skip_zeros vaille true, dans ce cas, si la partie décimale vaut zéro, 
     * seule la partie de gauche est retournée.
     *
     * Examples:
     * <code>
     * I18N::setLocale('fr_FR');
     * echo I18N::formatNumber("12.346");          // affiche: 12,35
     * echo I18N::formatNumber("12.346", 3);       // affiche: 12,346
     *
     * I18N::setLocale('en_GB');
     * echo I18N::formatNumber("12.300", 3, true); // affiche: 12.3
     * echo I18N::formatNumber("NotANumber");      // affiche: 0.00
     * echo I18N::formatNumber("NotANumber", 0);   // affiche: 0
     * </code>
     *
     * @static
     * @param  mixed int, double or string $number le nombre à formatter
     * @param  int $dec_num le nombre de décimales
     * @param  boolean $skip_zeros "effacer" les zeros en fin de chaine
     * @param  boolean $strict formate en tenant compte du separateur des milliers
     * @return string
     * @access public
     */
    public static function formatNumber($number, $dec_num=2, $skip_zeros=false, $strict=false)
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        if (strpos($number, ',') === true) {
            // XXX pas I18N compliant !
            $number == str_replace(',', '.', $number);
        }
        /* Mis en commentaire pour resolution sans trop d'impacts du bug 
           https://admin.ateor.com/mantis/view.php?id=3756
        $ret = number_format($number, $dec_num, self::$data['decimal_sep'], 
            self::$data['thousand_sep']);
        */
        $thousandSep = ($strict)?self::$data['thousand_sep']:'';
        $ret = number_format($number, $dec_num, self::$data['decimal_sep'],
                 $thousandSep);
            
        if ($skip_zeros) {
            // on enlève les zéros de trop
            $tokens = explode(self::$data['decimal_sep'], $ret);
            if (isset($tokens[1])) {
                // si les decimales sont a zero on retourne juste la partie
                if ((int)$tokens[1] == 0) {
                    return $tokens[0];
                }
                // on enleve les 0 de la fin des decimales
                while ($tokens[1]{strlen($tokens[1])-1} == '0') {
                    $tokens[1] = substr($tokens[1], 0, -1);
                }
                return $tokens[0] . self::$data['decimal_sep'] . $tokens[1];
            }
            return $tokens[0];
        }
        return $ret;
    }

    // }}}
    // I18N::formatCurrency() {{{

    /**
     * Formatte une devise conformément aux usages dans la langue courante.
     *
     * Le premier paramètre est la chaîne représentant la devise.
     * Le deuxième paramètre doit être soit un entier, soit un nombre décimal,
     * soit une chaîne représentant le nombre. 
     * Le troisième paramètre permet de spécifier le nombre de décimales à
     * afficher, pour un entier ce sera 0 * $dec_num à moins que le paramètre 
     * skip_zeros vaille true, dans ce cas, si la partie décimale vaut zéro, 
     * seule la partie de gauche est retournée.
     *
     * Examples:
     * <code>
     * I18N::setLocale('fr_FR');
     * echo I18N::formatCurrency("&euro;", "12.346");  // affiche: 12,35 &euro;
     *
     * I18N::setLocale('en_GB');
     * echo I18N::formatNumber('£', '3412.300', 3, true); // affiche: £3,412.3
     * </code>
     *
     * @static
     * @param  string $currency le symbole de la devise
     * @param  mixed int, double or string $number le nombre (montant)
     * @param  int $dec_num le nombre de décimales
     * @param  boolean $skip_zeros "effacer" les zeros en fin de chaine
     * @param  boolean $strict formate en tenant compte du separateur des milliers
     * @return string
     * @access public
     */
    public static function formatCurrency($currency, $number, $dec_num=2,
        $skip_zeros=false, $strict=false) 
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        $result = I18N::formatNumber($number, $dec_num, $skip_zeros, $strict);
        $tpl = str_replace('{$CURRENCY}', $currency, 
            self::$data['currency_format']);
        return sprintf($tpl, $result);
    }

    // }}}
    // I18N::formatPercent() {{{

    /**
     * Formatte un pourcentage conformément aux usages dans la langue courante.
     *
     * Le premier paramètre doit être soit un entier, soit un nombre décimal,
     * soit une chaîne représentant le nombre.
     * Le deuxième paramètre permet de spécifier le nombre de décimales à
     * afficher, pour un entier ce sera 0 * $dec_num à moins que le paramètre 
     * skip_zeros vaille true, dans ce cas, si la partie décimale vaut zéro, 
     * seule la partie de gauche est retournée.
     *
     * Examples:
     * <code>
     * I18N::setLocale('fr_FR');
     * echo I18N::formatPercent("12.346");  // affiche: 12,35 %;
     *
     * I18N::setLocale('en_GB');
     * echo I18N::formatPercent('3412.300', 3, true); // affiche: % 3,412.3
     * </code>
     *
     * @static
     * @param  mixed int, double or string $number le nombre (montant)
     * @param  int $dec_num le nombre de décimales
     * @param  boolean $skip_zeros "effacer" les zeros en fin de chaine
     * @param  boolean $strict formate en tenant compte du separateur des milliers
     * @return string
     * @access public
     */
    public static function formatPercent($number, $dec_num=2, $skip_zeros=false, $strict=false) 
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        $number = I18N::formatNumber($number, $dec_num, $skip_zeros, $strict);
        return sprintf(self::$data['percent_format'], $number);
    }

    // }}}
    // I18N::validateNumber() {{{

    /**
     * Valide un nombre conformément aux usages dans la langue courante et 
     * retourne true si le nombre est valide ou false sinon.
     *
     * Examples:
     * <code>
     * I18N::setLocale('fr_FR');
     * echo I18N::validateNumber("12,346"); // true
     * echo I18N::validateNumber("12.346"); // true
     * echo I18N::validateNumber("10,012.346"); // false
     * </code>
     *
     * @static
     * @param  mixed int, double or string $number le nombre à valider
     * @param  boolean $strict valide en tenant compte du separateur des milliers
     * @return boolean
     * @access public
     */
    public static function validateNumber($number, $strict=false)
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        $tsep = self::$data['thousand_sep'] == '.' ? 
            '\.':self::$data['thousand_sep'];
        $dsep = self::$data['decimal_sep'] == '.' ? 
            '\.':'(\.|'.self::$data['decimal_sep'].')';
        $regexp = ($strict)?"/^\d+(%s\d{3})*(%s\d+)?$/":"/^\d+(%s\d+)?$/";
        $rx = sprintf($regexp, $tsep, $dsep);
        return preg_match($rx, trim($number)) ? true : false;
    }

    // }}}
    // I18N::getMonthesArray() {{{

    /**
     * Retourne le tableau des mois avec en clé l'index et en valeur le nom du 
     * mois abrégé ou pas (selon le paramètre $abbrev) dans la locale courante.
     *
     * Example:
     * <code>
     * I18N::setLocale('fr_FR');
     * print_r(I18N::getMonthesArray());
     * // affichera:
     * // Array (
     * //     1=>'Janvier',
     * //     2=>'Février',
     * //     etc...,
     * // ) 
     * </code>
     *
     * @static
     * @param  boolean $abbrev si true récupère les noms abrégés.
     * @return array
     * @access public
     */
    public static function getMonthesArray($abbrev=false) 
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        $index = $abbrev?1:0;
        $return  = array();
        foreach (self::$data['monthes'] as $key=>$val) {
            $return[$key+1] = $val[$index];
        }
        return $return;
    }

    // }}}
    // I18N::getDaysArray() {{{

    /**
     * Retourne le tableau des jours avec en clé l'index et en valeur le nom du 
     * jour abrégé ou pas (selon le paramètre $abbrev) dans la locale courante.
     *
     * Example:
     * <code>
     * I18N::setLocale('fr_FR');
     * print_r(I18N::getDaysArray());
     * // affichera:
     * // Array (
     * //     1=>'Lundi',
     * //     2=>'Mardi',
     * //     etc...,
     * // ) 
     * </code>
     *
     * @static
     * @param  boolean $abbrev si true récupère les noms abrégés.
     * @return array
     * @access public
     */
    public static function getDaysArray($abbrev=false) 
    {
        if (empty(self::$data)) {
            trigger_error(self::$messages['unset'], E_USER_ERROR);
        }
        $index = $abbrev?1:0;
        $return  = array();
        foreach (self::$data['days'] as $key=>$val) {
            $return[$key] = $val[$index];
        }
        return $return;
    }

    // }}}
    // I18N::extractNumber() {{{
    
    /**
     * extractNumber
     *
     * Retourne un nombre au format standard 1234.56 à partir d'un nombre dans 
     * la locale courante. 
     * 
     * @param string $value Nombre formatté dans la locale courante
     * @static
     * @access public
     * @return float
     */
    public static function extractNumber($value) {
        /* Mis en commentaire pour resolution sans trop d'impacts du bug 
           https://admin.ateor.com/mantis/view.php?id=3756
        $code = self::$locales[self::getLocaleCode()];
        $return = str_replace(
            $code['extract_pattern'],
            $code['extract_replacement'],
            $value
        );
        return $return;
        */
        return (float)str_replace(array(' ', ','), array('', '.'), $value);
    }    
    
    // }}}
}

?>