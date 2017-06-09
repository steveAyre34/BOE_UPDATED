<?php
	require("header.php");
	require("connection.php");
	$county = $_GET["county"];
	$county = lcfirst($county);
	//table to get data from
	$table_name = $county . "_import";
	$result = mysqli_query($conn, "SELECT create_time FROM INFORMATION_SCHEMA.TABLES WHERE table_name = '$table_name'") or die("error");
	$row = $result->fetch_assoc();
	$date_uploaded = $row["create_time"];
?>
<form method="post" action="query_boe.php" id = "submit_query">
<table width="800" border="0" cellpadding="6" cellspacing="2">
	<tr valign="bottom">
		<td class="dcheader"><h1>Find Records (<?php echo ucwords($county); ?>):</h1></td><td class="dcheader" align="right"><h1>Data Loaded on: <?php echo date('m/d/Y', strToTime($date_uploaded)); ?></h1></td>
	</tr>
</table>
<hr size="1">
<!--All inputs for export go here-->
<!--Choosing the table headers for the exported file-->
<table width="800" border="1" cellpadding="10">
	<tr valign="bottom"><td class="dcheader" style = "width: 100%"><h2>Exported Table Headers</h2></td></tr>
	<tr valign="bottom"><td class="dcheader"><input type="checkbox" name="standardcols" id="standardcols" checked="checked" disabled="disabled" title="Standard output" /></td><td><label>VoterID, Full Name, Address/City/State/ZIP</label></td></tr>
	<tr valign="bottom"><td class="dcheader"><input type="checkbox" name="phone_col" id="phone_col"></td><td><label>Phone Number (Limited Data)</label></td></tr>
	<tr valign="bottom"><td class="dcheader"><input type="checkbox" name="voter_status_col" id="voter_status_col"></td><td><label>Voter Status</label></td></tr>
	<tr valign="bottom"><td class="dcheader"><input type="checkbox" name="reason_col" id="reason_col"></td><td><label>Reason</label></td></tr>
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
		$result_zipcodes = mysqli_query($conn, "SELECT DISTINCT zip FROM $table_name WHERE zip != '' ORDER BY zip ASC");
		while($row_zipcodes = $result_zipcodes->fetch_assoc()){
			$zip = $row_zipcodes["zip"];
			echo "<option value = '$zip'>$zip</option>";
		}
		?></select></td>
	</tr>
	</table>
	</div>
	<div style = "float: left; width: 25%">
		<div style = "width: 50%;">
			<label>Age</label><input type = "checkbox" name = "age" id = "age">
			<span id = "range" style = "visibility: hidden"><label>Min</label><input id = "min_age" name = "min_age" style = "width: 20%" value = "21"><label>Max</label><input id = "max_age" name = "max_age" style = "width: 20%" value = "120"></span>
		</div>
		<div style = "width: 100%; margin-top: 5%">
			<label>Sex</label><input type = "checkbox" name = "sex" id = "sex">
			<select id = "sex_choice" name = "sex_choice" style = "visibility: hidden"><option selected = "selected" value = "M">Male</option><option value = "F">Female</option></select>
		</div>
		<div style = "width: 100%; margin-top: 5%">
			<label>Registration Date</label><input type = "checkbox" name = "reg_date" id = "reg_date">
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
</form>
<div style = "position: fixed; bottom: 0; width: 100%;">
<input value = "Export" onclick = "submitForm()" type="submit" class="button" style="width: 500px; height: 100px;font-size:30px;background-color:#00C957;"/>
<input value = "Count" onclick = "generateCount()" type="submit" class="button" style="width: 500px; height: 100px;font-size:30px;background-color:#00C957;"/>
<input id = "count" placeholder = "Generated Count" onclick = "generateCount()" class="button" style="width: 500px; height: 100px;font-size:30px;" readonly>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
$("#household").on("change", function(){
	$('#individual').prop('checked', false);
});
$("#individual").on("change", function(){
	$("#household").prop("checked", false);
});
$("#age").on("change", function(){
	if($("#age").is(":checked")){
		$("#range").css("visibility", "visible");
	}
	else{
		$("#range").css("visibility", "hidden");
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
	var data = ["household", [],  0, 0, "both", "", [], "all", ["DN"], ""];
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
		data[2] = $("#min_age").val();
		data[3] = $("#max_age").val();
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
	
	$.ajax({
		type: "POST",
		url: "generateCount.php",
		data: {id: data, table_name: data[9]},
		dataType: "json", // Set the data type so jQuery can parse it for you
		success: function (data){
			$("#count").val(data);
		}
	});
}
$(document).ready(function(){
	var date = new Date();
	document.getElementById("reg_date_choice").valueAsDate = date;
})
</script>