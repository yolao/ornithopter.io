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
 * The main class of Ornithopter.io is "io" as it is short and easy to
 * call in common usage, e.g., io::model('sample')->action();.
 *
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
 * @method io::controller()->method();
 * @method io::model()->method();
 * @method io::library()->method();
 * @method io::helper()->method();
 * @method io::view();
 * @method io::info();
 * @method io::route();
 */
class io
{
    /**
     * Internal Ornithopter.io variables.
     *
     * @var array
     */
    private static $_internals = array();

    /**
     * External Ornithopter.io variables and notes for developers.
     *
     * @var array
     */
    protected static $_developers = array();

    /**
     * Returns the io::$_developers information.
     *
     * @method io::info();
     */
    public static function info()
    {
        // Record a note for the develop to troubleshoot
        return self::$_developers;
    }

    /**
     * Creates aliases using a class name and list of existing methods.
     *
     * @param string
     * @param array
     */
    public static function alias($class, $methodArr)
    {
        // Reserved Ornithopter.io methods
        $reservedMethods = array_merge(self::$_internals['methods']);

        // Iterate through class methods
        foreach ($methodArr as $method) {

            // Check for protected internal methods
            if (in_array($method, $reservedMethods)) {

                // Disallow overwriting internals
                return false;
            }

            // Do not create aliases for magic functions
            elseif (substr($method, 0, 2) != '__') {

                // Create the alias
                self::$_internals['alias'][$method] = $class;
            }
        }
    }

    /**
     * Returns the Ornithopter.io Routing information.
     *
     * @method io::route();
     */
    public static function route()
    {
        // Get the request information
        $request = self::$_developers['request'][0];

        // Get the query string if it exists
        if (isset(self::$_developers['request'][1])) {

            // Set the query string
            array($query = self::$_developers['request'][1]);
        } else {
            // No query string parameters
            array($query = false, $get = false);
        }

        // Iterate through the PHP global variables
        foreach ($gArr = ['get', 'post', 'cookie', 'session'] as $global) {

            // Create a reference to the globals
            $gArr[$global] = &$GLOBALS[ '_'.strtoupper($global) ];
        }

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
     */
    public static function ornithopter()
    {
        // Internal routing initialization
        self::_router();

        // initialization of the routed controller by Ornithopter.io
        $controller = self::_factory('controllers', self::$_developers['route']['controller']);

        /*
         * Iterates through possible methods looking for "before" and "after"
         * hooks for controllers. Replicating __contstruct() and __destruct()
         * specifically for methods instead of the entire class. Conveneince.
         */
        foreach (array('before', strtolower($_SERVER['REQUEST_METHOD']), 'after') as $k => $method) {

            // Check if the method exists within the controller
            if (method_exists($controller, $method.'_'.self::$_developers['route']['action'])) {

                // Execute the method within the routed controller if it exists
                $controller->{$method.'_'.self::$_developers['route']['action']}();
            }

            // Ignoreing missing before_method() and after_method()
            elseif ($k == 1) {

                // 404: Appears the routing method is missing
                self::helper('web')->error_404();
            }
        }
    }

    /**
     * Views load .php files by default, and extracts $args for an effecient
     * albeit basic teplating engine. Simply set the $key => $variables as you
     * would in your models or controllers and echo the variables in the view.
     *
     * @param string
     * @param array
     * @param string
     *
     * @return string
     */
    public static function view($__name, $__args = array(), $__ext = '.php')
    {
        // Encapsulates all output
        ob_start();

        // Arrays passed to the view become $key => $variables for templating
        (count($__args) != 0) ? extract($__args, EXTR_PREFIX_SAME, '_conflict_') : false;

        // Again we either (a) includes the file or (b) exit on failure
        (include(self::$_developers['files']['views'][$__name] = self::$_developers['paths']['views'].$__name.$__ext)) ?: exit();

        // Getting the contents of the buffer
        $__view = ob_get_contents();

        // Cleaning everything done here
        ob_end_clean();

        // Views sent back as strings
        return $__view;
    }

    /**
     * Initialize Ornithopter.io for normal and alternative usage.
     *
     * @param boolean
     */
    protected static function _init($alternative = false)
    {
        // Include Composer auto loading if available
        if (file_exists($composer = 'vendor/autoload.php')) {

            // Complimentary auto-loading
            include $composer;
        }

        // Create a list of helpers and libraries within Ornithopter.io
        self::$_developers['ext'] = array(
            'helpers'   => array_diff(scandir('./application/helpers'), ['.', '..']),
            'libraries' => array_diff(scandir('./application/libraries'), ['.', '..']),
        );

        // Iterate through and clean up the available extensions
        array_walk_recursive(self::$_developers['ext'], function(&$value, $key) {

            // Remove the file extensions
            $value = str_replace('.php', '', $value);
        });

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
        );

        // Root directory for index.php
        self::$_developers['paths']['root'] = dirname(__DIR__).'/';

        // Root directory for ornithopter.php
        self::$_developers['paths']['ioapp'] = __DIR__.'/';

        // Create the directory paths to each object type
        foreach (self::$_internals['methods'] as $path) {

            // Create the file paths for respective file types
            self::$_developers['paths'][$path[0]] = __DIR__.'/'.$path[0].'/';
        }

        // Lightweight (minimal) security measures
        foreach ($gArr = ['get', 'cookie'] as $global) {

            // Simple global cleaning
            self::_purify($global);
        }

        // Register shortcut aliases
        self::alias('route', ['has']);

        // Step to internal router
        self::_router($alternative);
    }

