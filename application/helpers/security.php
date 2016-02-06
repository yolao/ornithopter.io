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
 * A security class with helper security functions
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage  Helpers
 *
 * @method		io::library('security')->hash( $password );
 * @method 		io::library('security')->verify( $password, $stored_hash );
 */
namespace helpers;
class security
{
	/**
	 * This is a singleton class
	 *
	 * @var object
	 */
	private static $instance;

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
			self::$instance = new security;

		// Return existing instance
		return self::$instance;
	}

	/**
	 * Initialize security helper class
	 *
	 * @return  object
	 */
    public function __construct()
    {
		// Register shortcut aliases using h::method();
		\io::alias('helpers\security', get_class_methods(__CLASS__));
	}

	/**
	 * Creates a shortcut for io::security()
	 *
	 * @return  object
	 */
	public static function security()
	{
		// Shortcut for io::security()
		return self::$instance;
	}

	/**
	 * Default security difficulty
	 *
	 * @var int
	 */
	private static $default = 12;

	/**
	 * Supply a password and receive a security hash
	 *
	 * @param 	string
	 * @param 	int
	 * @return  string
	 */
	public static function hash( $password, $wf = 0 )
	{
		// Requires Open SSL to function
		if ( ! function_exists('openssl_random_pseudo_bytes') )
			throw new Exception('Bcrypt requires openssl PHP extension');

		// Appropriate work factor range
		if ( $wf < 10 OR $wf > 50)
			$wf = self::$default;

		// Creates a unique salt for each password
		$salt = '$2a$' . str_pad($wf, 2, '0', STR_PAD_LEFT) . '$'
		. substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)), '+', '.'), 0, 22);

		// Return the hashed password
		return crypt($password, $salt);
	}

	/**
	 * Supply a password and receive a security hash
	 *
	 * @param 	string
	 * @param 	string
	 * @param 	mixed
	 * @return  bool
	 */
	public static function verify( $password, $hash, $legacy_handler = null )
	{
		// Check to see if this is a legacy hash
		if ( self::legacy($hash) )

			// Call that legacy function to deal with the old password
			if ( $legacy_handler )

				// Handle the legacy password
				return call_user_func($legacy_handler, $password, $hash);

			else
				// There's no legacy function
				throw new Exception('Unsupported hash format');

		// Return boolean, does it match?
		return crypt($password, $hash) == $hash;
	}

	/**
	 * Checks for legacy hashes
	 *
	 * @param 	string
	 * @return  bool
	 */
	public static function legacy( $hash )
	{
		// Return a boolean answer
		return substr($hash, 0, 4) != '$2a$';
	}

	/**
	 *
	 *
	 * @return
	 */
	public static function xss()
	{

	}

	/**
	 * Sanitize a string for use as a filename
	 *
	 * @return
	 */
	public static function filename()
	{

	}

	/**
	 * Check images for bad behavior
	 *
	 * @return
	 */
	public static function image_tags()
	{

	}

	/**
	 * Cleans user input from potentially dangerous items
	 *
	 * @return
	 */
	public static function clean_input()
	{

	}

	/**
	 * Generate a CSRF Name
	 *
	 * @return
	 */
	public static function csrf_field()
	{

	}

	/**
	 * Generate a CSRF Token
	 *
	 * @return
	 */
	public static function csrf_token()
	{

	}
}
