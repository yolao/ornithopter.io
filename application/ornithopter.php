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
 * The main class of Ornithopter.io is "io" as it is short and easy to
 * call in common usage, e.g., io::model('sample')->action();

 * Please keep in mind Ornithopter.io breaks best practices in many ways and
 * is designed for speed, conciseness of code, and quick prototyping. Running a
 * production site should be fine, but keep in mind this is designed for quick
 * and dirty prototyping of concept projects. The core problems it solves are
 * mostly [1] project organization, [2] routing and [3] loading classes and
 * functions for your project. There are some basic optimizations to prevent
 * double loading of file resources along with highly optimized internals.
 *
 * Ornithopter.io should run within 1 millisecond without compression on any
 * modern web server, and with compression speed should be x2-8 faster. This is
 * the fastest known MVP framework for PHP because it places most functions in
 * a single file (reducing file reads), doesn't use lazy loading (composer) and
 * makes an effort to process files once; and uses various micro optimizations.
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage	io::ornithopter (main class)
 *
 * @method  	io::controller('class')->method();
 * @method  	io::model('class')->method();
 * @method  	io::view('file');
 * @method  	io::library('class')->method();
 * @method  	io::helper('file');
 *
 * @method  	io::help();
 * @method  	io::route();
 */
class io
{
	/**
	 * Internal Ornithopter.io variables
	 *
	 * @var array
	 */
	private static $_internals = array();

	/**
	 * External Ornithopter.io variables and notes for developers
	 *
	 * @var array
	 */
	protected static $_developers = array();

	/**
	 * Returns the io::$_developers information
	 *
	 * @method  io::help();
	 * @return  void
	 */
	public static function help()
	{
		// Record a note for the develop to troubleshoot
		return self::$_developers;
	}

	/**
	 * Creates aliases using a class name and list of existing methods
	 *
	 * @param   string
	 * @param   array
	 * @return  void
	 */
	public static function alias( $class, $methodArr )
	{
		// Reserved Ornithopter.io methods
		$reservedMethods = array_merge(self::$_internals['methods']);

		// Iterate through class methods
		foreach ( $methodArr as $method )

			// Check for protected internal methods
			if ( in_array($method, $reservedMethods ) )

				// Disallow overwriting internals
				return false;

			// Do not create aliases for magic functions
			else if ( substr($method, 0, 2) != '__' )

				// Create the alias
				self::$_internals['alias'][$method] = $class;
	}

	/**
	 * Returns the Ornithopter.io Routing information
	 *
	 * @method  io::route();
	 * @return  void
	 */
	public static function route()
	{
		// Get the request information
		$request = self::$_developers['request'][0];

		// Get the query string if it exists
		if ( isset(self::$_developers['request'][1]) )

			// Set the query string
			array($query = self::$_developers['request'][1]);

		else
			// No query string parameters
			array($query = false, $get = false);

		// Iterate through the PHP global variables
		foreach ($gArr = ['get', 'post', 'cookie', 'session'] as $global)

			// Create a reference to the globals
			$gArr[$global] =& $GLOBALS[ '_' . strtoupper($global) ];

		// Record a note for the develop to troubleshoot
		return array_merge(
			self::$_developers['route'],
			array('request' => $request),
			array('query' => $query),
			$gArr
		);
	}

	/**
	 * Standard Ornithopter.io routing from index.php and initialization of the
	 * framework. This is the standard way to use Ornithopter.io and no special
	 * parameters or output are needed or avaiable. This method basically parses
	 * REQUEST_URI and then traces out which controller to load, methods to run
	 * and makes the data available via io::$_developers static variable while running.
	 *
	 * @return  void
	 */
	public static function ornithopter()
	{
		// Internal routing initialization
		io::_router();

		// initialization of the routed controller by Ornithopter.io
		$controller = self::_factory('controllers', self::$_developers['route']['controller']);

		/*
		 * Iterates through possible methods looking for "before" and "after"
		 * hooks for controllers. Replicating __contstruct() and __destruct()
		 * specifically for methods instead of the entire class. Conveneince.
		 */
		foreach (array('before', strtolower($_SERVER['REQUEST_METHOD']), 'after') as $k => $method)

			// Check if the method exists within the controller
			if ( method_exists($controller, $method.'_'.self::$_developers['route']['action']) )

				// Execute the method within the routed controller if it exists
				$controller->{$method.'_'.self::$_developers['route']['action']}();

			// Ignoreing missing before_method() and after_method()
			else if ( $k == 1 )

				// 404: Appears the routing method is missing
				io::helper('web')->error_404();
	}

