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
 * @version     2016.02.14
 */

 // ########################################################################################

/**
 * A class for creating user agents. Great for web crawlers or interfacing with
 * other APIs. An agent can be customized in many ways, including speed, security
 * and agent names. This is a base class for the Ornithopter.io web crawler.
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage	Libraries
 *
 * @method 		io::library('agent')->timeout();
 * @method 		io::library('agent')->secure();
 * @method 		io::library('agent')->agent_name();
 * @method 		io::library('agent')->agent_site();
 * @method 		io::library('agent')->agent_custom();
 * @method 		io::library('agent')->execute();
 * @method 		io::library('agent')->redirects();
 * @method 		io::library('agent')->get();
 * @method 		io::library('agent')->post();
 * @method 		io::library('agent')->put();
 * @method 		io::library('agent')->delete();
 */
namespace libraries;
class agent
{
	/**
	 * Internal class variables
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Initialize the agent class
	 *
	 * @return
	 */
	public function __construct()
	{
		// Initialize
		self::timeout();
		self::secure();

		// Configure user agent (name)
		$this->data['agent']['name'] = \io::helper('web')->proper();

		// Configure user agent (site or domain)
		$this->data['agent']['site'] = \io::helper('web')->site();

		// Initialize NULL paramters
		foreach (['headers', 'body', 'post', 'put', 'delete'] as $key)

			// Initialize with NULL data
			$this->data[$key] = null;

		// Initialize FALSE paramters
		foreach (['status', 'redirect'] as $key)

			// Initialize with FALSE data
			$this->data[$key] = null;

		// Register shortcut aliases using io::method();
		\io::alias('libraries\agent', get_class_methods(__CLASS__));
	}

	/**
	 * Creates a shortcut for io::agent()
	 *
	 * @return  object
	 */
	public static function agent()
	{
		// Shortcut for io::agent()
		return $this->instance;
	}

	/**
	 * Configure crawler timeout
	 *
	 * @param 	int
	 * @return 	void
	 */
	public function timeout( $timeout = 5000 )
	{
		// Configure crawler speed in milliseconds
		$this->data['timeout'] = $timeout;
	}

	/**
	 * Configure crawler SSL verification
	 *
	 * @param 	boolean
	 * @return 	void
	 */
	public function secure( $secure = true )
	{
		// Configure crawler speed in milliseconds
		$this->data['secure'] = $secure;
	}

	/**
	 * Configure the crawler user agent name
	 *
	 * @param 	string
	 * @return void
	 */
	public function agent_name( $name )
	{
		// Configure user agent (name)
		$this->data['agent']['name'] = $name;
	}

	/**
	 * Configure the crawler user agent site
	 *
	 * @param 	string
	 * @return 	void
	 */
	public function agent_site( $site )
	{
		// Configure user agent (site or domain)
		$this->data['agent']['site'] = $site;
	}

	/**
	 * Configure the crawler user agent site
	 *
	 * @param 	string
	 * @return 	void
	 */
	public function agent_custom( $custom )
	{
		// Configure a custom user agent
		$this->data['agent']['custom'] = $custom;
	}

	private function user_agent()
	{
		// Check for a custom user agent
		if ( isset($this->data['agent']['custom']) )

			// Use the custom agent
			return $this->data['agent']['custom'];

		// Use Ornithopter.io user agent generated based of site and domain
		return 'Mozilla/5.0 (compatible; ' . $this->data['agent']['name'] . '/1.0; +' . $this->data['agent']['site'] . ')';
	}

	/**
	 * Request Execution
	 *
	 * @param 	mixed
	 * @return 	mixed
	 */
	public function curl( $path = null, $history = false )
	{
		// Check if an action has been specified
		if ( is_null($path) )
		{
			// No destination
			return false;
		}

		// Redirect history
		if ( ! $history )

			// Remove the redirect history
			$this->data['redirects'] = false;

		// Configure the request
		curl_setopt_array(
			$curl = curl_init(), array(
				CURLOPT_URL            => $path,
				CURLOPT_HEADER         => true,
				CURLOPT_VERBOSE        => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT_MS     => $this->data['timeout'],
				CURLOPT_SSL_VERIFYPEER => $this->data['secure'],
				CURLOPT_USERAGENT      => self::user_agent()
			)
		);

		// Check for POST Data
		if ( ! is_null($this->data['post']) )
		{
			// Configure for a POST Request
			curl_setopt($curl, CURLOPT_POST, true);

			// Set the POST data
			curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data['post']);
		}

