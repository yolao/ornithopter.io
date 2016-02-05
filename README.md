# Purpose

Designed to be lightweight, fast and easy to use. Provides the bare essentials
for a modern website; e.g., simple request routing to controllers which can then
go on to invoke models or views as required. Framework seeks to organize external
vendor code, helpers and libraries into their own folders as well. Basically this
provides a simple structure for your website or web application which can be used
in standard routing mode or alternative routing mode.

# Getting Started

Ornithopter works by routing all requests through index.php and translating those
requests to a controller. The main logic is in [application/ornithopter.php][4]. You
can also use the alternative routing method as seen in [sample.php][5] if you want to
build an application outside the standard folder structure. You will find folders
are intuitively labeled; controllers, helpers, libraries, models, vendors and views
all within the main **application** folder. Your website or web application should by
default by made up of files in the **controllers**, **models** and **views** folders or
alternatively routed files like the **samples.php** file which may be useful for building
RESTful API's or your own custom routing behavior. Using alternative routing still allows
you access to the Ornithopter libraries, helpers and vendors, but from your own routes.

The framework comes with a demo controller, demo model and demo view to showcase the
MVC relationship behavior. Highly recommend copying a tinkering with a local copy. After
a few minutes it should become apparent how Ornithopter works. 

# Routing

By default Ornithopter.io routes by {controller}/{action}/{param1}/{param2} and so
on and so forth. Printing the internal methods io::help() and io::route() should be
useful to developers looking to get started with understanding framework behavior.

The default controller is **home.php** with the default action being **index** which
you can see in the demo application controller. Ornithopter will look for before() and
after() methods to run respectively according to routing. For example;

//yoursite.com/project/list
 * Would route to the **project.php** controller
 * Would invoke the before_list() method if it exists
 * Would invoke the get_list() method for a HTTP GET request
 * Would invoke the after_list() method if it exists

You can see more information by printing io::help() or io::route()

# Basic Usage

##### Loading a Controller

	io::controller('home')->method();

##### Using a Model

	io::model('demo')->method();

##### Using a Helper

	io::helpers('arr')->method();

##### Using a Library

	io::library('benchmark')->method();

##### Displaying a View

	echo io::view('welcome');

# License

MIT License [OSS][6] or [Project file][7]

# Contributing

Not really seeking contributions, but see [contributing.md][1] for details.

# Support

First see code samples and inline documentation within helpers, libraries and
vendor classes. The best way to learn is to run it for yourself and tinker with
it a bit. Ask [@Olscore][2] on Twitter for help or [other ways to contact me][3].


[0]: http://ornithopter.io
[1]: https://github.com/olscore/ornithopter.io/blob/master/CONTRIBUTING.md
[2]: https://twitter.com/Olscore
[3]: http://coreyolson.me/
[4]: https://github.com/olscore/ornithopter.io/blob/master/application/ornithopter.php
[5]: https://github.com/olscore/ornithopter.io/blob/master/sample.php
[6]: https://opensource.org/licenses/MIT
[7]: https://github.com/olscore/ornithopter.io/blob/master/LICENSE.md
