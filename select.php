<?php
	require("header.php");
	require("connection.php");
	session_start();
	$county = $_GET["county"];
	$county = lcfirst($county);
	//table to get data from
	$table_name = $county . "_import";
	$table_name_verified = $county . "_verified";
	$_SESSION["table_name"] = $table_name;
	$result = mysqli_query($conn, "SELECT create_time FROM INFORMATION_SCHEMA.TABLES WHERE table_name = '$table_name_verified'") or die("error");
	$county_uppercase = ucwords($county);
	$result_codes = mysqli_query($conn, "SELECT * FROM codes WHERE county = '$county_uppercase'") or die("error in codes");
	$array_codes = array();
	while($row_codes = $result_codes->fetch_assoc()){
			$category = $row_codes["category"];
			$code = $row_codes["code"];
			$description = $row_codes["description"];
			$key = $category . "_" . $code;
			$array_codes[$key] = $description;
			//echo $key;
	}
	$row = $result->fetch_assoc();
	$date_uploaded = $row["create_time"];
?>
<style>
.dropdown{
	width: 20%;
	background: #2b6287;
	color: #FFFFFF;
	text-align: center;
	border-radius: 35px;
	font-size: 25px;
	cursor: pointer;
}
</style>
<form method="post" action="query_boe.php" id = "submit_query">
<table width="800" border="0" cellpadding="6" cellspacing="2">
	<tr valign="bottom">
		<td class="dcheader"><h1>Find Records (<?php echo ucwords($county); ?>):</h1></td><td class="dcheader" align="right"><h1>Data Loaded on: <?php echo date('m/d/Y', strToTime($date_uploaded)); ?></h1></td>
	</tr>
</table>
<hr size="1">
<!--All inputs for export go here-->
<!--Choosing the table headers for the exported file-->
<div class = "dropdown" onclick = "showExport()">Exported Table Headers<img src = "dropdown_arrow.png" width = "30" height = "30"></div>
<table id = "export_table" width="800" border="1" cellpadding="10" style = "display: none">
	<tr valign="bottom"><td class="dcheader" style = "width: 100%"><h2>Exported Table Headers</h2></td></tr>
	<tr valign="bottom"><td class="dcheader"><input type="checkbox" name="standardcols" id="standardcols" checked="checked" title="Standard output" /></td><td><label>VoterID, Full Name, Address/City/State/ZIP</label></td></tr>
	<tr valign="bottom"><td class="dcheader"><input type="checkbox" name="absentee_col" id="absentee_col"></td><td><label>Absentee</label></td></tr>
	<tr valign="top" class="dcfieldname">
		<td align="right">Run counts and reports for&nbsp;</td>
		<td><input type="radio" name="household" id="household" checked><label style = "margin-right: 25%">Households</label>
			<input type="radio" name="individual" id="individual"><label>Individuals</label></td>
	</tr>