    /**
     * Quick $_GET, $_COOKIE or $GLOBALS cleaning for lightweight security. This
     * is not comprehensive but is merely simple cleaning of certain globals while
     * still allowing the developer a great deal of freedom with $_POST data.
     *
     * @return array
     */
    private static function _purify($global)
    {
        // Shortcut reference
        $gVar = &$GLOBALS[ '_'.strtoupper($global) ];

        // Iterate through each varaible
        foreach ($gVar as $var => $unsanitized) {

            // Very basic $_GET and $_COOKIE cleaning
            $gVar[$var] = strip_tags($unsanitized);
        }

        // Return the global
        return $gVar;
    }

    /**
     * Factory method for creating objects within io.
     *
     * @param string
     * @param string
     * @param array
     *
     * @return object
     */
    private static function _factory($type, $name, $args = array())
    {
        // Configure on initialization
        if (!isset(self::$_developers['paths'])) {

            // Run initialization
            self::_init();
        }

        // Prevents processing files twice
        if (!isset(self::$_developers['files'][$type][$name])) {

            // This [1] Either (a) includes file or (b) exits on failure; [2] adds file tracking array
            (include self::$_developers['files'][$type][$name] = self::$_developers['paths'][$type].$name.'.php') ?: self::helper('web')->error_404();
        }

        // Remember original path
        $path = $name;

        // Sub directory controllers
        if (strpos($name, '/') !== false) {

            // Get controller of a subdirectory
            $name = substr(strrchr($name, '/'), 1);
        }

        // Remove hiphens from class names
        $name = str_replace('-', '', $name);

        // Executes singleton methods in classes
        if (method_exists($name, 'instance')) {

            // Returns the singleton
            return $name::instance();
        }

        // Executes singleton methods for Libraries & Helpers (namespaces)
        if (method_exists('ornithopter\\'.$type.'\\'.$name, 'instance')) {

            // Returns the instance of the singleton design pattern
            return call_user_func('ornithopter\\'.$type.'\\'.$name.'::instance');
        }

        // Initialize classes [1] with namespaces or [2] normally
        if (in_array($type, array('helpers', 'libraries'))) {

            // Initialization for Helpers and Libraries using namespaces
            $reflection = new ReflectionClass('ornithopter\\'.$type.'\\'.$name);

        } else {
            // self::_reflector() will determine how to access this object
            $reflection = self::_reflector($type, $name, true, $path);
        }

        // This [1] creates the object instances (with or without arguments) and [2] adds to object tracking array
        return self::$_developers['objects'][$name][] = (count($args) == 0) ? $reflection->newInstance() : $reflection->newInstanceArgs($args);
    }

