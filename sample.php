<?php

include './application/ornithopter.php';

/*
 * Sample: Routes true for any GET request
 *
 * @return void
 */
route::get('/sample.php', function () {

    // Ornithopter.io can be used outside of normal routing...
    io::library('session');

    // Load the page
    io::library('page')->theme('_layout');

    // Say hello
    echo '<h2>1: All matching <stron>GET</stron> Requests</h2>';

    // Say something else...
    echo '<p>This file exists outside of the /application/ folder (sample.php) ... You
        can use Ornithopter.io with or without standard routing. This might be useful if
        you want to build RESTful APIs. Or structure your application differently.</p>';

});

/*
 * Sample: Routes true for any POST request
 *
 * @return void
 */
route::post('.*', function () {

    // Say hello
    echo '<h2>2: All matching <stron>POST</stron> Requests</h2>';
});

/*
 * Sample: Routes true for any ANY request
 *
 * @return void
 */
route::any('.*', function () {

    // Say hello
    echo '<h2>3: Any matching HTTP Requests</h2>';

    // Uh... Say something
    echo '<p>Not much to say... But hopefully this helps you understand alternative routes!</p>';

    // Print a Go Home link
    echo '<a href="./">Go back to Ornithopter.io homepage</a>';

// Notice the "Stop Routing" true boolean here; Try changing to "false"
}, true);

/*
 * Sample: This function will not execute if the above functions routes
 *
 * @return void
 */
route::any('.*', function () {

    // Say hello
    echo '<h3>4: This will not show, because the previous route was TRUE</h3>';
});
