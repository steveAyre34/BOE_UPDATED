<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Search Records</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css">
		BODY, TH, TD { font-family: Courier New, Courier, monospace; font-size: 12px; line-height: 16px; }
		H1 { font-size: 14px; line-height: 16px; margin: 0px 0px 10px 5px; }
		TABLE {}
		TH { font-size: 14px; line-height: 16px; text-align: left; background-color: #BBB; padding: 2px 5px; }
		TD { vertical-align: top; text-align: left; padding: 2px 5px; }
		.odd TD { background-color: #EEE; }
		.even TD { background-color: #DDD; }
	</style>
</head>
<body>
<?php	
	require_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
	define('DEBUG', TRUE);
	if (isset($_GET['btnSearch']) && !empty($_GET['county'])) {
		
		//If they choose to search through All, loop through all of the counties and call searchThrough on each one.
		if($_GET['county'] == "All of them"){
			for($yolo = 0; $yolo < count($counties); $yolo++){
				searchThrough($counties[$yolo],$link);
				//You have to redo $link each time, not too sure why
				$link = mysqli_connect('localhost', 'boe', 'crst2778', 'boe');
				if (mysqli_connect_errno()){
					printf("Connect failed: %s\n", mysqli_connect_error());
					exit();
				}
			}			
			
			/**
				 ROFL:ROFL:LOL:ROFL:ROFL
			  L 	 _______|_______
			L O L---|              |_\
			  L     |      LMAO      |
			        |________________/
				   \_____|______|_____/	 
			         
			**/
		}
		else{
			searchThrough($_GET['county'],$link);
		}
	}
	
	//Functions have a weird interaction with $link, you have to pass it in with each call
	function searchThrough($currentCounty,$link){
		//if the county is not a borough of NYC, use search_address. Else.....
		$boroughs = array("queens", "bronx", "manhattan", "brooklyn", "statenisland");
		$isNYC = array_search($currentCounty, $boroughs);
		if($isNYC) {
			$proc = "search_address_nyc";
			//$proc = mssql_init('search_address_nyc', $link);
		} else{
			$proc = "search_address";
			//$proc = mssql_init('search_address', $link);
		}

		$grp = trim($currentCounty);
		$first_name = trim($_GET['first_name']);
		$last_name = trim($_GET['last_name']);
		$address = trim($_GET['address']);
		$city = trim($_GET['city']);
		$zip = trim($_GET['zip']);
		
		$sql = "call $proc( '$grp', '$first_name', '$last_name', '$address', '$city', '$zip')";
		
		/*
		if(!mssql_bind($proc, '@grp', trim($_GET['county']), SQLVARCHAR)) debug("Failed binding param");
		if(!mssql_bind($proc, '@first_name', trim($_GET['first_name']), SQLVARCHAR)) debug("Failed binding param");
		if(!mssql_bind($proc, '@last_name', trim($_GET['last_name']), SQLVARCHAR)) debug("Failed binding param");
		if(!mssql_bind($proc, '@address', trim($_GET['address']), SQLVARCHAR)) debug("Failed binding param");
		if(!mssql_bind($proc, '@city', trim($_GET['city']), SQLVARCHAR)) debug("Failed binding param");
		if(!mssql_bind($proc, '@zip', trim($_GET['zip']), SQLVARCHAR)) debug("Failed binding param");
		*/
		
		set_time_limit(360);
		//$rs = $link->query($sql);
		$rs = mysqli_query($link,$sql);
		//$count = mysqli_num_rows($rs);
		$count = $rs->num_rows;
			
		//table headers
		if ($count > 0) {
			if(!$isNYC)
				print '<table>
		<tr>
			<th>VoterID</th>
			<th>Name</th>
			<th>Address</th>
			<th>City, State ZIP</th>
			<th>Sex</th>
			<th>DOB</th>
			<th>Phone</th>
			<th>Party</th>
		</tr>';
			if($isNYC)
				print '<table>
		<tr>
			<th>VoterID</th>
			<th>Name</th>
			<th>Address</th>
			<th>City, State ZIP</th>
			<th>Sex</th>
			<th>DOB</th>
			<th>Party</th>
		</tr>';

			// loop through data
			for ($i=0; $i<$count; $i++) {
				set_time_limit(360);

				// get new record
				$row = mysqli_fetch_assoc($rs);
				if(!$isNYC)
					printf('<tr class="%s"><td>%s</td><td>%s %s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
						($i%2 > 0 ? 'even' : 'odd'),
						$row['voter id'],
						$row['first name'],
						$row['middle name'],
						$row['last name'],
						$row['address 1'],
						$row['address 3'],
						$row['gender'],
						$row['dob'],
						$row['phone'],
						$row['party']);
				else
					printf('<tr class="%s"><td>%s</td><td>%s %s %s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
						($i%2 > 0 ? 'even' : 'odd'),
						$row['voter ID'],
						$row['first name'],
						$row['middle name'],
						$row['last name'],
						$row['address 1'],
						$row['address 3'],
						$row['gender'],
						$row['dob'],
						$row['party']);
			}
			
			//Idk why but if I have it print before the table, it is displayed wrong
			//putting it down here displays it at the top of the table...
			print ("<h1>{$count} Record(s) Found in " . ucwords($currentCounty) . '.' . "</h1>");
		} else {
			if($_GET['county'] != "All of them"){
				print 'No Records Found in ' . ucwords($currentCounty) . '.' . '<br />';
			}
		}
	}
	$link->close();
?>
</body>
</html>