</table>
<div>
	<div style = "display: inline-block; float: left; margin-right: 2%; width: 15%">
	<table border="1" cellpadding="2" cellspacing="2">
	<tr valign="bottom">
	<td class="dcheader" style = "width: 50%"><select id = "zipcodes" name="zipcodes" style = "width: 100%; height: 300px" multiple>
		<option value="ignore" selected = "selected">--Select Zipcodes--</option>
		<option value="ignore">--ignore--</option>
		<?php
	//generate all zipcodes in the town or borough
		$result_zipcodes = mysqli_query($conn, "SELECT zip FROM $table_name WHERE zip != '' ORDER BY zip ASC");
		$array_zips = array();
		$array_zip_counts = array();
		$last = "";
		$index = -1;
		while($row_zipcodes = $result_zipcodes->fetch_assoc()){
			$zip = $row_zipcodes["zip"];
			if($zip == $last){
				$array_zips_counts[$index] += 1;
			}
			else{
				array_push($array_zips, $zip);
				array_push($array_zips_counts, 1);
				$index++;
			}
			$last = $zip;
		}
		
		for($i = 0; $i < count($array_zips); $i++){
			$zip = $array_zips[$i];
			$count = $array_zips_counts[$i];
			echo "<option value = '$zip'>$zip: Count= $count</option>";
		}
		?></select></td>
	</tr>
	</table>
	</div>
	<div style = "float: left; width: 25%">
		<div style = "width: 50%;">
			<label>Age</label><input type = "checkbox" name = "age" id = "age">
			<span id = "range" style = "visibility: hidden"><label>Min</label><input id = "min_age" name = "min_age" style = "width: 20%" value = "21"><label>Max</label><input id = "max_age" name = "max_age" style = "width: 20%" value = "120"></span>
			<span id = "suppression" style = "display: none"><label>Suppression</label><input type = "checkbox" id = "suppression_checked"><select id = "under_or_over"><option selected = "selected" value = "over">Over</option><option value = "under">Under</option></select><input id = "age_value" placeholder = "--Enter Age--"></span>
		</div>
		<div style = "width: 100%; margin-top: 5%">
			<label>Sex</label><input type = "checkbox" name = "sex" id = "sex">
			<select id = "sex_choice" name = "sex_choice" style = "visibility: hidden"><option selected = "selected" value = "M">Male</option><option value = "F">Female</option></select>
		</div>
		<div style = "width: 100%; margin-top: 5%">
			<label>After Registration Date</label><input type = "checkbox" name = "reg_date" id = "reg_date">
			<input id = "reg_date_choice" name = "reg_date_choice" style = "visibility: hidden" type = "date">
		</div>
	</div>
	<div style = "float: left; width: 30%">
		<div style = "width: 15%;">
			<label>All</label>
			<input type = "checkbox" name = "all_elections" class = "elections" id = "all_elections" value = "all" checked>
		</div>
		<div style = "width: 15%; margin-top: 1%">
			<label>General</label>
			<input type = "checkbox" name = "general_elections" class = "elections" id = "general_elections" value = "GE">
		</div>
		<div style = "margin-top: 1%">
			<label>Primary</label>
			<input type = "checkbox" name = "primary_elections" class = "elections" id = "primary_elections" value = "PE">
		</div>
		<div style = "margin-top: 1%">
			<label>Presidential Primary</label>
			<input type = "checkbox" name = "pres_primary_elections" class = "elections" id = "pres_primary_elections" value = "PP">
		</div>
		<div style = "margin-top: 1%">
			<select id = "years_voted" name = "years_voted"><option value = "all" selected = "selected">All</option><option value = "any">Any</option></select><span>of these years</span>
			<select id = "voting_years" name = "voting_years" style = "width: 15%" multiple>
			<?php
				$current_year = date("Y");
				for($i = $current_year; $i >= 1995; $i--){
					echo "<option value = '$i'>$i</option>";
				}
			?>
			</select>
		</div>
	</div>