		// Check if this is a DELETE request
		if ( ! is_null($this->data['delete']) )
		{
			// Specialized custom DELETE headers added
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}

		// Check if this is a PUT request
		if ( ! is_null($this->data['put']) )
		{
			// Specialized custom PUT headers added
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');

			// Set the POST data
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->data['put']));
		}

		// Cleanup previous data
		$this->data['status']  = false;
		$this->data['headers'] = null;
		$this->data['body']    = null;

		// Send the request
		$request = curl_exec($curl);

		// Check for request errors
		if( ! $request = curl_exec($curl) )
		{
			// Zero out the status code
			$this->data['status'] = false;

			// Nullify the headers
			$this->data['headers'] = null;

			// Return the CURL error
			return $this->data['body'] = curl_error($curl);
		}

		// Start parsing the headers
		foreach (explode("\r\n", substr($request, 0, strpos($request, "\r\n\r\n"))) as $headerline)
		{
			// Explode each headerline into KEY and VALUE
			@list ($key, $value) = explode(': ', $headerline);

			// Save the response headers
			$this->data['headers'][$key] = $value;
		}

		// Standardize Header Location / Link for developers
		$this->data['headers']['Path'] = $path;

		// Set the header status code
		$this->data['headers']['Status'] = key($this->data['headers']);

		// Set the CURL status code
		$this->data['status'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		// Set the CURL body
		$this->data['body'] = substr($request, curl_getinfo($curl, CURLINFO_HEADER_SIZE));

		// Detect redirects and record redirection history
		if ( in_array($this->data['status'], [301, 302, 303, 307, 308]) )
		{
			// Record history
			$this->data['redirects'][] = array(
				'code' => $this->data['status'],
				'path' => $this->data['headers']['Path'],
				'status' => $this->data['headers']['Status'],
				'headers' => $this->data['headers'],
				'body' => $this->data['body']
			);

			// Reissue the request (keeping redirect history)
			self::curl( $this->data['headers']['Location'], true );
		}

		// Perform cleanup
		$this->data['put']    = null;
		$this->data['post']   = null;
		$this->data['delete'] = null;

		// Return the model object for chaining
		return $this;
	}

	/**
	 * Request Redirect History
	 *
	 * @return 	array
	 */
	public function redirects()
	{
		// Execute Immediately
		return $this->data['redirects'];
	}

	/**
	 * Request CURL Response Status
	 *
	 * @return 	int
	 */
	public function status()
	{
		// Execute Immediately
		return $this->data['status'];
	}

	/**
	 * Request CURL Response Headers
	 *
	 * @return 	mixed
	 */
	public function headers()
	{
		// Execute Immediately
		return $this->data['headers'];
	}

	/**
	 * Request CURL Response Body
	 *
	 * @return 	mixed
	 */
	public function body()
	{
		// Execute Immediately
		return $this->data['body'];
	}

	/**
	 * Wrapper for CURL for standard GET Requests
	 *
	 * @param 	string
	 * @return 	mixed
	 */
	public function get( $path )
	{
		// Execute Immediately
		return self::crawl( $path );
	}

	/**
	 * Wrapper for CURL for POST Requests
	 *
	 * @param 	string
	 * @param 	array
	 * @return 	mixed
	 */
	public function post( $path, $post = null )
	{
		// Check validity
		if ( ! is_array($post) )
		{
			// Trigger an Exception
			throw new \Exception('Crawler POST Request: $post is not an array()');
		}

		// Set the POST data
		$this->data['post'] = $post;

		// Execute Immediately
		return self::crawl( $path );
	}

	/**
	 * Wrapper for CURL for PUT Requests
	 *
	 * @param 	string
	 * @param 	array
	 * @return 	mixed
	 */
	public function put( $path, $put = null )
	{
		// Check validity
		if ( ! is_array($put) )
		{
			// Trigger an Exception
			throw new \Exception('Crawler PUT Request: $put is not an array()');
		}

		// Set the put data
		$this->data['put'] = $put;

		// Execute Immediately
		return self::crawl( $path );
	}

	/**
	 * Wrapper for CURL for DELETE Requests
	 *
	 * @param 	string
	 * @return 	mixed
	 */
	public function delete( $path )
	{
		// Set the delete parameter
		$this->data['delete'] = true;

		// Execute Immediately
		return self::crawl( $path );
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
			'curl'	=> ['crawl', 'execute']
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
