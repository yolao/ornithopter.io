<?php
/**
 * Ornithopter.io
 * ------------------------------------------------
 * A minimalist, high-speed open source PHP 5.6+ framework.
 *
 * @author      Corey Olson
 * @copyright   Copyright (c) 2011 - 2016 Corey Olson
 * @license     http://opensource.org/licenses/MIT (MIT License)
 *
 * @link        https://github.com/olscore/ornithopter.io
 *
 * // ########################################################################################
 *
 * A helper for contextual time. Class allows chaining on settings for fast
 * configuration; io::helper('time')->display('tiny')->format('strtoupper');
 *
 * @method io::helper('time')->units();
 * @method io::helper('time')->relative();
 * @method io::helper('time')->calc('round');
 * @method io::helper('time')->calc('floor');
 * @method io::helper('time')->calc('ceil');
 * @method io::helper('time')->display('normal')
 * @method io::helper('time')->display('short')
 * @method io::helper('time')->display('tiny')
 * @method io::helper('time')->prefix('past', 'from');
 * @method io::helper('time')->prefix('future', 'in');
 * @method io::helper('time')->prefix('past', true);
 * @method io::helper('time')->prefix('past', false);
 * @method io::helper('time')->postfix('past', 'ago');
 * @method io::helper('time')->postfix('future', 'remaining');
 * @method io::helper('time')->postfix('past', true);
 * @method io::helper('time')->postfix('past', true);
 * @method io::helper('time')->format('none');
 * @method io::helper('time')->format('ucfirst');
 * @method io::helper('time')->format('ucwords');
 * @method io::helper('time')->format('strtoupper');
 * @method io::helper('time')->now();
 * @method io::helper('time')->sql();
 * @method io::helper('time')->unix();
 * @method io::helper('time')->friendly();
 * @method io::helper('time')->american();
 * @method io::helper('time')->days_in();
 * @method io::helper('time')->date_range();
 */
namespace ornithopter\helpers;

class time
{
    /**
     * This is a singleton class.
     *
     * @var object
     */
    private static $instance;

    /**
     * Internal settings.
     *
     * @var array
     */
    private static $settings = array();

    /**
     * Ornithopter.io looks for an instance() method when loading a library.
     *
     * @return object
     */
    public static function instance()
    {
        // Check for an instance
        if (!isset(self::$instance)) {

            // Create a new instance
            self::$instance = new self();
        }

        // Return existing instance
        return self::$instance;
    }

    /**
     * Initialize time helper class.
     *
     * @return object
     */
    public function __construct()
    {
        // Default settings
        self::$settings = array(

            // Contextual [1] X units ago or [2] day, month day and date
            'context' => ['units' => 1, 'relative' => 0],

            // Calculation [1] round, [2] floor or [3] ceil
            'calc' => ['round' => 1, 'floor' => 0, 'ceil' => 0],

            // Nouns [1] (singular / plural), [2] shorthand, [3] one-character
            'display' => ['normal' => 1, 'short' => 0, 'tiny' => 0],

            // Output [1] prefix, [2] postfix, [3] show prefix, [4] show postfix
            'past' => ['from ', ' ago', 0, 1],
            'future' => ['in ', ' remaining', 0, 1],

            // Output formatting [1] none, [2] ucfirst, [3] ucwords, [4] strtoupper
            'format' => ['normal' => 1, 'ucfirst' => 0, 'ucwords' => 0, 'strtoupper' => 0],
        );

        // Register shortcut aliases using h::method();
        \io::alias(__CLASS__, get_class_methods(__CLASS__));
    }

    /**
     * Creates a shortcut for io::time().
     *
     * @return object
     */
    public static function time()
    {
        // Shortcut for io::time()
        return self::$instance;
    }

    /**
     * Convenience wrapper for self::context().
     *
     * @param int
     *
     * @return string
     */
    public static function xago($time = 0)
    {
        // Wrapper function
        return self::context($time);
    }

