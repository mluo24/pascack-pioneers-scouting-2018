<?php
/*CONSTANTS*/

session_start();

define("SERVER", "localhost");
define("USERNAME", "root");
define("PASSWORD", "");
define("DATABASE", "Scouting2018");

const USERVALS = array("uid", "firstname", "lastname", "email", "username", "password", "team", "acl");

/*FUNCTIONS*/

function connect() {

	$conn = new mysqli(SERVER, USERNAME, PASSWORD, DATABASE);

	if ($conn->connect_error) die("There was a problem. Please contact Miranda or Larry to fix this problem. Here is the error message: Connect error: " . $conn->connect_error);
	
	return $conn;

}

function insert($table, $tablevals, $values) {

	// $tablevalsstring = "";
	// $datavalsstring = "";

	// for ($i = 0; $i < sizeof($tablevals); $i++) { 
	// 	$tablevalsstring .= $tablevals[$i] . ", ";
	// }
	// foreach ($values as $key => $value) {
	// 	if (gettype($value) == "string") $datavalsstring .= "\"" . $value . "\"" . ", ";
	// 	else $datavalsstring .= $value . ", ";
	// }

	// $tablevalsstring = substr($tablevalsstring, 0, strlen($tablevalsstring) - 2);
	// $datavalsstring = substr($datavalsstring, 0, strlen($datavalsstring) - 2);

	// $conn = connect();
	// $query = "INSERT INTO $table ($tablevalsstring) VALUES ($datavalsstring)";
	// $result = $conn->query($query);

	// if (!$result) {
	// 	echo "Error, ask Miranda or Larry for help: " . $conn->error;
	// }

	// return $result;

	$tablevalsstring = "";
	$typestring = "";
	$questionmarks = "";

	for ($i = 0; $i < count($tablevals); $i++) { 
		$tablevalsstring .= $tablevals[$i] . ", ";
		$questionmarks .= "?, ";
	}

	foreach ($values as $key => $value) {
		if (gettype($value) == "string") $typestring .= "s";
		else if (gettype($value) == "integer") $typestring .= "i";
		else if (gettype($value) == "double") $typestring .= "d";
		else $typestring .= "b";
	}

	$tablevalsstring = substr($tablevalsstring, 0, strlen($tablevalsstring) - 2);
	$questionmarks = substr($questionmarks, 0, strlen($questionmarks) - 2);

	$type = &$typestring;

	$params = arraytoref($values);

	$conn = connect();
	$sql = "INSERT INTO $table ($tablevalsstring) VALUES ($questionmarks)";
	$stmt = $conn->prepare($sql);
	if (!$stmt) echo "Error, ask Miranda or Larry for help: <br> $sql <br>" . $conn->error;

	call_user_func_array(array($stmt, "bind_param"), array_merge(array($type), $params));
	$stmt->execute();
	$stmt->close();

	return true;

}

function select($table, $tablevals, $options) {

	$tablevalsstring = "";

	// if (gettype($tablevals) == "array") {
		for ($i = 0; $i < count($tablevals); $i++) { 
			$tablevalsstring .= $tablevals[$i] . ", ";
		}
	// }

	$tablevalsstring = substr($tablevalsstring, 0, strlen($tablevalsstring) - 2);

	$conn = connect();
	$query = "SELECT $tablevalsstring FROM $table $options";
	$result = $conn->query($query);

	if (!$result) {
		echo "Error, ask Miranda or Larry for help: <br> $query <br>" . $conn->error;
	}

	$rows = $result->num_rows;

	$data = array();

	for ($i=0; $i < $rows; $i++) { 
		$result->data_seek($i);
		$data[] = $result->fetch_array(MYSQLI_ASSOC);
	}

	return $data;

}

function update($table, $tablevals, $newvals, $location) {

	$updatestring = "";

	$newvals = assoctonum($newvals);

	if (gettype($tablevals) == "array") {
		for ($i = 0; $i < count($tablevals); $i++) { 
			if (gettype($newvals[$i]) == "string") $newvals[$i] = "\"".$newvals[$i]."\"";
			$updatestring .= $tablevals[$i] . " = " . $newvals[$i] . ", ";
		}
	}
	else {
		if (gettype($newvals) == "string") $newvals = "\"$newvals\"";
		$updatestring = "$tablevals = $newvals";
	}

	$updatestring = substr($updatestring, 0, strlen($updatestring) - 2);

	$conn = connect();
	$query = "UPDATE $table SET $updatestring WHERE $location";

	$result = $conn->query($query);

	if (!$result) {
		echo "Error, ask Miranda or Larry for help: <br> $query <br>" . $conn->error;
	}

}

function delete($table, $location) {

	$conn = connect();
	$query = "DELETE FROM $table WHERE $location";
	$result = $conn->query($query);

	if (!$result) {
		echo "Error, ask Miranda or Larry for help: <br> $query <br>" . $conn->error;
	}

}

/*INCLUDES*/

require_once 'php/functions.php';
require_once 'php/user.php';
require_once 'php/ScoutingManager.php';
require_once 'php/Exception.php';
require_once 'php/PHPMailer.php';
require_once 'php/SMTP.php';

$manager = new ScoutingManager();

// $mail = new PHPMailer;
