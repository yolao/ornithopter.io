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
 * A helper class for working with HTML markup
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage	Helpers
 *
 * @method		io::helpers('html')->tag('h3#id.classone.classtwo', 'contents');
 * @method		io::helpers('html')->tag('p#intro', 'hello world');
 * @method		io::helpers('html')->tag('a.submit', 'submit', ['href' => '/link/path/']);
 */
namespace helpers;
class html
{
	/**
	 * This is a singleton class
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * An array of all valid HTML elements
	 *
	 * @var array
	 */
	private static $tags = array('a', 'abbr', 'address', 'area', 'article', 'aside', 'audio',
		'b', 'base', 'bdi', 'bdo', 'blockquote', 'body', 'br', 'button', 'canvas', 'caption',
		'cite', 'code', 'col', 'colgroup', 'command', 'datalist', 'dd', 'del', 'details',
		'dfn', 'div', 'dl', 'doctype', 'dt', 'em', 'embed', 'fieldset', 'figcaption',
		'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header',
		'hr', 'html', 'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'keygen', 'label',
		'legend', 'li', 'link', 'main', 'map', 'mark', 'menu', 'meta', 'meter', 'nav',
		'noscript', 'object', 'ol', 'optgroup', 'option', 'output', 'p', 'param', 'pre',
		'progress', 'q', 'rp', 'rt', 'ruby', 's', 'samp', 'script', 'section', 'select',
		'small', 'source', 'span', 'strong', 'style', 'sub', 'summary', 'sup', 'table',
		'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track',
		'u', 'ul', 'var', 'video', 'wbr');

	/**
	 * An array of all valid HTML elements without closing tags
	 *
	 * @var array
	 */
	private static $singleTags = array('area', 'base', 'br', 'col', 'command', 'doctype',
		'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'source', 'track', 'wbr');

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
			self::$instance = new html;

		// Return existing instance
		return self::$instance;
	}

	/**
	 * Initialize html helper class
	 *
	 * @return  object
	 */
	public function __construct()
	{
		// Register shortcut aliases using h::method();
		\io::alias('helpers\html', ['html', 'tag', 'mailto']);
	}

	/**
	 * Creates a shortcut for io::html()
	 *
	 * @return  object
	 */
	public static function html()
	{
		// Shortcut for io::html()
		return self::$instance;
	}

	/**
	 * Prepares an HTML element tag for self::create(). Attempts to detect CSS
	 * selectors to make tags with ID and Classes. Only creates valid tags.
	 *
	 * @param	string
	 * @param	string
	 * @param	array
	 * @return  string
	 */
	public static function tag( $str, $contents = false, $attributes = array() )
	{
		// Split by # and . to get element
		$iArr = preg_split( '/(#|\.)/', $str );

		// Get the element
		$tag = strtolower($iArr[0]);

		// Check if this is a valid element
		if ( ! in_array($tag, self::$tags) )

			// Not a valid tag
			return false;

		// Search for an element ID
		preg_match( '/#\w+/', $str, $idArr );

		// Get the ID if specified
		$id = ( isset($idArr[0]) ) ? substr($idArr[0],1) : false;

		// Search for class names
		preg_match_all( '/\.\w+/', $str, $classArr );

		// Check for classes
		if ( count($classArr[0]) )

			// Get the classes
			foreach ($classArr[0] as $class)

				// Add classes and remove CSS selection
				$classes[] = substr($class,1);

		else
			// No classes provided
			$classes = array();

		// Create the tag
		return self::create( $tag, $id, $classes, $contents, $attributes );
	}

	/**
	 * Forms the HTML tag string based on parameters
	 *
	 * @param	string
	 * @param	string
	 * @param	array
	 * @param	string
	 * @return  string
	 */
	private static function create( $tag, $id, $classes, $contents, $attributes )
	{
		// Create a string
		$str = '';

		// Add the opening tag
		$str .= '<' . $tag;

		// Check if an ID has been specified
		if ( $id )

			// Add ID to the element if specified
			$str .= ' id="' . $id . '"';

		// Check if there are any classes
		if ( count($classes) )

			// Add class(es) to the HTML element
			$str .= ' class="' . implode(' ', $classes) . '"';

		// Check if there are any attributes to add
		if ( count($attributes) )

			// Iterate through the attributes
			foreach ($attributes as $attr => $value)

				// Add the attributes
				$str .= ' ' . $attr . '="' . $value . '"';

		// Check for the type of HTML element
		if ( in_array($tag, self::$singleTags) )

			// Add closing tag for single tag elements
			return $str .= ' />';

		else
			// This is a normal HTML element
			$str .= '>';

		// Add contents to the element string
		$str .= $contents;

		// Add closing tag for normal tag elements
		$str .= '</' . $tag . '>';

		// Return HTML element string
		return $str;
	}

	/**
	 * Create a mailto link with optional ordinal encoding
	 *
	 * @param  string
	 * @param  boolean
	 * @return  object
	 */
	public static function mailto( $email, $encode = false )
	{
		// Create a variable for output
		$ordinal = '';

		// Iterate through each letter in the email string
		for ( $i = 0; $i < strlen($email); $i++ )

			// Begin concatenation of the email string
			$ordinal .= ( $encode ) ? '&#' . ord($email[$i]) . ';' : $email[$i];

		// Return mailto tag
		return $ordinal;
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
			'tag' => ['element']
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
