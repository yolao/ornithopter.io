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
 *
 */

// ########################################################################################

/**
 * Ornithopter.io - Front Controller
 * ------------------------------------------------
 * All requests get routed through this file.
 */

include('./application/ornithopter.php');

/*
 * Looking for a way to implement universal rules for your site? You can place
 * routes in this file that will run across the entire application. Add your
 * alternative routes (see: sample.php) before and after io::ornithopter(); as
 * needed for your project. You can use this for custom routing, loading default
 * models or classes, templating engines, sessio management and so much more!
 */

// Pre application code
route::any('/*', function(){

	// Your code here
	io::library('page')->theme('_layout');
});

// Standard routing
io::ornithopter();

// Post application code
route::any('/*', function(){

	// Your code here
});

/**
 * Other routing types are possible based on any REQUEST_METHOD, url pattern,
 * or even exact route matching. You can add as many unique routes to this file
 * as you want. Just remember io::ornithopter() is the main framework routing.
 *
 * @method  route::get('/*', function(){ //code });
 * @method  route::post('/*', function(){ //code });
 * @method  route::any('/*', function(){ //code });
 * @method  route::put('/*', function(){ //code });
 * @method  route::delete('/*', function(){ //code });
 * @method  route::update('/*', function(){ //code });
 * @method  route::custom('/*', function(){ //code });
 */
