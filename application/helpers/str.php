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
 * @version     2016.01.30
 */

 // ########################################################################################

/**
 * A helper class for assisting with text and string manipulation
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage	Helpers
 *
 * @method io::helpers('str')->studly( $str );
 * @method io::helpers('str')->camel( $str );
 * @method io::helpers('str')->snake( $str );
 * @method io::helpers('str')->underscore( $str );
 * @method io::helpers('str')->slug( $str );
 * @method io::helpers('str')->hiphen( $str );
 * @method io::helpers('str')->human( $str );
 * @method io::helpers('str')->sentence( $str );
 *
 * @method io::helpers('str')->alternator( $str [, $str2] [, $str3] );
 * @method io::helpers('str')->reduce_slashes( $str );
 * @method io::helpers('str')->reduce_multiples( $str, $multi );
 * @method io::helpers('str')->code( $str );
 *
 * @method io::helpers('str')->begins( $str, $begins );
 * @method io::helpers('str')->ends( $str, $ends );
 * @method io::helpers('str')->contains( $str, $contains );
 *
 * @method io::helpers('str')->finish( $str, $ending );
 * @method io::helpers('str')->unfinish( $str, $ending );
 *
 * @method io::helpers('str')->singular( $str );
 * @method io::helpers('str')->plural( $str );
 *
 * @method io::helpers('str')->random( $str );
 * @method io::helpers('str')->word_random( $str );
 *
 * @method io::helpers('str')->limit( $str, $limit);
 * @method io::helpers('str')->ellipsis( $str, $limit);
 * @method io::helpers('str')->word_limit( $str, $limit );
 * @method io::helpers('str')->word_ellipsis( $str, $limit );
 *
 * @method io::helpers('str')->between( $str, $start, $end );
 */
