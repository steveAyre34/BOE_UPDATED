<?php
	require("connection.php");
?>
<h1>Choose County</h1>
<form method = "post" action = "search_index.php">
	<select name = "county">
		<?php
			$count = 1;
			$counties = array();
			$result = mysqli_query($conn,"SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'boe2_database'") or die("error");
			while($row = $result->fetch_assoc()){
				$table = $row["TABLE_NAME"];
				$county_explode = explode("_", $table);
				$county = $county_explode[0];
				$county_cap = ucwords($county);
				if($count == 1 && $county != "codes"){
					echo "<option selected = 'selected' value = '$county'>$county_cap</option>";
					array_push($counties, $county);
					$count++;
				}
				else{
					if(!in_array($county, $counties) && $county != "codes"){
						echo "<option value = '$county'>$county_cap</option>";
						array_push($counties, $county);
					}
				}
			}
		?>
	</select>
	<input type = "submit" value = "Go">
</form>