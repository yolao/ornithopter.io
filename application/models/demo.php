<?php

class demo
{
    public function hello()
    {
        echo io::view('welcome');
    }

    public function extensions()
    {
        /*
         * Ornithopter.io is very simple to use. Some sample controllers and
         * sample models (like this one) have been setup to help you understand
         * the basics of using the MVC framework and structuring projects.
         *
         * Access the framework by calling... io::method();
         */

        // My first name
        $first = 'Corey';

        // My last name
        $last = 'Olson';

        // My birthday is...
        $dob = 'November 14, 1987';

        // My birthday was a long time ago...
        $birthday = io::helper('time')->context(strtotime($dob));

        /*
         * You can check if a $_GET variable exists by using either
         * route::has('var') or the short syntax io::has('var')
         */

        // Hash a password or skip
        if (io::has('do_bcrypt')) {

            // A long time ago I used the password...
            $password = io::helper('security')->hash('yippie');
        } else {
            // Slows down the script runtime severely
            $password = 'Skipped secure password hashing (CPU intensive)';
        }

        // My home town is Chicago...
        date_default_timezone_set('America/Chicago');

        // The current time here is...
        $now = time();

        // Ornithopter.io has built in session management...
        io::library('session');

        // Load the session library on dynamic pages for users
        $session_id = io::library('session')->id();

        // Did you notice that?
        io::library('session')->set('favorite_food', 'pizza');

        // Chaining is allowed on most libraries and helpers (convenience)
        $session = io::library('session');

        // Making a reference; now I can type even less...
        $session->set('favorite_drink', 'coffee');

        // You can call libraries, helpers, models and controllers by shortnames
        $time = io::h('time');

        // Again I can now use the time helper class by referencing $time
        $christmas = $time->prefix('future')->postfix('future')->context(strtotime('December 25 '.date('Y')));

        // Actually you can call libraries and helpers like this too... (Works for any library or helper)
        $alt = io::html()->tag('blockquote', 'Alternative call to (any helper or library) via <strong>io::html()->tag();</strong>');

        // Now we can show some information with a view
        $page = io::view('example', array(
            'name'      => $first.' '.$last,
            'bday'      => $dob,
            'bday_ago'  => $birthday,
            'pwd'       => $password,
            'currently' => $now,
            'sessid'    => $session_id,
            'xmas_is'   => $christmas,
            'alt'       => $alt,
        ));

        /*
         * Noticed how we passed variables to the view? The array we passed to
         * the view will create the $key => $variables within the view for an
         * easy and effective templating system. Just echo the view to see!
         */

        echo $page;
    }

    public function framework()
    {
        /*
         * You can echo HTML and other information directly from  models or controllers
         * however it is usually better to create views for displaying model data.
         */

        // Session tracking
        io::library('session');

        // HTML Header
        echo '<h2>Internal Framework Information</h2>';
        echo '<p>This method may be useful during development. It contains a list of available helpers, libraries, paths to the various folders, routing information, files that have been loaded, as well as all the objects loaded by Ornithopter.io ...</p>';

        // Explain a little bit
        echo '<blockquote>Accessible by calling <strong class="io">io::info()</strong> within Ornithopter.io</blockquote>';

        // Like so...
        var_dump(io::info());
    }

    public function routing()
    {
        /*
         * You can echo HTML and other information directly from models or controllers
         * however it is usually better to create views for displaying model data.
         */

        // Session tracking
        io::library('session');

        // HTML Header
        echo '<h2>Routing Information</h2>';

        // Explain a little bit
        echo '<p>This method may be useful during development or even within your application. It provides easy access to important information related to routing, like which controller and action were routed. It also conveniently provides access to $_GET, $_POST, $_SESSION, $_COOKIE and parameters passed to Ornithopter during normal routing... </p>';
        echo '<blockquote>Accessible by calling <strong class="io">io::route()</strong> within Ornithopter.io</blockquote>';

        // Access the internals of Ornithopter.io easily
        var_dump(io::route());
    }

    public function session()
    {
        /*
         * You can echo HTML and other information directly from models or controllers
         * however it is usually better to create views for displaying model data.
         */

        // Session tracking
        io::library('session');

        // HTML Header
        echo '<h2>Session Information</h2>';
        echo '<p>This page is here to show you a little bit more about automatic session tracking within Ornithopter.io ... By default the framework logs each page view with a timestamp, tracks the landing and exit time, and calculates a duration of user time spent on site. This provides a useful way to see what the user did, when and in what order, built-in!</p>';

        // Show session data for demo purposes
        var_dump($_SESSION);
    }

    public function get_site($site)
    {
        /*
         * One of the nift features built-in to Ornithopter is a user agent (CURL wrapper)
         * with a farily decent web crawler. This does require CURL to be installed though.
         */

        // Explain a little bit
        echo '<h2>Crawling a website</h2>';
        echo '<p>A nifty feature built into Ornithopter is crawling websites and interacting with APIs. With the io::agent() library and io::crawler() you should have no problem interacting with other RESTful APIs or crawling other websites if necessary.';

        // Check if CURL is installed
        if ( extension_loaded('curl') ) {

            // Crawl a website
            var_dump(io::crawler()->crawl($site));
        }
    }
}
