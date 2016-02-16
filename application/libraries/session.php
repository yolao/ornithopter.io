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
 * A session library.
 *
 * @method io::library('session')->session_id();
 * @method io::library('session')->regenerate();
 * @method io::library('session')->get();
 * @method io::library('session')->set();
 * @method io::library('session')->id();
 * @method io::library('session')->flashdata();
 * @method io::library('session')->isset_flashdata();
 * @method io::library('session')->set_flashdata();
 * @method io::library('session')->keep_flashdata();
 * @method io::library('session')->clean_flashdata();
 */
namespace ornithopter\libraries;

class session
{
    /**
     * This is a singleton class.
     *
     * @var object
     */
    private static $instance;

    /**
     * Session identifier.
     *
     * @var string
     */
    private static $session;

    /**
     * Number of seconds to keep users logged in.
     *
     * @var int
     */
    private static $session_length = 3600;

    /**
     * Number of seconds to renew session identifier.
     *
     * @var int
     */
    private static $session_renew = 300;

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
     * Setup the session environment, check session exists, runs security.
     */
    public function __construct()
    {
        // This sets the cookie name
        session_name('app_id');

        // Check for a valid session cookie
        if (!isset($_COOKIE['app_id']) or !isset($_COOKIE['app_id'][63])) {

            // Generate a new session id
            session_id(self::session_id(true));
        } else {
            // Use the user provided session
            self::$session = $_COOKIE['app_id'];
        }

        // Starts the session
        session_start();

        // Security measures
        self::security();

        // Flashdata cleanup
        self::clean_flashdata();

        // User tracking
        self::tracking();

        // Register shortcut aliases using io::method();
        \io::alias(__CLASS__, get_class_methods(__CLASS__));
    }

    /**
     * Creates a shortcut for io::session().
     *
     * @return object
     */
    public static function session()
    {
        // Shortcut for io::()
        return self::$instance;
    }