	/**
	 * Views load .php files by default, and extracts $args for an effecient
	 * albeit basic teplating engine. Simply set the $key => $variables as you
	 * would in your models or controllers and echo the variables in the view.
	 *
	 * @param   string
	 * @param   array
	 * @param   string
	 * @return  string
	 */
	public static function view( $__name, $__args = array(), $__ext = '.php' )
	{
		// Encapsulates all output
		ob_start();

		// Arrays passed to the view become $key => $variables for templating
		( count($__args) != 0 ) ? extract( $__args, EXTR_PREFIX_SAME, '_conflict_' ) : false ;

		// Again we either (a) includes the file or (b) exit on failure
		(include( self::$_developers['files']['views'][$__name] = self::$_developers['paths']['views'] . $__name . $__ext ) )?:exit();

		// Getting the contents of the buffer
		$__view = ob_get_contents();

		// Cleaning everything done here
		ob_end_clean();

		// Views sent back as strings
		return $__view;
	}

	/**
	 * Initialize Ornithopter.io for normal and alternative usage
	 *
	 * @param 	boolean
	 * @return  void
	 */
	protected static function _init( $alternative = false )
	{
		/*
		 * These are special aliases required for accessing internal functionality
		 * like loading and using helpers, libraries, models, views and controllers.
		 */
		self::$_internals['methods'] = array(
			['models', 'm', 'model'],
			['views', 'v', 'view'],
			['controllers', 'c', 'controller'],
			['libraries', 'l', 'library'],
			['helpers', 'h', 'helper'],
			['vendors', 'v3', 'vendor']
		);

		// Root directory for index.php
		self::$_developers['paths']['root'] = dirname(__DIR__) . '/';

		// Root directory for ornithopter.php
		self::$_developers['paths']['ioapp'] = __DIR__ . '/';

		// Create the directory paths to each object type
		foreach ( self::$_internals['methods'] as $path )

			// Create the file paths for respective file types
			self::$_developers['paths'][$path[0]] = __DIR__ . '/' . $path[0] . '/';

		// Lightweight (minimal) security measures
		foreach ( $gArr = ['get', 'cookie'] as $global )

			// Simple global cleaning
			self::_purify($global);

		// Step to internal router
		io::_router( $alternative );
	}

	/**
	 * Quick $_GET, $_COOKIE or $GLOBALS cleaning for lightweight security. This
	 * is not comprehensive but is merely simple cleaning of certain globals while
	 * still allowing the developer a great deal of freedom with $_POST data.
	 *
	 * @return  array
	 */
	private static function _purify( $global )
	{
		// Shortcut reference
		$gVar =& $GLOBALS[ '_' . strtoupper($global) ];

		// Iterate through each varaible
		foreach ( $gVar as $var => $unsanitized )

			// Very basic $_GET and $_COOKIE cleaning
			$gVar[$var] = strip_tags($unsanitized);

		// Return the global
		return $gVar;
	}

