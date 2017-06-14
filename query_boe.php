<?php
	session_start();
	$table_name = $_SESSION["table_name"];
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

	if(isset($_POST["household"])){
		$result = mysqli_query($conn, $query);
		$array_unique_family_counts = array();
		$last_string = "";
		$value = 1;
		$index = -1;
		while($row = $result->fetch_assoc()){
			$unique_family_string = "";
			$unique_family_string .= $row["last_name"] . "_";
			$unique_family_string .= $row["street_no"] . "_";
			$unique_family_string .= $row["street_name"] . "_";
			$unique_family_string .= $row["apt_no"];
			if($unique_family_string == $last_string){
				$array_unique_family_counts[$index] = $array_unique_family_counts[$index] + 1;
			}
			else{
				array_push($array_unique_family_counts, 1);
				$index = $index + 1;
			}
			$last_string = $unique_family_string;
		}
		$query .= " GROUP BY last_name, street_no, street_name, apt_no";
		$query = str_replace("ORDER BY last_name, street_no, street_name, apt_no GROUP BY last_name, street_no, street_name, apt_no", "GROUP BY last_name, street_no, street_name, apt_no ORDER BY last_name, street_no, street_name, apt_no", $query);
		$result = mysqli_query($conn, $query);
		fputcsv($file, $headers);
		$counts_index = 0;
		while($row = $result->fetch_assoc()){
			if($array_unique_family_counts[$counts_index] > 1){
				$row["voter_id"] = "";
				$row["last_name"] = "The " . $row["last_name"] . " Family";
				$row["first_name"] = "";
				$row["middle_name"] = "";
				fputcsv($file, $row);
			}
			else{
				fputcsv($file, $row);
			}
			$counts_index++;
		}
	}
	else{
		$result = mysqli_query($conn, $query);
		fputcsv($file, $headers);
		while($row = $result->fetch_assoc()){
			fputcsv($file, $row);
		}
	}
	//die("stop");
	fseek($file, 0);
	header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="query.csv";');
    fpassthru($file);
?>