    /**
     * Return contextual time.
     *
     * @param int
     *
     * @return string
     */
    public static function context($time = 0)
    {
        /*
         * Unit timeframe nouns and lengths in a multi-dimensional array
         * with [1] singular, [2] plural, [3] shorthand, [4] one-char forms.
         */
        self::$settings['timeframes'] = array(
            [['second', 'seconds', 'sec', 's'], 60],
            [['minute', 'minutes', 'min', 'm'], 60],
            [['hour', 'hours', 'hr', 'h'], 24],
            [['day', 'days', 'day', 'd'], 7],
            [['week', 'weeks', 'wk', 'w'], 4.348214286],
            [['month', 'months', 'mo', 'm'], 12],
            [['year', 'years', 'yr', 'y'], 10],
            [['decade', 'decades', '', 'X'], 10],
            [['century', 'centuries', '', 'C'], 10],
            [['millenium', 'millenia', '', 'M'], PHP_INT_MAX],
        );

        // Current
        if (!$time) {
            return 'now';
        }

        // abs() for past / future
        $units = abs(time() - $time);

        // Iterate through the unit timeframes and find matches
        foreach (self::$settings['timeframes'] as list($noun, $timeframe)) {

            // Check this timeframe
            if ($units < $timeframe) {

                // Match: Get string from self::display()
                return self::display($units, $noun, $time);
            } else {
                // Try next timeframe by Flooring
                if (self::$settings['calc']['floor']) {
                    $units = floor($units / $timeframe);
                }

                // Try next timeframe by Flooring
                elseif (self::$settings['calc']['ceil']) {
                    $units = ceil($units / $timeframe);
                } else {
                    // Try next timeframe by Rounding (Default)
                    $units = round($units / $timeframe);
                }
            }
        }
    }

    /**
     * Return contextual time.
     *
     * @param int
     *
     * @return string
     */
    private static function display($units, $noun, $time)
    {
        // Get the past or future tense
        $tense = ($time <= time()) ? 'past' : 'future';

        // Check if display style is shorthand; only available for <10 years
        if (self::$settings['display']['short'] and abs($time - time()) <= 315576000) {

            // Shorthand for everything less than 10 years
            $noun = ' '.$noun[2].(($units == 1) ? '' : 's');
        } elseif (self::$settings['display']['tiny']) {

            // Tiny one-character display nouns
            $noun = $noun[3];
        } else {
            // Reverting to normal display; singular and plural
            $noun = ' '.(($units == 1) ? $noun[0] : $noun[1]);
        }

        // Initialize the string
        $string = '';

        // Detect string pre-fixing
        if (self::$settings[$tense][2]) {

            // Add the string pre-fix
            $string .= self::$settings[$tense][0];
        }

        // Add the noun to the string
        $string .= $units.$noun;

        // Detect string pre-fixing
        if (self::$settings[$tense][3]) {

            // Add the string post-fix
            $string .= self::$settings[$tense][1];
        }

        // Format the string using ucfirst()
        if (self::$settings['format']['ucfirst']) {
            return ucfirst($string);
        }

        // Format the string using ucwords()
        elseif (self::$settings['format']['ucwords']) {
            return ucwords($string);
        }

        // Format the string using strtoupper()
        elseif (self::$settings['format']['strtoupper']) {
            return strtoupper($string);
        }

        // Return the concatenated string
        return $string;
    }

    /**
     * Changing the prefix on past or future tense. Allows changing adjectives
     * displayed before the units and also toggling or controlling visibility.
     *
     * @param string
     * @param mixed
     *
     * @return object
     */
    public static function prefix($tense, $mixed = null)
    {
        // Shortcut toggle
        if (is_null($mixed)) {

            // A boolean trick to flip the boolean
            self::$settings[$tense][2] = !self::$settings[$tense][2];
        }

        // Detecting a boolean
        elseif (is_bool($mixed)) {

            // Manually set the visibility of this setting
            self::$settings[$tense][2] = $mixed;
        }

        // Detecting a string
        elseif (is_string($mixed)) {

            // Update the prefix for this tense
            self::$settings[$tense][0] = trim($mixed).' ';
        }

        // Allow chaining
        return self::$instance;
    }

    /**
     * Changing the postfix on past or future tense. Allows changing adjectives
     * displayed before the units and also toggling or controlling visibility.
     *
     * @param string
     * @param mixed
     *
     * @return object
     */
    public static function postfix($tense, $mixed = null)
    {
        // Shortcut toggle
        if (is_null($mixed)) {

            // A boolean trick to flip the boolean
            self::$settings[$tense][3] = !self::$settings[$tense][3];
        }

        // Detecting a boolean
        elseif (is_bool($mixed)) {

            // Manually set the visibility of this setting
            self::$settings[$tense][3] = $mixed;
        }

        // Detecting a string
        elseif (is_string($mixed)) {

            // Update the postfix for this tense
            self::$settings[$tense][1] = ' '.trim($mixed);
        }

        // Allow chaining
        return self::$instance;
    }

