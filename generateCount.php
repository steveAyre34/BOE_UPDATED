<?php
	require("connection.php");
	session_start();
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
	$party = $data[10];
	$town = $data[11];
	$ward = $data[12];
	$district = $data[13];
	$cong_district = $data[14];
	$sen_district = $data[15];
	$school_district = $data[16];
	$asm_district = $data[17];
	$fire_district = $data[18];
	$leg_district = $data[19];
	$village = $data[20];
	$user1 = $data[21];
	$user2 = $data[22];
	$user3 = $data[23];
	$user4 = $data[24];
	
	$sql_query = "";
	//what we've used so far
	$used_zip = FALSE;
	$used_age = FALSE;
	$used_sex = FALSE;
	$used_reg_date = FALSE;
	$used_election_info = FALSE;
	$used_party = FALSE;
	$used_town = FALSE;
	$used_ward = FALSE;
	$used_district = FALSE;
	$used_cong_district = FALSE;
	$used_sen_district = FALSE;
	$used_school_district = FALSE;
	$used_asm_district = FALSE;
	$used_fire_district = FALSE;
	$used_leg_district = FALSE;
	$used_village = FALSE;
	$used_user1 = FALSE;
	$used_user2 = FALSE;
	$used_user3 = FALSE;
	$used_user4 = FALSE;
	
	if($counts_for == "individual"){
		$sql_query .= "SELECT count(voter_id) as this_count FROM $table_name";
	}
	else{
		$sql_query .= "SELECT count(DISTINCT last_name, street_no, street_name, apt_no) as this_count FROM $table_name";
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
		$used_election_info = TRUE;
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
		$used_election_info = TRUE;
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
	if($party[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE){
			for($i = 0; $i < count($party); $i++){
				$party_selected = $party[$i];
				if($count == 1){
					$sql_query .= " WHERE (party = '$party_selected'";
				}
				else if($count == count($party)){
					$sql_query .= " OR party = '$party_selected')";
				}
				else{
					$sql_query .= " OR party = '$party_selected'";
				}
				
				if($count == 1 && $count == count($party)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($party); $i++){
				$party_selected = $party[$i];
				if($count == 1){
					$sql_query .= " AND (party = '$party_selected'";
					$used_party = TRUE;
				}
				else if($count == count($party)){
					$sql_query .= " OR party = '$party_selected')";
				}
				else{
					$sql_query .= " OR party = '$party_selected'";
				}
				
				if($count == 1 && $count == count($party)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_party = TRUE;
	}
	
	if($town[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE){
			for($i = 0; $i < count($town); $i++){
				$town_selected = $town[$i];
				if($count == 1){
					$sql_query .= " WHERE (town = '$town_selected'";
				}
				else if($count == count($town)){
					$sql_query .= " OR town = '$town_selected')";
				}
				else{
					$sql_query .= " OR town = '$town_selected'";
				}
				
				if($count == 1 && $count == count($town)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($town); $i++){
				$town_selected = $town[$i];
				if($count == 1){
					$sql_query .= " AND (town = '$town_selected'";
				}
				else if($count == count($town)){
					$sql_query .= " OR town = '$town_selected')";
				}
				else{
					$sql_query .= " OR town = '$town_selected'";
				}
				
				if($count == 1 && $count == count($town)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_town = TRUE;
	}
	
	if($ward[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE){
			for($i = 0; $i < count($ward); $i++){
				$ward_selected = $ward[$i];
				if($count == 1){
					$sql_query .= " WHERE (ward = '$ward_selected'";
				}
				else if($count == count($ward)){
					$sql_query .= " OR ward = '$ward_selected')";
				}
				else{
					$sql_query .= " OR ward = '$ward_selected'";
				}
				
				if($count == 1 && $count == count($ward)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($ward); $i++){
				$ward_selected = $ward[$i];
				if($count == 1){
					$sql_query .= " AND (ward = '$ward_selected'";
				}
				else if($count == count($ward)){
					$sql_query .= " OR ward = '$ward_selected')";
				}
				else{
					$sql_query .= " OR ward = '$ward_selected'";
				}
				
				if($count == 1 && $count == count($ward)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_ward = TRUE;
	}
	
	if($district[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE){
			for($i = 0; $i < count($district); $i++){
				$district_selected = $district[$i];
				if($count == 1){
					$sql_query .= " WHERE (district = '$district_selected'";
				}
				else if($count == count($district)){
					$sql_query .= " OR district = '$district_selected')";
				}
				else{
					$sql_query .= " OR district = '$district_selected'";
				}
				
				if($count == 1 && $count == count($district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($district); $i++){
				$district_selected = $district[$i];
				if($count == 1){
					$sql_query .= " AND (district = '$district_selected'";
				}
				else if($count == count($district)){
					$sql_query .= " OR district = '$district_selected')";
				}
				else{
					$sql_query .= " OR district = '$district_selected'";
				}
				
				if($count == 1 && $count == count($district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_district = TRUE;
	}
	
	if($cong_district[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE){
			for($i = 0; $i < count($cong_district); $i++){
				$cong_district_selected = $cong_district[$i];
				if($count == 1){
					$sql_query .= " WHERE (cong_district = '$cong_district_selected'";
				}
				else if($count == count($cong_district)){
					$sql_query .= " OR cong_district = '$cong_district_selected')";
				}
				else{
					$sql_query .= " OR cong_district = '$cong_district_selected'";
				}
				
				if($count == 1 && $count == count($cong_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($cong_district); $i++){
				$cong_district_selected = $cong_district[$i];
				if($count == 1){
					$sql_query .= " AND (cong_district = '$cong_district_selected'";
				}
				else if($count == count($cong_district)){
					$sql_query .= " OR cong_district = '$cong_district_selected')";
				}
				else{
					$sql_query .= " OR cong_district = '$cong_district_selected'";
				}
				
				if($count == 1 && $count == count($cong_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_cong_district = TRUE;
	}
	
	if($sen_district[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE){
			for($i = 0; $i < count($sen_district); $i++){
				$sen_district_selected = $sen_district[$i];
				if($count == 1){
					$sql_query .= " WHERE (sen_district = '$sen_district_selected'";
				}
				else if($count == count($sen_district)){
					$sql_query .= " OR sen_district = '$sen_district_selected')";
				}
				else{
					$sql_query .= " OR sen_district = '$sen_district_selected'";
				}
				
				if($count == 1 && $count == count($sen_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($sen_district); $i++){
				$sen_district_selected = $sen_district[$i];
				if($count == 1){
					$sql_query .= " AND (sen_district = '$sen_district_selected'";
				}
				else if($count == count($sen_district)){
					$sql_query .= " OR sen_district = '$sen_district_selected')";
				}
				else{
					$sql_query .= " OR sen_district = '$sen_district_selected'";
				}
				
				if($count == 1 && $count == count($sen_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_sen_district = TRUE;
	}
	
	if($school_district[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE && $used_sen_district == FALSE){
			for($i = 0; $i < count($school_district); $i++){
				$school_district_selected = $school_district[$i];
				if($count == 1){
					$sql_query .= " WHERE (school_district = '$school_district_selected'";
				}
				else if($count == count($school_district)){
					$sql_query .= " OR school_district = '$school_district_selected')";
				}
				else{
					$sql_query .= " OR school_district = '$school_district_selected'";
				}
				
				if($count == 1 && $count == count($school_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($school_district); $i++){
				$school_district_selected = $school_district[$i];
				if($count == 1){
					$sql_query .= " AND (school_district = '$school_district_selected'";
				}
				else if($count == count($school_district)){
					$sql_query .= " OR school_district = '$school_district_selected')";
				}
				else{
					$sql_query .= " OR school_district = '$school_district_selected'";
				}
				
				if($count == 1 && $count == count($school_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_school_district = TRUE;
	}
	
	if($asm_district[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE && $used_sen_district == FALSE && $used_school_district == FALSE){
			for($i = 0; $i < count($asm_district); $i++){
				$asm_district_selected = $asm_district[$i];
				if($count == 1){
					$sql_query .= " WHERE (asm_district = '$asm_district_selected'";
				}
				else if($count == count($asm_district)){
					$sql_query .= " OR asm_district = '$asm_district_selected')";
				}
				else{
					$sql_query .= " OR asm_district = '$asm_district_selected'";
				}
				
				if($count == 1 && $count == count($asm_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($asm_district); $i++){
				$asm_district_selected = $asm_district[$i];
				if($count == 1){
					$sql_query .= " AND (asm_district = '$asm_district_selected'";
				}
				else if($count == count($asm_district)){
					$sql_query .= " OR asm_district = '$asm_district_selected')";
				}
				else{
					$sql_query .= " OR asm_district = '$asm_district_selected'";
				}
				
				if($count == 1 && $count == count($asm_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_asm_district = TRUE;
	}
	
	if($fire_district[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE && $used_sen_district == FALSE && $used_school_district == FALSE && $used_asm_district == FALSE){
			for($i = 0; $i < count($fire_district); $i++){
				$fire_district_selected = $fire_district[$i];
				if($count == 1){
					$sql_query .= " WHERE (fire_district = '$fire_district_selected'";
				}
				else if($count == count($fire_district)){
					$sql_query .= " OR fire_district = '$fire_district_selected')";
				}
				else{
					$sql_query .= " OR fire_district = '$fire_district_selected'";
				}
				
				if($count == 1 && $count == count($fire_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($fire_district); $i++){
				$fire_district_selected = $fire_district[$i];
				if($count == 1){
					$sql_query .= " AND (fire_district = '$fire_district_selected'";
				}
				else if($count == count($fire_district)){
					$sql_query .= " OR fire_district = '$fire_district_selected')";
				}
				else{
					$sql_query .= " OR fire_district = '$fire_district_selected'";
				}
				
				if($count == 1 && $count == count($fire_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_fire_district = TRUE;
	}
	
	if($leg_district[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE && $used_sen_district == FALSE && $used_school_district == FALSE && $used_asm_district == FALSE && $used_fire_district == FALSE){
			for($i = 0; $i < count($leg_district); $i++){
				$leg_district_selected = $leg_district[$i];
				if($count == 1){
					$sql_query .= " WHERE (leg_district = '$leg_district_selected'";
				}
				else if($count == count($leg_district)){
					$sql_query .= " OR leg_district = '$leg_district_selected')";
				}
				else{
					$sql_query .= " OR leg_district = '$leg_district_selected'";
				}
				
				if($count == 1 && $count == count($leg_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($leg_district); $i++){
				$leg_district_selected = $leg_district[$i];
				if($count == 1){
					$sql_query .= " AND (leg_district = '$leg_district_selected'";
				}
				else if($count == count($leg_district)){
					$sql_query .= " OR leg_district = '$leg_district_selected')";
				}
				else{
					$sql_query .= " OR leg_district = '$leg_district_selected'";
				}
				
				if($count == 1 && $count == count($leg_district)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_leg_district = TRUE;
	}
	
	if($village[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE && $used_sen_district == FALSE && $used_school_district == FALSE && $used_asm_district == FALSE && $used_fire_district == FALSE && $used_leg_district == FALSE){
			for($i = 0; $i < count($village); $i++){
				$village_selected = $village[$i];
				if($count == 1){
					$sql_query .= " WHERE (village = '$village_selected'";
				}
				else if($count == count($village)){
					$sql_query .= " OR village = '$village_selected')";
				}
				else{
					$sql_query .= " OR village = '$village_selected'";
				}
				
				if($count == 1 && $count == count($village)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($village); $i++){
				$village_selected = $village[$i];
				if($count == 1){
					$sql_query .= " AND (village = '$village_selected'";
				}
				else if($count == count($village)){
					$sql_query .= " OR village = '$village_selected')";
				}
				else{
					$sql_query .= " OR village = '$village_selected'";
				}
				
				if($count == 1 && $count == count($village)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_village = TRUE;
	}
	
	if($user1[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE && $used_sen_district == FALSE && $used_school_district == FALSE && $used_asm_district == FALSE && $used_fire_district == FALSE && $used_leg_district == FALSE && $used_village == FALSE){
			for($i = 0; $i < count($user1); $i++){
				$user1_selected = $user1[$i];
				if($count == 1){
					$sql_query .= " WHERE (user1 = '$user1_selected'";
				}
				else if($count == count($user1)){
					$sql_query .= " OR user1 = '$user1_selected')";
				}
				else{
					$sql_query .= " OR user1 = '$user1_selected'";
				}
				
				if($count == 1 && $count == count($user1)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($user1); $i++){
				$user1_selected = $user1[$i];
				if($count == 1){
					$sql_query .= " AND (user1 = '$user1_selected'";
				}
				else if($count == count($user1)){
					$sql_query .= " OR user1 = '$user1_selected')";
				}
				else{
					$sql_query .= " OR user1 = '$user1_selected'";
				}
				
				if($count == 1 && $count == count($user1)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_user1 = TRUE;
	}
	
	if($user2[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE && $used_sen_district == FALSE && $used_school_district == FALSE && $used_asm_district == FALSE && $used_fire_district == FALSE && $used_leg_district == FALSE && $used_village == FALSE && $used_user1 == FALSE){
			for($i = 0; $i < count($user2); $i++){
				$user2_selected = $user2[$i];
				if($count == 1){
					$sql_query .= " WHERE (user2 = '$user2_selected'";
				}
				else if($count == count($user2)){
					$sql_query .= " OR user2 = '$user2_selected')";
				}
				else{
					$sql_query .= " OR user2 = '$user2_selected'";
				}
				
				if($count == 1 && $count == count($user2)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($user2); $i++){
				$user2_selected = $user2[$i];
				if($count == 1){
					$sql_query .= " AND (user2 = '$user2_selected'";
				}
				else if($count == count($user2)){
					$sql_query .= " OR user2 = '$user2_selected')";
				}
				else{
					$sql_query .= " OR user2 = '$user2_selected'";
				}
				
				if($count == 1 && $count == count($user2)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_user2 = TRUE;
	}
	
	if($user3[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE && $used_sen_district == FALSE && $used_school_district == FALSE && $used_asm_district == FALSE && $used_fire_district == FALSE && $used_leg_district == FALSE && $used_village == FALSE && $used_user1 == FALSE && $used_user2 == FALSE){
			for($i = 0; $i < count($user3); $i++){
				$user3_selected = $user3[$i];
				if($count == 1){
					$sql_query .= " WHERE (user3 = '$user3_selected'";
				}
				else if($count == count($user3)){
					$sql_query .= " OR user3 = '$user3_selected')";
				}
				else{
					$sql_query .= " OR user3 = '$user3_selected'";
				}
				
				if($count == 1 && $count == count($user3)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($user3); $i++){
				$user3_selected = $user3[$i];
				if($count == 1){
					$sql_query .= " AND (user3 = '$user3_selected'";
				}
				else if($count == count($user3)){
					$sql_query .= " OR user3 = '$user3_selected')";
				}
				else{
					$sql_query .= " OR user3 = '$user3_selected'";
				}
				
				if($count == 1 && $count == count($user3)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_user3 = TRUE;
	}
	
	if($user4[0] != "ignore"){
		$count = 1;
		if($used_zip == FALSE && $used_age == FALSE && $used_sex == FALSE && $used_reg_date == FALSE && $used_election_info == FALSE && $used_party == FALSE && $used_town == FALSE && $used_ward == FALSE && $used_district == FALSE && $used_cong_district == FALSE && $used_sen_district == FALSE && $used_school_district == FALSE && $used_asm_district == FALSE && $used_fire_district == FALSE && $used_leg_district == FALSE && $used_village == FALSE && $used_user1 == FALSE && $used_user2 == FALSE && $used_user3 == FALSE){
			for($i = 0; $i < count($user4); $i++){
				$user4_selected = $user4[$i];
				if($count == 1){
					$sql_query .= " WHERE (user4 = '$user4_selected'";
				}
				else if($count == count($user4)){
					$sql_query .= " OR user4 = '$user4_selected')";
				}
				else{
					$sql_query .= " OR user4 = '$user4_selected'";
				}
				
				if($count == 1 && $count == count($user4)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		else{
			for($i = 0; $i < count($user4); $i++){
				$user4_selected = $user4[$i];
				if($count == 1){
					$sql_query .= " AND (user4 = '$user4_selected'";
				}
				else if($count == count($user4)){
					$sql_query .= " OR user4 = '$user4_selected')";
				}
				else{
					$sql_query .= " OR user4 = '$user4_selected'";
				}
				
				if($count == 1 && $count == count($user4)){
					$sql_query .= ")";
				}
				$count++;
			}
		}
		$used_user4 = TRUE;
	}
	$sql_query .= " ORDER BY last_name, street_no, street_name, apt_no";
	$result = mysqli_query($conn, $sql_query) or die("error");
	$row = $result->fetch_assoc();
	$count = $row["this_count"];
	$array = array($count, $sql_query);
	echo json_encode($array);
?>