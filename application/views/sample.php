<style>
	strong { display: inline-block; width: 120px; }
</style>

<h1>Welcome to Ornithopter.io</h1>
<ul>
	<li>
		<!-- Notice how we echo the variable -->
		<strong>Created by</strong> <?php echo $name; ?>
	</li>
	<li>
		<!-- Now using shorthand PHP echo -->
		<strong>Born on:</strong> <?= $bday; ?>
	</li>
	<li>
		<!-- You can also access libraries, helpers and models within views -->
		<strong>Favorite Food: </strong> <?= io::library('session')->get('favorite_food'); ?>
	</li>
	<li>
		<!-- Remember you can call libraries, helpers and models by shortnames too -->
		<strong>Favorite Drink: </strong> <?= io::l('session')->get('favorite_drink'); ?>
	</li>
</ul>

<?
 /*
  * Notice how the $key => $variables transferred over into the view. The variables
  * from design.php are not avaialable, we only access what was passed to the view
  * via the array containing the $key => $value pairs. Another look at the view.
  */

/*
 *	View loaded from design.php
 *
	$page = io::view('sample', array(
		'name' 		=> $first . ' ' . $last,
		'bday' 		=> $dob,
		'bday_ago'	=> $birthday,
		'pwd' 		=> $password,
		'currently' => $now,
		'sessid' 	=> $session_id,
		'xmas_is' 	=> $christmas
	));

	Variables visible within this view;
		$name, $bday, $bday_ago, $pwd, $currently, $sessid, $xmas_ago

	Variables not visible within this view (but visible in design.php);
		$first, $last, $dob, $birthday, $password, $now, $session_id, $christmas

	Do you see how the scope of variables is contained within the model and view
	separately? This encapsulation is so you do not accidentally overwrite any
	important variables in while processing views, or data within the models.
*/?>

<h3>Did you know?</h3>
<ul>
	<li>
		<strong>Chicago Time:</strong> <?= date('h:i A - l, F d, Y ', $currently); ?>
	</li>
	<li>
		<strong>My B-day was:</strong> <?= $bday_ago; ?>
	</li>
	<li>
		<strong>Christmas is:</strong> <?= $xmas_is; ?>
	</li>
</ul>
<h3>Remember those other functions?</h3>
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
<h3><a href="sample.php">sample.php :: See how alternative routing works!</a></h3>

<h3><a href="/info">Ornithopter.io/info :: Show internal framework information</a></h3>
<p>
	Note: If you can not see the above page, you may want to check that mod_rewrite (see the .htaccess file) is enabled and working. Routing depends on it.
</p>