    /**
     * Get the current time.
     *
     * @return int
     */
    public static function now()
    {
        // Current UNIX time
        return time();
    }

    /**
     * Get an DATETIME string for SQL.
     *
     * @param mixed
     *
     * @return mixed
     */
    public static function sql($time = false)
    {
        // Check for user provided time
        if (!$time) {

            // Use current time
            $time = time();
        }

        // Check for strings
        if (is_string($time)) {

            // Attempt a conversion to UNIX
            $time = strtotime($time);
        }

        // Check for valid time
        if (!$time) {

            // Improper input
            return false;
        }

        // Convert time to SQL DATETIME
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * Wrapper function for strtotime with failure checking.
     *
     * @param mixed
     *
     * @return mixed
     */
    public static function unix($time = false)
    {
        // Check for user provided time
        if (!$time) {

            // Use current time
            $time = time();
        }

        // Check for strings
        if (is_string($time)) {

            // Attempt conversion to UNIX time
            $time = strtotime($time);
        }

        // Check for valid time
        if (!$time) {

            // Improper input
            return false;
        }

        // Seems OK
        return $time;
    }

    /**
     * Return a 12-hour AM/PM human timestamp.
     *
     * @param mixed
     *
     * @return mixed
     */
    public static function friendly($time = false)
    {
        // Check for user provided time
        if (!$time) {

            // Use current time
            $time = time();
        }

        // Check for strings
        if (is_string($time)) {

            // Attempt conversion to UNIX time
            $time = strtotime($time);
        }

        // Check for valid time
        if (!$time) {

            // Improper input
            return false;
        }

        // Create a human friendly time
        return date('Y-m-d H:i:s A', $time);
    }

    /**
     * Return an (American) friendly timestamp #murica.
     *
     * @param mixed
     *
     * @return mixed
     */
    public static function american($time = false)
    {
        // Check for user provided time
        if (!$time) {

            // Use current time
            $time = time();
        }

        // Check for strings
        if (is_string($time)) {

            // Attempt conversion to UNIX time
            $time = strtotime($time);
        }

        // Check for valid time
        if (!$time) {

            // Improper input
            return false;
        }

        // Create an American friendly time
        return date('l, F j, Y g:i A', $time);
    }

    /**
     * Wrapper for cal_days_in_month(CAL_GREGORIAN, $month, $year).
     *
     * @param mixed
     * @param mixed
     *
     * @return mixed
     */
    public static function days_in($month, $year = false)
    {
        // Valid months
        $mArr = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

        // Year selection
        if (!$year) {

            // Use the current year
            $year = date('Y');
        }

        // Check for strings
        if (is_string($month)) {

            // Check validity of string months
            if (in_array(strtolower(substr($month, 0, 3)), $mArr)) {

                // Attempt conversion to UNIX time
                $month = (int) date('n', strtotime($month));
            } else {
                // Unexpected input
                return false;
            }
        }

        // Check for valid time
        if (!$month) {

            // Improper input
            return false;
        }

        // Number of days in the month for that year
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    /**
     * Determine if a year is a leap year or not.
     *
     * @return int
     * @return bool
     */
    public function leap_year($year)
    {
        // Ternary magic for detecting leap years in PHP
        return (($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0));
    }

    /**
     * Creates a concise way to manage the class settings since most of the
     * settings are held in a multi-dimensional array. Reduces code size.
     *
     * ----------------------------------------------------------------------------------------
     *
     * Method aliases and function wrappers for coders who like to use alternative
     * names for these methods. Slight performance impact when using method aliases.
     *
     * @param string
     * @param mixed
     *
     * @return object
     */
    public function __call($category, $args = array())
    {
        // Checks for a valid setting call
        if (isset(self::$settings[$category][$args[0]])) {
            // Iterate through [Category] [Named] Settings
            foreach (self::$settings[$category] as $setting => $flag) {

                // Sets the setting to false or true based on matching
                self::$settings[$category][$setting] = ($setting == $args[0]);
            }

            // Allow chaining
            return self::$instance;
        }

        // Check for aliases
        $aliases = array(
            'method' => ['alias'],
        );

        // Iterate through methods
        foreach ($aliases as $method => $list) {

            // Check called against accepted alias list
            if (in_array($called, $list)) {

                // Dynamic method (alias) call with arbitrary arguments
                return call_user_func_array(array(__CLASS__, $method), $args);
            }
        }

        // No alias found
        return false;
    }
}
