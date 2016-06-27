<?php

class home
{
    /*
     * If it exists a before_method() will be executed prior to your main method
     * providing a per method __construct() like feature for many use cases!
     */
    public function before_index()
    {
        // Set a variable for this class
        $this->variable = "<h4>This variables was created before io::model('demo')->hello(); ran!</h4>";

        // Record script start time
        $this->runtime = microtime(true);
    }

    /*
     * The default routing for Ornithopter.io is the class Home with the method
     * index. For all subdirectories... home::index() is the starting point. So
     * you will need to create a class called "home" with a "get_index()" method
     * in each subdirectory where you want Ornithopter.io to route to. You can
     * nest directories, but performance takes a hit for each nesting level.
     */
    public function get_index()
    {
        io::model('demo')->hello();
    }

    /*
     * Accessing this method can be done by adding /ext/ to your URL bar which
     * will then inform Ornithopter.io to route home::get_ext() instead! If it
     * doesn't work be sure to check that mod_rewrite is enabled and working.
     */
    public function get_ext()
    {
        io::model('demo')->extensions();
    }

    /*
     * Accessing this method can be done by adding /session/ to your URL bar which
     * will then inform Ornithopter.io to route home::get_session() instead! If it
     * doesn't work be sure to check that mod_rewrite is enabled and working.
     */
    public function get_session()
    {
        io::model('demo')->session();
    }

    /*
     * Accessing this method can be done by adding /crawl/ to your URL bar which
     * will then inform Ornithopter.io to route home::get_crawl() instead! If it
     * doesn't work be sure to check that mod_rewrite is enabled and working.
     */
    public function get_crawl()
    {
        io::model('demo')->get_site('php.net');
    }

    /*
     * Accessing this method can be done by adding /info/ to your URL bar which
     * will then inform Ornithopter.io to route home::get_info() instead! If it
     * doesn't work be sure to check that mod_rewrite is enabled and working.
     */
    public function get_info()
    {
        io::model('demo')->framework();
    }

    /*
     * Accessing this method can be done by adding /route/ to your URL bar which
     * will then inform Ornithopter.io to route home::get_route() instead! If it
     * doesn't work be sure to check that mod_rewrite is enabled and working.
     */
    public function get_route()
    {
        io::model('demo')->routing();
    }

    /*
     * This route is blocked by a simple security mechanism. By using the Security
     * helper you can easily build a user authentication system.
     */
    public function get_secret()
    {
        // Require users to login in one line
        io::helper('security')->authenticate('_login');

        // Can you see this?
        echo io::helper('html')->tag('h3', 'If you can see this, you are logged in!');

        // Try signing out
        echo '<a href="/logout">Click here to sign out</a>';
    }

    /*
     * This method is only accessible by a POST request. Since this is only a demo
     * we are going to log the user in without any password checking.
     */
    public function post_login()
    {
        // Authenticate the user
        io::helper('security')->login();

        // Redirect the user back to the secret location
        io::helper('web')->redirect('/secret');
    }

    /*
     * This method is only accessible by a POST request. Since this is only a demo
     * we are going to log the user in without any password checking.
     */
    public function get_logout()
    {
        // Logout the user
        io::helper('security')->logout();

        // Redirect the user back to the secret location
        io::helper('web')->redirect('/secret');
    }

    /*
     * Notice the "get_" and "post_" prepended names for methods. This relates
     * to the REQUEST_METHOD that is used in routing. So home::post_index() will
     * only be executed if you correctly perform a HTTP POST to this route. The
     * standard HTTP method is a "GET" request for reguarl viewing of webpages.
     */
    public function post_index()
    {
        // You just posted something!
        var_dump($_POST);
    }

    /*
     * If it exists a after_method() will be executed after to your main method
     * providing a per method __destruct() feature; again for various uses.
     */
    public function after_index()
    {
        // Runs after home::get_index();
        echo $this->variable;

        // Calculate runtime
        $this->runtime = microtime(true) - $this->runtime;

        // Print out the runtime
        echo '<small>Ornithopter.io generated this page in: <strong>'.round($this->runtime,3).'</strong> seconds.';

        // This page is kinda long
        echo '<p><a href="#">Back to top</a></small></p><br /><br /><br /><br />';
    }
}
