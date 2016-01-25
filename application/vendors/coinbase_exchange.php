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
 * @version     2016.01.23
 */

// ########################################################################################

/**
 * A Coinbase Exchange API wrapper: https://docs.exchange.coinbase.com/
 *
 * @author      Corey Olson
 * @package     Ornithopter.io
 * @subpackage  Vendors
 */
namespace vendors;
class coinbase_exchange
{
	/**
	 * Coinbase Static Reference
	 *
	 * @var string
	 */
	public static $api;

	/**
	 * Coinbase API Key
	 *
	 * @var string
	 */
	private $key = '';

	/**
	 * Coinbase API Secret
	 *
	 * @var string
	 */
	private $secret = '';

	/**
	 * Coinbase API Passphrase
	 *
	 * @var string
	 */
	private $passphrase = '';

	/**
	 * Coinbase REST API Endpoint URL
	 *
	 * @var string
	 */
	private $endpoint = 'https://api.exchange.coinbase.com';

	/**
	 * Coinbase REST API Request Path URL
	 *
	 * @var string
	 */
	private $path = null;

	/**
	 * Coinbase CURL Response Headers
	 *
	 * @var string
	 */
	private $headers = null;

	/**
	 * Coinbase Request POST Data
	 *
	 * @var string
	 */
	private $post = null;

	/**
	 * Coinbase DELETE Request
	 *
	 * @var string
	 */
	private $delete = null;

	/**
	 * Coinbase Product
	 *
	 * @var string
	 */
	private $product = null;

	/**
	 * Coinbase API Timestamp
	 *
	 * @var string
	 */
	private $timestamp = 0;

	/**
	 * Internal Class Data Storage
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public function __construct( $pair = 'BTC-USD' )
	{
		// Set the product
		$this->product = $pair;

		// Static reference
		self::$api = $this;
	}

	/**
	 * Coinbase Signature
	 *
	 * @return string
	 */
	private function signature()
	{
		// Create the prehash string
		$prehash = $this->timestamp;

		// Add the HTTP request type (GET or POST or DELETE)
		if ( ! $this->delete )
			// Standard GET or POST request
			$prehash .= ( is_null($this->post) ) ? 'GET' : 'POST';

		else
			// Specialized DELETE request
			$prehash .= 'DELETE';

		// Add the REST API Request Path URL
		$prehash .= $this->path;

		// Add the POST data if it exists
		$prehash .= ( is_null($this->post) ) ? '' : $this->post;

		// Create and return the Coinbase Signature
		return base64_encode(hash_hmac("sha256", $prehash, base64_decode($this->secret), true));
	}

	/**
	 * Coinbase Exchange Request Execution
	 *
	 * @return json
	 */
	public function execute()
	{
		// Set the timestamp
		$this->timestamp = time();

		// Path not specified
		if ( is_null($this->path) )
			return false;

		// Configure the request
		curl_setopt_array(
			$curl = curl_init(), array(
				CURLOPT_URL => $this->endpoint . $this->path,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => true,
				CURLOPT_VERBOSE => true,
				CURLOPT_USERAGENT => 'localhost',
				CURLOPT_HTTPHEADER => array(
					'CB-ACCESS-KEY: '.$this->key,
					'CB-ACCESS-SIGN: '.$this->signature(),
					'CB-ACCESS-TIMESTAMP: '.$this->timestamp,
					'CB-ACCESS-PASSPHRASE: '.$this->passphrase,
					'Content-Type: application/json'
				)
			)
		);

		// Check for POST Data
		if ( ! is_null($this->post) )
		{
			// Configure for a POST Request
			curl_setopt($curl, CURLOPT_POST, true);

			// Set the POST data
			curl_setopt($curl, CURLOPT_POSTFIELDS, $this->post);
		}

		// Check if this is a DELETE request
		if ( $this->delete )

			// Specialized custom DELETE headers added
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');

		// Send the request
		$request = curl_exec($curl);

		// Perform cleanup
		$this->post = null;
		$this->path = null;
		$this->delete = null;

		// Start parsing the headers
		foreach (explode("\r\n", substr($request, 0, strpos($request, "\r\n\r\n"))) as $headerline)
		{
			// Explode each headerline into KEY and VALUE
			@list ($key, $value) = explode(': ', $headerline);

			// Save the response headers
			$this->headers[$key] = $value;
		}

		// Return the JSON object
		return json_decode(substr($request, curl_getinfo($curl, CURLINFO_HEADER_SIZE)));
	}

