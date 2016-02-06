<?php
/**
 * Ornithopter.io
 * ------------------------------------------------
 * A minimalist, high-speed open source PHP 5.6+ framework
 *
 * @package     Ornithopter.io
 * @author      Corey Olson
 * @copyright   Copyright (c) 2011 - 2016 Corey Olson
 * @license     http://opensource.org/licenses/MIT (MIT License)
 * @link        https://github.com/olscore/ornithopter.io
 * @version     2016.01.31
 */

 // ########################################################################################

/**
 * A class for handling pages and layouts
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage	Libraries
 *
 * @method		io::library('page')->theme( $view );
 * @method		io::library('page')->nav( $path, $str );
 */
namespace libraries;
class page
{
    /**
	 * This is a singleton class
	 *
	 * @var object
	 */
	private static $instance;

    /**
	 * Internal class variables
	 *
	 * @var array
	 */
    private static $data;

    /**
	 * Ornithopter.io looks for an instance() method when loading a library
	 *
	 * @return  object
	 */
	public static function instance()
	{
		// Check for an instance
		if ( ! isset( self::$instance ) )

			// Create a new instance
			self::$instance = new page;

		// Return existing instance
		return self::$instance;
	}

    /**
     * Initialize the page
     *
     * @return
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
            'title' => ucwords( $r['controller'] . ' - ' . $r['action'] . ' | ' . \io::h('web')->domain() ),

            // Sets a default page description
            'description' => false
        );

        // Register shortcut aliases using io::method();
		\io::alias('libraries\page', get_class_methods(__CLASS__));
    }

	/**
	 * Creates a shortcut for io::page()
	 *
	 * @return  object
	 */
	public static function page()
	{
		// Shortcut for io::page()
		return self::$instance;
	}

    /**
     * Sets a theme for rendering
     *
     * @return
     */
    public static function theme( $view )
    {
        // Set the page design
        self::$data['theme'] = $view;
    }

	/**
     * Returns a string if controller / action is matched
     *
     * @return
     */
    public static function nav( $path, $str )
    {
		// Chec if the path is an alternative route
        if ( \io::route()['controller'] == $path )

            // Return the provided string
            return $str;

        // Check if the path equals the controller action
        if ( $path == \io::route()['controller'] . '/' . \io::route()['action'] )

            // Return the provided string
            return $str;

        // Does not match
		return false;
    }

    /**
     * Generate the page
     *
     * @return
     */
    public function __destruct()
    {
        // Check HTTP Status Code
        if ( http_response_code() != 200 )

            // Only show pages on 200 OK
            return false;

        // Ensure a theme is selected
        if ( isset(self::$data['theme']) )

            // Prepare the page with content
            $__page = \io::view( self::$data['theme'] , array(

                // Getting the page title
                '__title' => self::$data['title'],

                // Getting the page description
                '__description' => self::$data['description'],

                // Getting the contents from the buffer
                '__content' => ob_get_contents()
            ));

        else
            // Return a page without a theme
            $__page = ob_get_contents();

        // Cleaning output
		ob_end_clean();

		// Send the page to the browser (Compressed) or normally
        echo ( false ) ? preg_replace('~>\s+<~', '><', $__page) : $__page;
    }
}
