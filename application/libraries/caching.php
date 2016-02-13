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
 * @version     2016.02.05
 */

 // ########################################################################################

/**
 * A class for handling Server-side PHP caching
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage	Libraries
 *
 * @method
 */
namespace libraries;
class caching
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
			self::$instance = new caching;

		// Return existing instance
		return self::$instance;
	}

	/**
	 * Initialize the caching class
	 *
	 * @return
	 */
	public function __construct()
	{
		// Register shortcut aliases using io::method();
		\io::alias('libraries\caching', get_class_methods(__CLASS__));
	}
}

/**
 * Creates a shortcut for io::caching()
 *
 * @return  object
 */
public static function caching()
{
	// Shortcut for io::caching()
	return self::$instance;
}
