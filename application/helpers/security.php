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
 * A security class with helper security functions
 *
 * @method io::helpers('security')->bcrypt( $password );
 * @method io::helpers('security')->password( $password );
 * @method io::helpers('security')->verify( $password, $hash );
 * @method io::helpers('security')->css();
 * @method io::helpers('security')->filename();
 * @method io::helpers('security')->image_tags();
 * @method io::helpers('security')->clean_input();
 * @method io::helpers('security')->csrf_field();
 * @method io::helpers('security')->csrf_token();
 * @method io::helpers('security')->tmp_username( [$prefix] [, $length] );
 * @method io::helpers('security')->tmp_password( $length );
 */
namespace ornithopter\helpers;

class security
{
    /**
     * This is a singleton class.
     *
     * @var object
     */
    private static $instance;

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
     * Initialize security helper class.
     *
     * @return object
     */
    public function __construct()
    {
        // Register shortcut aliases using h::method();
        \io::alias(__CLASS__, get_class_methods(__CLASS__));
    }

    /**
     * Creates a shortcut for io::security().
     *
     * @return object
     */
    public static function security()
    {
        // Shortcut for io::security()
        return self::$instance;
    }

    /**
     * Default security difficulty.
     *
     * @var int
     */
    private static $cost = 12;

    /**
     * Hash a password using bcrypt (default as of PHP 5.5).
     *
     * @return string
     */
    public static function bcrypt($pwd)
    {
        return password_hash($pwd, PASSWORD_BCRYPT, ['cost' => self::$cost]);
    }

    /**
     * Hash a password using the default PHP hashing algo.
     *
     * @return string
     */
    public static function password($pwd)
    {
        return password_hash($pwd, PASSWORD_DEFAULT, ['cost' => self::$cost]);
    }

    /**
     * Verify a password against a hash.
     *
     * @return boolean
     */
    public static function verify($pwd, $hash)
    {
        // Verifies password against a stored hash
        return password_verify($pwd, $hash);
    }

    /**
     * @return
     */
    public static function xss()
    {
    }

    /**
     * Sanitize a string for use as a filename.
     *
     * @return
     */
    public static function filename()
    {
    }

    /**
     * Check images for bad behavior.
     *
     * @return
     */
    public static function image_tags()
    {
    }

    /**
     * Cleans user input from potentially dangerous items.
     *
     * @return
     */
    public static function clean_input()
    {
    }

    /**
     * Generate a CSRF Name.
     *
     * @return
     */
    public static function csrf_field()
    {
    }

    /**
     * Generate a CSRF Token.
     *
     * @return
     */
    public static function csrf_token()
    {
    }

    /**
     * Generate a temporary username.
     *
     * @return string
     */
    public static function tmp_username($prefix = 'user', $length = 5)
    {
        // Temporary username
        $username = $prefix;

        // Randomly add numbers to prefix
        for ($i = 0; $i < $length; $i++) {

            // Add random character
            $username .= mt_rand(0,9);
        }

        // Random username for new users
        return $username;
    }

    /**
     * Generate a temporary password.
     *
     * @return string
     */
    public static function tmp_password($length = 8)
    {
        // Random password from string helper
        return \io::helpers('str')->uuid($length);
    }

    /**
     * Method aliases and function wrappers for coders who like to use alternative
     * names for these methods. Slight performance impact when using method aliases.
     *
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public function __call($called, $args = array())
    {
        $aliases = array(
            'password'     => ['hash', 'hash_pwd', 'hash_password'],
            'verify'       => ['verify_pwd', 'verify_pass', 'verify_password'],
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