    /**
     * This method searches for an object within various namespaces. What this
     * allows for is various types of namespace usagage in controllers and models
     * meaning you can use [0] no namepsaces, [1] simple namespaces likes "model"
     * and "controller", [2] or even the path name of the controller or model in
     * namespaces. This can be useful for very large applications, or even smaller
     * apps who may choose to use the same class names for models and controllers
     *
     * @param string
     * @param string
     * @param string
     * @param string
     *
     * @return object
     */
    private static function _reflector($type, $name, $alias, $path = '')
    {
        // Flip path brackets for namespacing
        $nsp = str_replace('/', '\\', str_replace('/'.$name, '', $path));

        // Inflected type
        $ntype = (substr($type,-1)!='s')?$type:substr($type, 0, -1);

        // Namespaces to check for class
        $namespace = ['', $ntype.'\\', $type.'\\', $nsp, $ntype.'\\'.$nsp.'\\', $type.'\\'.$nsp.'\\'];

        // Iterate through namespaces
        foreach (array_unique($namespace) as $ns) {

            // Check for object within namespace
            if ( class_exists($ns.$name) ) {

                // Do not alias global namespace
                if ( $alias AND $ns != '' ) {

                    // Universal access to Ornithopter.io without "use" declarations
                    [class_alias('\io', $ns.'io'), class_alias('\route', $ns.'route')];
                }

                // Object found in namespace stop processing
                return $reflection = new ReflectionClass($ns.$name);
            }
        }

        // Unexpected namespace or class name used in this file
        throw new \Exception('Unable to load ' . $name . '() in ' . ucfirst($type));
    }

    /**
     * Internal routing logic.
     *
     * @param bool
     */
    private static function _router($alternative = false)
    {
        // Readability reference
        $r = &self::$_developers['route'];

        // Splits the REQUEST_URI for [0] the Path and [1] the Query String
        self::$_developers['request'] = explode('?', (stripos($_SERVER['REQUEST_URI'], 'index.php'))
            // Ornithopter working without mod_rewrite (index.php visible)
            ? $mod_rewrite_disabled = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI'])
            // Ornithopter with mod_rewrite enabled (index.php not visible)
            : $mod_rewrite_enabled = str_replace(str_replace('index.php', '', $_SERVER['SCRIPT_NAME']), '/', $_SERVER['REQUEST_URI'])
        );

        // Removes bad characters except ":" (colon), "~" (tilde), "/" (slash) and "." (period)
        self::$_developers['request'][0] = preg_replace('/[^a-zA-Z0-9:~\/\.\-\_]|:{2,}|\.{2,}/', '', self::$_developers['request'][0]);

        // Recording routes for io::$_developers
        $r = array(
            // Initially setting controller and action to empty
            'controller' => '', 'action' => '',
            // This [1] out empty parameters and [2] splits parameters on "/" marks
            'params' => array_filter(explode('/', (self::$_developers['request'][0]) ?: '')),
        );

        // Alternative routing
        if ($alternative) {

            // Set the alternative Controller, Action and then stop further processing
            return [$r['controller'] = $_SERVER['SCRIPT_NAME'], $r['action'] = $_SERVER['REQUEST_METHOD']];
        }

        /*
         * Iterates through parameters and checks for sub directories. Routing
         * will prefer Directories > Home Methods > Controllers in that order.
         */
        foreach ($r['params'] as $piece) {

            /*
             * Figuring out if the request path is a directory, file, or method
             * by process of elimination in (hopefully) the most effecient way.
             */
            if (is_dir(__DIR__.'/controllers/'.$r['controller'].$piece)) {
                $r['controller'] .= array_shift($r['params']).'/';
            }

            // Check if this $piece is a php file in one of the subdirs
            elseif (is_file(__DIR__.'/controllers/'.$r['controller'].$piece.'.php')) {
                // Found file stop processing
                break;
            } else {
                // Check to see if this is a method within a home.php file
                if (is_file(__DIR__.'/controllers/'.$r['controller'].'home.php')) {
                    // Set a temporary name
                    $name = $r['controller'].'home';

                    // Prevents processing files twice
                    if (!isset(self::$_developers['files']['controllers'][$name])) {

                        // This [1] Either (a) includes file or (b) exits on failure; [2] adds file tracking array
                        (include self::$_developers['files']['controllers'][$name] = __DIR__.'/controllers/'.$name.'.php') ?: self::helper('web')->error_404();
                    }

                    // self::_reflector() will determine how to access this object
                    $reflection = self::_reflector('controller', 'home', false);

                    // Pull the methods from the reflected class
                    foreach ($reflection->getMethods() as $methods) {

                        // Add to class tracking array
                        $classes[] = $methods->name;
                    }

                    // Check for a matching method in the reflected class
                    if (in_array(strtolower($_SERVER['REQUEST_METHOD'].'_'.$piece), $classes)) {

                        // Shift array to use the default home controller
                        array_unshift($r['params'], 'home');
                    }

                    // Alternative route check
                    elseif (!$alternative) {

                        // 404: Parameter makes no sense
                        self::helper('web')->error_404();
                    }

                    // Prevent errors
                    break;
                }
            }
        }

        // Setting the controller to run based on routing (default: home)
        $r['controller'] .= (array_shift($r['params']) ?: 'home');

        // Setting the method to run based on routing (default: index)
        $r['action'] = (array_shift($r['params']) ?: 'index');
    }

