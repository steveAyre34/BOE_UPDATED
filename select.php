<link href="style.css" rel="stylesheet">
<script src="JSFiles/jquery-3.2.1.min.js"></script>
<script src = "JSFiles/select.js"></script>
<body>
	<div class = "page-title">
		<span>BOE Filter</span>
	</div>
	<div class = "hotbuttons-div">
		<div class = "hotbutton retrieve-query-btn"><span>Retrieve Query</span></div>
		<div class = "hotbutton add-to-query-btn"><span>Add to Queue</span></div>
		<div class = "hotbutton reset-btn"><span>Reset</span></div>
	</div>
	<div class = "content-div">
		<div class = "results-div current-query-div">
			<p><u>Current Query Results</u></p>
			<p><b>Est. Individual Count: </b><span class = "Individual-result"></span></p>
			<p><b>Est. Householded Count: </b><span class = "householded-result"></span></p>
		</div>
		<div class = "section-div">
			<div class = "title">
				<span>Step 1: General Information</span>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div age-div">
					<input class = "checkbox-input number-inputs checkbox-select" type = "checkbox" id = "age"></input>
					<label>Age</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div gender-div">
					<input class = "checkbox-input static-dropdown checkbox-select" type = "checkbox" id = "gender"></input>
					<label>Gender</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div registered-div">
					<input class = "checkbox-input date-inputs checkbox-select" type = "checkbox" id = "registered"></input>
					<label>Registered</label>
				</div>
			</div>
		</div>
		<div class = "section-div">
			<div class = "title">
				<span>Step 2: Location Information</span>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div zip-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "zip"></input>
					<label>ZIP codes</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div town-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "town"></input>
					<label>Town Codes</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div village-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "village"></input>
					<label>Village Codes</label>
				</div>
			</div>
		</div>
		<div class = "results-div query-queue">
			<p><u>Queued Results</u><a class = "clear-queue-btn">clear</a></p>
			<ul class = "queued-results-list">
			</ul>
			<form class = "export-form" action = "serverside/boe-serverside.php" method = "POST">
				<input class = "export-btn" type = "submit" value = "Export">
			</form>
		</div>
		<div class = "section-div">
			<div class = "title">
				<span>Step 3: Voting/Party Data</span>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div voting-history-div">
					<input type = "checkbox" class = "checkbox-input special checkbox-select" id = "voting-history"></input>
					<label>Voting History</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div party-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "party"></input>
					<label>Party Affiliation</label>
				</div>
			</div>
		</div>
		<div class = "section-div last-section-div">
			<div class = "title">
				<span>Step 4: Other</span>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div ward-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "ward"></input>
					<label>Ward</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div fire_district-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "fire_district"></input>
					<label>Fire District</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div asm_district-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "asm_district"></input>
					<label>Assembly District</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div district-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "district"></input>
					<label>District</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div leg_district-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "leg_district"></input>
					<label>Legislative District</label>
				</div>
			</div>
			<div class = "section-option">
				<div class = "checkbox-options-div sen_district-div">
					<input type = "checkbox" class = "checkbox-input dynamic-dropdown checkbox-select" id = "sen_district"></input>
					<label>Senatorial District</label>
				</div>
			</div>
		</div>
	</div>
</body>