	/**
	 * Request: Coinbase API Response Headers
	 *
	 * @return void
	 */
	public function headers()
	{
		// Execute Immediately
		return $this->headers;
	}

	/**
	 * Request: Coinbase API Response Headers
	 *
	 * @return void
	 */
	public function pagination( $page = null )
	{
		// Check if this is a pagination request
		if ( is_null($page) )
			return false;

		// Navigate: Previous Page
		if ( $page == 'before' )

			// Add the query string parameters
			$this->path .= '&before=' . urlencode(coinbase::$api->headers()['cb-before']);

		// Navigate: Next page
		if ( $page == 'after' )

			// Add the query string parameters
			$this->path .= '&after=' . urlencode(coinbase::$api->headers()['cb-after']);
	}

	/**
	 * Request: Ledgers
	 *
	 * @return json
	 */
	public function ledgers()
	{
		// Execute Immediately
		return $this->data['ledger'];
	}

	/**
	 * Request: Wallets
	 *
	 * @return json
	 */
	public function wallets()
	{
		// Execute Immediately
		return $this->data['wallet'];
	}

	/**
	 * Request: Accounts
	 *
	 * @return json
	 */
	public function accounts()
	{
		// Set the path
		$this->path = '/accounts';

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Buy Order
	 *
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return json
	 */
	public function buy( $size, $price, $maker = false )
	{
		// Set the path
		$this->path = '/orders';

		// Set the Post data
		$this->post = json_encode(array(
			'side' 			=> 'buy',
			'size' 			=> $size,
			'price' 		=> $price,
			'product_id' 	=> $this->product,
			'post_only'		=> $maker
		));

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Sell Order
	 *
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return json
	 */
	public function sell( $size, $price, $maker = false )
	{
		// Set the path
		$this->path = '/orders';

		// Set the Post data
		$this->post = json_encode(array(
			'side' 			=> 'sell',
			'size' 			=> $size,
			'price' 		=> $price,
			'product_id' 	=> $this->product,
			'post_only'		=> $maker
		));

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Market Buy or Sell (Immediate / Taker) Order
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return json
	 */
	public function marketOrder( $side, $amount, $currency )
	{
		// Set the path
		$this->path = '/orders';

		if ( $currency == 'BTC' )
		{
			// Ensure minimum
			if ( $amount < 0.01 )
			{
				return false;
			}

			// Set the Post data
			$this->post = json_encode(array(
				'type' 			=> 'market',
				'size' 			=> $amount,
				'side' 			=> $side,
				'product_id' 	=> $this->product
			));
		}
		else if ( $currency == 'USD' )
		{
			// Ensure minimum
			if ( $amount < 0.01 )
			{
				return false;
			}

			// Set the Post data
			$this->post = json_encode(array(
				'type' 			=> 'market',
				'funds' 		=> $amount,
				'side' 			=> $side,
				'product_id' 	=> $this->product
			));
		}

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Account History
	 *
	 * @param  string
	 * @param  mixed
	 * @return json
	 */
	public function ledger( $id = 'btc', $page = null )
	{
		// Select account
		switch ( strtolower( $id ) ) {

			// Bitcoin Account
			case 'btc':
			case 'bitcoin':
				$id = $this->data['ledger']['btc'];
				break;

			// Bitcoin Account
			case 'usd':
			case 'fiat':
			case 'dollar':
				$id = $this->data['ledger']['usd'];
				break;

			default:
				$id = $id;
				break;
		}

		// Set the path
		$this->path = '/accounts/' . $id . '/ledger?limit=100';

		// Pagination
		$this->pagination( $page );

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Get Holds
	 *
	 * @param  string
	 * @return json
	 */
	public function holds( $id )
	{
		// Set the path
		$this->path = '/accounts/' . $id . '/holds';

		// Pagination
		$this->pagination( $page );

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Cancel Order
	 *
	 * @param  string
	 * @return json
	 */
	public function cancel( $id )
	{
		// Set the path
		$this->path = '/orders/' . $id;

		// Set DELETE request flag
		$this->delete = true;

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: List Orders
	 *
	 * @param  string
	 * @return json
	 */
	public function orders( $page = null )
	{
		// Set the path
		$this->path = '/orders?limit=100';

		// Pagination
		$this->pagination( $page );

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Get a Specic Order
	 *
	 * @param  string
	 * @return json
	 */
	public function order( $id )
	{
		// Set the path
		$this->path = '/orders/' . $id;

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: List Fills
	 *
	 * @param  string
	 * @return json
	 */
	public function fills( $page = null )
	{
		// Set the path
		$this->path = '/fills?limit=100';

		// Pagination
		$this->pagination( $page );

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Deposit Wrapper for Transfer()
	 *
	 * @param  string
	 * @param  string
	 * @return json
	 */
	public function deposit( $amount, $account = null )
	{
		return $this->transfer('deposit', $amount, $account);
	}

	/**
	 * Request: Withdraw Wrapper for Transfer()
	 *
	 * @param  string
	 * @param  string
	 * @return json
	 */
	public function withdraw( $amount, $account = null )
	{
		return $this->transfer('withdraw', $amount, $account);
	}

	/**
	 * Request: Transfer Funds
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return json
	 */
	private function transfer( $type, $amount, $account = null )
	{
		// Set the path
		$this->path = '/transfers';

		// Set the Post data
		$this->post = json_encode(array(
			'type' 					=> $type,
			'amount' 				=> $amount,
			'coinbase_account_id'	=> $account
		));

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Create Reports
	 *
	 * @param  string
	 * @param  int
	 * @param  int
	 * @param  string
	 * @return json
	 */
	public function report( $type = 'fills', $start, $end, $format = 'csv' )
	{
		// Set the path
		$this->path = '/reports';

		// Set the Post data
		$this->post = json_encode(array(
			'type' 			=> $type,
			'start_date' 	=> $start,
			'end_date' 		=> $end,
			'format'		=> $format
		));

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Report Status
	 *
	 * @param  string
	 * @return json
	 */
	public function status( $id )
	{
		// Set the path
		$this->path = '/reports/' . $id;

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Get Products
	 *
	 * @return json
	 */
	public function products()
	{
		// Set the path
		$this->path = '/products';

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Get Product Order Book
	 *
	 * @param  mixed
	 * @param  int
	 * @return json
	 */
	public function orderbook( $pair = null, $level = 2 )
	{
		// Detect specification
		if ( is_null($pair) )

			// Default pair
			$pair = $this->product;

		// Set the path
		$this->path = '/products/' . $pair . '/book?level=' . $level;

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Get Product Ticker
	 *
	 * @param  mixed
	 * @return json
	 */
	public function ticker( $pair = null )
	{
		// Detect specification
		if ( is_null($pair) )

			// Default pair
			$pair = $this->product;

		// Set the path
		$this->path = '/products/' . $pair . '/ticker';

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Get Trades
	 *
	 * @param  mixed
	 * @return json
	 */
	public function trades( $page = null, $pair = null )
	{
		// Detect specification
		if ( is_null($pair) )

			// Default pair
			$pair = $this->product;

		// Set the path
		$this->path = '/products/' . $pair . '/trades?limit=100';

		// Pagination
		$this->pagination( $page );

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Get Historic Rates
	 *
	 * @param  int
	 * @param  int
	 * @param  int
	 * @param  mixed
	 * @return json
	 */
	public function rates( $start, $end, $granularity = 60, $pair = null )
	{
		// Detect specification
		if ( is_null($pair) )

			// Default pair
			$pair = $this->product;

		// Format Start Date
		$start = date('c', $start);

		// Format End Date
		$end = date('c', $end);

		// Set the path
		$this->path = '/products/' . $pair . '/candles?start=' . $start . '&end=' . $end . '&granularity=' . $granularity;

		// Execute query
		$exArr = $this->execute();

		// Check query data
		if ( ! is_array($exArr) )

			// Return the error message
			return $exArr;

		// Make the array more informative
		foreach ($rates = $exArr as $interval => $arr)

			// Create a multi-array
			$rates[$interval] = array(
				'time' 		=> $arr[0],
				'low' 		=> $arr[1],
				'high' 		=> $arr[2],
				'open' 		=> $arr[3],
				'close' 	=> $arr[4],
				'volume' 	=> $arr[5]
			);

		// Put the historic rates in chronological order
		$rates = array_reverse($rates);

		// Return the informative array
		return $rates;
	}

	/**
	 * Request: Get 24-hour Statistics
	 *
	 * @param  string
	 * @return json
	 */
	public function stats( $pair = null )
	{
		// Detect specification
		if ( is_null($pair) )

			// Default pair
			$pair = $this->product;

		// Set the path
		$this->path = '/products/' . $pair . '/stats';

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Get Currencies
	 *
	 * @return json
	 */
	public function currencies()
	{
		// Set the path
		$this->path = '/currencies';

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: API Server Time
	 *
	 * @return json
	 */
	public function time()
	{
		// Set the path
		$this->path = '/time';

		// Execute Immediately
		return $this->execute();
	}
}