    /**
     * Serves as a wrapper and condenses code. This allows developers to use
     * abbreviations for loading controllers, models, helpers and libraries. It
     * also serves as an aliasing mechanism for helper and library methods and
     * optionally allows the lazy loading of helpers and libraries too.
     *
     * @param string
     * @param mixed
     *
     * @return object
     */
    public static function __callStatic($called, $args = array())
    {
        // Check available aliases
        if ( array_key_exists($called, self::$_internals['alias']) ) {

            // Use the alias to call the static class method with arguments
            return call_user_func_array([self::$_internals['alias'][$called], $called], $args);
        }

        // Iterate MVC and Library / Helper methods
        foreach (self::$_internals['methods'] as $method => $aliases) {

            // Check for valid aliases
            if (in_array($called, $aliases)) {

                // Send to factory for MCLH and send V to self::views()
                return self::_factory($aliases[0], array_shift($args), $args);
            }
        }

        // Iterate through Ornithopter.io extensions
        foreach (['helpers', 'libraries'] as $type) {

            // Check if this extension exists as a library or helper
            if ( in_array($called, self::$_developers['ext'][$type]) ) {

                // Shortcut for libraries and helpers
                return self::_factory($type, $called, $args);
            }
        }

        // Class or method could not be found in aliases or methods
        throw new \Exception('Call to ' . $called . '() could not be resolved.');
    }
}

// ------------------------------------------------------------------------------------------------

/**
 * The secondary class of Ornithopter.io is "route" as it is short and easy to
 * call in common usage, e.g., route::get('.*', function(){}, true);.
 *
 * This simple routing class should be able to handle very advanced routing as
 * it [1] allows custom REQUEST_METHOD's and [2] regex pattern matching. A few
 * examples have been provided below. This is Ornithopter.io alternative routing
 * for tying models, libraries, helpers and even controllers from external apps;
 * however this form of routing can be very useful for building RESTful APIs.
 *
 * @method route::get('.*', function(){}, false)
 * @method route::post('/[0-9]/.*', function(){}, true)
 * @method route::any('.*', function(){})
 * @method route::put('/[a-z]/.*', function(){})
 * @method route::delete('/user/delete/[0-9]/', function(){})
 * @method route::custom('.*', function(){})
 *
 * @return closure
 */
class route extends io
{
    /**
     * Checks if a $_GET variable exists.
     *
     * @param string
     *
     * @return bool
     */
    public static function has($var)
    {
        // Return $var existance as boolean
        return isset(io::route()['get'][$var]);
    }

    /**
     * Route matching.
     *
     * @param string
     * @param string
     *
     * @return bool
     */
    public static function _match($request, $route)
    {
        // Check REQUEST_METHOD method against route
        if ($request == 'ANY') {
            return true;
        }

        // Check route request against REQUEST_METHOD
        elseif ($request != $_SERVER['REQUEST_METHOD']) {
            return false;
        }

        // Update the internal variables for developers
        io::$_developers['request'] = explode('?', $_SERVER['REQUEST_URI']);

        // Removes bad characters except ":" (colon), "~" (tilde), "/" (slash) and "." (period)
        $url = (preg_replace('/[^a-zA-Z0-9:~\/\.\-\_]|:{2,}|\.{2,}/', '', io::$_developers['request'][0])) ?: '/';

        // Route matching; Checks [1] literal matches, then [2] Regex
        if ($route == $url or preg_match('#^'.$route.'$#', $url)) {

            // Add to internal routes tracking array
            return io::$_developers['route'][][$request] = $route;
        }

        // No pattern matches
        return false;
    }

    /**
     * Allows custom REQUEST_METHOD's instead of limiting developers to
     * standard HTTP request types by using a magic PHP function for routing.
     *
     * @param string
     * @param mixed
     */
    public static function __callStatic($type, $args = array())
    {
        // Configure on initialization
        if (!isset(io::$_developers['paths'])) {

            // Run initialization
            io::_init(true);
        }

        // Check route against self::match()
        if (self::_match(strtoupper($type), $args[0])) {

            // Closure
            $args[1]();
        }

        // Discontinue processing on TRUE
        (isset($args[2]) && $args[2]) ? exit() : 0;
    }
}
