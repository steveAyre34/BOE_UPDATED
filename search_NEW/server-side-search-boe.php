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
	$sql = "SELECT * FROM $table_import WHERE first_name LIKE '%{$first_name}%' AND last_name LIKE '%{$last_name}%' AND street_no LIKE '%{$street_no}%' AND street_name LIKE '%{$street_name}%' AND apt_no LIKE '%{$apt_no}%' AND city LIKE '%{$city}%' AND zip LIKE '%{$zip}%'";
	$query=mysqli_query($conn, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.


	$sql = "SELECT * FROM $table_import WHERE 1=1 AND first_name LIKE '%{$first_name}%' AND last_name LIKE '%{$last_name}%' AND street_no LIKE '%{$street_no}%' AND street_name LIKE '%{$street_name}%' AND apt_no LIKE '%{$apt_no}%' AND city LIKE '%{$city}%' AND zip LIKE '%{$zip}%'";
}


require("connection.php");
// storing  request (ie, get/post) global array to a variable
$requestData= $_REQUEST;
$columns = array(
// datatable column index  => database column name
	0 => 'voter_id',
	1=> 'first_name',
	2=> 'last_name',
	3 =>'city',
	4 => 'party',
);

	if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
		$sql.=" AND (voter_id LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR first_name LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR last_name LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR city LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR state LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR zip LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR zip4 LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR party LIKE '%".$requestData['search']['value']."%' ";
		$sql.=" OR street_name LIKE '%".$requestData['search']['value']."%' ";
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
	while( $row=mysqli_fetch_array($query) ) {  // preparing an array
		$nestedData=array();
		$nestedData[] = $row["voter_id"];
		$nestedData[] = $row["first_name"];
		$nestedData[] = $row["last_name"];
		$nestedData[] = $row["street_no"] . " " . $row["street_name"] . ", " . $row["city"] . ", " . $row["state"] . ", " . $row["zip"];
		$nestedData[] = $row["party"];
		
		$data[] = $nestedData;
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