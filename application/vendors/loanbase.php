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
 * A Loanbase API wrapper: https://loanbase.com/developers
 *
 * @package     Ornithopter.io
 * @subpackage  Vendors
 * @author      Corey Olson
 */
namespace vendors;
class loanbase
{
	/**
	 * Loanbase Static Reference
	 *
	 * @var string
	 */
	public static $api;

	/**
	 * Loanbase API Key
	 *
	 * @var string
	 */
	private $key = '';

	/**
	 * Loanbase REST API Endpoint URL
	 *
	 * @var string
	 */
	private $endpoint = 'https://api.loanbase.com/api';

	/**
	 * Loanbase Site URL
	 *
	 * @var string
	 */
	private $site = 'https://loanbase.com';

	/**
	 * Loanbase REST API Request Path URL
	 *
	 * @var string
	 */
	private $path = null;

	/**
	 * Loanbase CURL Response Headers
	 *
	 * @var string
	 */
	private $headers = null;

	/**
	 * Loanbase Request POST Data
	 *
	 * @var string
	 */
	private $post = null;

	/**
	 * Loanbase Request PUT flag
	 *
	 * @var string
	 */
	private $put = null;

	/**
	 * Loanbase DELETE Request
	 *
	 * @var string
	 */
	private $delete = null;

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
	public function __construct()
	{
		// Static reference
		self::$api = $this;

		// Configure internal data
		$this->data = array();
	}

	/**
	 * Loanbase Exchange Request Execution
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

		// Configure the CURL request
		curl_setopt_array(
			$curl = curl_init(), array(
				CURLOPT_URL => $this->endpoint . $this->path,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => true,
				CURLOPT_VERBOSE => true,
				CURLOPT_USERAGENT => 'localhost',
				CURLOPT_HTTPHEADER => array(
					'Accept: '.'application/vnd.blc.v1+json',
					'Authorization: '.'Bearer '.$this->key
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

		// Check if this is a PUT request
		if ( ! is_null($this->put) )
		{
			// Specialized custom PUT headers added
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');

			// Set the POST data
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->put));
		}

		// Send the request
		$request = curl_exec($curl);

		// Perform cleanup
		$this->put = null;
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
	 * Request: Loanbase API Response Headers
	 *
	 * @return void
	 */
	public function headers()
	{
		// Execute Immediately
		return $this->headers;
	}