	/**
	 * Factory method for creating objects within io
	 *
	 * @param   string
	 * @param   string
	 * @param   array
	 * @return  object
	 */
	private static function _factory( $type, $name, $args = array() )
	{
		// Configure on initialization
		if ( ! isset( self::$_developers['paths'] ) )

			// Run initialization
			self::_init();

		// Prevents processing files twice
		if ( ! isset( self::$_developers['files'][$type][$name] ) )

			// This [1] Either (a) includes file or (b) exits on failure; [2] adds file tracking array
			( include self::$_developers['files'][$type][$name] = self::$_developers['paths'][$type] . $name . '.php' ) ?:io::helper('web')->error_404();

		// Sub directory controllers
		if ( strpos($name, '/') !== false )

			// Get controller of a subdirectory
			$name = substr(strrchr($name, '/'),1);

		// Remove hiphens from class names
		$name = str_replace('-', '', $name);

		// Executes singleton methods in classes
		if ( method_exists( $name, 'instance' ) )

			// Returns the singleton
			return $name::instance();

		// Executes singleton methods for Libraries & Helpers (namespaces)
		if ( method_exists( $type . '\\' . $name, 'instance' ) )

			// Returns the instance of the singleton design pattern
			return call_user_func( $type . '\\' . $name . '::instance' );

		// Initialize classes [1] with namespaces or [2] normally
		if ( in_array($type, array('helpers', 'libraries', 'vendors')) )

			// Initialization for Helpers and Libraries using namespaces
			$reflection = new ReflectionClass($type . '\\' . $name);

		else
			// Controllers and models do not have namespaces
			$reflection = new ReflectionClass($name);

		// This [1] creates the object instances (with or without arguments) and [2] adds to object tracking array
		return self::$_developers['objects'][$name][] = ( count($args) == 0 ) ? $reflection->newInstance() : $reflection->newInstanceArgs($args);
	}

	/**
	 * Internal routing logic
	 *
	 * @param   boolean
	 * @return  void
	 */
	private static function _router( $alternative = false )
	{
		// Readability reference
		$r =& self::$_developers['route'];

		// Splits the REQUEST_URI for [0] the Path and [1] the Query String
		self::$_developers['request'] = explode('?', $_SERVER['REQUEST_URI']);

		// Removes bad characters except ":" (colon), "~" (tilde), "/" (slash) and "." (period)
		self::$_developers['request'][0] = preg_replace('/[^a-zA-Z0-9:~\/\.\-\_]|:{2,}|\.{2,}/', '', self::$_developers['request'][0] );

		// Recording routes for io::$_developers
		$r = array(
			// Initially setting controller and action to empty
			'controller' => '', 'action' => '',
			// This [1] out empty parameters and [2] splits parameters on "/" marks
			'params' => array_filter( explode("/", (self::$_developers['request'][0])?: '' ) )
		);

		/*
		 * Iterates through parameters and checks for sub directories. Routing
		 * will prefer Directories > Home Methods > Controllers in that order.
		 */
		foreach ($r['params'] as $piece)

			/*
			 * Figuring out if the request path is a directory, file, or method
			 * by process of elimination in (hopefully) the most effecient way.
			 */
			if ( is_dir( __DIR__ . '/controllers/' . $r['controller'] . $piece ) )
				$r['controller'] .= array_shift($r['params']) . '/';

			// Check if this $piece is a php file in one of the subdirs
			else if ( is_file( __DIR__ . '/controllers/' . $r['controller'] . $piece . '.php' ) )
				break;

			else
				// Check to see if this is a method within a home.php file
				if ( is_file( __DIR__ . '/controllers/' . $r['controller'] . 'home.php' ) )
				{
					// Set a temporary name
					$name = $r['controller'] . 'home';

					// Prevents processing files twice
					if ( ! isset( self::$_developers['files']['controllers'][$name] ) )

						// This [1] Either (a) includes file or (b) exits on failure; [2] adds file tracking array
						( include self::$_developers['files']['controllers'][$name] = __DIR__ . '/controllers/' . $name . '.php' ) ?:io::helper('web')->error_404();

					// Reverse engineer this controller class
					$reflection = new ReflectionClass('home');

					// Pull the methods from the reflected class
					foreach( $reflection->getMethods() as $methods )

						// Add to class tracking array
						$classes[] = $methods->name;

					// Check for a matching method in the reflected class
					if ( in_array(strtolower($_SERVER['REQUEST_METHOD'].'_'.$piece), $classes) )

						// Shift array to use the default home controller
						array_unshift($r['params'], 'home');

					// Alternative route check
					else if ( ! $alternative )

						// 404: Parameter makes no sense
						io::helper('web')->error_404();

					// Prevent errors
					break;
				}

		// Setting the controller to run based on routing (default: home)
		$r['controller'] .= ( array_shift($r['params']) ?: 'home');

		// Setting the method to run based on routing (default: index)
		$r['action'] = ( array_shift($r['params']) ?: 'index');
	}

