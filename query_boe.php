<?php
	require("connection.php");
	$file = fopen("php://memory", "w");
	$query = $_POST["query"];
	$headers = array("Voter ID", "First Name", "Middle Name", "Last Name", "Street #", "Street Name", "Apartment #", "City", "State", "Zip", "Zip4");
	$columns_selected = "voter_id, first_name, middle_name, last_name, street_no, street_name, apt_no, city, state, zip, zip4";
	//check if additional columns were checked for export
	if(isset($_POST["voter_status_col"])){
		$columns_selected .= ", voter_status";
		array_push($headers, "Voter Status");
	}
	if(isset($_POST["reason_col"])){
		$columns_selected .= ", reason";
		array_push($headers, "Reason");
	}
	if(isset($_POST["absentee_col"])){
		$columns_selected .= ", absentee";
		array_push($headers, "Absentee");
	}
	$query = str_replace("count(voter_id) as this_count", $columns_selected, $query);
	$query = str_replace("count(DISTINCT last_name, street_no, street_name, apt_no) as this_count", $columns_selected, $query);
	$result = mysqli_query($conn, $query);
	fputcsv($file, $headers);
	while($row = $result->fetch_assoc()){
		fputcsv($file, $row);
	}
	fseek($file, 0);
	header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="query.csv";');
    fpassthru($file);
?>