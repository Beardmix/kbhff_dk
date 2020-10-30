<?php
/**
* @package janitor.shop
* Meant to allow local shop additions/overrides
*/

include_once("classes/shop/supershop.core.class.php");


class SuperShop extends SuperShopCore {

	/**
	*
	*/
	function __construct() {

		parent::__construct(get_class());

	}

	// register manual payment
	// also updates order state
	# /#controller#/registerPayment
	function registerPayment($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 1 && $this->validateList(array("payment_amount", "payment_method_id", "order_id", "transaction_id"))) {


			$order_id = $this->getProperty("order_id", "value");
			$transaction_id = $this->getProperty("transaction_id", "value");
			$payment_amount = $this->getProperty("payment_amount", "value");
			$payment_method_id = $this->getProperty("payment_method_id", "value");
			$receiving_user_id = $this->getProperty("receiving_user_id", "value");

			$order = $this->getOrders(array("order_id" => $order_id));

			if($order) {

				$query = new Query();

				$sql = "INSERT INTO ".$this->db_payments." SET order_id=$order_id, currency='".$order["currency"]."', payment_amount=$payment_amount, transaction_id='$transaction_id', payment_method_id=$payment_method_id";
				if($query->sql($sql)) {
					$payment_id = $query->lastInsertId();
					$this->validateOrder($order["id"]);

					global $page;

					$payment_method = $page->paymentMethods($payment_method_id);

					if($payment_method && $payment_method["name"] == "Cash") {

						$UC = new SuperUser();
						$department = $UC->getUserDepartment(["user_id" => $receiving_user_id]);
						
						include_once("classes/shop/tally.class.php");
						$TC = new Tally();

						$tally = $TC->getTally(["department_id" => $department["id"]]);

						if($tally) {

							$TC->addRegisteredCashPayment($tally["id"], $payment_id);
						}
						
					}

					$page->addLog("SuperShop->addPayment: order_id:$order_id, payment_method_id:$payment_method_id, payment_amount:$payment_amount");

					message()->addMessage("Payment added");
					return $payment_id;
				
				}
			}

		}
		message()->addMessage("Payment could not be added", array("type" => "error"));
		return false;
	}

	function getRegisteredCashOrder($payment_id) {

		$query = new Query();

		$sql = "SELECT * FROM ".$this->db_payments." as payments, ".$this->db_orders." as orders WHERE payments.order_id = orders.id AND payments.id = $payment_id";

		// print $sql."<br>\n";
		if($query->sql($sql)) {
			return $query->results();
		}

		return false;
	}

	function getMobilepayLink($amount, $mobilepay_id, $comment) {

		$mobilepay_link = "https://www.mobilepay.dk/erhverv/betalingslink/betalingslink-svar?"
			.$this->getPhonenumberText($mobilepay_id)
			.$this->getAmountText($amount)
			.$this->getCommentText($comment)
			.$this->getLockText(true);

		return $mobilepay_link;
	}

	private static function getPhonenumberText($phonenumber){
        if(!(is_string($phonenumber) && preg_match("/^[0-9]+$/", $phonenumber) === 1)){
            throw new InvalidArgumentException("Phone number should be a string containing only numbers");
        }

        return sprintf("phone=%s", $phonenumber);
    }

    private static function getAmountText($amount){
        if(is_null($amount))
            return "";
        elseif ($amount < 0)
            throw new InvalidArgumentException("Amount should be positive");
        //Mobilepay's QR code generator doesn't include a decimal point for integer amounts
        elseif (is_integer($amount))
            return sprintf("&amount=%d", $amount);
        else
            return sprintf("&amount=%.2f", $amount);
    }

    private static function getCommentText($comment){
        if(strlen($comment) > 25)
            throw new InvalidArgumentException("Comment must be at most 25 characters long");

        if($comment === "")
            return "";
        else
            return sprintf("&comment=%s", rawurlencode($comment));
    }

    private static function getLockText($lockCommentField){
        if($lockCommentField)
            return "&lock=1";
        else
            return "";
	}
	

	/**
	 * Remove cart items that belongs to a past pickupdate
	 *
	 * Run by cron job
	 * 
	 * @return boolean
	 */
	function removePastPickupdateCartItems($action) {

		if(count($action) == 1) {

			$query = new Query();

			$sql = "DELETE cart_items 
			FROM ".$this->db_cart_items." AS cart_items
			JOIN ".$this->db_pickupdate_cart_items." AS pickupdate_cart_items 
			ON cart_items.id = pickupdate_cart_items.cart_item_id 
			JOIN ".$this->db_pickupdates." AS pickupdates 
			ON pickupdates.id = pickupdate_cart_items.pickupdate_id
			WHERE pickupdates.pickupdate < CURDATE()"; 

			if($query->sql($sql)) {

				return true;
			}

		}

		return false;
	}

	/**
	 * Cancel orders that are unpaid on the deadline (1 week before the first coming pickup date)
	 * Run by cronjob
	 * 
	 * 
	 * @return void 
	 */
	function cancelUnpaidOrders($action) {

		if(count($action == 1)) {

			include_once("classes/shop/pickupdate.class.php");
			$PC = new Pickupdate();

			// get pickupdates that are less than a week from now
			$pickupdates = $PC->getPickupdates(["before" => date("Y-m-d", strtotime("+1 weeks"))]);

			foreach ($pickupdates as $pickupdate) {

				$order_items = $this->getPickupdateOrderItems($pickupdate["id"]);

				foreach ($order_items as $order_item) {

					$order = $this->getOrders(["id" => $order_item["order_id"]]);
					
					$this->cancelOrder(["cancelOrder", $order["id"], $order["user_id"]]);
				}
				
				message()->resetMessages();
			}

			return true;
		}

		return false;
	}

	function getCartPickupdates($_options = false) {

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_reference"             : $cart_reference                  = $_value; break;
				}
			}
		}
		
		$cart = $this->getCarts(["cart_reference" => $cart_reference]);
		
		if($cart && $cart["items"]) {

			$query = new Query();
			$query->checkDbExistence($this->db_pickupdates);
			$query->checkDbExistence($this->db_pickupdate_cart_items);

			$cart_id = $cart["id"];

			$sql = "SELECT DISTINCT pickupdates.* FROM ".$this->db_pickupdates." AS pickupdates, ".$this->db_pickupdate_cart_items." AS pickupdate_cart_items, ".$this->db_cart_items." AS cart_items WHERE cart_items.cart_id = $cart_id AND cart_items.id = pickupdate_cart_items.cart_item_id AND pickupdates.id = pickupdate_cart_items.pickupdate_id";
			if($query->sql($sql)) {
	
				$cart_pickupdates = $query->results();
	
				return $cart_pickupdates;
			}
		}


		return false;
	}
	
	function getCartPickupdateItems($pickupdate_id, $_options = false) {

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_reference"             : $cart_reference                  = $_value; break;
				}
			}
		}

		$query = new Query();
		$cart = $this->getCarts(["cart_reference" => $cart_reference]);

		if($cart && $cart["items"]) {

			$sql = "SELECT cart_items.* FROM ".$this->db_pickupdate_cart_items." AS pickupdate_cart_items, ".$this->db_cart_items." AS cart_items WHERE pickupdate_cart_items.pickupdate_id = $pickupdate_id AND cart_items.id = pickupdate_cart_items.cart_item_id AND cart_items.cart_id = ".$cart["id"];
			if($query->sql($sql)) {
				
				$cart_pickupdate_items = $query->results();
				
				return $cart_pickupdate_items;
				
			}
		}


		
		return false;
	}

	function getCartItemsWithoutPickupdate($_options = false) {

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_reference"             : $cart_reference                  = $_value; break;
				}
			}
		}

		$query = new Query();
		$cart = $this->getCarts(["cart_reference" => $cart_reference]);
		$cart_id = $cart["id"];

		if($cart && $cart["items"]) {

			$sql = "SELECT cart_items.* 
			FROM ".$this->db_cart_items." AS cart_items
			WHERE cart_items.id NOT IN (
				SELECT pickupdate_cart_items.cart_item_id 
				FROM ".$this->db_pickupdate_cart_items." AS pickupdate_cart_items 
				) 
			AND cart_items.cart_id = $cart_id";

			if($query->sql($sql)) {

				$cart_items_without_pickupdate = $query->results();

				return $cart_items_without_pickupdate;
			}
		}

		return false;
	}

	// Add item to cart
	# /janitor/admin/shop/addToCart/#cart_reference#/
	// Items and quantity in $_post
	
	/**
	 * ### Add item to cart
	 * Custom kbhff version also calls getExistingCartItem() to account for pickupdates
	 * 
	 * /janitor/admin/shop/addToCart/#cart_reference#/
	 *
	 * Values in $_POST
	 * - item_id (required)
	 * - quantity (required)
	 * - custom_price
	 * - custom_name
	 * 
	 * @param array $action
	 * @return array|false Cart object. False on error. 
	 */
	function addToCart($action) {

		if(count($action) > 1) {

			$cart_reference = $action[1];
			
			// get cart
			$cart = $this->getCarts(array("cart_reference" => $cart_reference));
			// print_r($cart);

			// get posted values to make them available for models
			$this->getPostedEntities();

			// cart exists and values are valid
			if($cart && $this->validateList(array("quantity", "item_id"))) {

				$query = new Query();
				$IC = new Items();

				$custom_name = $this->getProperty("custom_name", "value");
				$custom_price = $this->getProperty("custom_price", "value");
				$quantity = $this->getProperty("quantity", "value");
				$item_id = $this->getProperty("item_id", "value");
				$pickupdate_id = getPost("pickupdate_id", "value");
				$price = $this->getPrice($item_id);

				$item = $IC->getItem(array("id" => $item_id));

				// are there any items in cart already?
				if($cart["items"]) {

					// what kind of itemtype is being added
					// if it is a membership, then remove existing memberships from cart
					if($item["itemtype"] == "signupfee") {

						foreach($cart["items"] as $cart_item) {
							$existing_cart_item = $IC->getItem(array("id" => $cart_item["item_id"]));
							if($existing_cart_item["itemtype"] == "signupfee") {
								$cart = $this->deleteFromCart(array("deleteFromCart", $cart_reference, $cart_item["id"]));
							}
						}
					}
				}

				// item has a price (price can be zero)
				if ($price !== false) {
					
					// look in cart to see if the added item is already there
					// if added item already exists with a different custom_name or custom_price, create new line
					if ($custom_price !== false && $custom_name) {

						$existing_cart_item = $this->getCartItem($cart_reference, $item_id, ["custom_price" => $custom_price, "custom_name" => $custom_name]);
					}
					else if($custom_price !== false) {

						$existing_cart_item = $this->getCartItem($cart_reference, $item_id, ["custom_price" => $custom_price]);
					}
					else if($custom_name) {
						
						$existing_cart_item = $this->getCartItem($cart_reference, $item_id, ["custom_name" => $custom_name]);
					}
					else {
						
						$existing_cart_item = $this->getCartItem($cart_reference, $item_id);
					}

					if($existing_cart_item) {
						
						// check if same item_id with same pickupdate is already in cart
						$existing_cart_item = $this->getExistingCartItem($cart["id"], $item_id, $pickupdate_id);
					}
					

					// added item is already in cart
					if($existing_cart_item) {
						
						$existing_quantity = $existing_cart_item["quantity"];
						$new_quantity = intval($quantity) + intval($existing_quantity);
	
						// update item quantity
						$sql = "UPDATE ".$this->db_cart_items." SET quantity=$new_quantity WHERE id = ".$existing_cart_item["id"]." AND cart_id = ".$cart["id"];
	//					print $sql;
					}
					else {
						
						// insert new cart item
						$sql = "INSERT INTO ".$this->db_cart_items." SET cart_id=".$cart["id"].", item_id=$item_id, quantity=$quantity";

						if($custom_price !== false) {

							// use correct decimal seperator
							$custom_price = preg_replace("/,/", ".", $custom_price);

							$sql .= ", custom_price=$custom_price";
						}
						if($custom_name) {
							$sql .= ", custom_name='".$custom_name."'";
						}
						// print $sql;	
					}
	
					if($query->sql($sql)) {

						if($existing_cart_item) {
							$cart_item_id = $existing_cart_item["id"];
						}
						else {
							$cart_item_id = $query->lastInsertId();
							if($pickupdate_id) {
								$this->addPickupdateCartItem($pickupdate_id, $cart_item_id);
							}
						}
	
						// update modified at time
						$sql = "UPDATE ".$this->db_carts." SET modified_at=CURRENT_TIMESTAMP WHERE id = ".$cart["id"];
						$query->sql($sql);
	
						$cart = $this->getCarts(array("cart_id" => $cart["id"]));
	
						// add callback to addedToCart
						$model = $IC->typeObject($item["itemtype"]);
						if(method_exists($model, "addedToCart")) {
							$model->addedToCart($item, $cart);
						}
	
						message()->addMessage("Item added to cart");
						return $cart;
	
					}
				}
			}
		}

		message()->addMessage("Item could not be added to cart", array("type" => "error"));
		return false;
	}

}

?>