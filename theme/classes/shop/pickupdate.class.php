
<?php

/**
 * Manages pickup dates.
 * 
 **/
class Pickupdate extends Model {

	/**
	* Initialization: set variable names and validation rules for Pickupdate model.
	*
	* @return void 
	*/
	function __construct() {

		// Construct Model class, passing the Pickupdate model as a parameter. 
		// The Model class is constructed *before* adding values to Pickupdate model to ensure Pickupdate overwrites the standard values of Model 
		parent::__construct(get_class());


		// Define the name of table in database
		$this->db = SITE_DB.".project_pickupdates";


		// pickup date
		$this->addToModel("pickupdate", array(
			// "type" => "date",
			"label" => "Pickup date",
			"required" => true,
			"hint_message" => "State the pickup date", 
			"error_message" => "You must enter a pickup date."
		));

		$this->addToModel("comment", array(
			"type" => "text",
			"label" => "Comment",
			"hint_message" => "Anything unusual? Note it here.",
			"error_message" => "Invalid text."
		));

		
	}
	
	/**
	 * Get list of pickupdates from database.
	 *
	 * @return array|false Pickupdate object. False on error. 
	 */
	function getPickupdates($_options = false) {

		$query = new Query();
		$query->checkDbExistence($this->db);

		// define default sorting order
		$order = "pickupdate ASC";
		$before = false;
		$after = false;
		

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "order"             : $order                  = $_value; break;
					case "after"             : $after                  = $_value; break;
					case "before"            : $before                 = $_value; break;
				}
			}
		}

		$sql = "SELECT * FROM ".$this->db;
		
		if($before && $after) {
			$sql .= " WHERE pickupdate < '$before' AND pickupdate >= '$after'";

		}
		elseif($after) {
			$sql .= " WHERE pickupdate >= '$after'";
		}
		elseif($before) {
			
			$sql .= " WHERE pickupdate < '$before'";
		}

		
		$sql .= " ORDER BY $order";


		if($query->sql($sql)) {
			return $query->results();
		}
		
		return false;
	}

	
	/**
	 * Saves the pickupdate to the database.
	 * 
	 * @param array $action REST parameters of current request
	 * @return array|false Pickupdate id. False on error.
	 */
	function savePickupdate($action) {
		
		$this->getPostedEntities();

		
		if(count($action) == 1 && $this->validateList(["pickupdate", "comment"])) {
			
			$query = new Query();
			$query->checkDbExistence($this->db);
			
			// Get posted values
			$pickupdate = $this->getProperty("pickupdate", "value");
	
			
			// Check if the pickupdate is already created (to avoid faulty double entries)  
			$sql = "SELECT * FROM ".$this->db." WHERE pickupdate='$pickupdate'";
			if(!$query->sql($sql)) {


				// enter the pickupdate into the database
				$sql = "INSERT INTO ".$this->db." SET pickupdate='$pickupdate'";
				
				// if successful, add message and return pickupdate id
				if($query->sql($sql)) {
					
					$pickupdate_id = $query->lastInsertId();

					// add the new pickupdate to all departments
					include_once("classes/system/department.class.php");
					$DC = new Department();

					// $departments = $DC->getDepartments();
					// foreach($departments as $department) {
						
					// 	$DC->addPickupdate(["addPickupdate", $department["id"], $pickupdate_id]);
					// }

					$DC->updatePickupdateDepartments(["updatePickupdateDepartments", $pickupdate_id]);
					
					message()->addMessage("Pickup date created");
					return ["item_id" => $pickupdate_id];
				}
			}
			else {
				message()->addMessage("This pickup date already exists.", ["type" => "error"]);
				return false;
			}

		}

		// something went wrong
		message()->addMessage("Could not create pickup date.", ["type" => "error"]);
		return false;
	}

	
	/**
	 * Get a single pickupdate from database.
	 *
	 * @param array|boolean $_options Associative array containing unsorted function parameters.
	 * 		$id				int			Pickupdate id
	 * 		$pickupdate 	string		Pickupdate (Y-m-d)
	 * 
	 * @return array|false Pickupdate item (via callback to Query->result(0))
	 */
	function getPickupdate($_options = false) {
		
		$query = new Query();
		$query->checkDbExistence($this->db);

		// Define default values
		$id = false;
		$pickupdate = false;
		
		// Search through $_options to find recognized parameters
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "id"                         : $id             = $_value; break;
					case "pickupdate"                 : $pickupdate     = $_value; break;
				}
			}
		}


		// Query database for pickupdate with specific id.
		if($id) {
			$sql = "SELECT * FROM ".$this->db." WHERE id = '$id'";
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}
		else if($pickupdate) {
			$sql = "SELECT * FROM ".$this->db." WHERE pickupdate = '$pickupdate'";
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}

		return false;
	}

	/**
	 * Update a single pickupdate.
	 *
	 * @param array $action REST parameters of current request
	 * @return array|false Updated Pickupdate data object
	 */
	function updatePickupdate($action) {

		// Get content of $_POST array which have been "quality-assured" by Janitor 
		$this->getPostedEntities();

		// Check that the number of REST parameters is as expected.
		if(count($action) == 2) {
			
			$id = $action[1];

			$old_pickupdate = $this->getPickupdate(["id" => $id])["pickupdate"] ?? false;
			$new_pickupdate = $this->getProperty("pickupdate", "value");


			$comment = $this->getProperty("comment", "value");


			$query = new Query();

			// base sql
			$sql = "UPDATE ".$this->db." SET modified_at=CURRENT_TIMESTAMP";

			if($new_pickupdate) {
				$sql .= ", pickupdate='$new_pickupdate'";
			}
			if($comment) {
				$sql .= ", comment = '$comment'";
			}
			
			$sql .= " WHERE id = '$id'";
			// debug($sql);

			// Check if the pickupdate is already created (to avoid faulty double entries)  
			if($new_pickupdate == $old_pickupdate || !$query->sql("SELECT * FROM ".$this->db." WHERE pickupdate='$new_pickupdate'")) {


				// if successful, add message and return the pickupdate data object
				if($query->sql($sql)) {
					message()->addMessage("Pickup date updated");
	
					global $page;
					$user_id = session()->value("user_id");
					$page->addLog("Pickupdate: pickupdate id:$id updated by user_id:$user_id; sql = '$sql'");
	
					return $this->getPickupdate(["id"=>$id]);
				}
				
			}
			else {
				message()->addMessage("This pickup date already exists.", ["type" => "error"]);
				return false;
			}


		}

		// something went wrong
		return false;
	}

	/**
	 * Delete a single pickupdate.
	 *
	 * @param array $action REST parameters
	 * @return boolean
	 */
	function deletePickupdate($action) {

		// Checks for unexpected number of parameters
		if(count($action) == 2) {
			
			$query = new Query();
			
			$pickupdate_id = $action[1];
			$pickupdate = $this->getPickupdate(["id" => $pickupdate_id]);

			$SC = new Shop();
			$pickupdate_order_items = $SC->getPickupdateOrderItems($pickupdate_id);
			
			$sql = "DELETE FROM ".$this->db." WHERE id = '$pickupdate_id'";
			if($query->sql($sql)) {
				message()->addMessage("Pickupdate deleted");

				if($pickupdate_order_items) {

					$order_item_links = [];
					foreach ($pickupdate_order_items as $order_item) {
						
						$order_item_links[] = SITE_URL."/janitor/order-item/edit/".$order_item["id"];
					}

					// send notification email to admin
					mailer()->send(array(
						"recipients" => ADMIN_EMAIL,
						"subject" => SITE_URL . " - ACTION NEEDED: Order items have been orphaned",
						"message" => "The pickupdate ".$pickupdate['pickupdate']." has been deleted from the system. This has caused ".count($pickupdate_order_items)." order items to lose their time and place of pickup. \n\nHere are links to each of the affected order items:\n\n".implode("\n", $order_item_links). ". \n\nFollow the links to assign a new department/pickupdate to each order item.",
						"tracking" => false
						// "template" => "system"
					));
				}

				global $page;
				$user_id = session()->value("user_id");
				$page->addLog("Pickupdate: pickupdate id:$pickupdate_id deleted by user_id:$user_id");

				return true;		
			}

		}
		message()->addMessage("Pickupdate could not be deleted.", array("type" => "error"));
		return false;
	}

}

?>