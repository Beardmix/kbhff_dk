<?php
/**
* Foodnet platform
* Copyright (C) 2018  Københavns Fødevarefællesskab and think.dk
*
* Københavns Fødevarefællesskab
* KPH-Projects
* Enghavevej 80 C, 3. sal
* 2450 København SV
* Denmark
* mail: bestyrelse@kbhff.dk
*
* think.dk
* Æbeløgade 4
* 2100 København Ø
* Denmark
* mail: start@think.dk
*	
* This source code is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This source code is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this source code.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @package janitor.users
* This file contains simple user extensions
* Meant to allow local user additions/overrides
*/

/**
* User customization class
*/
class User extends UserCore {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());

		$this->addToModel("reset-token", array(
			"type" => "string", 
			"label" => "Kode", 
			"required" => true, 
			"pattern" => "^[0-9A-Za-z]{24}$", 
			"hint_message" => "Din verificerings kode", 
			"error_message" => "Ugyldig kode. Kunne der være mellemrum i enden af din indtastede kode?"
		));
		$this->addToModel("department_id", array(
			"type" => "string", 
			"label" => "Afdeling", 
			"required" => true, 
			"hint_message" => "Vælg en afdeling", 
			"error_message" => "Du skal vælge en afdeling."
		));

	}

	function updateDepartment($action) {
		
		
	}

	function validateCode($action) {
		// get posted variables
		$this->getPostedEntities();
		$token = $this->getProperty("reset-token", "value");

		// correct information available
		if(count($action) == 1 && $this->checkResetToken($token)) {
			return $token;
		}
		return false;
	}
	/**
	 * Get the current user, including her associated department.
	 * 
	 * @return array The user object, with the department object appended as a new property.  
	 */
	
	 function getKbhffUser(){
		$user = $this->getUser();
		$user["department"] = $this->getUserDepartment();
		// print_r($user);

		return $user;
	}

	/**
	 * Get the current user's associated department.
	 *
	 * @param int $user_id
	 * @return array|false The department object, or false if the current user isn't associated with a department. 
	 */
	function getUserDepartment(){
		
		$user_id = session()->value("user_id");

		//Query current user ID i user_departments and get the associated department ID 
		$query = new Query();
		$sql = "SELECT department_id FROM ".SITE_DB.".user_department WHERE user_id = $user_id";

		if($query->sql($sql)) {
			$department_id = $query->result(0,"department_id");
			// print_r ($department_id);
			
			//Use getDepartment to find the department with the specified department ID.
			include_once("classes/system/department.class.php");
			
			$DC = new Department();
			$department = $DC->getDepartment(["id"=>$department_id]);
			
			return $department;
		}

		return false;

	}

	/**
	 * Update or set the current user's associated department.
	 *
	 * @param array $action REST parameters of current request
	 * @return boolean
	 */
	function updateUserDepartment($action){
		// Get content of $_POST array that have been "quality-assured" by Janitor 
		$this->getPostedEntities();

		print_r ($action);
		// Check that the number of REST parameters is as expected and that the listed entries are valid.
		if(count($action) == 1 && $this->validateList(array("department_id"))) {
			
			$user = $this->getKbhffUser();
			$user_id = $user["id"];
			$department_id = $this->getProperty("department_id", "value");
			
			$query = new Query();

			$query->checkDbExistence(SITE_DB.".user_department");

			
			
			//Check if the user is associated with a department and adjust query accordingly
			if ($user["department"]) {
				//Update department
				$sql = "UPDATE ".SITE_DB.".user_department SET department_id = $department_id WHERE user_id = $user_id";
				
				if($query->sql($sql)) {
					message()->addMessage("Department updated");
					return true;
				} 
			}
			else {
				// Set department
				$sql = "INSERT INTO ".SITE_DB.".user_department SET department_id = $department_id, user_id = $user_id";
				
				if($query->sql($sql)) {
					message()->addMessage("Department assigned");
					return true;
				} 
			}	

		}

		return false;

	}

	/**
	 * Remove accept of terms – for testing purposes
	 *
	 * @return boolean
	 */
	function unacceptTerms() {

		$user_id = session()->value("user_id");

		$query = new Query();

		$sql = "DELETE FROM ".SITE_DB.".user_log_agreements WHERE user_id = $user_id";

		if($query->sql($sql)) {	
			return true;
		}

		return false;


		
	}
}

?>