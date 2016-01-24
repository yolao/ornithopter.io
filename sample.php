<?php include './application/ornithopter.php';

/**
 * Sample: Routes true for any GET request
 *
 * @return void
 */
route::get('/sample.php', function(){

	// Ornithopter.io can be used outside of normal routing...
	io::library('session');

	// Print a Go Home link
	echo '<a href="/">Go back to Ornithopter.io homepage</a>';

	// Say hello
	echo '<h3>1: All matching <stron>GET</stron> Requests</h3>';

	// Show what session information has been recorded
	var_dump($_SESSION);

});

/**
 * Sample: Routes true for any POST request
 *
 * @return void
 */
route::post('.*', function(){

	// Say hello
	echo '<h3>2: All matching <stron>POST</stron> Requests</h3>';
});

/**
 * Sample: Routes true for any ANY request
 *
 * @return void
 */
route::any('.*', function(){

	// Say hello
	echo '<h3>3: Any matching HTTP Requests</h3>';

	// Print a Go Home link
	echo '<a href="/">Go back to Ornithopter.io homepage</a>';

// Notice the "Stop Routing" true boolean here; Try changing to "false"
}, true);


/**
 * Sample: This function will not execute if the above functions routes
 *
 * @return void
 */
route::any('.*', function(){

	// Say hello
	echo '<h3>4: This will not show, because the previous route was TRUE</h3>';
});
