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
 * A helper class for dealing with websites, urls and curl requests
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage	Helpers
 *
 * @method 		io::helpers('web')->code( $http_status_code [, boolean] );
 * @method 		io::helpers('web')->status( $http_status_code );
 * @method 		io::helpers('web')->refresh();
 * @method 		io::helpers('web')->redirect( $redirect_location [, boolean] );
 * @method 		io::helpers('web')->temporary( $redirect_location );
 * @method 		io::helpers('web')->permanent( $redirect_location );
 * @method 		io::helpers('web')->request( boolean );
 * @method 		io::helpers('web')->action();
 * @method 		io::helpers('web')->protocol();
 * @method 		io::helpers('web')->secure();
 * @method 		io::helpers('web')->insecure();
 * @method 		io::helpers('web')->upgrade();
 * @method 		io::helpers('web')->site();
 * @method 		io::helpers('web')->domain();
 * @method 		io::helpers('web')->requesturi();
 * @method 		io::helpers('web')->querystring();
 * @method 		io::helpers('web')->get( $param );
 * @method 		io::helpers('web')->index();
 * @method 		io::helpers('web')->current();
 */
namespace helpers;
class web
{
	/**
	 * This is a singleton class
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Array of HTTP Status Codes
	 *
	 * @var array
	 */
	private static $codes = array(
		100	=> 'Continue',
		101	=> 'Switching Protocols',

		200	=> 'OK',
		201	=> 'Created',
		202	=> 'Accepted',
		203	=> 'Non-Authoritative Information',
		204	=> 'No Content',
		205	=> 'Reset Content',
		206	=> 'Partial Content',

		300	=> 'Multiple Choices',
		301	=> 'Moved Permanently',
		302	=> 'Found',
		303	=> 'See Other',
		304	=> 'Not Modified',
		305	=> 'Use Proxy',
		307	=> 'Temporary Redirect',

		400	=> 'Bad Request',
		401	=> 'Unauthorized',
		402	=> 'Payment Required',
		403	=> 'Forbidden',
		404	=> 'Not Found',
		405	=> 'Method Not Allowed',
		406	=> 'Not Acceptable',
		407	=> 'Proxy Authentication Required',
		408	=> 'Request Timeout',
		409	=> 'Conflict',
		410	=> 'Gone',
		411	=> 'Length Required',
		412	=> 'Precondition Failed',
		413	=> 'Request Entity Too Large',
		414	=> 'Request-URI Too Long',
		415	=> 'Unsupported Media Type',
		416	=> 'Requested Range Not Satisfiable',
		417	=> 'Expectation Failed',
		422	=> 'Unprocessable Entity',

		500	=> 'Internal Server Error',
		501	=> 'Not Implemented',
		502	=> 'Bad Gateway',
		503	=> 'Service Unavailable',
		504	=> 'Gateway Timeout',
		505	=> 'HTTP Version Not Supported'
	);

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
			self::$instance = new web;

