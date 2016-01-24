<?php
/**
 * Ornithopter.io
 * ------------------------------------------------
 * A minimalist, high-speed open source PHP 5.5+ framework
 *
 * @package     Ornithopter.io
 * @author      Corey Olson
 * @copyright   Copyright (c) 2011 - 2016 Corey Olson
 * @license     http://opensource.org/licenses/MIT (MIT License)
 * @link        https://github.com/olscore/ornithopter.io
 * @version     2016.01.23
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
 * @var			io::$api
 * @method  	io::controller('class')->method();
 * @method  	io::model('class')->method();
 * @method  	io::view('file');
 * @method  	io::library('class')->method();
 * @method  	io::helper('file');
 *
 * @return
 */
class io
{
	/**
	 * Internal variables
	 *
	 * @var array
	 */
	public static $api = array();

	/**
	 * Factory method for creating objects within io
	 *
	 * @param   string
	 * @param   string
	 * @param   array
	 * @return  object
	 */
	private static function create( $type, $name, $args = array() )
	{
		// Configure on initialization
		if ( ! isset( self::$api['paths'] ) )
		{
			// Root directory for index.php
			self::$api['paths']['root'] = dirname(__DIR__) . '/';

			// Root directory for ornithopter.php
			self::$api['paths']['ioapp'] = __DIR__ . '/';

			// Create the file paths for respective file types
			foreach ( array('controllers', 'models', 'views', 'helpers', 'libraries', 'vendors') as $path )
				self::$api['paths'][$path] = __DIR__ . '/' . $path . '/';
		}

		// Prevents processing files twice
		if ( ! isset( self::$api['files'][$name] ) )

			// This [1] Either (a) includes file or (b) exits on failure; [2] adds file tracking array
			( include self::$api['files'][$name] = self::$api['paths'][$type] . $name . '.php' ) ?:io::error_404();

		if ( ! isset( self::$api['objects'][$name] ) )
		{
			// Subdirectory controllers
			if ( strpos($name, '/') !== false )
				$name = substr(strrchr($name, '/'),1);

			// Remove hiphens from class names
			$name = str_replace('-', '', $name);

			// Executes singleton methods in classes
			if ( method_exists( $name, 'instance' ) )
				return $name::instance();

			// Executes singleton methods for Libraries & Helpers (namespaces)
			if ( method_exists( $type . '\\' . $name, 'instance' ) )
				return call_user_func( $type . '\\' . $name . '::instance' );

			// Initialize classes [1] with namespaces or [2] normally
			if ( in_array($type, array('helpers', 'libraries', 'vendors')) )
				$reflection = new ReflectionClass($type . '\\' . $name);
			else
				$reflection = new ReflectionClass($name);

			// This [1] creates the object instances (with or without arguments) and [2] adds to object tracking array
			self::$api['objects'][$name] = ( count($args) == 0 ) ? $reflection->newInstance() : $reflection->newInstanceArgs($args);
		}

		// Returns object; allows chaining
		return self::$api['objects'][$name];
	}

	/**
	 * Serves as a wrapper, condenses code and allows developers to use
	 * abbreviations for loading controllers, models, helpers and libraries.
	 *
	 * @param   string
	 * @param   mixed
	 * @return  object
	 */
	public static function __callStatic( $type, $args )
	{
		// Uses magic PHP function to allow abbreviations and condense wrappers into one
		$wrapperArray = array('c' => 'controllers', 'm' => 'models', 'h' => 'helpers', 'l' => 'libraries', 'v' => 'vendors');

		// Wrapper for self::create() factory method; allows abbreviations
		return self::create( $wrapperArray[substr($type,0,1)], array_shift($args), $args );
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
		(include( self::$api['paths']['views'] . $__name . $__ext ) )?:exit();

		// Getting the contents of the buffer
		$__view = ob_get_contents();

		// Cleaning everything done here
		ob_end_clean();

		// Views sent back as strings
		return $__view;
	}