	/**
	 * Request: List Loans
	 *
	 * @return json
	 */
	public function loan_list( $args = array() )
	{
		// Set the path
		$this->path = '/loans?limit=500';

		// Allowable arguments
		$allowed = array(
			'offset', 'country', 'type', 'term', 'frequency', 'status', 'trusted', 'social',
			'amountFrom', 'amountTo', 'reputationFrom', 'reputationTo', 'timeLeftFrom', 'timeLeftTo',
			'denomination', 'paymentStatus', 'fundedFrom', 'fundedTo', 'ratioFrom', 'ratioTo',
			'listingDateFrom', 'listingDateTo', 'expirationDateFrom', 'expirationDateTo',
			'repaidDateFrom', 'repaidDateTo', 'dueDateFrom', 'dueDateTo', 'votesFrom', 'votesTo', 'salary'
		);

		// Iterate through the arguments
		foreach ($args as $argument => $value)

			// Check if this argument is valud
			if ( in_array($argument, $allowed) )

				// Add to the Query string
				$this->path .= '&' . $argument . '=' . $value;


		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Loan Details
	 *
	 * @return json
	 */
	public function loan_details( $loanid )
	{
		// Set the path
		$this->path = '/loan/' . $loanid;

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Repay a Loan
	 *
	 * @return json
	 */
	public function loan_repay( $loanid )
	{
		// Set the path
		$this->path = '/loan/' . $loanid .'/repay';

		// Set the POST data
		$this->post = array();

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Get Amortization Schedule
	 *
	 * @return json
	 */
	public function loan_schedule( $loanid )
	{
		// Set the path
		$this->path = '/loan/' . $loanid .'/schedule';

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: List Investments in a Loan
	 *
	 * @return json
	 */
	public function invest_list( $loanid )
	{
		// Set the path
		$this->path = '/investments/' . $loanid;

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Investment Details
	 *
	 * @return json
	 */
	public function invest_details( $investmentid )
	{
		// Set the path
		$this->path = '/investment/' . $investmentid;

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Create an Investment
	 *
	 * @return json
	 */
	public function invest_create( $loanid, $amount, $rate )
	{
		// Set the path
		$this->path = '/investment/';

		// Set the Post data
		$this->post = array(
			'loan_id' 	=> $loanid,
			'amount' 	=> $amount,
			'rate' 		=> $rate
		);

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Modify an Investment
	 *
	 * @return json
	 */
	public function invest_modify( $investmentid, $loanid, $amount, $rate )
	{
		// Set the path
		$this->path = '/investment/' . $investmentid;

		// Set the Post data
		$this->put = array(
			'loan_id' 	=> $loanid,
			'amount' 	=> $amount,
			'rate' 		=> $rate
		);

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Request: Delete an Investment
	 *
	 * @return json
	 */
	public function invest_delete( $investmentid )
	{
		// Set the path
		$this->path = '/investment/' . $investmentid;

		// Set the DELETE flag
		$this->delete = true;

		// Execute Immediately
		return $this->execute();
	}

	/**
	 * Scrape: Scrape a page
	 * @param  string
	 *
	 * @return string
	 */
	private function scrape( $path )
	{
		// Page to scrape
		$html = file_get_contents( $this->site . $path );

		// Get the content within the body tags
		$html = $this->between($html, '<body>', '</body>');

		// Remove the script, svg and style tags from the html
		$html = preg_replace('#<(script|svg|style)(.*?)>(.*?)</(script|svg|style)>#is', '', $html);

		// Remove any html comments from the code
		$html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

		// Strip the whitespace from the content
		$html = preg_replace('/\s+/', ' ', $html);

		// Remove spaces between tags
		$html = str_replace('> <', '><', $html);

		// Correct some issues with spacing
		$html = str_replace('" ', '"', $html);

		// Trim the resulting html
		$html = trim($html);

		return $html;
	}

	/**
	 * Scrape: Scrape a loan page
	 * @param  int
	 *
	 * @return string
	 */
	public function scrape_loan( $id )
	{
		// Get this loan contents
		return $this->scrape('/loan/browse/lid/' . $id . '/');
	}

	/**
	 * Scrape: Scrape a user profile page
	 * @param  string
	 *
	 * @return string
	 */
	public function scrape_user( $id )
	{
		// Get this user profile
		return $this->scrape('/user/index/id/' . $id . '/');
	}

	/**
	 * Scrape: Scrape the loan listings
	 *
	 * @return string
	 */
	public function scrape_listings( $amount = 500 )
	{
		// Enforce maximum 500 listings
		$amount = ( $amount > 500 ) ? 500 : $amount ;

		// Get the top X current listings on Loanbase
		$listings = $this->scrape('/listings/perPage/' . $amount . '/show_filter');

		// Find all the loans
		preg_match_all('/\/loan\/browse\/lid\/[0-9]+\//', $listings, $loanArr);

		// Clean the array to just loan IDs
		foreach ($loanArr[0] as $key => $value)

			// Just return the loan ID
			$loanArr[$key] = (int) $this->between( $value, '/loan/browse/lid/', '/' );

		// Sort the array by loan ID
		sort($loanArr);

		// Unset the preg_match variable
		unset($loanArr[0]);

		// Return the listings
		return $loanArr;
	}

	/**
	 * Scrape: Scrape the members listings
	 *
	 * @return string
	 */
	public function scrape_members( $amount = 500 )
	{
		// Enforce maximum 500 listings
		$amount = ( $amount > 500 ) ? 500 : $amount ;

		// Get the top 500 current listings on Loanbase
		$listings = $this->scrape('/loan/verified-users/perPage/' . $amount);

		// Find all the users
		preg_match_all('/\/user\/index\/id\/[0-9]+\//', $listings, $userArr);

		// Clean the array to just user IDs
		foreach ($userArr[0] as $key => $value)

			// Just return the user ID
			$userArr[$key] = (int) $this->between( $value, '/user/index/id/', '/' );

		// Sort the array by user ID
		sort($userArr);

		// Unset the preg_match variable
		unset($userArr[0]);

		// Return the listings
		return $userArr;
	}

	public function parse_user( $id )
	{
		// Get the user from the database
		$html = $this->scrape_user($id);

		// Preformat the html for pipe parsing
		$pfhtml = str_replace('><', '>|<', $html);

		// Remove the ratings information
		$profileData = explode('<h2>Ratings</h2>', $pfhtml);

		// Strip the HTML tags from the string
		$stripped = strip_tags($profileData[0]);

		// Remove useless information
		$stripped = str_ireplace('General Info:', '', $stripped);

		// Condense pipes from multiple to single pipe
		$stripped = preg_replace('/\|{2,}/', '|', $stripped);

		// Remove excessive colon marks
		$stripped = str_replace(':|', ':', $stripped);

		// Explode on the pipes
		$pipeArr = explode('|', $stripped);

		// Not allowed array
		$nonAllowed = array('trusted_connections', 'social_connections', 'verification');

		// Cleaned data array for storage
		$dataArr = array(
			'user' => $this->between($html, '<a href="/user/index/id/' . $id . '/', '"'),
			'userid' => (int) $id,
			'followers' => (int) $this->between($html, '<em class="count-followers">', '</em>'),
			'social_facebook' => array('url' => null, 'connections' => 0),
			'social_google' => array('url' => null, 'connections' => 0),
			'social_linkedin' => array('url' => null, 'connections' => 0),
			'social_twitter' => array('url' => null, 'connections' => 0),
			'social_loanbase' => array('url' => null, 'likes' => 0, 'posts' => 0)
		);

		// Find all the loans
		preg_match_all('/[0-9]+ [a-zA-Z]+/', $stripped, $socialArr);

		// Correct the preg match
		$socialArr = $socialArr[0];

		// Iterate through Number Array
		foreach ($socialArr as $key => $connection) {

			// Explode each line on space
			$line = explode(' ', $connection);

			// Detect keywords
			if ( ! in_array($line[1], array('followers', 'friends', 'connections')) )

				// Unset this line item otherwise
				unset($socialArr[$key]);
		}

		// Reindex the social array
		$socialArr = array_values($socialArr);

		// Prefix for multiple key values
		$prefix = '';

		// Iterate through the piped array
		foreach ($pipeArr as $key => $value)

			// Detect key : value pairs
			if ( strpos($value, ':') )
			{
				// Explode on the colon
				$t = explode(':', $value);

				// Standardize the key to lowercase
				$newkey = str_replace(' ', '_', strtolower($t[0]));

				// Detect prefix changes
				if ( in_array($newkey, array('usd', 'btc', 'brl')) )

					// Add prefixes for further processing
					$prefix = $newkey . '_';

				else if ( $newkey == 'reputation' )
				{
					// Positive Reputation
					$dataArr['reputation_positive'] = (int) $this->between($value,'+',',');

					// Negative Reputation
					$dataArr['reputation_negative'] = (int) $this->between($value,', ',' )');

					// Reputation as Percentage
					$dataArr['reputation'] = (double) $this->between($value,'Reputation:','%');
				}
				// Keep processing otherwise
				else if ( ! in_array($newkey, $nonAllowed) )

					// For prefixed things separate values
					if ( stripos($t[1], '(') )
					{
						// Get the numerical value
						$dataArr[ $prefix . $newkey ] = (double) $this->between($t[1], ' ', ' ');

						// Count the amount of this item
						$dataArr[ $prefix . $newkey . '_count'] = (int) $this->between($t[1], '(', ')');
					}
					else
					{
						// Remove unwanted percentage signs
						$t[1] = str_replace(' %', '', $t[1]);

						// Remove unwanted Bitcoin signs
						$t[1] = str_replace('฿ ', '', $t[1]);

						// Remove unwanted other money signs
						$t[1] = str_replace('R$ ', '', $t[1]);

						// Remove unwanted dollar signs
						$t[1] = str_replace('$ ', '', $t[1]);

						// Remove unwanted dollar signs
						$t[1] = str_replace('$', '', $t[1]);

						// Detect interest values
						if ( stripos($newkey, 'interest') OR stripos($newkey, 'ratio') )

							// Add the data to the clean array
							$dataArr[ $prefix . $newkey ] = (double) $t[1];

						else
							// Add the data to the clean array
							$dataArr[ $prefix . $newkey ] = $t[1];
					}
			}

		// Data correction: Country
		$dataArr['country'] = ( $dataArr['country'] == 'City' ) ? null : $dataArr['country'] ;

		// Data correction: State
		$dataArr['state'] = ( $dataArr['state'] == '- - -' ) ? null : $dataArr['state'] ;

		// Data correction: City
		$dataArr['city'] = ( ! isset($dataArr['city']) ) ? null : $dataArr['city'] ;

		// Data correction: Annual Income
		$dataArr['annual_income'] = (double) $dataArr['annual_income'];

		// Data correction: Credit Limit
		$dataArr['credit_limit'] = (double) $dataArr['credit_limit'];

		// Data correction: Member Since
		@$dataArr['member_since'] = strtotime(explode(' ', $dataArr['member_since'])[0]);

		// Data correction: Same IP Addresses
		$dataArr['same_ip'] = (int) str_replace('users', '', $dataArr['same_ip']);

		// Data parse: Social: Facebook
		$dataArr['social_facebook']['url'] = (int) $this->between($html, 'https://www.facebook.com/app_scoped_user_id/', '/"');

		// Data parse: Social: Google
		$dataArr['social_google']['url'] = (float) $this->between($html, 'https://plus.google.com/', '"class="google"target="_blank">');

		// Data parse: Social: LinkedIn
		$dataArr['social_linkedin']['url'] = $this->between($html, '<a href="https://www.linkedin.com/in/', '"class="linkedin"target="_blank">LinkedIn</a>');

		// Data parse: Social: Twitter
		$dataArr['social_twitter']['url'] = (int) $this->between($html, 'https://twitter.com/intent/user?user_id=', '"');

		// Data parse: Social: Loanbase
		$dataArr['social_loanbase']['url'] = $this->between($html, 'http://forum.loanbase.com/index.php?members/', '"class="xenforo"target="_blank">Loanbase Forum</a>');

		// Iterate through the social networks
		foreach (array('facebook', 'google', 'linkedin', 'twitter') as $social) {

			// Check if this user has social accounts
			if ( $dataArr['social_' . $social]['url'] !== false )
			{
				// Correlate the social array with the social account connection counts
				@$dataArr['social_' . $social]['connections'] = (int) array_shift(explode(' ', array_shift($socialArr)));
			}
		}

		// Data Correction: LinkedIn
		$dataArr['social_linkedin']['url'] = ( strlen($dataArr['social_linkedin']['url']) > 50 ) ? 0 : $dataArr['social_linkedin']['url'];

		// Find the users likes
		preg_match_all('/Likes [0-9]+/', $stripped, $likes);

		// Data parse: Social: Loanbase
		$dataArr['social_loanbase']['likes'] = (int) str_replace('Likes ', '', $likes[0][0]);

		// Find the users likes
		preg_match_all('/Posts [0-9]+/', $stripped, $posts);

		// Data parse: Social: Loanbase
		$dataArr['social_loanbase']['posts'] = (int) str_replace('Posts ', '', $posts[0][0]);

		// Data Correction: Remove brl_show
		unset($dataArr['brl_show']);

		// Return the data
		return $dataArr;
	}

	/**
	 * Get string between two strings
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	function between( $str, $s, $e )
	{
		// Start trimming from
		$r = strpos($str, $s) + strlen($s);

		// Length to trim to
		$len = strpos($str, $e, $r) - $r;

		// Return the string
		return substr($str, $r, $len);
	}
}
