<h2>Basic Usage</h2>
<p>
  This page is intended to help you understand how to use and access libraries and helpers. Spending a few
  minutes reviewing these files should get you up to speed pretty quickly and teahc you how to use Ornithopter.io ...
</p>
<blockquote>
  <h3>Please open files the following 3 files ... </h3>
  <ul>
    <li>
      <strong class="controller">application/controllers/home.php</strong> to see the <strong>Controller</strong> finding action: <strong class="action">get_ext()</strong>
    </li>
    <li>
      <strong class="action">application/models/demo.php</strong> to see the <strong>Model</strong> finding method: <strong class="action">extensions()</strong>
    </li>
    <li>
      <strong class="ugly">application/views/example.php</strong> to see the <strong>View</strong>
    </li>
  </ul>
  <small>
    <strong>Did you know?</strong> MVC stands for Model, View and Controller. Using models (and views) are
    optional. Technically you can use controllers and views alone. But it is good practice to put most of your
    business logic into models. That way your controllers are mostly used for routing request logic, that call
    on models to do the heavy lifting.
  </small>
</blockquote>
<p>
  All helpers and libraries can be accessed by one of two ways. It is usually a matter of preference between
  you and whoever you may be working with. It is probably best to pick one way or the other and not mix the
  types. Either way works, it's simply a matter of preference. Pick the one that you like best and stick with it
  throughout your project.
</p>
<blockquote>
  <ol>
    <li>
      Long, formal or explicit way ... <strong class="io">io::helper('class')->method();</strong>
    </li>
    <li>
      Short, informal or shorthand way ... <strong class="io">io::class()->method();</strong>
    </li>
  </ol>

</blockquote>

<h2>Real Examples of Using Helpers &amp; Libraries</h2>
<p>
  The following story showcases how to access variables from a model in a view. And provides various ways for
  accessing io:: from within the view as well (although not recommended). It is better practice to keep your logic
  contained to models or controllers and simply render out variables within your views. But the choice is yours.
</p>
<p>
  Anyways... here is a short story that shows basic usage of Ornithopter extensions.
</p>
<blockquote>
  <h3>Ornithopter.io was create by <?php echo $name; ?>.</h3>
  <p>
    He was born on <?= $bday; ?> which was approximately <?= $bday_ago; ?>. He currently lives in Chicago where
    the time is <?= date('h:i A'); ?> and the date is <?= date('l, F d, Y', $currently); ?>. Not many know this
    but his favorite food is <?= io::library('session')->get('favorite_food'); ?> (for the sake of this example,
    at least, haha) ... But seriously he does love <?= io::l('session')->get('favorite_drink'); ?>. And this is
    irrelevant, but Christmas is <?= $xmas_is; ?>.
  </p>
</blockquote>
<?= $alt; ?>
<h3>And by the way, here are two other pieces of information;</h3>
<ul>
    <li>
        <!-- This identifies the user -->
        <strong>Session ID:</strong> <?= $sessid; ?>
    </li>
    <li>
        <!-- We safely hashed a password for storage -->
        <strong>Password Hash:</strong> <?= $pwd; ?>
    </li>
</ul>
<?php
// Detect $_GET variables
if (route::has('do_bcrypt')) {

    // Skips password hashing (by default) for demo purposes of showing route::has() feature
    echo '<h3><a href="./ext">Don\'t hash password?</a> Skipping hashing will load this page faster!</h3>';

} elseif (!route::has('do_bcrypt')) {

    // Performs a secure password hash using the io::helper('security')->hash() method
    echo '<h3><a href="./ext?do_bcrypt=true">Hash Password?</a> Will take a moment longer to load...</h3>';
}

 /*
  * Notice how the $key => $variables transferred over into the view. The variables
  * from demo.php are not available, we only access what was passed to the view
  * via the array containing the $key => $value pairs. Another look at the code:
  */

/*
 *	View loaded from application/models/demo.php
 *
    $page = io::view('welcome', array(
        'name'       => $first.' '.$last,
        'bday'       => $dob,
        'bday_ago'   => $birthday,
        'pwd'        => $password,
        'currently'  => $now,
        'sessid'     => $session_id,
        'xmas_is'    => $christmas
    ));

    Variables visible within this view;
        $name, $bday, $bday_ago, $pwd, $currently, $sessid, $xmas_is

    Variables not visible within this view (but visible in demo.php);
        $first, $last, $dob, $birthday, $password, $now, $session_id, $christmas

    Do you see how the scope of variables is contained within the model and view
    separately? This encapsulation is so you do not accidentally overwrite any
    important variables in while processing views, or data within the models.
*/?>