		// Return existing instance
		return self::$instance;
	}

	/**
	 * Initialize web helper class
	 *
	 * @return  object
	 */
    public function __construct()
    {
		// Register shortcut aliases using h::method();
		\io::alias('helpers\web', get_class_methods(__CLASS__));
	}

	/**
	 * Creates a shortcut for io::web()
	 *
	 * @return  object
	 */
	public static function web()
	{
		// Shortcut for io::web()
		return self::$instance;
	}

	/**
	 * Lookup an HTTP Statuc Code
	 *
	 * @param	int
	 * @param  	boolean
	 * @return  mixed
	 */
	public static function code( $code, $mode = false )
	{
		// Check if this is a valid code
		if ( array_key_exists($code, self::$codes) )

			// Decide how to return the code
			if ( $mode )

				// Return the full
				return array(
					'Status' => $code,
					'Status Code' => $code . ' ' . self::$codes[$code],
					'Description' => self::$codes[$code]
				);

			else
				// Return the status code description
				return self::$codes[$code];

		// Not found
		return false;
	}

	/**
	 * Get the current status code or set a status code
	 *
	 * @param	string
	 * @return  void
	 */
	public static function status( $status = false )
	{
		// Check for Getter
		if ( ! $status )

			// Wrapper PHP call
			return http_response_code();

		// Check for a valid Setter
		if ( ! self::code($status) )

			// Not a valid code
			return false;

		// Wrapper PHP call
		return http_response_code($status);
	}

	/**
	 * Force a refresh of a page
	 *
	 * @return  void
	 */
	public static function refresh()
	{
		// Refresh header
		header("Refresh:0");
	}

	/**
	 * Force a redirect (301 Permanent) or (302 Temporary)
	 *
	 * @param	string
	 * @param  	boolean
	 * @return  void
	 */
	public static function redirect( $location, $permanent = false )
	{
		// Redirect type
		if ( $permanent )

			// 301 Permanent redirect
			header("HTTP/1.1 301 Moved Permanently");

		else
			// 302 Permanent redirect
			header("HTTP/1.1 302 Found");

		// Set the redirect location
		header("Location: " . $location);
	}

	/**
	 * Force a 302 Temporary redirect
	 *
	 * @param	string
	 * @return  void
	 */
	public static function temporary( $location )
	{
		// Wrapper call for redirect
		self::redirect( $location, false );
	}

	/**
	 * Force a 301 Permanent redirect
	 *
	 * @param	string
	 * @return  void
	 */
	public static function permanent( $location )
	{
		// Wrapper call for redirect
		self::redirect( $location, true );
	}

	public static function request( $formal = false )
	{
		// Return type
		if ( $formal )

			// Formal Request Information (similar to browser dev tools)
			return array(
				'Request URL' => self::current(),
				'Request Method' => $_SERVER['REQUEST_METHOD'],
				'Status Code' => http_response_code()
			);

		// Developer friendly
		return array_merge( \io::route(), array(
			'url' => self::current(),
			'method' => $_SERVER['REQUEST_METHOD'],
			'status' => http_response_code()
		));
	}

	/**
	 * Get the action request URL minus query string parameters
	 *
	 * @return  string
	 */
	public static function action()
	{
		// Return the action request url
		return self::site() . \io::route()['request'];
	}

	/**
	 * Get the current protocol (either HTTP or HTTPS)
	 *
	 * @return  string
	 */
	public static function protocol()
	{
		// Determine the protocol (HTTP vs HTTPS)
		return ( $_SERVER['SERVER_PORT'] == 80 ) ? 'http' : 'https';
	}

	/**
	 * Detect if HTTPS is being used
	 *
	 * @return  boolean
	 */
	public static function secure()
	{
		// Convert protocol status to boolean
		return ( self::protocol() == 'https' );
	}

	/**
	 * Detect if HTTP is being used
	 *
	 * @return  boolean
	 */
	public static function insecure()
	{
		// Convert protocol status to boolean
		return ( self::protocol() == 'http' );
	}

	/**
	 * Automatically upgrade from HTTP to HTTPS
	 *
	 * @return  void
	 */
	public static function upgrade()
	{
		// Detect current security
		if ( self::insecure() )

			// Perform a redirect (upgrade to HTTPS) keeping parameters
			self::redirect( str_replace('http', 'https', self::current()) );
	}

	/**
	 * Get the site URL with the protocol being used
	 *
	 * @return  string
	 */
	public static function site()
	{
		// Get the current site url
		return self::protocol() . '://' . $_SERVER['SERVER_NAME'];
	}

	/**
	 * Get the domain name of the current site
	 *
	 * @return  string
	 */
	public static function domain()
	{
		// Get the current site domain
		return $_SERVER['SERVER_NAME'];
	}

	/**
	 * Get the REQUEST_URI
	 *
	 * @return  string
	 */
	public static function requesturi()
	{
		// Return the REQUEST_URI
		return \io::route()['request'];
	}

	/**
	 * Get the query string
	 *
	 * @return  string
	 */
	public static function querystring()
	{
		// Return the Query String
		return \io::route()['query'];
	}

	/**
	 * Wrapper for $_GET parameters with decoding
	 *
	 * @return  string
	 */
	public static function get( $param )
	{
		// Check if parameter exists
		if ( isset(\io::route()['get'][$param]) )

			// Return specific $_GET variable
			return urldecode(\io::route()['get'][$param]);

		// Not found
		return false;
	}

	/**
	 * Get the Ornithopter.io index.php URL
	 *
	 * @return  string
	 */
	public static function index()
	{
		// Return the Ornithopter.io index.php url
		return self::site() . $_SERVER['SCRIPT_NAME'];
	}

	/**
	 * Get the current request URL (full domain and path)
	 *
	 * @return  string
	 */
	public static function current()
	{
		// Return the current request url
		return self::site() . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Public function the developer can call for sending a 404 error. This is
	 * the default error which uses 404.html in the root directory. If the file
	 * is not provided PHP will still send a 404 HEADER to the browser.
	 *
	 * @return  void
	 */
	public static function error_404( $path = false )
	{
		// Send a 404 HTTP HEADER error code to the browser
		header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');

		// Include the 404.html file or exit on failure
		( include (($path)?: \io::help()['paths']['root'] . '/404.html') ) ?:exit();

		// Exit anyways
		exit();
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
			'secure' 	=> ['https', 'ssl'],
			'insecure' 	=> ['http']
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
