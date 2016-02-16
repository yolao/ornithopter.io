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
        $this->variable = "<h4>This variables was created before io::model('design.php')->hello(); ran!</h4>";

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
     * Accessing this method can be done by adding /session/ to your URL bar which
     * will then inform Ornithopter.io to route home::get_session() instead! If it
     * doesn't work be sure to check that mod_rewrite is enabled and working.
     */
    public function get_session()
    {
        io::model('demo')->session();
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
        echo '<small>This script ran in: '.$this->runtime.' seconds</small>';

        // Detect $_GET variables
        if (route::has('do_bcrypt')) {

            // Skips password hashing (by default) for demo purposes of showing route::has() feature
            echo '<h3><a href="./">Don\'t hash password?</a> Skipping hashing will load this page faster!</h3>';

        } elseif (!route::has('do_bcrypt')) {

            // Performs a secure password hash using the io::helper('security')->hash() method
            echo '<h3><a href="?do_bcrypt=true">Hash Password?</a> Will take a moment longer to load...</h3>';
        }
    }
}