    /**
     * Getter and setter method for session identifiers.
     *
     * @return string
     */
    public static function session_id($new = false)
    {
        // Get the current session identifier
        if (!$new) {

            // Session Identifier
            return self::$session;
        }

        // Create a new session identifier
        $id = sha1($_SERVER['REMOTE_ADDR']);

        do {
            // Add some randomness
            $id .= mt_rand(0, mt_getrandmax());
        } while (strlen($id) < 64);

        // Should be enough entropy to prevent collisions
        return self::$session = hash_hmac('sha256', uniqid($id, true), $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Implements some safety measures for sessions.
     */
    private static function security()
    {
        // This is a new sessions
        if (!isset($_SESSION['last_active'])) {

            // Timestamps
            $_SESSION = array(
                'last_active' => time(),
                'last_regeneration' => time(),
            );
        }

        // Logout old sessions
        if ($_SESSION['last_active'] <= (time() - self::$session_length)) {
            // Destroy the data
            unset($_SESSION);

            // Sends back an invalid cookie
            setcookie(session_name(), '', time() - self::$session_length - 3600);

            // Ends the session
            session_destroy();
        } else {
            // Check for regeneration
            if ($_SESSION['last_regeneration'] <= (time() - self::$session_renew)) {
                // Remove time indentifiers
                unset($_SESSION['last_active']);
                unset($_SESSION['last_regeneration']);

                // Recreate session
                self::regenerate();
            }

            // Update last active time
            $_SESSION['last_active'] = time();
        }
    }

    /**
     * Regenerate the session key.
     */
    public static function regenerate()
    {
        // Prevent changes
        session_write_close();

        // Store the data session
        $temp = $_SESSION;

        // Destroy this session
        unset($_SESSION);

        // Generate a new session id
        session_id(self::session_id(true));

        // Open the new session
        session_start();

        // Restore session data
        $_SESSION = $temp;

        // Add session security
        self::security();
    }

    /**
     * Get: Retrieve Session Data.
     *
     * @param string
     *
     * @return string
     */
    public static function get($var)
    {
        // Check that this exists
        if (!isset($_SESSION[$var])) {

            // Does not exist
            return false;
        }

        return $_SESSION[$var];
    }

    /**
     * Set: Store Session Data.
     *
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public static function set($var, $val)
    {
        return $_SESSION[$var] = $val;
    }

    /**
     * Get the Anonymous Session ID.
     *
     * @param bool
     *
     * @return string
     */
    public static function id($truncate = false)
    {
        // Check that $_COOKIE exists
        if (!isset($_COOKIE['app_id'])) {

            // Create a new session
            return self::session_id(true);
        }

        // Trunacte session ID
        if ($truncate !== false) {

            // Get a truncated version of the session ID
            return substr($_COOKIE['app_id'], 0, $truncate);
        }

        // Return session identifier
        return $_COOKIE['app_id'];
    }

    /**
     * Get the visitor's landing page.
     *
     * @return string
     */
    public static function landing()
    {
        // Get pages visited
        $pages = self::get('pages');

        // Page array exists
        if (!is_array($pages)) {

            // No landing page available
            return false;
        }

        // Return visitor's landing page
        return end($pages)['url'];
    }

    /**
     * Get the visitor's previous page.
     *
     * @return string
     */
    public static function back()
    {
        // Get pages visited
        $pages = self::get('pages');

        // Page array exists
        if (!is_array($pages)) {

            // No landing page available
            return false;
        }

        // Move cursor to end of pages array
        end($pages);

        // Return visitor's previous page
        return prev($pages)['url'];
    }

    /**
     * Return one-time use variable.
     *
     * @return mixed
     */
    public static function flashdata($var)
    {
        // Data lasts for one (default) request
        return $_SESSION['_flashdata'][$var]['value'];
    }

    /**
     * Checks if flashdata isset.
     *
     * @return mixed
     */
    public static function isset_flashdata($var)
    {
        // Data lasts for one (default) request
        return isset($_SESSION['_flashdata'][$var]);
    }

    /**
     * One-time use variables.
     *
     * @param 	string
     * @param 	mixed
     */
    public static function set_flashdata($var, $value, $persist = 1)
    {
        // Sets the flash data
        $_SESSION['_flashdata'][$var] = array(
            'value' => $value,
            'persist' => $persist + (($persist != 1) ? time() : 0),
            'time' => $persist != 1,
        );
    }

    /**
     * Persist the flashdata 1 more request, or X many seconds.
     *
     * @param 	string
     * @param 	int
     */
    public static function keep_flashdata($var = false, $persist = 1)
    {
        // Exit if there's no _flashdata
        if (!isset($_SESSION['_flashdata'])) {
            return false;
        }

        // Convenience
        $_flashdata = &$_SESSION['_flashdata'];

        // Keep all flashdata
        if (!$var) {

            // Iterate through flashdata
            foreach ($_flashdata as $var => $data) {

                // Request based flashdata
                if (!$_flashdata[$var]['time']) {

                    // Update request-based flashdata
                    $_flashdata[$var]['persist'] = $persist;
                }

                // Time based flashdata
                elseif ($persist > 1) {

                    // Update time-based flashdata
                    $_flashdata[$var]['persist'] = time() + $persist;
                } else {
                    // Keeps a single item
            $_flashdata[$var]['persist'] = $persist + (($persist != 1) ? time() : 0);
                }
            }
        }
    }

    /**
     * Flashdata cleanup.
     */
    public static function clean_flashdata()
    {
        // Exit if there's no _flashdata
        if (!isset($_SESSION['_flashdata'])) {
            return false;
        }

        // Convenience
        $_flashdata = &$_SESSION['_flashdata'];

        // Iterate the flashdata
        foreach ($_flashdata as $var => $data) {

            // Remove expired time-based flashdata
            if ($_flashdata[$var]['time'] and $_flashdata[$var]['persist'] < time()) {

                // Goodbye flashdata
                unset($_flashdata[$var]);
            }

            // Remove expired request-based flashdata
            elseif ($_flashdata[$var]['persist']-- <= 0) {

                // Goodbye flashdata
                unset($_flashdata[$var]);
            }
        }
    }

    /**
     * Simple user tracking.
     */
    private static function tracking()
    {
        // Get pages visited
        $pages = self::get('pages');

        // Page array exists
        if (!is_array($pages)) {

            // Create array
            $pages = array();
        }

        // Detect https by SERVER_PORT (80: http)
        $https = ($_SERVER['SERVER_PORT'] == 80) ? '' : 's';

        // Push non-POST data to page history
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {

            // Add page to array
            $pages[] = array(
                'time' => time(),
                'url' => 'http'.$https.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
                'page' => $_SERVER['REQUEST_URI'],
            );
        }

        // Generate statistics
        $stats = array(
            'time_hello' => $hi = $pages[0]['time'],
            'time_goodbye' => $bye = $pages[count($pages) - 1]['time'],
            'time_onsite' => ($bye - $hi),
            'time_friendly' => round(($bye - $hi) / 60, 1).' minutes',
            'pages_visited' => count($pages),
        );

        // Record the session data
        self::set('pages', $pages);
        self::set('stats', $stats);
    }
}