namespace helpers;
class str
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
			self::$instance = new str;

		// Return existing instance
		return self::$instance;
	}

	/**
	 * Initialize str helper class
	 *
	 * @return  object
	 */
	public function __construct()
	{
		// Private methods to exclude from shortcuts
		$excluded = array('casespace');

		// Register shortcut aliases using h::method();
		\io::alias('helpers\str', array_diff(get_class_methods(__CLASS__), $excluded));
	}

	/**
	 * Creates a shortcut for io::str()
	 *
	 * @return  object
	 */
	public static function str()
	{
		// Shortcut for io::str()
		return self::$instance;
	}

	/**
	 * Internal function for casing and spacing strings
	 *
	 * @param	string
	 * @return  array
	 */
	private static function casespace( $str, $tolower = true )
	{
		// Explode on various types of separators
		$sArr = preg_split('/(-|_|\.|\s)/', $str);

		// Mode selection
		if ( $tolower )

			// Iterate through words
			foreach ($sArr as $k => $word)

				// Perform a string to lower
				$sArr[$k] = strtolower($word);

		// Return
		return $sArr;
	}

	/**
	 * Convert a string to StudlyCase
	 *
	 * @param	string
	 * @return  string
	 */
	public static function studly( $str )
	{
		// Get a usuable array
		$sArr = self::casespace( $str );

		// Iterate through words
		foreach ($sArr as $k => $words)

			// Uppercase first letter
			$sArr[$k] = ucfirst($words);

		// Return StudlyCase string
		return trim(implode('', $sArr));
	}

	/**
	 * Convert a string to camelCase
	 *
	 * @param	string
	 * @return  string
	 */
	public static function camel( $str )
	{
		// Return the snake case
		return lcfirst(self::studly($str));
	}

	/**
	 * Convert a string to snake_case (removes capitalization)
	 *
	 * @param	string
	 * @return  string
	 */
	public static function snake( $str )
	{
		// Return the snake case
		return trim(implode('_', self::casespace( $str )));
	}

	/**
	 * Convert a string to underscore_spacing (keeps capitalization)
	 *
	 * @param	string
	 * @return  string
	 */
	public static function underscore( $str )
	{
		// Return the snake case
		return trim(implode('_', self::casespace( $str, false )));
	}

	/**
	 * Convert a string to title-slug (removes capitalization)
	 *
	 * @param	string
	 * @return  string
	 */
	public static function slug( $str )
	{
		// Return the snake case
		return trim(implode('-', self::casespace( $str )));
	}

	/**
	 * Convert a string to hiphen-spacing (keeps capitalization)
	 *
	 * @param	string
	 * @return  string
	 */
	public static function hiphen( $str )
	{
		// Return the snake case
		return trim(implode('-', self::casespace( $str, false )));
	}

	/**
	 * Convert a string to Human readable
	 *
	 * @param	string
	 * @return  string
	 */
	public static function human( $str )
	{
		// Explode on capital letters
		$cArr = preg_split('/(?=\p{Lu})/u', $str);

		// Explode on various types of separators
		$sArr = preg_split('/(-|_|\.)/', implode(' ', $cArr));

		// Return human readable
		return trim(implode(' ', $sArr));
	}

	/**
	 * Convert a string to a proper sentence
	 *
	 * @param	string
	 * @return  string
	 */
	public static function sentence( $str )
	{
		// Return the snake case
		return self::finish(ucfirst(strtolower(self::human($str))), '.');
	}

	/**
	 * Alternates between an arbitrary number of strings (use with a loop)
	 *
	 * @param	...
	 * @return  string
	 */
	public static function alternator( ...$arr )
	{
		// Static variable
		static $i = 0;

		// Return an alternating value
		return $arr[($i++%count($arr))];
	}

	/**
	 * Removed double slashes from a string
	 *
	 * @param	string
	 * @return  string
	 */
	public static function reduce_slashes( $str )
	{
		// Wrapper function for some regex
		return preg_replace('/\/{2,}/', '/', $str);
	}

	/**
	 * Removes multiple instances of strings immediately after one another
	 *
	 * @param	string
	 * @return  string
	 */
	public static function reduce_multiples( $str, $multi )
	{
		// Wrapper function for some regex
		return preg_replace('/' . preg_quote($multi, '/') .'{2,}/', $multi, $str);
	}

	/**
	 * Wrapper for calling htmlentities() on the string
	 *
	 * @param	string
	 * @return  string
	 */
	public static function code( $str )
	{
		// Wrapper function for htmlentities
		return htmlentities($str);
	}

	/**
	 * Returns a boolean if a string begins with a matching string
	 *
	 * @param	string
	 * @return  boolean
	 */
	public static function begins( $str, $begins )
	{
		// Wrapper for preg_match string search
		return preg_match('/^'. preg_quote($begins, '/') .'/', $str);
	}

	/**
	 * Returns a boolean if a string ends with a matching string
	 *
	 * @param	string
	 * @return  boolean
	 */
	public static function ends( $str, $ends )
	{
		// Wrapper for preg_match string search
		return preg_match('/'. preg_quote($ends, '/') .'$/', $str);
	}

	/**
	 * Returns a boolean if a string contrains another string
	 *
	 * @param	string
	 * @return  boolean
	 */
	public static function contains( $str, $contains )
	{
		// Wrapper for preg_match string search
		return preg_match('/'. preg_quote($contains, '/') .'/', $str);
	}

	/**
	 * Ensures that a string ends with a specific character
	 *
	 * @param	string
	 * @return  string
	 */
	public static function finish( $str, $ending )
	{
		// Check if a string ends with a character
		if ( substr($str, -1) == $ending )

			// Already ends with $ending
			return $str;

		// Add $ending to string
		return $str . $ending;
	}

	/**
	 * Ensures that a string does not end with a specific character
	 *
	 * @param	string
	 * @return  string
	 */
	public static function unfinish( $str, $ending )
	{
		// Check if a string ends with a character
		if ( substr($str, -1) == $ending )

			// Removes $ending
			return substr($str, 0, -1);

		// String does not end with $ending
		return $str;
	}

	/**
	 * Wrapper function for inflector pluralization
	 *
	 * @param	string
	 * @return  string
	 */
	public static function singular( $str )
	{
		// Get the singular English noun
		return \io::helper('inflector')->single($str);
	}

	/**
	 * Wrapper function for inflector pluralization
	 *
	 * @param	string
	 * @return  string
	 */
	public static function plural( $str )
	{
		// Get the plural English noun
		return \io::helper('inflector')->plural($str);
	}

	/**
	 * Returns a random sub string of a string
	 *
	 * @param	string
	 * @return  string
	 */
	public static function random( $str )
	{
		// Count the letters
		$length = strlen($str);

		// Return a random sub string
		return substr($str, rand(0, $length), rand(0, $length));
	}

	/**
	 * Returns a random word from a string
	 *
	 * @param	string
	 * @return  string
	 */
	public static function word_random( $str )
	{
		// Separate words
		$words = explode(' ', $str);

		// Returns a random word from the string
		return trim(implode(' ', array_slice($words, rand(0, count($words)), 1)));
	}

	/**
	 * Truncates a string to a given length
	 *
	 * @param	string
	 * @param 	int
	 * @return  string
	 */
	public static function limit( $str, $limit )
	{
		// Wrapper for substr()
		return substr($str, 0, $limit);
	}

	/**
	 * Truncates a string to a given length and adds an ellipsis
	 *
	 * @param	string
	 * @param 	int
	 * @return  string
	 */
	public static function ellipsis( $str, $limit )
	{
		// Wrapper for self::limit()
		return self::limit($str, $limit) . '...';
	}

	/**
	 * Truncates a string to a given word length
	 *
	 * @param	string
	 * @param 	int
	 * @return  string
	 */
	public static function word_limit( $str, $limit )
	{
		// Separate words
		$words = explode(' ', $str);

		// Return the string limited by words
		return trim(implode(' ', array_slice($words, 0, $limit)));
	}

	/**
	 * Truncates a string to a given word length and adds an ellipsis
	 *
	 * @param	string
	 * @param 	int
	 * @return  string
	 */
	public static function word_ellipsis( $str, $limit )
	{
		// Wrapper for self::word_limit()
		return self::word_limit($str, $limit) . '...';
	}

	/**
	 * Get string between two strings
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function between( $str, $start, $end )
	{
		// Check if $start exists
		if ( $from = strpos($str, $start) )

			// Trim from $start
			$from += strlen($start);

		else
			// Trim from beginning of $str
			$start = 0;

		// Check if $end exists
		if ( $to = strpos($str, $end, $from) )

			// Trim to $end
			$to -= $from;

		else
			// Trim to end of $str
			return substr($str, $from);

		// Return a string between two strings
		return substr($str, $from, $to);
	}

	/**
	 * Method aliases and function wrappers for coders who like to use alternative
	 * names for these methods. Slight performance impact when using method aliases.
	 *
	 * @param   string
	 * @param   mixed
	 * @return  mixed
	 */
	public function __call( $called, $args = array() )
	{
		$aliases = array(
			'ends'			=> ['ends_with', 'ending_with', 'with_ending', 'with_end'],
			'begins'		=> ['begins_with', 'beginning_with', 'with_beginning', 'with_begins'],
			'word_random'	=> ['random_word', 'rand_word', 'word_rand'],
			'word_limit'	=> ['limit_word']
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