	/**
	 * Standard Ornithopter.io routing from index.php and initialization of the
	 * framework. This is the standard way to use Ornithopter.io and no special
	 * parameters or output are needed or avaiable. This method basically parses
	 * REQUEST_URI and then traces out which controller to load, methods to run
	 * and makes the data available via io::$api static variable while running.
	 *
	 * @return  void
	 */
	public static function ornithopter()
	{
		// Readability reference
		$r =& self::$api['route'];

		// Splits the REQUEST_URI for [0] the Path and [1] the Query String
		self::$api['request'] = explode('?', $_SERVER['REQUEST_URI']);

		// Removes bad characters except ":" (colon), "~" (tilde), "/" (slash) and "." (period)
		self::$api['request'][0] = preg_replace('/[^a-zA-Z0-9:~\/\.\-\_]|:{2,}|\.{2,}/', '', self::$api['request'][0] );

		// Recording routes for io::$api
		$r = array(
			// Initially setting controller and action to empty
			'controller' => '', 'action' => '',
			// This [1] out empty parameters and [2] splits parameters on "/" marks
			'params' => array_filter( explode("/", (self::$api['request'][0])?: '' ) )
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
					if ( ! isset( self::$api['files'][$name] ) )

						// This [1] Either (a) includes file or (b) exits on failure; [2] adds file tracking array
						( include self::$api['files'][$name] = __DIR__ . '/controllers/' . $name . '.php' ) ?:io::error_404();

					// Reverse engineer this controller class
					$reflection = new ReflectionClass('home');

					// Pull the methods from the reflected class
					foreach( $reflection->getMethods() as $methods )
						$classes[] = $methods->name;

					// Check for a matching method in the reflected class
					if ( in_array(strtolower($_SERVER['REQUEST_METHOD'].'_'.$piece), $classes) )

						// Shift array to use the default home controller
						array_unshift($r['params'], 'home');
					else
						// 404: Parameter makes no sense
						self::error_404();

					// Prevent errors
					break;
				}

		// Setting the controller to run based on routing (default: home)
		$r['controller'] .= ( array_shift($r['params']) ?: 'home');

		// Setting the method to run based on routing (default: index)
		$r['action'] = ( array_shift($r['params']) ?: 'index');

		// initialization of the routed controller by Ornithopter.io
		$controller = self::create('controllers', self::$api['route']['controller']);

		/*
		 * Iterates through possible methods looking for "before" and "after"
		 * hooks for controllers. Replicating __contstruct() and __destruct()
		 * specifically for methods instead of the entire class. Conveneince.
		 */
		foreach (array('before', strtolower($_SERVER['REQUEST_METHOD']), 'after') as $k => $method)

			// Check if the method exists within the controller
			if ( method_exists($controller, $method.'_'.self::$api['route']['action']) )

				// Execute the method within the routed controller if it exists
				$controller->{$method.'_'.self::$api['route']['action']}();

			// Ignoreing missing before_method() and after_method()
			else if ( $k == 1 )

				// 404: Appears the routing method is missing
				self::error_404();
	}

	/**
	 * Public function the developer can call for sending a 404 error. This is
	 * the default error which uses 404.html in the root directory. If the file
	 * is not provided PHP will still send a 404 HEADER to the browser.
	 *
 	 * @method  io::error_404();
	 * @return  void
	 */
	public static function error_404()
	{
		// Send a 404 HTTP HEADER error code to the browser
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

		// Include the 404.html file or exit on failure
		( include dirname(__DIR__) . '/404.html' ) ?:exit();

		// Exit anyways
		exit();
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
class route
{
	/**
	 * Route matching
	 *
	 * @param   string
	 * @param   string
	 * @return  boolean
	 */
	public static function match( $request, $route )
	{
		// Check REQUEST_METHOD method against route
		if ( $request == 'ANY' )
			return true;

		// Check route request against REQUEST_METHOD
		else if ( $request != $_SERVER['REQUEST_METHOD'] )
			return false;

		// Update the internal variables for developers
		io::$api['request'] = explode('?', $_SERVER['REQUEST_URI']);

		// Removes bad characters except ":" (colon), "~" (tilde), "/" (slash) and "." (period)
		$url = ( preg_replace('/[^a-zA-Z0-9:~\/\.\-\_]|:{2,}|\.{2,}/', '', io::$api['request'][0] ) ) ?:'/';

		// Route matching; Checks [1] literal matches, then [2] Regex
		if ( $route == $url OR preg_match( '#^' . $route . '$#' , $url) )
			return io::$api['route'][][$request] = $route;

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
	public static function __callStatic( $type, $args )
	{
		// Check route against self::match()
		if ( self::match(strtoupper($type), $args[0]) )
			$args[1]();

		// Discontinue processing on TRUE
		(isset($args[2])&&$args[2])?exit():0;
	}
}
