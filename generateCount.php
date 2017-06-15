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
	$array_table_name = explode("_", $table_name);
	$table_name_verified = $array_table_name[0] . "_verified";
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
	
	$used_election_info = FALSE;
	
	$sql_query = "";
	//what we've used so far
	
	if($counts_for == "individual"){
		$sql_query .= "SELECT count($table_name.voter_id) as this_count FROM $table_name, $table_name_verified WHERE $table_name.voter_id = $table_name_verified.voter_id";
	}
	else{
		$sql_query .= "SELECT count(DISTINCT $table_name.last_name, $table_name_verified.address1) as this_count FROM $table_name, $table_name_verified WHERE $table_name.voter_id = $table_name_verified.voter_id";
	}
	//add zipcodes to statement
	$count = 1;
	if($zipcodes[0] != "ignore"){
		for($i = 0; $i < count($zipcodes); $i++){
			$zip = $zipcodes[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.zip = '$zip'";
				$used_zip = TRUE;
			}
			else if($count == count($zipcodes)){
				$sql_query .= " OR $table_name.zip = '$zip')";
			}
			else{
				$sql_query .= " OR $table_name.zip = '$zip'";
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
		$sql_query .= " AND ($table_name.dob >= '$max_age' AND $table_name.dob <= '$min_age')";
	}
	
	//add sex to statement
	if($sex != "both"){
		$sql_query .= " AND $table_name.sex = '$sex'";
	}
	
	//add registration to statement
	if($reg_date != ""){
		$sql_query .= " AND $table_name.reg_datetime >= '$reg_date'";
	}
	
	//add election info to statement
	if($election_info[0] != "all" && $years_voted[0] == "DN"){
		$count = 1;
		for($i = 1; $i <= 12; $i++){
				for($ii = 0; $ii < count($election_info); $ii++){
					$history = "history" . $i;
					$info = $election_info[$ii];
					if($count == 1){
						$sql_query .= " AND ($table_name.$history LIKE '%{$info}%'";
					}
		            else{
						$sql_query .= " OR $table_name.$history LIKE '%{$info}%'";
					}
					$count++;
				}
		}
		$sql_query .= ")";
	}
	else if($election_info[0] != "all" && $years_voted[0] == "U"){
		if($all_or_any == "all"){
			$count = 1;
			for($i = 0; $i < count($election_info); $i++){
			$count_2 = 1;
				for($ii = 1; $ii < count($years_voted); $ii++){
					$info = $election_info[$i];
					$year = substr($years_voted[$ii], 2);
					$info_year = $info . $year;
					if($count == 1){
						$sql_query .= " AND (('$info_year' IN ($table_name.history1, $table_name.history2, $table_name.history3, $table_name.history4, $table_name.history5, $table_name.history6, $table_name.history7, $table_name.history8, $table_name.history9, $table_name.history10, $table_name.history11, $table_name.history12)";
					}
						
					else if($count_2 == 1){
						$sql_query .= " AND ('$info_year' IN ($table_name.history1, $table_name.history2, $table_name.history3, $table_name.history4, $table_name.history5, $table_name.history6, $table_name.history7, $table_name.history8, $table_name.history9, $table_name.history10, $table_name.history11, $table_name.history12)";
					}
					else{
						$sql_query .= " AND '$info_year' IN ($table_name.history1, $table_name.history2, $table_name.history3, $table_name.history4, $table_name.history5, $table_name.history6, $table_name.history7, $table_name.history8, $table_name.history9, $table_name.history10, $table_name.history11, $table_name.history12)";
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
						$sql_query .= " AND (('$info_year' IN ($table_name.history1, $table_name.history2, $table_name.history3, $table_name.history4, $table_name.history5, $table_name.history6, $table_name.history7, $table_name.history8, $table_name.history9, $table_name.history10, $table_name.history11, $table_name.history12)";
					}
						
					else if($count_2 == 1){
						$sql_query .= " OR ('$info_year' IN ($table_name.history1, $table_name.history2, $table_name.history3, $table_name.history4, $table_name.history5, $table_name.history6, $table_name.history7, $table_name.history8, $table_name.history9, $table_name.history10, $table_name.history11, $table_name.history12)";
					}
					else{
						$sql_query .= " AND '$info_year' IN ($table_name.history1, $table_name.history2, $table_name.history3, $table_name.history4, $table_name.history5, $table_name.history6, $table_name.history7, $table_name.history8, $table_name.history9, $table_name.history10, $table_name.history11, $table_name.history12)";
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
		if($all_or_any == "all"){
			$count = 1;
			for($i = 1; $i < count($years_voted); $i++){
					$count_2 = 1;
					for($ii = 1; $ii <= 12; $ii++){
						$history = "history" . $ii;
						$year = substr($years_voted[$i], 2);
						if($count == 1){
							$sql_query .= " AND (($table_name.$history LIKE '%{$year}%'";
						}
						else if($count_2 == 1){
							$sql_query .= " AND ($table_name.$history LIKE '%{$year}%'";
						}
						else{
							$sql_query .= " OR $table_name.$history LIKE '%{$year}%'";
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
							$sql_query .= " AND (($table_name.$history LIKE '%{$year}%'";
						}
						else if($count_2 == 1){
							$sql_query .= " OR ($table_name.$history LIKE '%{$year}%'";
						}
						else{
							$sql_query .= " OR $table_name.$history LIKE '%{$year}%'";
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
	if($party[0] != "ignore"){
		$count = 1;
		
		for($i = 0; $i < count($party); $i++){
			$party_selected = $party[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.party = '$party_selected'";
				$used_party = TRUE;
			}
			else if($count == count($party)){
				$sql_query .= " OR $table_name.party = '$party_selected')";
			}
			else{
				$sql_query .= " OR $table_name.party = '$party_selected'";
			}
				
			if($count == 1 && $count == count($party)){
				$sql_query .= ")";
			}
			$count++;
		}
		
	}
	
	if($town[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($town); $i++){
			$town_selected = $town[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.town = '$town_selected'";
			}
			else if($count == count($town)){
				$sql_query .= " OR $table_name.town = '$town_selected')";
			}
			else{
				$sql_query .= " OR $table_name.town = '$town_selected'";
			}
				
			if($count == 1 && $count == count($town)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($ward[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($ward); $i++){
			$ward_selected = $ward[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.ward = '$ward_selected'";
			}
			else if($count == count($ward)){
				$sql_query .= " OR $table_name.ward = '$ward_selected')";
			}
			else{
				$sql_query .= " OR $table_name.ward = '$ward_selected'";
			}
			
			if($count == 1 && $count == count($ward)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($district[0] != "ignore"){
		$count = 1;
		
		for($i = 0; $i < count($district); $i++){
			$district_selected = $district[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.district = '$district_selected'";
			}
			else if($count == count($district)){
				$sql_query .= " OR $table_name.district = '$district_selected')";
			}
			else{
				$sql_query .= " OR $table_name.district = '$district_selected'";
			}
				
			if($count == 1 && $count == count($district)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($cong_district[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($cong_district); $i++){
			$cong_district_selected = $cong_district[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.cong_district = '$cong_district_selected'";
			}
			else if($count == count($cong_district)){
				$sql_query .= " OR $table_name.cong_district = '$cong_district_selected')";
			}
			else{
				$sql_query .= " OR $table_name.cong_district = '$cong_district_selected'";
			}
				
			if($count == 1 && $count == count($cong_district)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($sen_district[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($sen_district); $i++){
			$sen_district_selected = $sen_district[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.sen_district = '$sen_district_selected'";
			}
			else if($count == count($sen_district)){
				$sql_query .= " OR $table_name.sen_district = '$sen_district_selected')";
			}
			else{
				$sql_query .= " OR $table_name.sen_district = '$sen_district_selected'";
			}
				
			if($count == 1 && $count == count($sen_district)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($school_district[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($school_district); $i++){
			$school_district_selected = $school_district[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.school_district = '$school_district_selected'";
			}
			else if($count == count($school_district)){
				$sql_query .= " OR $table_name.school_district = '$school_district_selected')";
			}
			else{
				$sql_query .= " OR $table_name.school_district = '$school_district_selected'";
			}
				
			if($count == 1 && $count == count($school_district)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($asm_district[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($asm_district); $i++){
			$asm_district_selected = $asm_district[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.asm_district = '$asm_district_selected'";
			}
			else if($count == count($asm_district)){
				$sql_query .= " OR $table_name.asm_district = '$asm_district_selected')";
			}
			else{
				$sql_query .= " OR $table_name.asm_district = '$asm_district_selected'";
			}
				
			if($count == 1 && $count == count($asm_district)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($fire_district[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($fire_district); $i++){
			$fire_district_selected = $fire_district[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.fire_district = '$fire_district_selected'";
			}
			else if($count == count($fire_district)){
				$sql_query .= " OR $table_name.fire_district = '$fire_district_selected')";
			}
			else{
				$sql_query .= " OR $table_name.fire_district = '$fire_district_selected'";
			}
				
			if($count == 1 && $count == count($fire_district)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($leg_district[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($leg_district); $i++){
			$leg_district_selected = $leg_district[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.leg_district = '$leg_district_selected'";
			}
			else if($count == count($leg_district)){
				$sql_query .= " OR $table_name.leg_district = '$leg_district_selected')";
			}
			else{
				$sql_query .= " OR $table_name.leg_district = '$leg_district_selected'";
			}
				
			if($count == 1 && $count == count($leg_district)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($village[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($village); $i++){
			$village_selected = $village[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.village = '$village_selected'";
			}
			else if($count == count($village)){
				$sql_query .= " OR $table_name.village = '$village_selected')";
			}
			else{
				$sql_query .= " OR $table_name.village = '$village_selected'";
			}
				
			if($count == 1 && $count == count($village)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($user1[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($user1); $i++){
			$user1_selected = $user1[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.user1 = '$user1_selected'";
			}
			else if($count == count($user1)){
				$sql_query .= " OR $table_name.user1 = '$user1_selected')";
			}
			else{
				$sql_query .= " OR $table_name.user1 = '$user1_selected'";
			}
				
			if($count == 1 && $count == count($user1)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($user2[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($user2); $i++){
			$user2_selected = $user2[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.user2 = '$user2_selected'";
			}
			else if($count == count($user2)){
				$sql_query .= " OR $table_name.user2 = '$user2_selected')";
			}
			else{
				$sql_query .= " OR $table_name.user2 = '$user2_selected'";
			}
				
			if($count == 1 && $count == count($user2)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($user3[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($user3); $i++){
			$user3_selected = $user3[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.user3 = '$user3_selected'";
			}
			else if($count == count($user3)){
				$sql_query .= " OR $table_name.user3 = '$user3_selected')";
			}
			else{
				$sql_query .= " OR $table_name.user3 = '$user3_selected'";
			}
				
			if($count == 1 && $count == count($user3)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	
	if($user4[0] != "ignore"){
		$count = 1;
		for($i = 0; $i < count($user4); $i++){
			$user4_selected = $user4[$i];
			if($count == 1){
				$sql_query .= " AND ($table_name.user4 = '$user4_selected'";
			}
			else if($count == count($user4)){
				$sql_query .= " OR $table_name.user4 = '$user4_selected')";
			}
			else{
				$sql_query .= " OR $table_name.user4 = '$user4_selected'";
			}
				
			if($count == 1 && $count == count($user4)){
				$sql_query .= ")";
			}
			$count++;
		}
	}
	//die($sql_query);
	$sql_query .= " ORDER BY $table_name.last_name, $table_name_verified.address1";
	$result = mysqli_query($conn, $sql_query) or die("error");
	$row = $result->fetch_assoc();
	$count = $row["this_count"];
	$array = array($count, $sql_query);
	echo json_encode($array);
?>