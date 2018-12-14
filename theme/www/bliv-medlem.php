<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$SC = new Shop();
$model = new User();


$page->bodyClass("signup");
$page->pageTitle("Bliv medlem");



if(is_array($action) && count($action)) {

	// /bliv-medlem/kvittering
	if($action[0] == "kvittering") {

		$page->page(array(
			"templates" => "signup/receipt.php"
		));
		exit();
	}

	// /bliv-medlem/addToCart (submitted from /bliv-medlem)
	else if($action[0] == "addToCart" && $page->validateCsrfToken()) {

		// Check if user is already a member
		$user_id = session()->value("user_id");
		if($user_id > 1) {

			$UC = new User();
			$membership = $UC->getMembership();
			if($membership && $membership["subscription_id"]) {

				header("Location: allerede-medlem");
				exit();

			}
		}

		// add membership to new or existing cart
		$cart = $SC->addToCart(array("addToCart"));
		// successful creation
		if($cart) {
			header("Location: tilmelding");
			exit();

		}
		// something went wrong
		else {
			message()->addMessage("Der skete en fejl! Prøv igen senere.", array("type" => "error"));
		}

	}

	// /bliv-medlem/save (submitted from /bliv-medlem/tilmelding)
	else if($action[0] == "save" && $page->validateCsrfToken()) {

		// create new user
		$user = $model->newUser(array("newUser"));

		// successful creation
		if(isset($user["user_id"])) {

			// redirect to leave POST state
			header("Location: verificer");
			exit();
		}

		// user exists
		else if(isset($user["status"]) && $user["status"] == "USER_EXISTS") {

			// redirect to leave post state
			message()->addMessage("Det ser ud til at du allerede er registreret som bruger. Prøv at log ind.", array("type" => "error"));

			header("Location: /login");
			exit();
		}
		// something went wrong
		else {
			message()->addMessage("Der skete en fejl under oprettelsen. Prøv igen.", array("type" => "error"));

			header("Location: tilmelding");
			exit();
		}

	}

	// bliv-medlem/confirm
	else if($action[0] == "confirm" && $page->validateCsrfToken()) {

		// Verify and enable user
		$result = $model->confirmUser($action);

		// user has already been verified
		if($result && isset($result["status"]) && $result["status"] == "USER_VERIFIED") {
			message()->addMessage("Du er allerede verificeret! Prøv at logge ind.", array("type" => "error"));
			$page->page(array(
				"templates" => "pages/kbhff-login.php"
			));
			exit();
		}

		// code is valid
		else if($result) {

			header("Location: bekraeft/til-betaling");
			exit();
		}

		// code is not valid
		else {
			message()->addMessage("Forkert verificeringskode. Prøv igen!", array("type" => "error"));
			$page->page(array(
				"templates" => "signup/verify.php"
			));
			exit();
		}

	}

	// /signup/confirm/email|mobile/#email|mobile#/#verification_code#
	else if($action[0] == "confirm" && count($action) == 3) {

		// session()->value("signup_type", $action[1]);
		// session()->value("signup_username", $action[2]);

		// Confirm user returns either true, false or an object
		$result = $model->confirmUser($action);

		// user han already been verified
		if($result && isset($result["status"]) && $result["status"] == "USER_VERIFIED") {
			message()->addMessage("Du er allerde verificeret. Pøv at logge ind.", array("type" => "error"));
			$page->page(array(
				"templates" => "pages/kbhff-login.php"
			));
			exit();
		}

		// code is valid
		else if($result) {

			header("Location: /bliv-medlem/bekraeft/til-betaling");
			exit();
		}
		// code is not valid
		else {
			// redirect to leave POST state
			header("Location: /bliv-medlem/bekraeft/fejl");
			exit();
		}
	}


	// THIS SECTION HAS NOT BEEN UPDATED YET
	// START OLD SECTION

	// /signup/confirm/email|mobile/#email|mobile#/#verification_code#
	else if($action[0] == "confirm" && count($action) == 4) {

		session()->value("signup_type", $action[1]);
		session()->value("signup_username", $action[2]);

		if($model->confirmUser($action)) {

			// redirect to leave POST state
			header("Location: /signup/confirm/receipt");
			exit();

		}
		else {

			// redirect to leave POST state
			header("Location: /signup/confirm/error");
			exit();

		}
		exit();
	}

	else if($action[0] == "confirm" && $action[1] == "receipt") {

		$page->page(array(
			"templates" => "signup/confirmed.php"
		));
		exit();
	}

	// post username, maillist_id and verification_token
	else if($action[0] == "unsubscribe" && $page->validateCsrfToken()) {

		// successful creation
		if($model->unsubscribeUserFromMaillist(["unsubscribe", "unsubscribeUserFromMaillist"])) {

			// redirect to leave POST state
			header("Location: /signup/unsubscribed");
			exit();

		}

		$page->page(array(
			"templates" => "signup/unsubscribe.php"
		));
		exit();

	}
	// /signup/unsubscribe/#maillist_id#/#username#/#verification_code#
	else if($action[0] == "unsubscribe") {

		$page->page(array(
			"templates" => "signup/unsubscribe.php"
		));
		exit();

	}
	// /signup/unsubscribed
	else if($action[0] == "unsubscribed") {

		$page->page(array(
			"templates" => "signup/unsubscribed.php"
		));
		exit();

	}

	// END OLD SECTION
	// BELOW THIS LINE IS NEW STUFF

	else if($action[0] == "bekraeft" && $action[1] == "til-betaling") {

		$order = $SC->newOrderFromCart(array("newOrderFromCart", $_COOKIE["cart_reference"]));
		if($order) {

			// redirect to leave POST state
			header("Location: /butik/betaling/".$order["order_no"]);
			exit();
		}

		else {
			message()->addMessage("Det ser ud til at der er sket en fejl.", array("type" => "error"));

			header("Location: /butik/kurv");
			exit();
		}
	}


	else if($action[0] == "bekraeft" && $action[1] == "fejl") {

		$page->page(array(
			"templates" => "signup/confirmation_failed.php"
		));
		exit();
	}


	//bliv-medlem/verficier
	else if($action[0] == "verificer") {

		$page->page(array(
			"templates" => "signup/verify.php"
		));
		exit();


	}

	// bliv-medlem/spring-over
	else if($action[0] == "spring-over") {

		$page->page([
			"templates" => "signup/verify-skip.php"
		]);
		exit();

	}


	// /bliv-medlem/tilmelding
	else if($action[0] == "tilmelding") {

		$page->page(array(
			"templates" => "signup/signup.php"
		));
		exit();

	}
	// /bliv-medlem/allerede-medlem
	else if($action[0] == "allerede-medlem") {

		$page->page(array(
			"templates" => "signup/already-member.php"
		));
		exit();

	}
	// view specific membership
	// /bliv-medlem/medlemsskaber/#sindex#
	else if(count($action) == 2 && $action[0] == "medlemskaber") {

		$page->page(array(
			"templates" => "signup/membership.php"
		));
		exit();
	}
}


// plain signup directly
// /bliv-medlem
$page->page(array(
	"templates" => "signup/signupfees.php"
));

?>
