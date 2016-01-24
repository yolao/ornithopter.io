<?php
/**
 * Ornithopter.io
 * ------------------------------------------------
 * A minimalist, high-speed open source PHP 5.3+ framework
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
 * A FreshBooks API wrapper: http://developers.freshbooks.com/
 *
 * @package     Ornithopter.io
 * @subpackage  Vendors
 * @author      Corey Olson
 */
namespace vendors;
class freshbooks
{
	/**
	 * App configuration
	 *
	 * @var string
	 */
	private static $token = '';

	/**
	 * App configuration
	 *
	 * @var string
	 */
	private static $endpoint = 'https://{yoursite}.freshbooks.com/api/2.1/xml-in';

	/**
	 * Initialize FreshBooks API
	 *
	 * @var string
	 * @var array
	 */
	public function __construct( $method, $post )
	{
		// Create a new XML document
		$XMLRequest = new FreshbooksXMLRequest("1.0", "utf-8");

		// Build the post data into the document
		$XMLRequest->build(array(
			'request' => $post
		));

		// Prepare the API Call using cURL
		curl_setopt_array(
			$curl = curl_init(), array(
				CURLOPT_URL            => self::$endpoint,
				CURLOPT_USERPWD        => self::$token . ":X",
				CURLOPT_POSTFIELDS     => $XMLRequest->saveFreshbooksXML( $method ),
				CURLOPT_RETURNTRANSFER => 1
			)
		);

		// Send it off and load the response into an object
		$this->response = simplexml_load_string( curl_exec($curl) );

		// Close the connection
		curl_close($curl);
	}

	/**
	 * Access the returned API data
	 *
	 * @var 	string
	 * @var 	array
	 * @return 	object
	 */
	public function __get( $method )
	{
		return $this->response->{$method};
	}
}

// ------------------------------------------------------------------------------------------------

/**
 * Formats an XML document for the Freshbooks API. The above class require this
 * one below so they have been combined into one file for convenience as usual.
 *
 * @package     Ornithopter.io
 * @subpackage  Libraries
 * @author      Corey Olson
 */
class FreshbooksXMLRequest extends \DOMDocument
{
	/**
	 * Recursively call self and build XML document
	 *
	 * @var 	mixed
	 * @var 	mixed
	 * @return  object
	 */
	public function build( $args, \DOMElement $domElement = null )
	{
		// Check for text node
		if ( ! is_array($args) )
			return $domElement->appendChild($this->createTextNode($args));

		// Loop through arrays and be recursive
		foreach ( $args as $element => $mixed )
		{
			// Decide which element we are appending to
			$domObject = ${ is_null( $domElement ) ? 'this' : 'domElement' }->appendChild( $this->createElement($element) );

			// Recursive
			$this->build($mixed, $domObject);
		}

		// Return the object for object chaining
		return $this;
	}

	/**
	 * Add Freshbooks API Call
	 *
	 * @var 	mixed
	 * @var 	mixed
	 * @return  object
	 */
	public function saveFreshbooksXML( $method )
	{
		// Fix up the method properties; which API call are we performing
		return str_replace("<request>", '<request method="' . $method . '">', $this->saveXML());
	}
}
