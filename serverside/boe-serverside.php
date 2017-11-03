<?php
require("connection.php");

header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('America/New_York');
$data = json_decode(file_get_contents("php://input"));
if(is_array($data) || is_object($data)){
	foreach ($data as $key => $value) {
		$_POST[$key] = $value;
	}
}

/*Get general information based on column selected*/
/*PARAMS: column, county(lowercase letters)*/
/*RETURNS: count with what the count represents*/
if(isset($_POST["get_column_info"])){
	$data_array = array();
	$column = $_POST["get_column_info"]->columnName;
	$county = $_POST["get_column_info"]->countyName;
	$table = $county . "_import";
	$result = mysqli_query($conn, "SELECT $column, COUNT(*) AS `num` FROM $table GROUP BY $column");
	$result_codes = mysqli_query($conn, "SELECT * codes WHERE county = '$county'");
	$code_array = getCodes($county, $column);
	$temp_array = array();
	while($info = $result->fetch_assoc()){
		$temp_array[$info[$column]]["count"] = $info["num"];
		if(isset($code_array[$info[$column]])){
			$temp_array[$info[$column]]["textual_representation"] = $code_array[$info[$column]];
		}
		else{
			$temp_array[$info[$column]]["textual_representation"] = "";
		}
		
		$data_array["content"] = $temp_array;
	}
	echo json_encode($data_array);
}

/*Retrieves query along with counts for householded and individual counts
 *PARAMS: searchCriteria(type array) {columnName, match, value, type}
 *RETURNS: Query, Individual Count, Householded Count Grouped by last name and address*/
else if(isset($_POST["retrieve_query"])){
	$data_array = array();
	$match_sql_array = array("exact" => "=",
							 "like" => "LIKE",
							 "less than" => "<=",
							 "greater than" => ">="
					);
	$searchCriteria = $_POST["retrieve_query"]->searchCriteria;
	$query = "SELECT count(*) as count FROM columbia_import WHERE 1=1";
	for($i = 0; $i < count($searchCriteria); $i++){
		$columnName = $searchCriteria[$i]->columnName;
		$match = $searchCriteria[$i]->match;
		$value = $searchCriteria[$i]->value;
		$type = $searchCriteria[$i]->type;
		
		if($type == "multiple"){ //for all multiple value options of dynamic dropdowns
			if(count($value) > 0){
				$query .= " AND (";
				for($ii = 0; $ii < count($value); $ii++){
					$this_value = $value[$ii];
					if($ii == 0){
						$query .= " $columnName = '$this_value'";
					}
					else{
						$query .= " OR $columnName = '$this_value'";
					}
				}
				$query .= ")";
			}
		}
		else if($type == "single"){ //For Searches with Single Value
			$match = $match_sql_array[$match];
			$query .= " AND ($columnName $match '$value')";
		}
		
		/*SPECIAL CASES*/
		else if($type == "like_history_years" || $type == "like_history_elections"){ //SPECIAL CASE: Voting History where either one or the other is selected(radio buttons or years ONLY)
			$query .= " AND (";
			for($ii = 0; $ii < count($value); $ii++){
				$history_count = 1;
				while($history_count <= 12){
					$history_column = "history" . $history_count;
					$item = $value[$ii];
					if($ii > 0 && $columnName == "Any" && $history_count == 1 && $type == "like_history_years"){
						$query .= " OR ";
					}
					else if($ii > 0 && (($columnName == "All" && $history_count == 1) || ($type == "like_history_elections" && $history_count == 1))){
						$query .= " AND ";
					}
					
					if($history_count == 1){
						$query .= "($history_column LIKE '%{$item}%'";
					}
					else{
						$query .= " OR $history_column LIKE '%{$item}%'";
					}
					$history_count++;
				}
				$query .= ")";
			}
			$query .= ")";
		}
		else if($type == "in_history"){ //For selecting specific elections with years
			$query .= " AND ((";
			for($i = 0; $i < count($value); $i++){
				if($i > 0 && $columnName == "Any"){
					$query .= " OR (";
				}
				else if($i > 0 && $columnName == "All"){
					$query .= " AND (";
				}
				for($ii = 0; $ii < count($value[$i]); $ii++){
					$this_value = $value[$i][$ii];
					if($ii == 0){
						$query .= "'$this_value' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
					}
					else{
						$query .= " AND '$this_value' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
					}
				}
				
				$query .= ")";
			}
			
			$query .= ")";
		}
	}
	
	$query_householded_count = $query . " GROUP BY last_name, street_no, street_name, apt_no, city, state, zip";
	$result_householded = mysqli_query($conn, $query_householded_count);
	$result = mysqli_query($conn, $query);
	$count = $result->fetch_assoc()["count"];
	$count_householded = mysqli_num_rows($result_householded);
	$data_array["sql_query"] = $query;
	$data_array["count"] = $count;
	$data_array["count_householded"] = $count_householded;
	echo json_encode($data_array);
}
else if(isset($_POST["this_export"])){
	$queries = $_POST["this_export"]->queries;
	$columns_selected = " voter_id, first_name, middle_name, last_name, street_no, street_name, apt_no, city, state, zip, zip4 ";
	$union_queries = array();
	
	foreach($queries as $query){
		$query = $query->query;
		$query_statement_1 = explode(" count(*) as count ", $query)[0];
		$query_statement_2 = explode(" count(*) as count ", $query)[1];
		array_push($union_queries, "(" . $query_statement_1 . $columns_selected . $query_statement_2 . ")");
	}
	
	$union_select = implode(" UNION ", $union_queries);
	$final_query = "SELECT voter_id, first_name, middle_name, last_name, street_no, street_name, apt_no, city, state, zip, zip4, count(voter_id) as count FROM ("
					. $union_select . ") as t2 GROUP BY last_name, street_no, street_name, apt_no, city, state, zip";
	
	$file = fopen("sample.csv", "w");
	fputcsv($file, ["Voter ID", "First Name", "Middle Name", "Last Name", "Mail Address", "City", "State", "ZIP", "ZIP+4"]);
	$result_final = mysqli_query($conn, $final_query);
	while($voter = $result_final->fetch_assoc()){
		if($voter["count"] == 1){
			fputcsv($file, $voter);
		}
		else{
			$voter["voter_id"] = "";
			$voter["first_name"] = "";
			$voter["middle_name"] = "";
			$voter["last_name"] = "The " . $voter["last_name"] . " Family";
			fputcsv($file, $voter);
		}
	}
	fseek($file, 0);
	header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="sample.csv";');
	header('location: sample.csv');
	//echo json_encode("sample.csv");
}

/*Return Code definitions from codes table*/
/*PARAMS: county, column*/
/*RETURNS: Assoc array with key as code and value as description*/
function getCodes($county, $column){
	require("connection.php");
	$codes_array = array();
	$county_capital = ucwords($county);
	$result = mysqli_query($conn, "SELECT code, description FROM codes WHERE county = '$county_capital' AND category = '$column'");
	while($code = $result->fetch_assoc()){
		$codes_array[$code["code"]] = $code["description"];
	}
	return $codes_array;
}
?>