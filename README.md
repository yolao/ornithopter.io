# Ornithopter.io

[![Latest Stable Version](https://poser.pugx.org/olscore/ornithopter.io/v/stable)](https://packagist.org/packages/olscore/ornithopter.io)
[![Total Downloads](https://poser.pugx.org/olscore/ornithopter.io/downloads)](https://packagist.org/packages/olscore/ornithopter.io)
[![Latest Unstable Version](https://poser.pugx.org/olscore/ornithopter.io/v/unstable)](https://packagist.org/packages/olscore/ornithopter.io)
[![License](https://poser.pugx.org/olscore/ornithopter.io/license)](https://packagist.org/packages/olscore/ornithopter.io)

## High-speed, Minimalist, Simple MVC PHP 5.6+ Framework

Designed to be lightweight, fast and easy to use. Provides the bare essentials
for a modern website; e.g., simple request routing to controllers which can then
go on to invoke models or views as required. Framework seeks to organize external
vendor code, helpers and libraries into their own folders as well. Basically this
provides a simple structure for your website or web application which can be used
in standard routing mode or alternative routing mode.

[Ornithopter.io][0] has been used in high-frequency trading algorithms along with other
high-performance, mission-critical production environments. Ornithopter.io is designed
for speed first while remaining highly convenient for fast prototyping and is capable
of operating in production environments, so long as you are familiar with security and
best practices (depending on your application). This framework is lax and forgiving
while also not holding your hand as a developer and making security assumptions which
in turn can slow down application speeds. First and foremost; a minimalist framework.

# Getting Started

Ornithopter works by routing all requests through _index.php_ and translating those
requests to a controller. The main logic is in [application/ornithopter.php][4]. You
can also use the alternative routing method as seen in _[sample.php][5]_ if you want to
build an application outside the standard folder structure. You will find folders
are intuitively labeled; controllers, helpers, libraries, models, vendors and views
all within the main **application** folder.

### Quick Installation

Ornithopter.io will work out of the box if you download the zip and unpack the
contents. Just make sure you have **mod_rewrite enabled**. The included _.htaccess_ file
routes everything to _index.php_ already. Modify for your own purposes.

##### Using PHP Composer (command line)

    composer create-project olscore/ornithopter.io folder_name

### Structuring your website or web application

Your website or web application should be made up of files in the **controllers**,
**models** and **views** folders or alternatively routed files like the _samples.php_
file which may be useful for building RESTful API's or your own custom routing. Using
alternative routes still allows you access to the Ornithopter libraries, helpers, etc.

The framework comes with a demo controller, demo model and demo view to showcase the
MVC relationship behavior. I highly recommend copying and tinkering with a local copy. After
a few minutes, how Ornithopter works should be obvious.

# Routing

By default Ornithopter.io routes by {controller}/{action}/{param1}/{param2} and so
on and so forth. Printing the internal methods `io::help()` and `io::route()` should be
useful for those looking to get started with understanding Ornithopter.

The default controller is _home.php_ with the default action being **index** which
you can see in the demo application controller. Ornithopter will look for `before()` and
`after()` methods to run respectively according to routing. For example;

### //yoursite.com/project/list
 * Would route to the **project.php** controller
 * Would invoke the `before_list()` method if it exists
 * Would invoke the `get_list()` method for a HTTP GET request
 * Would invoke the `after_list()` method if it exists

You can see more information by printing `io::help()` or `io::route()`

# Basic Usage

##### Use (auto-load) any helper or library

    io::class()->method();

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

# Contributions, Support and Contact

First see code samples and inline documentation within helpers, libraries and
vendor classes. The best way to learn is to run it for yourself and tinker with
it a bit. Ask [@Olscore][2] on Twitter for help or [other ways to contact me][3].

Not seeking contributions, but [contributing.md][1] has details. This is a [MIT License][6]
only project; aiming to remain true to open source ideology. The official documentation is
planned for when the main developer (Olscore) has more time.

[0]: http://ornithopter.io
[1]: https://github.com/olscore/ornithopter.io/blob/master/CONTRIBUTING.md
[2]: https://twitter.com/Olscore
[3]: http://coreyolson.me/
[4]: https://github.com/olscore/ornithopter.io/blob/master/application/ornithopter.php
[5]: https://github.com/olscore/ornithopter.io/blob/master/sample.php
[6]: https://opensource.org/licenses/MIT
[7]: https://github.com/olscore/ornithopter.io/blob/master/LICENSE.md
