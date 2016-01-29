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
 * @version     2016.01.20
 */

 // ########################################################################################

/**
 * An inflector class for language functions
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage	Helpers
 *
 * @method
 */
namespace helpers;
class inflector
{
	/**
	 * Allows global self reference
	 *
	 * @var array
	 */
	public static $self;

	/**
	 * Initialize inflector helper class
	 *
	 * @return  object
	 */
    public function __construct()
    {
		// Create an instance
		self::$self = $this;

		// Register shortcut aliases using h::method();
		\io::alias('helpers\inflector', get_class_methods(__CLASS__));
	}

	/**
	 * Creates a shortcut for io::arr()
	 *
	 * @return  object
	 */
	public static function inflector()
	{
		// Shortcut for io::inflector()
		return self::$self;
	}

	// TODO: Make io::helper('inflector'); class

	/**
	 * Method aliases and function wrappers for coders who like to use alternative
	 * names for these methods. Slight performance impact when using method aliases.
	 *
	 * @param   string
	 * @param   mixed
	 * @return  mixed
	 */
	public static function __call( $called, $args = array() )
	{
		$aliases = array(
			'name'		=> ['alias']
		);

		// Iterate through methods
		foreach ( $aliases as $method => $list )

			// Check called against accepted alias list
			if ( in_array($called, $list) )

				// Dynamic method (alias) call with arbitrary arguments
				return call_user_func_array(array(__CLASS__, $method), $args);

		// No alias found
		return false;
	}
}
