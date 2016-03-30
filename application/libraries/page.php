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
 * A class for handling pages and layouts
 *
 * @method io::library('page')->theme( $view );
 * @method io::library('page')->nav( $path, $str );
 * @method io::library('page')->title();
 * @method io::library('page')->description();
 * @method io::library('page')->disable();
 */
namespace ornithopter\libraries;

class page
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
     * Initialize the page.
     *
     * @return void
     */
    public function __construct()
    {
        // Encapsulates output
        ob_start();

        // Shortcut reference
        $r = \io::route();

        // Set defaults for a page
        self::$data = array(

            // Sets a default page title
            'title' => ucwords($r['controller'].' - '.$r['action'].' | '.\io::h('web')->domain()),

            // Sets a default page description
            'description' => false,

            // Optimization is disabled by default
            'optimize' => false,
        );

        // Register shortcut aliases using io::method();
        \io::alias(__CLASS__, get_class_methods(__CLASS__));
    }

    /**
     * Creates a shortcut for io::page().
     *
     * @return object
     */
    public static function page()
    {
        // Shortcut for io::page()
        return self::$instance;
    }

    /**
     * Sets a theme for rendering.
     *
     * @param string
     *
     * @return void
     */
    public static function theme($view)
    {
        // Set the page design
        self::$data['theme'] = $view;

        // Allow chaining
        return self::$instance;
    }

    /**
     * Optimize page by stripping whitespace.
     *
     * @param string
     *
     * @return void
     */
    public static function optimize($enabled = true)
    {
        // Set the optimization value
        self::$data['optimize'] = $enabled;
    }

    /**
     * Set title for page theme.
     *
     * @param string
     *
     * @return void
     */
    public static function title($title)
    {
        // Set page title
        self::$data['title'] = $title;
    }

    /**
     * Set description for page theme.
     *
     * @param string
     *
     * @return void
     */
    public static function description($description)
    {
        // Set page description
        self::$data['description'] = $description;
    }

    /**
     * Disable a page theme.
     *
     * @param string
     *
     * @return void
     */
    public static function disable()
    {
        // Disable the page theme
        unset(self::$data['theme']);

        // Disable the optimizer
        self::$data['optimize'] = false;
    }

    /**
     * Returns a string if controller / action is matched.
     *
     * @return mixed
     */
    public static function nav($path, $str)
    {
        // Check if the path is an alternative route
        if (\io::route()['controller'] == $path) {

            // Return the provided string
            return $str;
        }

        // Check if the path equals the controller action
        if ($path == \io::route()['controller'].'/'.\io::route()['action']) {

            // Return the provided string
            return $str;
        }

        // Check if the path is equal to the REQUEST_URI
        if ($_SERVER['REQUEST_URI'] == $path) {

            // Return the provided string
            return $str;
        }

        // Does not match
        return false;
    }

    /**
     * Generate the page.
     *
     * @return void
     */
    public function __destruct()
    {
        // Check HTTP Status Code
        (http_response_code() == 200)?:exit();

        // Ensure a theme is selected
        if (isset(self::$data['theme'])) {

            // Prepare the page with content
            $__page = \io::view(self::$data['theme'], array(

                // Getting the page title
                '__title' => self::$data['title'],

                // Getting the page description
                '__description' => self::$data['description'],

                // Getting the contents from the buffer
                '__content' => ob_get_contents(),
            ));
        } else {
            // Return a page without a theme
            $__page = ob_get_contents();
        }

        // Cleaning output
        ob_end_clean();

        // Send the page to the browser (Compressed) or normally
        echo (self::$data['optimize']) ? preg_replace('~>\s+<~', '> <', $__page) : $__page;
    }
}
