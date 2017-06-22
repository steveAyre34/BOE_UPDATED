<?php
	require("header.php");
	require("connection.php");
?>
<h1>Board of Elections Data Filter</h1>
<br/>
<a href = "search_NEW/search_index_choose_county.php">Search BOE</a>
<form method="get" action="select.php">
Select County:<br/>

<select name="county">
<?php
	$result = mysqli_query($conn,"SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'boe2_database'") or die("error");
	$county_array = array();
	$borough_array = array("bronx", "brooklyn", "manhattan", "queens", "statenisland");
	while($row = $result->fetch_assoc()){
		$table_split = explode("_", $row["TABLE_NAME"]);
		$county = $table_split[0];
		if(!in_array($county, $county_array) && !in_array($county, $borough_array)){
			array_push($county_array, $county);
			echo "<option value = '$county'>" . ucwords($county) . "</option>";
		}
	}
?>
</select><input type="submit" value="go" />
</form>
<form method="get" action="/selectnyc.php">
Select NYC Borough:<br/>
<select name="county">
<?php
	$result = mysqli_query($conn,"SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'boe2_database'") or die("error");
	$borough_array = array("bronx", "brooklyn", "manhattan", "queens", "statenisland");
	while($row = $result->fetch_assoc()){
		$table_split = explode("_", $row["TABLE_NAME"]);
		$county = $table_split[0];
		if(in_array($county, $borough_array)){
			echo "<option value = '$county'>" . ucwords($county) . "</option>";
		}
	}
?>
</select><input type="submit" value="go" />
</form><br />
</body>
</html>