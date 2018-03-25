<?php

/*
 * This file provides a valid $CSRF form field for the application
 * to embed in HTML forms upon GET requests.
 * On POSTs it validates the token, which is stored
 * in the $_SESSION variable
 * 
 * Example usage:
 *   require_once('../other/csrf_handler.php');
 *   ...
 *   <form method="POST">
 *     <?php echo $CSRF; ?>
 *     <button type="submit" name="FooBar">
 *   </form>
 */

session_start();

// Generate new token for the session
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Check if the request is a POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	// Validate token
    if (hash_equals($_SESSION['token'], $_POST['token'])) {
         // Everything okay
    } else {
		// Token does not match: Redirect to self (GET)
		header('Location: '.$_SERVER['PHP_SELF']);
		die();
    }
}

$CSRF = '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';

?>