</div>
<div style = "width: 100%;">
	<div style = "width: 15%; padding-bottom: 1%; float: left; clear: both; margin-right: 2%">
		<label><h4>Party</h4></label>
		<select id = "party" name = "party" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT party from $table_name WHERE party != '' ORDER BY party");
				while($row = $result->fetch_assoc()){
					$party = $row["party"];
					echo "<option value = '$party'>$party</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>Town</h4></label>
		<select id = "town" name = "town" style = "height: 200px; width: 100%;" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT town from $table_name WHERE town != '' ORDER BY town");
				while($row = $result->fetch_assoc()){
					$town = $row["town"];
					$key = "town_" . $town;
					$description = $array_codes[$key];
					echo "<option value = '$town'>$town: $description</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>Ward</h4></label>
		<select id = "ward" name = "ward" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT ward from $table_name WHERE ward != '' ORDER BY ward");
				while($row = $result->fetch_assoc()){
					$ward = $row["ward"];
					$key = "ward_" . $ward;
					$description = $array_codes[$key];
					echo "<option value = '$ward'>$ward: $description</option>";
				}
			?>
		</select>
	</div>
</div>
<div style = "width: 100%;">
	<div style = "width: 15%; padding-bottom: 1%; float: left; clear: both; margin-right: 2%">
		<label><h4>District</h4></label>
		<select id = "district" name = "district" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT district from $table_name WHERE district != '' ORDER BY district");
				while($row = $result->fetch_assoc()){
					$district = $row["district"];
					$key = "district_" . $district;
					$description = $array_codes[$key];
					echo "<option value = '$district'>$district: $description</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>Congressional District</h4></label>
		<select id = "cong_district" name = "cong_district" style = "height: 200px; width: 100%;" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT cong_district from $table_name WHERE cong_district != '' ORDER BY cong_district");
				while($row = $result->fetch_assoc()){
					$cong_district = $row["cong_district"];
					$key = "cong_district_" . $cong_district;
					$description = $array_codes[$key];
					echo "<option value = '$cong_district'>$cong_district: $description</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>Senate District</h4></label>
		<select id = "sen_district" name = "sen_district" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT sen_district from $table_name WHERE sen_district != '' ORDER BY sen_district");
				while($row = $result->fetch_assoc()){
					$sen_district = $row["sen_district"];
					$key = "sen_district_" . $sen_district;
					$description = $array_codes[$key];
					echo "<option value = '$sen_district'>$sen_district: $description</option>";
				}
			?>
		</select>
	</div>
</div>
<div style = "width: 100%;">
	<div style = "width: 15%; padding-bottom: 1%; float: left; clear: both; margin-right: 2%">
		<label><h4>School District</h4></label>
		<select id = "school_district" name = "school_district" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT school_district from $table_name WHERE school_district != '' ORDER BY school_district");
				while($row = $result->fetch_assoc()){
					$school_district = $row["school_district"];
					$key = "school_district_" . $school_district;
					$description = $array_codes[$key];
					echo "<option value = '$school_district'>$school_district: $description</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>Assembly District</h4></label>
		<select id = "asm_district" name = "asm_district" style = "height: 200px; width: 100%;" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT asm_district from $table_name WHERE asm_district != '' ORDER BY asm_district");
				while($row = $result->fetch_assoc()){
					$asm_district = $row["asm_district"];
					$key = "asm_district_" . $asm_district;
					$description = $array_codes[$key];
					echo "<option value = '$asm_district'>$asm_district: $description</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>Fire District</h4></label>
		<select id = "fire_district" name = "fire_district" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT fire_district from $table_name WHERE fire_district != '' ORDER BY fire_district");
				while($row = $result->fetch_assoc()){
					$fire_district = $row["fire_district"];
					$key = "fire_district_" . $fire_district;
					$description = $array_codes[$key];
					echo "<option value = '$fire_district'>$fire_district: $description</option>";
				}
			?>
		</select>
	</div>
</div>
<div style = "width: 100%;">
	<div style = "width: 15%; padding-bottom: 1%; float: left; clear: both; margin-right: 2%">
		<label><h4>Legislative District</h4></label>
		<select id = "leg_district" name = "leg_district" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT leg_district from $table_name WHERE leg_district != '' ORDER BY leg_district");
				while($row = $result->fetch_assoc()){
					$leg_district = $row["leg_district"];
					$key = "leg_district_" . $leg_district;
					$description = $array_codes[$key];
					echo "<option value = '$leg_district'>$leg_district: $description</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>Village</h4></label>
		<select id = "village" name = "village" style = "height: 200px; width: 100%;" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT village from $table_name WHERE village != '' ORDER BY village");
				while($row = $result->fetch_assoc()){
					$village = $row["village"];
					$key = "village_" . $village;
					$description = $array_codes[$key];
					echo "<option value = '$village'>$village: $description</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>User 1</h4></label>
		<select id = "user1" name = "user1" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT user1 from $table_name WHERE user1 != '' ORDER BY user1");
				while($row = $result->fetch_assoc()){
					$user1 = $row["user1"];
					$key = "user1_" . $user1;
					$description = $array_codes[$key];
					echo "<option value = '$user1'>$user1: $description</option>";
				}
			?>
		</select>
	</div>
</div>
<div style = "width: 100%;">
	<div style = "width: 15%; float: left; margin-right: 2%; padding-bottom: 10%; clear: both">
		<label><h4>User 2</h4></label>
		<select id = "user2" name = "user2" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT user2 from $table_name WHERE user2 != '' ORDER BY user2");
				while($row = $result->fetch_assoc()){
					$user2 = $row["user2"];
					$key = "user2_" . $user2;
					$description = $array_codes[$key];
					echo "<option value = '$user2'>$user2: $description</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>User 3</h4></label>
		<select id = "user3" name = "user3" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT user3 from $table_name WHERE user3 != '' ORDER BY user3");
				while($row = $result->fetch_assoc()){
					$user3 = $row["user3"];
					$key = "user3_" . $user3;
					$description = $array_codes[$key];
					echo "<option value = '$user3'>$user3: $description</option>";
				}
			?>
		</select>
	</div>
	<div style = "width: 15%; float: left; margin-right: 2%">
		<label><h4>User 4</h4></label>
		<select id = "user4" name = "user4" style = "height: 200px; width: 100%" multiple>
		<option value = "ignore" selected = "selected">--ignore--</option>
			<?php
				$result = mysqli_query($conn, "SELECT DISTINCT user4 from $table_name WHERE user4 != '' ORDER BY user4");
				while($row = $result->fetch_assoc()){
					$user4 = $row["user4"];
					$key = "user4_" . $user4;
					$description = $array_codes[$key];
					echo "<option value = '$user4'>$user4: $description</option>";
				}
			?>
		</select>
	</div>
</div>
<input id = "query" style = "display: none" name = "query" value = "">
<input id = "count_get" style = "display: none" name = "count_get" value = "">
</form>
<div style = "margin-bottom: 20%">
</div>
<div style = "position: fixed; bottom: 0; width: 100%;">
<input id = "export" value = "Export" onclick = "submitForm()" type="submit" class="button" style="display: none; width: 500px; height: 100px;font-size:30px;background-color:#2572ed; color: #FFFFFF"/>
<input value = "Count and Retrieve Query" onclick = "generateCount()" type="submit" class="button" style="width: 500px; height: 100px;font-size:30px;background-color:#2572ed; color: #FFFFFF"/>
<input id = "count" placeholder = "Generated Count" onclick = "generateCount()" class="button" style="width: 500px; height: 100px;font-size:30px;" readonly><img id = "loader" style = "display: none" width = "100" height = "100" src = "loader.gif">
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
function showExport(){
	if($("#export_table").css("display") == 'none'){
		$("#export_table").show(1000);
	}
	else{
		$("#export_table").hide(1000);
	}
}
$('#submit_query input').on("change", function(){
    $("#export").hide(1000);
});
$('#submit_query select').on("change", function(){
    $("#export").hide(1000);
});
$("#standardcols").on("change", function(){
	$('#standardcols').prop('checked', true);
});
$("#household").on("change", function(){
	$('#individual').prop('checked', false);
});
$("#individual").on("change", function(){
	$("#household").prop("checked", false);
});
$("#age").on("change", function(){
	if($("#age").is(":checked")){
		$("#range").css("visibility", "visible");
		$("#suppression").show();
		$("#under_or_over").hide();
		$("#age_value").hide();
		$("#suppression_checked").prop("checked", false);
	}
	else{
		$("#range").css("visibility", "hidden");
		$("#suppression").hide();
	}
});
$("#suppression_checked").on("change", function(){
	if($("#suppression_checked").is(":checked")){
		$("#under_or_over").show();
		$("#age_value").show();
		$("#range").css("visibility", "hidden");
		$("#min_age").val(0);
		$("#max_age").val(0);
	}
	else{
		$("#age_value").val(0);
		$("#under_or_over").hide();
		$("#age_value").hide();
		$("#range").css("visibility", "visible");
	}
});
$("#sex").on("change", function(){
	if($("#sex").is(":checked")){
		$("#sex_choice").css("visibility", "visible");
	}
	else{
		$("#sex_choice").css("visibility", "hidden");
	}
});
$("#reg_date").on("change", function(){
	if($("#reg_date").is(":checked")){
		$("#reg_date_choice").css("visibility", "visible");
	}
	else{
		$("#reg_date_choice").css("visibility", "hidden");
	}
});
$(".elections").on("change", function(){
	if($("#all_elections").is(":checked")){
		$("#general_elections").prop("checked", false);
		$("#primary_elections").prop("checked", false);
		$("#pres_primary_elections").prop("checked", false);
	}
	if(!$("#all_elections").is(":checked") && !$("#general_elections").is(":checked") && !$("#primary_elections").is(":checked") && !$("#pres_primary_elections").is(":checked")){
		$("#all_elections").prop("checked", true);
	}
});
$(".elections").on("click", function(){
	if($(this).attr("id") != "all_elections"){
		$("#all_elections").prop("checked", false);
}
});
function submitForm(){
	$("#submit_query").submit();
}
function generateCount(){
	$("#count").val("");
	$("#loader").show();
	var data = ["household", [],  0, 0, "both", "", [], "all", ["DN"], "", [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], 0, "over"];
	//check if individual or household
	if($("#individual").is(":checked")){
		data[0] = "individual";
	}
	
	//check for all zipcodes
	$('#zipcodes :selected').each(function(){ 
		data[1].push($(this).val());
	});
	
	//check if age is checked off
	if($("#age").is(":checked")){
		if(!$("#suppression_checked").is(":checked")){
			data[2] = $("#min_age").val();
			data[3] = $("#max_age").val();
		}
		else{
			data[25] = $("#age_value").val();
			data[26] = $("#under_or_over").val();
		}
	}
	//check if sex is checked off
	if($("#sex").is(":checked")){
		data[4] = $("#sex_choice").val();
	}
	
	//check if registration is checked off
	if($("#reg_date").is(":checked")){
		data[5] = $("#reg_date_choice").val();
	}
	
	
	//get all elections from each
	$(".elections").each(function(){
		var count = 1;
		if($(this).is(":checked")){
			data[6].push($(this).val())
		}
	});
	
	//get any or all years for voting
	data[7] = $("#years_voted").val();
	
	//get all voting years
	$('#voting_years :selected').each(function(){
		data[8][0] = "U";
		data[8].push($(this).val());
	});
	
	var table_name = <?php echo json_encode($table_name); ?>;
	data[9] = table_name;
	
	//check for all parties
	var count = 0;
	$('#party :selected').each(function(){ 
		data[10].push($(this).val());
		count++;
	});
	if(count == 0){
		data[10].push("ignore");
	}
	
	//check for towns
	count = 0;
	$('#town :selected').each(function(){ 
		data[11].push($(this).val());
		count++;
	});
	if(count == 0){
		data[11].push("ignore");
	}
	
	//check for all wards
	count = 0;
	$('#ward :selected').each(function(){ 
		data[12].push($(this).val());
		count++;
	});
	if(count == 0){
		data[12].push("ignore");
	}
	
	//check for all districts
	count = 0;
	$('#district :selected').each(function(){ 
		data[13].push($(this).val());
		count++;
	});
	if(count == 0){
		data[13].push("ignore");
	}
	
	//check for all congressional Districts
	count = 0;
	$('#cong_district :selected').each(function(){ 
		data[14].push($(this).val());
		count++;
	});
	if(count == 0){
		data[14].push("ignore");
	}
	
	//check for all senate districts
	count = 0;
	$('#sen_district :selected').each(function(){ 
		data[15].push($(this).val());
		count++;
	});
	if(count == 0){
		data[15].push("ignore");
	}
	
	//check for all school districts
	count = 0;
	$('#school_district :selected').each(function(){ 
		data[16].push($(this).val());
		count++;
	});
	if(count == 0){
		data[16].push("ignore");
	}
	
	//check for all assembly districts
	count = 0;
	$('#asm_district :selected').each(function(){ 
		data[17].push($(this).val());
		count++;
	});
	if(count == 0){
		data[17].push("ignore");
	}
	
	//check for all fire districts
	count = 0;
	$('#fire_district :selected').each(function(){ 
		data[18].push($(this).val());
		count++;
	});
	if(count == 0){
		data[18].push("ignore");
	}
	
	//check for all legislative districts
	count = 0;
	$('#leg_district :selected').each(function(){ 
		data[19].push($(this).val());
		count++;
	});
	if(count == 0){
		data[19].push("ignore");
	}
	
	//check for all villages
	count = 0;
	$('#village :selected').each(function(){ 
		data[20].push($(this).val());
		count++;
	});
	if(count == 0){
		data[20].push("ignore");
	}
	
	//check for all user 1, user 2, user 3, and user 4
	count = 0;
	$('#user1 :selected').each(function(){ 
		data[21].push($(this).val());
		count++;
	});
	if(count == 0){
		data[21].push("ignore");
	}
	
	count = 0;
	$('#user2 :selected').each(function(){ 
		data[22].push($(this).val());
		count++;
	});
	if(count == 0){
		data[22].push("ignore");
	}
	
	count = 0;
	$('#user3 :selected').each(function(){ 
		data[23].push($(this).val());
		count++;
	});
	if(count == 0){
		data[23].push("ignore");
	}
	
	count = 0;
	$('#user4 :selected').each(function(){ 
		data[24].push($(this).val());
		count++;
	});
	if(count == 0){
		data[24].push("ignore");
	}
	
	
	$.ajax({
		type: "POST",
		url: "generateCount.php",
		data: {id: data, table_name: data[9]},
		dataType: "json", // Set the data type so jQuery can parse it for you
		success: function (data){
			$("#count").val(data[0]);
			$("#count_get").val(data[0]);
			$("#query").val(data[1]);
			$("#loader").hide();
			$("#export").show(1000);
		}
	});
}
$(document).ready(function(){
	var date = new Date();
	document.getElementById("reg_date_choice").valueAsDate = date;
})
</script>