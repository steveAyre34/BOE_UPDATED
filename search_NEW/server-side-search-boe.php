<?php
/*
Follow this tutorial for further clarification --> https://coderexample.com/datatable-demo-server-side-in-phpmysql-and-ajax/
*/
$sql = "";
$totalData = "";
$totalFiltered = "";
//---QUERIED DATA--
$county = $_GET["county"];
$first_name = $_GET["first_name"];
$last_name = $_GET["last_name"];
$street_no = $_GET["street_no"];
$street_name = $_GET["street_name"];
$apt_no = $_GET["apt_no"];
$city = $_GET["city"];
$zip = $_GET["zip"];
$table_import = $county . "_import";
$table_verified = $county . "_verified";
if($_POST['function'] ==1){
	// getting total number records without any search
	require("connection.php");
	global $sql, $totalData, $totalFiltered;
	if($county == "ulster"  || $county == "columbia" || $county == "dutchess" || $county == "albany"){
		$sql = "SELECT $table_import.voter_id, $table_import.first_name, $table_import.last_name, $table_import.street_no, $table_import.street_name, $table_import.city as city1, $table_import.state as state1, $table_import.zip as zip1, $table_import.party, $table_verified.address1, $table_verified.city as city2, $table_verified.state as state2, $table_verified.zip as zip2 FROM $table_import INNER JOIN $table_verified ON $table_import.voter_id = $table_verified.voter_id WHERE $table_import.first_name LIKE '%{$first_name}%' AND $table_import.last_name LIKE '%{$last_name}%' AND $table_import.street_no LIKE '%{$street_no}%' AND $table_import.street_name LIKE '%{$street_name}%' AND $table_import.apt_no LIKE '%{$apt_no}%' AND $table_import.city LIKE '%{$city}%' AND $table_import.zip LIKE '%{$zip}%'";
	}
	else if($county != "brooklyn" && $county != "queens" && $county != "statenisland" && $county != "bronx" && $county != "manhattan"){
		$sql = "SELECT $table_import.voter_id, $table_import.first_name, $table_import.last_name, $table_import.street_no, $table_import.street_name, $table_import.city as city1, $table_import.state as state1, $table_import.zip as zip1, $table_import.party, $table_verified.address2, $table_verified.address4 FROM $table_import INNER JOIN $table_verified ON $table_import.voter_id = $table_verified.voterid WHERE $table_import.first_name LIKE '%{$first_name}%' AND $table_import.last_name LIKE '%{$last_name}%' AND $table_import.street_no LIKE '%{$street_no}%' AND $table_import.street_name LIKE '%{$street_name}%' AND $table_import.apt_no LIKE '%{$apt_no}%' AND $table_import.city LIKE '%{$city}%' AND $table_import.zip LIKE '%{$zip}%'";
	}
	else{
		$sql = "SELECT $table_import.voter_id, $table_import.first_name, $table_import.last_name, $table_import.street_no, $table_import.street_name, $table_import.city as city1, $table_import.zip as zip1, $table_import.party, $table_verified.address2, $table_verified.address4 FROM $table_import INNER JOIN $table_verified ON $table_import.voter_id = $table_verified.voterid WHERE $table_import.first_name LIKE '%{$first_name}%' AND $table_import.last_name LIKE '%{$last_name}%' AND $table_import.street_no LIKE '%{$street_no}%' AND $table_import.street_name LIKE '%{$street_name}%' AND $table_import.apt_no LIKE '%{$apt_no}%' AND $table_import.city LIKE '%{$city}%' AND $table_import.zip LIKE '%{$zip}%'";
	}
	$query=mysqli_query($conn, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

	if($county == "ulster"  || $county == "columbia" || $county == "dutchess" || $county == "albany"){
		$sql = "SELECT $table_import.voter_id, $table_import.first_name, $table_import.last_name, $table_import.street_no, $table_import.street_name, $table_import.city as city1, $table_import.state as state1, $table_import.zip as zip1, $table_import.party, $table_verified.address1, $table_verified.city as city2, $table_verified.state as state2, $table_verified.zip as zip2 FROM $table_import INNER JOIN $table_verified ON $table_import.voter_id = $table_verified.voter_id WHERE 1=1 AND $table_import.first_name LIKE '%{$first_name}%' AND $table_import.last_name LIKE '%{$last_name}%' AND $table_import.street_no LIKE '%{$street_no}%' AND $table_import.street_name LIKE '%{$street_name}%' AND $table_import.apt_no LIKE '%{$apt_no}%' AND $table_import.city LIKE '%{$city}%' AND $table_import.zip LIKE '%{$zip}%'";
	}
	else if($county != "brooklyn" && $county != "queens" && $county != "statenisland" && $county != "bronx" && $county != "manhattan"){
		$sql = "SELECT $table_import.voter_id, $table_import.first_name, $table_import.last_name, $table_import.street_no, $table_import.street_name, $table_import.city as city1, $table_import.state as state1, $table_import.zip as zip1, $table_import.party, $table_verified.address2, $table_verified.address4 FROM $table_import INNER JOIN $table_verified ON $table_import.voter_id = $table_verified.voterid WHERE 1=1 AND $table_import.first_name LIKE '%{$first_name}%' AND $table_import.last_name LIKE '%{$last_name}%' AND $table_import.street_no LIKE '%{$street_no}%' AND $table_import.street_name LIKE '%{$street_name}%' AND $table_import.apt_no LIKE '%{$apt_no}%' AND $table_import.city LIKE '%{$city}%' AND $table_import.zip LIKE '%{$zip}%'";
	}
	else{
		$sql = "SELECT $table_import.voter_id, $table_import.first_name, $table_import.last_name, $table_import.street_no, $table_import.street_name, $table_import.city as city1, $table_import.zip as zip1, $table_import.party, $table_verified.address2, $table_verified.address4 FROM $table_import INNER JOIN $table_verified ON $table_import.voter_id = $table_verified.voterid WHERE 1=1 AND $table_import.first_name LIKE '%{$first_name}%' AND $table_import.last_name LIKE '%{$last_name}%' AND $table_import.street_no LIKE '%{$street_no}%' AND $table_import.street_name LIKE '%{$street_name}%' AND $table_import.apt_no LIKE '%{$apt_no}%' AND $table_import.city LIKE '%{$city}%' AND $table_import.zip LIKE '%{$zip}%'";
	}
}


require("connection.php");
// storing  request (ie, get/post) global array to a variable
$requestData= $_REQUEST;
$columns = array(
// datatable column index  => database column name
	0 => 'voter_id',
	1=> 'first_name',
	2=> 'last_name',
	3 =>'street_name',
	4 => 'address1',
	5 => 'party'
);

	if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
		$sql.=" AND (voter_id LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR first_name LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR last_name LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR city1 LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR party LIKE '%".$requestData['search']['value']."%' ";
	}
	//getting records as per search parameters
	for ($i = 0; $i < count($columns); $i++) {
		if( !empty($requestData['columns'][$i]['search']['value']) ){   //each column name search
		    $sql.=" AND ".$columns[$i]."  LIKE '%".$requestData['columns'][$i]['search']['value']."%' ";
		}
	}

	$jsonsql = $sql;

	$query=mysqli_query($conn, $sql) or die (mysqli_error($conn));
	$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result.

	$sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   ";

	/* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */
	$query=mysqli_query($conn, $sql);
	
	$data = array();
	if($county == "ulster"  || $county == "columbia" || $county == "dutchess" || $county == "albany"){
		while( $row=mysqli_fetch_array($query) ) {  // preparing an array
			$nestedData=array();
			$nestedData[] = $row["voter_id"];
			$nestedData[] = $row["first_name"];
			$nestedData[] = $row["last_name"];
			$nestedData[] = $row["street_no"] . " " . $row["street_name"] . ", " . $row["city1"] . ", " . $row["state1"] . ", " . $row["zip1"];
			$nestedData[] = $row["address1"] . ", " . $row["city2"] . ", " . $row["state2"] . ", " . $row["zip2"];
			$nestedData[] = $row["party"];
			
			$data[] = $nestedData;
		}
	}
	else if($county != "brooklyn" && $county != "queens" && $county != "statenisland" && $county != "bronx" && $county != "manhattan"){
		while( $row=mysqli_fetch_array($query) ) {  // preparing an array
			$nestedData=array();
			$nestedData[] = $row["voter_id"];
			$nestedData[] = $row["first_name"];
			$nestedData[] = $row["last_name"];
			$nestedData[] = $row["street_no"] . " " . $row["street_name"] . ", " . $row["city1"] . ", " . $row["state1"] . ", " . $row["zip1"];
			$nestedData[] = $row["address1"] . ", " . $row["address4"];
			$nestedData[] = $row["party"];
			
			$data[] = $nestedData;
		}
	}
	else{
		while( $row=mysqli_fetch_array($query) ) {  // preparing an array
			$nestedData=array();
			$nestedData[] = $row["voter_id"];
			$nestedData[] = $row["first_name"];
			$nestedData[] = $row["last_name"];
			$nestedData[] = $row["street_no"] . " " . $row["street_name"] . ", " . $row["city1"] . ", " . $row["zip1"];
			$nestedData[] = $row["address1"] . ", " . $row["address4"];
			$nestedData[] = $row["party"];
			
			$data[] = $nestedData;
		}
	}

$json_data = array(
			"sql"							=> $jsonsql,
			"draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal"    => intval( $totalData ),  // total number of records
			"recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data"            => $data   // total data array
			);

echo json_encode($json_data);  // send data as json format

?>