	/**
	 * Python provides some notation and documention for functions so there is
	 * an attempt to provide as much info as possible within Ornithopter.io
	 *
	 * @method  io::notes();
	 * @param 	string
	 * @param 	string
	 * @return  void
	 */
	public static function _notes( $title, $note )
	{
		// Record a note for the develop to troubleshoot
		self::$_developers['notes'][][$title] = $note;
	}

	/**
	 * Serves as a wrapper, condenses code and allows developers to use
	 * abbreviations for loading controllers, models, helpers and libraries.
	 *
	 * @param   string
	 * @param   mixed
	 * @return  object
	 */
	public static function __callStatic( $called, $args = array() )
	{
		// Iterate MVC and Library / Helper methods and Vendor libraries
		foreach ( self::$_internals['methods'] as $method => $aliases )

			// Check for valid aliases
			if ( in_array($called, $aliases) )

				// Send to factory self::create() for MCLH and send V to self::views()
				return self::_factory( $aliases[0], array_shift($args), $args );

		// Use the alias to call the static class method with arguments
		return call_user_func_array([self::$_internals['alias'][$called], $called], $args);
	}
}

// ------------------------------------------------------------------------------------------------

/**
 * The secondary class of Ornithopter.io is "route" as it is short and easy to
 * call in common usage, e.g., route::get('.*', function(){}, true);
 *
 * This simple routing class should be able to handle very advanced routing as
 * it [1] allows custom REQUEST_METHOD's and [2] regex pattern matching. A few
 * examples have been provided below. This is Ornithopter.io alternative routing
 * for tying models, libraries, helpers and even controllers from external apps;
 * however this form of routing can be very useful for building RESTful APIs.
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage  io::ornithopter (Routing class)
 *
 * @method  	route::get('.*', function(){}, false)
 * @method		route::post('/[0-9]/.*', function(){}, true)
 * @method		route::any('.*', function(){})
 * @method		route::put('/[a-z]/.*', function(){})
 * @method		route::delete('/user/delete/[0-9]/', function(){})
 * @method		route::custom('.*', function(){})
 *
 * @return		closure
 */
class route extends io
{
	/**
	 * Route matching
	 *
	 * @param   string
	 * @param   string
	 * @return  boolean
	 */
	public static function _match( $request, $route )
	{
		// Check REQUEST_METHOD method against route
		if ( $request == 'ANY' )
			return true;

		// Check route request against REQUEST_METHOD
		else if ( $request != $_SERVER['REQUEST_METHOD'] )
			return false;

		// Update the internal variables for developers
		io::$_developers['request'] = explode('?', $_SERVER['REQUEST_URI']);

		// Removes bad characters except ":" (colon), "~" (tilde), "/" (slash) and "." (period)
		$url = ( preg_replace('/[^a-zA-Z0-9:~\/\.\-\_]|:{2,}|\.{2,}/', '', io::$_developers['request'][0] ) ) ?:'/';

		// Route matching; Checks [1] literal matches, then [2] Regex
		if ( $route == $url OR preg_match( '#^' . $route . '$#' , $url) )

			// Add to internal routes tracking array
			return io::$_developers['route'][][$request] = $route;

		// No pattern matches
		return false;
	}

	/**
	 * Allows custom REQUEST_METHOD's instead of limiting developers to
	 * standard HTTP request types by using a magic PHP function for routing.
	 *
	 * @param   string
	 * @param   mixed
	 * @return  void
	 */
	public static function __callStatic( $type, $args = array() )
	{
		// Configure on initialization
		if ( ! isset( io::$_developers['paths'] ) )

			// Run initialization
			io::_init( true );

		// Check route against self::match()
		if ( self::_match(strtoupper($type), $args[0]) )

			// Closure
			$args[1]();

		// Discontinue processing on TRUE
		(isset($args[2])&&$args[2])?exit():0;
	}
}
