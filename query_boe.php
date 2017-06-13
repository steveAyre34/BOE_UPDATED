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
	$sql_count = "SELECT ";
	$array_counts = array();
	if(isset($_POST["household"])){
		$query .= " GROUP BY last_name, street_no, street_name, apt_no";
		$result = mysqli_query($conn, $query);
		$count = 1;
		while($row = $result->fetch_assoc()){
			$last_name = $row["last_name"];
			$street_no = $row["street_no"];
			$street_name = $row["street_name"];
			$apt_no = $row["apt_no"];
			$sql_count .= "sum(last_name = '$last_name' AND street_no = '$street_no' AND street_name = '$street_name' AND apt_no = '$apt_no') as count" . $count . ", ";
			if(fmod($count, 1000) == 0){
				$sql_count = rtrim($sql_count,", ");
				$sql_count .= " FROM $table_name";
				array_push($array_counts, $sql_count);
				$sql_count = "SELECT ";
			}
			$count++;
		}
		$sql_count = rtrim($sql_count,", ");
		$sql_count .= " FROM $table_name";
		array_push($array_counts, $sql_count);
		//echo $sql_count;
	}
	$result = mysqli_query($conn, $query);
	fputcsv($file, $headers);
	if(isset($_POST["household"])){
		$result_counts = mysqli_query($conn, $array_counts[0]);
		$row_counts = $result_counts->fetch_assoc();
		$count = 1;
		$array_index = 1;
		while($row = $result->fetch_assoc()){
			if($row_counts["count" . $count] > 1){
				$row["last_name"] = "The " . $row["last_name"] . " Family";
				$row["first_name"] = "";
				$row["middle_name"] = "";
				fputcsv($file, $row);
			}
			else{
				fputcsv($file, $row);
			}
			if(fmod($count, 1000) == 0){
				$result_counts = mysqli_query($conn, $array_counts[$array_index]);
				$row_counts = $result_counts->fetch_assoc();
				$array_index++;
			}
			$count++;
		}
	}
	else{
		while($row = $result->fetch_assoc()){
			fputcsv($file, $row);
		}
	}
	fseek($file, 0);
	header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="query.csv";');
    fpassthru($file);
?>