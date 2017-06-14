<?php
	session_start();
	$table_name = $_SESSION["table_name"];
	$array_table_name = explode("_", $table_name);
	$table_name_verified = $array_table_name[0] . "_verified";
	require("connection.php");
	$file = fopen("php://memory", "w");
	$query = $_POST["query"];
	$headers = array("Voter ID", "First Name", "Middle Name", "Last Name", "Address", "City", "State", "Zip", "Zip4");
	$columns_selected = "$table_name.voter_id, $table_name.first_name, $table_name.middle_name, $table_name.last_name, $table_name_verified.address_1, $table_name_verified.city, $table_name_verified.state, $table_name_verified.zip, $table_name_verified.zip4";
	//check if additional columns were checked for export
	if(isset($_POST["voter_status_col"])){
		$columns_selected .= ", $table_name.voter_status";
		array_push($headers, "Voter Status");
	}
	if(isset($_POST["reason_col"])){
		$columns_selected .= ", $table_name.reason";
		array_push($headers, "Reason");
	}
	if(isset($_POST["absentee_col"])){
		$columns_selected .= ", $table_name.absentee";
		array_push($headers, "Absentee");
	}
	$query = str_replace("count($table_name.voter_id) as this_count", $columns_selected, $query);
	$query = str_replace("count(DISTINCT $table_name.last_name, $table_name_verified.address_1) as this_count", $columns_selected, $query);
	if(isset($_POST["household"])){
		$result = mysqli_query($conn, $query);
		$array_unique_family_counts = array();
		$last_string = "";
		$value = 1;
		$index = -1;
		while($row = $result->fetch_assoc()){
			$unique_family_string = "";
			$unique_family_string .= $row["last_name"] . "_";
			$unique_family_string .= $row["address_1"];
			if($unique_family_string == $last_string){
				$array_unique_family_counts[$index] = $array_unique_family_counts[$index] + 1;
			}
			else{
				array_push($array_unique_family_counts, 1);
				$index = $index + 1;
			}
			$last_string = $unique_family_string;
		}
		//die($query);
		$query .= " GROUP BY $table_name.last_name, $table_name_verified.address_1";
		$query = str_replace("ORDER BY $table_name.last_name, $table_name_verified.address_1 GROUP BY $table_name.last_name, $table_name_verified.address_1", "GROUP BY $table_name.last_name, $table_name_verified.address_1 ORDER BY $table_name.last_name, $table_name_verified.address_1", $query);
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