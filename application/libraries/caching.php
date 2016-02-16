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
 * A class for handling Server-side PHP caching
 *
 * @method placeholder class
 */
namespace ornithopter\libraries;

class caching
{
    /**
     * This is a singleton class.
     *
     * @var object
     */
    private static $instance;

    /**
     * Internal class variables.
     *
     * @var array
     */
    private static $data;

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
     * Initialize the caching class.
     *
     * @return
     */
    public function __construct()
    {
        // Register shortcut aliases using io::method();
        \io::alias(__CLASS__, get_class_methods(__CLASS__));
    }

    /**
     * Creates a shortcut for io::caching().
     *
     * @return object
     */
    public static function caching()
    {
        // Shortcut for io::caching()
        return self::$instance;
    }

    /**
     * Method aliases and function wrappers for coders who like to use alternative
     * names for these methods. Slight performance impact when using method aliases.
     *
     * @param   string
     * @param   mixed
     *
     * @return mixed
     */
    public function __call($called, $args = array())
    {
        $aliases = array(
            'method' => ['shortcut'],
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
