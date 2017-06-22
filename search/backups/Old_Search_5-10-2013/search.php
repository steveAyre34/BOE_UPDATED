<?php
	require_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Search Records</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css">
		H1 {
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 20px;
			color: #000066;
		}
		
		INPUT, SELECT, TEXTAREA, OPTION {
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 11px;
			color: #4B524C;
		}

		INPUT.button {
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 12px;
			color: #000000;
		}

	</style>
</head>

<body>
<h1>Search BoE Data</h1>
<form action="results.php" method="get" target="results">
<table>
	<tr>
		<th>First Name:</th>
		<td><input type="text" name="first_name" />    <b>Last Name:</b>  <input type="text" name="last_name" /></td>
	</tr>
	<tr>
		<th>Address:</th>
		<td><input type="text" name="address" /></td>
	</tr>
	<tr>
		<th>City</th>
		<td><input type="text" name="city" /></td>
	</tr>
	<tr>
		<th>ZIP</th>
		<td><input type="text" name="zip" /></td>
	</tr>
	<tr>
		<th>County:</th>
		<td><select name="county"><option></option>
<?
	foreach($counties as $c) {
		?><option value="<?= $c ?>"><?= ucwords($c) ?></option><?
	}
?>
			</select></td>
	</tr>
	<tr>
		<td colspan="2" align="right"><input type="submit" name="btnSearch" value="Search" /></td>
	</tr>
</table>
</form>
</body>
</html>