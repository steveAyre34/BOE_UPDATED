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
		if($used_age == FALSE && $used_zip == FALSE){
			$sql_query .= " WHERE sex = '$sex'";
		}
		else{
			$sql_query .= " AND sex = '$sex'";
		}
		
		$used_sex = TRUE;
	}
	
	//add registration to statement
	if($reg_date != ""){
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE){
			$sql_query .= " WHERE reg_datetime >= '$reg_date'";
		}
		else{
			$sql_query .= " AND reg_datetime >= '$reg_date'";
		}
		$used_reg_date = TRUE;
	}
	
	//add election info to statement
	if($election_info[0] != "all" && $years_voted[0] == "DN"){
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE){
			$count = 1;
			for($i = 1; $i <= 12; $i++){
					for($ii = 0; $ii < count($election_info); $ii++){
						$history = "history" . $i;
						$info = $election_info[$ii];
						if($count == 1){
							$sql_query .= " WHERE ($history LIKE '%{$info}%'";
						}
						else{
							$sql_query .= " OR $history LIKE '%{$info}%'";
						}
						$count++;
					}
			}
			$sql_query .= ")";
		}
		else{
			$count = 1;
			for($i = 1; $i <= 12; $i++){
					for($ii = 0; $ii < count($election_info); $ii++){
						$history = "history" . $i;
						$info = $election_info[$ii];
						if($count == 1){
							$sql_query .= " AND ($history LIKE '%{$info}%'";
						}
		                else{
							$sql_query .= " OR $history LIKE '%{$info}%'";
						}
						$count++;
					}
			}
			$sql_query .= ")";
		}
	}
	else if($election_info[0] != "all" && $years_voted[0] == "U"){
		if($all_or_any == "all"){
			if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE){
				$count = 1;
				for($i = 0; $i < count($election_info); $i++){
					$count_2 = 1;
					for($ii = 1; $ii < count($years_voted); $ii++){
						$info = $election_info[$i];
						$year = substr($years_voted[$ii], 2);
						$info_year = $info . $year;
						if($count == 1){
							$sql_query .= " WHERE (('$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						
						else if($count_2 == 1){
							$sql_query .= " AND ('$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						else{
							$sql_query .= " AND '$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						$count++;
						$count_2++;
					}
					$sql_query .= ")";
				}
				$sql_query .= ")";
			}
			else{
				$count = 1;
				for($i = 0; $i < count($election_info); $i++){
					$count_2 = 1;
					for($ii = 1; $ii < count($years_voted); $ii++){
						$info = $election_info[$i];
						$year = substr($years_voted[$ii], 2);
						$info_year = $info . $year;
						if($count == 1){
							$sql_query .= " AND (('$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						
						else if($count_2 == 1){
							$sql_query .= " AND ('$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						else{
							$sql_query .= " AND '$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						$count++;
						$count_2++;
					}
					$sql_query .= ")";
				}
				$sql_query .= ")";
			}
		}
		else{
			if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE){
				$count = 1;
				for($i = 1; $i < count($years_voted); $i++){
					$count_2 = 1;
					for($ii = 0; $ii < count($election_info); $ii++){
						$info = $election_info[$ii];
						$year = substr($years_voted[$i], 2);
						$info_year = $info . $year;
						if($count == 1){
							$sql_query .= " WHERE (('$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						
						else if($count_2 == 1){
							$sql_query .= " OR ('$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						else{
							$sql_query .= " AND '$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						$count++;
						$count_2++;
					}
					$sql_query .= ")";
				}
				$sql_query .= ")";
			}
			else{
				$count = 1;
				for($i = 1; $i < count($years_voted); $i++){
					$count_2 = 1;
					for($ii = 0; $ii < count($election_info); $ii++){
						$info = $election_info[$ii];
						$year = substr($years_voted[$i], 2);
						$info_year = $info . $year;
						if($count == 1){
							$sql_query .= " AND (('$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						
						else if($count_2 == 1){
							$sql_query .= " OR ('$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						else{
							$sql_query .= " AND '$info_year' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
						}
						$count++;
						$count_2++;
					}
					$sql_query .= ")";
				}
				$sql_query .= ")";
			}
		}
	}
	else{
		if($all_or_any == "all"){
			if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE){
				$count = 1;
				for($i = 1; $i < count($years_voted); $i++){
						$count_2 = 1;
						for($ii = 1; $ii <= 12; $ii++){
							$history = "history" . $ii;
							$year = substr($years_voted[$i], 2);
							if($count == 1){
								$sql_query .= " WHERE (($history LIKE '%{$year}%'";
							}
							else if($count_2 == 1){
								$sql_query .= " AND ($history LIKE '%{$year}%'";
							}
							else{
								$sql_query .= " OR $history LIKE '%{$year}%'";
							}
							$count++;
							$count_2++;
							$used_election_info = TRUE;
						}
						$sql_query .= ")";
				}
				if($used_election_info == TRUE){
					$sql_query .= ")";
				}
			}
			else{
				$count = 1;
				for($i = 1; $i < count($years_voted); $i++){
						$count_2 = 1;
						for($ii = 1; $ii <= 12; $ii++){
							$history = "history" . $ii;
							$year = substr($years_voted[$i], 2);
							if($count == 1){
								$sql_query .= " AND (($history LIKE '%{$year}%'";
							}
							else if($count_2 == 1){
								$sql_query .= " AND ($history LIKE '%{$year}%'";
							}
							else{
								$sql_query .= " OR $history LIKE '%{$year}%'";
							}
							$count++;
							$count_2++;
							$used_election_info = TRUE;
						}
						$sql_query .= ")";
				}
				if($used_election_info == TRUE){
					$sql_query .= ")";
				}
			}
		}
		else{
			if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE){
				$count = 1;
				for($i = 1; $i < count($years_voted); $i++){
						$count_2 = 1;
						for($ii = 1; $ii <= 12; $ii++){
							$history = "history" . $ii;
							$year = substr($years_voted[$i], 2);
							if($count == 1){
								$sql_query .= " WHERE (($history LIKE '%{$year}%'";
							}
							else if($count_2 == 1){
								$sql_query .= " OR ($history LIKE '%{$year}%'";
							}
							else{
								$sql_query .= " OR $history LIKE '%{$year}%'";
							}
							$count++;
							$count_2++;
							$used_election_info = TRUE;
						}
						$sql_query .= ")";
				}
				if($used_election_info == TRUE){
					$sql_query .= ")";
				}
			}
			else{
				$count = 1;
				for($i = 1; $i < count($years_voted); $i++){
						$count_2 = 1;
						for($ii = 1; $ii <= 12; $ii++){
							$history = "history" . $ii;
							$year = substr($years_voted[$i], 2);
							if($count == 1){
								$sql_query .= " AND (($history LIKE '%{$year}%'";
							}
							else if($count_2 == 1){
								$sql_query .= " OR ($history LIKE '%{$year}%'";
							}
							else{
								$sql_query .= " OR $history LIKE '%{$year}%'";
							}
							$count++;
							$count_2++;
							$used_election_info = TRUE;
						}
						echo "here";
						$sql_query .= ")";
				}
				if($used_election_info == TRUE){
					$sql_query .= ")";
				}
			}
		}
	}
	$result = mysqli_query($conn, $sql_query) or die("error");
	$row = $result->fetch_assoc();
	$count = $row["this_count"];
	echo json_encode($count);
?>