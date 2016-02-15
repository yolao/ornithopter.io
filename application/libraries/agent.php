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
 * @method 		io::library('agent')->status();
 * @method 		io::library('agent')->path();
 * @method 		io::library('agent')->protocol();
 * @method 		io::library('agent')->domain();
 * @method 		io::library('agent')->headers();
 * @method 		io::library('agent')->body();
 * @method 		io::library('agent')->redirects();
 * @method 		io::library('agent')->details();
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
	 * Ignore duplicate CURL information
	 *
	 * @var array
	 */
	private static $ignore = array('url', 'content_type', 'http_code',
		'ssl_verify_result', 'redirect_count', 'redirect_url', 'certinfo');

	/**
	 * Internal class parameters for resetting
	 *
	 * @var array
	 */
	private $param = array(
		'pre'    => ['code', 'headers', 'protocol', 'domain', 'tld', 'root', 'body', 'details'],
		'post'   => ['post', 'put', 'delete'],
		'status' => ['status', 'redirect']
	);

	/**
	 * Non-exhaustive list of common second-level domain TLDs. Should you absolutely need better
	 * domain parsing consider using project: https://github.com/jeremykendall/php-domain-parser
	 *
	 * @var array
	 */
	private static $secondary = array('com', 'net', 'org', 'edu', 'gov', 'mil', 'int', 'rec', 'web',
		'nic', 'ltd', 'sch', 'soc', 'grp', 'asn', 'med', 'biz', 'gob', 'info', 'pro', 'nom');

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

		// Prepares parameters for new CURL request
		$this->resetter('pre', 'post', 'status');

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
	 * Main Request Execution
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

		// Remember the path
		$this->data['path'] = $path;

		// Redirect history
		if ( ! $history )

			// Remove the redirect history
			$this->data['redirects'] = false;

		// Configure the request
		curl_setopt_array(
			$this->data['curl'] = curl_init(), array(
				CURLOPT_URL            => $this->data['path'],
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
			curl_setopt($this->data['curl'], CURLOPT_POST, true);

			// Set the POST data
			curl_setopt($this->data['curl'], CURLOPT_POSTFIELDS, $this->data['post']);
		}

		// Check if this is a DELETE request
		if ( ! is_null($this->data['delete']) )
		{
			// Specialized custom DELETE headers added
			curl_setopt($this->data['curl'], CURLOPT_CUSTOMREQUEST, 'DELETE');
		}

		// Check if this is a PUT request
		if ( ! is_null($this->data['put']) )
		{
			// Specialized custom PUT headers added
			curl_setopt($this->data['curl'], CURLOPT_CUSTOMREQUEST, 'PUT');

			// Set the POST data
			curl_setopt($this->data['curl'], CURLOPT_POSTFIELDS, http_build_query($this->data['put']));
		}

		// Cleanup previous data and prepare for a new request
		$this->resetter('pre', 'status');

		// Send the request
		$this->data['request'] = curl_exec($this->data['curl']);

		// Check for request errors
		if( ! $this->data['request'] = curl_exec($this->data['curl']) )
		{
			// Zero out the status code
			$this->data['status'] = false;

			// Nullify the headers
			$this->data['headers'] = null;

			// Return the CURL error
			return $this->data['body'] = curl_error($this->data['curl']);
		}

		// Digest the CURL Request
		$this->process_request();

		// Detect redirects and record redirection history
		if ( isset($this->data['code']) AND in_array($this->data['code'], [301, 302, 303, 307, 308]) )
		{
			// Add to the redirect history
			$this->data['redirects'][] = array(
				'Status'  => $this->data['code'],
				'Path'    => $this->data['path'],
				'Protocol'=> $this->data['protocol'],
				'Root'    => $this->data['root'],
				'Domain'  => $this->data['domain'],
				'TLD'     => $this->data['tld'],
				'Headers' => $this->data['headers'],
				'Body'    => $this->data['body'],
				'Details' => $this->data['details']
			);

			// Reissue the request (keeping redirect history)
			self::curl( $this->data['headers']['Location'], true );
		}

		// Cleanup data and prepare for a new request
		$this->resetter('post');

		// Return and set the status code
		return $this->data['status'] = $this->data['code'];
	}

	/**
	 * CURL Request Response Status
	 *
	 * @return 	string
	 */
	public function status()
	{
		// Get the full HTTP Status
		return $this->data['code'];
	}

	/**
	 * Returns the path used in the CURL Request
	 *
	 * @return 	string
	 */
	public function path()
	{
		// Get the path called
		return $this->data['path'];
	}

	/**
	 * Returns the protocol used in the CURL Request
	 *
	 * @return 	string
	 */
	public function protocol()
	{
		// Get protocol used
		return $this->data['protocol'];
	}

	/**
	 * Returns the domain root from the CURL Request
	 *
	 * @return 	string
	 */
	public function root()
	{
		// Get the root domain name
		return $this->data['root'];
	}

	/**
	 * Returns the domain or sub-domain from the CURL Request
	 *
	 * @return 	string
	 */
	public function domain()
	{
		// Get the domain name (potentially a sub-domain)
		return $this->data['domain'];
	}

	/**
	 * Returns the domain TLD from the CURL Request
	 *
	 * @return 	string
	 */
	public function tld()
	{
		// Get the TLD of the domain name
		return $this->data['tld'];
	}

	/**
	 * CURL Request Response Headers
	 *
	 * @return 	mixed
	 */
	public function headers()
	{
		// Get the HTTP Headers
		return $this->data['headers'];
	}

	/**
	 * CURL Request Response Body
	 *
	 * @return 	mixed
	 */
	public function body()
	{
		// Get the CURL Response Body
		return $this->data['body'];
	}

	/**
	 * Request Redirect History
	 *
	 * @return 	array
	 */
	public function redirects()
	{
		// Get the redirects
		return $this->data['redirects'];
	}

	/**
	 * CURL Request Detailed Information
	 *
	 * @return 	mixed
	 */
	public function details()
	{
		// Get the full HTTP Status
		return $this->data['details'];
	}

	/**
	 * Wrapper for CURL for standard GET Requests
	 *
	 * @param 	string
	 * @return 	mixed
	 */
	public function get( $path )
	{
		// Execute CURL request
		return self::curl( $path );
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

		// Execute CURL request
		return self::curl( $path );
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

		// Execute CURL request
		return self::curl( $path );
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

		// Execute CURL request
		return self::curl( $path );
	}

	/**
	 * Separates logic from curl() and handles most of the variable processing
	 *
	 * @param 	mixed
	 * @param 	mixed
	 * @return 	void
	 */
	private function process_request()
	{
		// Start parsing the headers
		foreach (explode("\r\n", substr($this->data['request'], 0, strpos($this->data['request'], "\r\n\r\n"))) as $headerline)
		{
			// Explode each headerline into KEY and VALUE
			@list ($key, $value) = explode(': ', $headerline);

			// Save the response headers
			$this->data['headers'][$key] = $value;
		}

		// Standardize Header Location / Link for developers
		$this->data['headers']['Path'] = $this->data['path'];

		// Set the header status code
		$this->data['headers']['Status'] = key($this->data['headers']);

		// Set the CURL status code
		$this->data['code'] = curl_getinfo($this->data['curl'], CURLINFO_HTTP_CODE);

		// Set the CURL body
		$this->data['body'] = substr($this->data['request'], curl_getinfo($this->data['curl'], CURLINFO_HEADER_SIZE));

		// Iterate through information from CURL Request details
		foreach ($curlDetails = curl_getinfo($this->data['curl']) as $key => $cInfo)

			// Detect duplicate information
			if ( ! in_array($key, self::$ignore) )

				// Standardize array key formatting and add to CURL Request details
				$this->data['details'][ implode('-', array_map('ucwords',explode('_', str_replace('ip', 'IP', $key)))) ] = $cInfo;

		// Parse the $path domain information
		$domainArr = array_filter(explode('/', $curlDetails['url']));

		// Set the protocol used
		$this->data['protocol'] = strtolower(substr(array_shift($domainArr), 0, -1));

		// Set the domain name (might be a sub-domain)
		$this->data['domain'] = array_shift($domainArr);

		// Parse the domain and get the root
		$rootArr = explode('.', $this->data['domain']);

		// Set the domain name TLD
		$tldBackup = $this->data['tld'] = array_pop($rootArr);

		// Next piece is a wildcard (e.g., second-level TLD)
		$mixed = array_pop($rootArr);

		// Check for third-level domains and second-level TLDs
		if ( strlen($mixed) <= 2 OR in_array($mixed, self::$secondary) )
		{
			// Reset the domain name TLD using the secondary $mixed piece
			$this->data['tld'] = $mixed . '.' . $this->data['tld'];

			// Set the root using the second-level domain
			$this->data['root'] = array_pop($rootArr) . '.' . $this->data['tld'];

			// Exceptions for sites like Web.com
			if ( $this->data['domain'] == $this->data['root'] )
			{
				// Revert back to first TLD
				$this->data['tld'] = $tldBackup;

				// Reset the root
				$this->data['root'] = $mixed . '.' . $this->data['tld'];
			}

			// Stop processing
			return false;
		}

		// Seems to be a normal second level domain and TLD
		$this->data['root'] = $mixed . '.' . $this->data['tld'];
	}

	/**
	 * Resets parameters to defaults for next CURL Request
	 *
	 * @param 	mixed
	 * @return 	void
	 */
	private function resetter( ...$args )
	{
		// Params as keys
		$args = array_flip($args);

		// Parameter types for NULL resetting
		foreach (['pre', 'post'] as $arg)

			// Check which type
			if ( isset($args[$arg]) )

				// Iterate through these parameters
				foreach ($this->param[$arg] as $key)

					// Reset params
					$this->data[$key] = null;

		// Parameters for FALSE resetting
		if ( isset($args['status']) )

			// Iterate through these parameters
			foreach ($this->param['status'] as $key)

				// Reset params
				$this->data[$key] = false;
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
			'curl'	 => ['execute'],
			'status' => ['status_code', 'code', 'http_code']
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
