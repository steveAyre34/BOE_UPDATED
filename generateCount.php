<?php
	require("connection.php");
	
	$data = $_POST["id"];
	//Build SQL QUERY
	$counts_for = $data[0];
	$zipcodes = $data[1];
	$age_min = $data[2];
	$age_max = $data[3];
	$sex = $data[4];
	$reg_date = $data[5];
	$election_info = $data[6];
	$all_or_any = $data[7];
	$years_voted = $data[8];
	$table_name = $_POST["table_name"];
	$sql_query = "";
	//what we've used so far
	$used_zip = FALSE;
	$used_age = FALSE;
	$used_sex = FALSE;
	$used_reg_date = FALSE;
	$used_election_info = FALSE;
	$used_years_voted = FALSE;
	if($counts_for == "individual"){
		$sql_query .= "SELECT count(voter_id) as this_count FROM $table_name";
	}
	else{
		$sql_query .= "SELECT count(DISTINCT last_name, street_no, street_name) as this_count FROM $table_name";
	}
	//add zipcodes to statement
	$count = 1;
	if($zipcodes[0] != "ignore"){
		for($i = 0; $i < count($zipcodes); $i++){
			$zip = $zipcodes[$i];
			if($count == 1){
				$sql_query .= " WHERE (zip = '$zip'";
				$used_zip = TRUE;
			}
			else if($count == count($zipcodes)){
				$sql_query .= " OR zip = '$zip')";
			}
			else{
				$sql_query .= " OR zip = '$zip'";
			}
			
			if($count == 1 && $count == count($zipcodes)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	//add age to statement	
	if($age_min != 0 && $age_max != 0){
		$min_year = date("Y") - $age_min;
		$max_year = date("Y") - $age_max;
		$today_month = date("m");
		$today_day = date("d");
		$min_age = $min_year . "-" . $today_month . "-" . $today_day;
		$max_age = $max_year . "-" . $today_month . "-" . $today_day;
		if($used_zip == FALSE)
		{
			$sql_query .= " WHERE (dob >= '$max_age' AND dob <= '$min_age')";
		}
		else{
			$sql_query .= " AND (dob >= '$max_age' AND dob <= '$min_age')";
		}
		$used_age = TRUE;
	}
	
	//add sex to statement
	if($sex != "both"){
		if()
	}
	$result = mysqli_query($conn, $sql_query) or die("error");
	$row = $result->fetch_assoc();
	$count = $row["this_count"];
	echo json_encode($count);
?>