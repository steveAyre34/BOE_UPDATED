<?php
	require("connection.php");
?>
<head>
<title>BOE Search</title>
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
		<link rel="stylesheet" type="text/css" href="jquery.dataTables.css">
</title>
</head>
<h1>Choose County</h1>
<form action = "search_index.php" method = "POST">
<div class='allcontacts-table'><table border='0' cellspacing='0' cellpadding='0' class='table-bordered allcontacts-table' >
<tbody>
<tr valign='top'><td colspan='2'><table id = 'w_m_table' border='0' cellspacing='0' cellpadding='0' class='table-striped main-table contacts-list'><tbody>
<tr><td><label>First Name</label></td><td><input name = "first_name"></td></tr>
<tr><td><label>Last Name</label></td><td><input name = "last_name"></td></tr>
<tr><td><label>Street No.</label></td><td><input name = "street_no"></td></tr>
<tr><td><label>Street Name</label></td><td><input name = "street_name"></td></tr>
<tr><td><label>Apt. No.(if necessary)</label></td><td><input name = "apt_no"></td></tr>
<tr><td><label>City</label></td><td><input name = "city"></td></tr>
<tr><td><label>Zip</label></td><td><input name = "zip"></td></tr>
</tbody></table></td></tr></tbody></table></div>
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
	<input type = "submit" value = "Go" onclick = "test()">
</form>