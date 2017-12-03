/*All Javascript for select.php page*/

//Global static dropdown options
var static_dropdowns = {"gender": {
							M: "Male",
							F: "Female"
						}};

//global variables for query list
var queue_statements = [];
var individual_queue_count = 0;
var householded_queue_count = 0;

var current_query = "";

(function(){
	/*Destroy Queue Session on page load*/
	$.ajax({
		type: "POST",
		url: "./serverside/boe-serverside.php",
		data: JSON.stringify({clear_queue: {}}),
		contentType: "application/json", // Set the data type so jQuery can parse it for you
		success: function (response){
			$('.queued-results-list').html("");
		}
	});
	
	/*Save county into session*/
	window.onload = function(){
		$.ajax({
			type: "POST",
			url: "./serverside/boe-serverside.php",
			data: JSON.stringify({county_session: {countyName: $('.county-name').val()}}),
			contentType: "application/json", // Set the data type so jQuery can parse it for you
			success: function (response){
				
			}
		});
	}
	/*Reset checkboxes selected and trigger change event*/
	$(document).on("click", '.reset-btn', function(){
		$('.checkbox-select').prop("checked", false);
		$('.checkbox-select').trigger("change");
		$('.current-query-div').css("background", "#dbdbdb");
		$('.Individual-result').text("");
		$('.householded-result').text("");
		current_query = "";
	});
	/*Add all important input fields depending on checkbox selected*/
	$(document).on("change", '.checkbox-input', function(e){
		//Get all basic information for adding extra information
		var class_name = $(this).attr("class").split(" ")[1];
		var this_id = $(this).attr("id");
		var div = $('.' + this_id + '-div').attr("class");
		var is_checked = $(this).prop("checked");
		
		switch(class_name){
			case "number-inputs":
				if(is_checked){
					addNumberInputs(div, this_id);
				}
				else{
					removeNumberInputs(this_id);
				}
				break;
			case "static-dropdown":
				if(is_checked){
					addStaticDropdown(div, this_id);
				}
				else{
					removeStaticDropdown(this_id);
				}
				break;
			case "date-inputs":
				if(is_checked){
					addDateInputs(div, this_id);
				}
				else{
					removeDateInputs(this_id);
				}
				break;
			case "dynamic-dropdown":
				if(is_checked){
					addDynamicDropdown(div, this_id);
				}
				else{
					removeDynamicDropdown(this_id);
				}
				break;
			default:
				if(this_id == "voting-history"){
					if(is_checked){
						addVotingHistory(div);
					}
					else{
						removeVotingHistory();
					}
				}
				break;
		}
	});
	
	/*Change number inputs amount based on match dropdown selection*/
	$(document).on("change", '.match-dropdown-selection', function(){
		var this_id = $(this).attr("id");
		
		if($(this).val() == "Between"){
			$('.' + this_id + '-first-number-div').after('<div class = "checkbox-options-div ' + this_id + '-second-number-div">'
															+'and<input class = "number-option ' + this_id + '-second-number-value" type = "number">'
														  +'</div>');
		}
		else{
			$('.' + this_id + '-second-number-div').remove();
		}
	});
	
	/*Change date inputs amount based on match dropdown selection*/
	$(document).on("change", '.match-dropdown-selection-date', function(){
		var this_id = $(this).attr("id");
		
		if($(this).val() == "Between"){
			$('.' + this_id + '-first-date-div').after('<div class = "checkbox-options-div ' + this_id + '-second-date-div">'
															+'and<input class = "date-option ' + this_id + '-second-date-value" type = "date">'
														  +'</div>');
		}
		else{
			$('.' + this_id + '-second-date-div').remove();
		}
	});
	
	/*Add value to ul based on option list selected*/
	/*add item number for deleting option as well*/
	var item_id = 1;
	$(document).on("change", '.dynamic-dropdown-selection', function(){
		
		var this_id = $(this).attr("id");
		var value = $('.' + this_id + "-dynamic-dropdown").val();
		if(!inDynamicChosen(value, this_id)){
			$('.' + this_id + '-dynamic-chosen-list').append('<li class = "dynamic-list-item item' + item_id + '">' + value + '<span class = "dynamic-list-item-delete-btn" id = "' + item_id + '">x</span></li>');
			item_id++;
		}
		else{
			alert("Already in list");
		}
		$('.' + this_id + "-dynamic-dropdown").val($('.' + this_id + '-dynamic-dropdown option:first').val());
	});
	
	/*Delete option from list*/
	$(document).on("click", '.dynamic-list-item-delete-btn', function(){
		var this_id = $(this).attr("id");
		$('.item' + this_id).remove();
		$(".current-query-div").css("background", "#ffcece");
	});
	
	/*Voting History Radio Buttons*/
	$(document).on("click", '.voting-history-radio', function(){
		var value = $(this).val();
		if(value == "all"){
			$(".voting-history-radio").prop("checked", false);
			$(this).prop("checked", true);
		}
		else{
			$(".voting-history-radio[value='all']").prop("checked", false);
		}
	});
	
	//-------------------------------------
	//Retrieve Query with count
	//-------------------------------------
	$(document).on("click", '.retrieve-query-btn', function(){
		$('.loading-query').css("visibility", "visible");
		var this_search_criteria = [];
		$('.checkbox-select:checked').each(function(){
			var this_id = $(this).attr("id");
			switch(this_id){
				case "age": //Add Age search criteria
					var d = new Date();
					var day = d.getDate();
					var month = d.getMonth() + 1;
					var year = d.getFullYear();
					var first_year = year - $('.age-first-number-value').val();
					first_year += "-" + month + "-" + day;
					if($(".age-match-selection").val() == "Between"){
						var second_year = year - $('.age-second-number-value').val();
						second_year += "-" + month + "-" + day;
						this_search_criteria.push(addSearchIndex("dob", "less than", first_year, "single"));
						this_search_criteria.push(addSearchIndex("dob", "greater than", second_year, "single"));
					}
					else if($(".age-match-selection").val() == "less than"){
						this_search_criteria.push(addSearchIndex("dob", "greater than", first_year, "single"));
					}
					else if($(".age-match-selection").val() == "greater than"){
						this_search_criteria.push(addSearchIndex("dob", "less than", first_year, "single"));
					}
					else{
						this_search_criteria.push(addSearchIndex("dob", $(".age-match-selection").val(), first_year, "single"));
					}
					break;
				case "gender": //add gender search criteria
					this_search_criteria.push(addSearchIndex("sex", "exact", $('.gender-static-dropdown').val(), "single"));
					break;
				case "registered":
					if($(".registered-match-selection").val() == "Between"){
						this_search_criteria.push(addSearchIndex("reg_datetime", "greater than", $('.registered-first-date-value').val(), "single"));
						this_search_criteria.push(addSearchIndex("reg_datetime", "less than", $('.registered-second-date-value').val(), "single"));
					}
					else{
						this_search_criteria.push(addSearchIndex("reg_datetime", $(".registered-match-selection").val(), $('.registered-first-date-value').val(), "single"));
					}
					break;
				case "voting-history": //add voting history search criteria
					var radio_values = [];
					$('.voting-history-radio:checked').each(function(){
						radio_values.push($(this).val());
					});
					
					var year_values = [];
					$('.voting-history-dynamic-chosen-list li').each(function(){
						year_values.push($(this).text().substring(0,  $(this).text().length - 1));
					});

					if(year_values.length > 0 || radio_values[0] != "all"){
						this_search_criteria.push(addHistorySearch(radio_values, year_values, $('.voting-history-years-type').val()));
					}
					break;
				default: //all dynamic dropdowns
					var values = [];
					$('.' + this_id + '-dynamic-chosen-list li').each(function(){
						values.push($(this).text().substring(0,  $(this).text().length - 1));
					});
					this_search_criteria.push(addSearchIndex(this_id, "exact", values, "multiple"));
					break;
			}
		});
		$.ajax({
			type: "POST",
			url: "./serverside/boe-serverside.php",
			data: JSON.stringify({retrieve_query: {countyName: $('.county-name').val(), searchCriteria: this_search_criteria}}),
			contentType: "application/json", // Set the data type so jQuery can parse it for you
			success: function (response){
				$('.loading-query').css("visibility", "hidden");
				$('.Individual-result').text(response["count"]);
				$('.householded-result').text(response["count_householded"]);
				$(".current-query-div").css("background", "#ceffd2");
				current_query = response["sql_query"];
			}
		});
	});
	
	//-----------------------------------------
	//Color Code Functions for current results
	//-----------------------------------------
	
	/*highlight current query block when changing results*/
	$(document).on("change keyup", 'input', function(){
		$(".current-query-div").css("background", "#ffcece");
	});
	
	$(document).on("change", 'select', function(){
		$(".current-query-div").css("background", "#ffcece");
	});
	
	/*==================================
	 *===========Add to Queue===========
	 *==================================*/
	$(document).on("click", '.add-to-query-btn', function(){
		if($('.current-query-div').css("background-color") == "rgb(206, 255, 210)"){
			var query_name = prompt("Title of query(45 characters)");
			var check_exists = checkQueueStatements(query_name);
			if(query_name != null && query_name.length <= 45 && query_name != "" && !check_exists){
				$.ajax({
					type: "POST",
					url: "./serverside/boe-serverside.php",
					data: JSON.stringify({add_to_queue: {name: query_name, query: current_query}}),
					contentType: "application/json", // Set the data type so jQuery can parse it for you
					success: function (response){
						$('.queued-results-list').append('<li>' + query_name + '</li>');
					}
				});
			}
			else if(query_name.length > 45){ //error checks
				alert("Name exceeds max length");
				$('.add-to-query-btn').trigger("click");
			}
			else if(query_name == ""){
				alert("Name left blank");
				$('.add-to-query-btn').trigger("click");
			}
			else if(check_exists){
				alert("Name already exists");
				$('.add-to-query-btn').trigger("click");
			}
		}
		else{
			alert("You must Retrieve Query first");
		}
	});
	
	/*clear queue results*/
	$(document).on("click", '.clear-queue-btn', function(){
		$.ajax({
			type: "POST",
			url: "./serverside/boe-serverside.php",
			data: JSON.stringify({clear_queue: {}}),
			contentType: "application/json", // Set the data type so jQuery can parse it for you
			success: function (response){
				$('.queued-results-list').html("");
			}
		});
		$('.queued-results-list').html("");
	});
	
	
	/*======================================
	 *==========EXPORT CHECK================
	 *======================================*/
	 
	$(document).on("click", '.export-btn', function(e){
		if($(".queued-results-list li").length == 0){
			e.preventDefault();
			alert("Queue Empty");
		}
		else{
			submitForm('.export-form');
		}
	});
	
})();

/*======================================
 *=============OTHER FUNCTIONS==========
 *======================================*/
 
/*Create Number Inputs for a select*/
/*PARAMS: div(to place after), this_id(represent the number inputs field its creating)*/
function addNumberInputs(div, this_id){
	$('.' + div.split(' ').join('.')).after('<div class = "checkbox-options-div ' + this_id + '-match-dropdown-div">'
												+'<select class = "dropdown-select match-dropdown-selection ' + this_id + '-match-selection" id = "' + this_id + '">'
													+'<option>Between</option>'
													+'<option value = "less than">Less Than</option>'
													+'<option value = "greater than">Greater Than</option>'
													+'<option value = "exact">Exact</option>'
												+'</select>'
											+'</div>'
											+'<div class = "checkbox-options-div ' + this_id + '-first-number-div">'
												+'<input class = "number-option ' + this_id + '-first-number-value" type = "number">'
											+'</div>'
											+'<div class = "checkbox-options-div ' + this_id + '-second-number-div">'
												+'and<input class = "number-option ' + this_id + '-second-number-value" type = "number">'
											+'</div>');
}

/*Remove number inputs depending on id of checkbox selected*/
/*PARAMS: Id of checkbox(Used to find and delete all necessary divs)*/
function removeNumberInputs(this_id){
	$('.' + this_id + '-match-dropdown-div').remove();
	$('.' + this_id + '-first-number-div').remove();
	$('.' + this_id + '-second-number-div').remove();
}

/*Add static dropdown items by checkbox selected*/
function addStaticDropdown(div, this_id){
	$('.' + div.split(' ').join('.')).after('<div class = "checkbox-options-div ' + this_id + '-static-dropdown-div">'
												+'<select class = "dropdown-select ' + this_id + '-static-dropdown">'
													+'<option>--Select ' + this_id + '--</option>'
												+'</select>'
											+'</div>');
	for(item in static_dropdowns[this_id]){
		var value = item;
		var this_text = static_dropdowns[this_id][item];
		$('.' + this_id + '-static-dropdown').append($('<option>', {
			value: value,
			text: this_text,
		}));
	}
}

/*remove static dropdown by checkbox deselected*/
function removeStaticDropdown(this_id){
	$('.' + this_id + '-static-dropdown-div').remove();
}

/*Add date inputs for selected item*/
function addDateInputs(div, this_id){
	$('.' + div.split(' ').join('.')).after('<div class = "checkbox-options-div ' + this_id + '-match-dropdown-div">'
														+'<select class = "dropdown-select match-dropdown-selection-date ' + this_id + '-match-selection" id = "' + this_id + '">'
															+'<option>Between</option>'
															+'<option value = "less than">Less Than</option>'
															+'<option value = "greater than">Greater Than</option>'
															+'<option value = "exact">Exact</option>'
														+'</select>'
													+'</div>'
													+'<div class = "checkbox-options-div ' + this_id + '-first-date-div">'
														+'<input class = "date-option ' + this_id + '-first-date-value" type = "date">'
													+'</div>'
													+'<div class = "checkbox-options-div ' + this_id + '-second-date-div">'
														+'and<input class = "date-option ' + this_id + '-second-date-value" type = "date">'
													+'</div>');
}

/*remove date inputs by checkbox id*/
function removeDateInputs(this_id){
	$('.' + this_id + '-match-dropdown-div').remove();
	$('.' + this_id + '-first-date-div').remove();
	$('.' + this_id + '-second-date-div').remove();
}

/*add a dynamic dropdown based on checkbox selected*/
/*Calls database in order to retrieve appropriate data*/
function addDynamicDropdown(div, this_id){
	$('.' + div.split(' ').join('.')).after('<div class = "loading-div dropdown-load">'
												+'<img width = "20" height = "20" src = "loader.gif">'
											+'</div>');
	$.ajax({
		type: "POST",
		url: "./serverside/boe-serverside.php",
		data: JSON.stringify({get_column_info: {columnName: this_id, countyName: $('.county-name').val()}}),
		contentType: "application/json", // Set the data type so jQuery can parse it for you
		success: function (response){
			$('.dropdown-load').remove();
			$('.' + div.split(' ').join('.')).after('<div class = "checkbox-options-div ' + this_id + '-dynamic-dropdown-div">'
														+'<select class = "dropdown-select dynamic-dropdown-selection ' + this_id + '-dynamic-dropdown" id = "' + this_id + '">'
															+'<option>--Select ' + this_id + '--</option>'
														+'</select>'
													+'</div>'
													+'<div class = "checkbox-options-div ' + this_id + '-chosen-list-div">'
														+'<ul class = "dynamic-chosen-list ' + this_id + '-dynamic-chosen-list">'
														+'</ul>'
													+'</div>');
			for(item in response["content"]){
				var code = item;
				var textual_rep = response["content"][item]["textual_representation"];
				 $('.' + this_id + '-dynamic-dropdown').append($('<option>', { 
					value: item,
					text : item + "(" + textual_rep + ")--->" + response["content"][item]["count"] 
				 }));
			}
		}
	});
}

/*Remove dynamic list options based on checkbox selected*/
function removeDynamicDropdown(this_id){
	$('.' + this_id + '-dynamic-dropdown-div').remove();
	$('.' + this_id + '-chosen-list-div').remove();	
}

/*Check if already in list*/
function inDynamicChosen(value, this_id){
	var flag = false;
	$('.' + this_id + '-dynamic-chosen-list li').each(function(){
		if(value == $(this).text().substring(0,  $(this).text().length - 1)){
			flag = true;
		}
	});
	return flag;
}
/*===============================================
 *==============SPECIAL INPUT FUNCTIONS==========
 *===============================================*/

 /*Add voting history input options*/
function addVotingHistory(div){
	$('.' + div.split(' ').join('.')).after('<div class = "checkbox-options-div voting-history-election-selection-div">'
												+'<input type = "radio" class = "checkbox-input voting-history-radio" value = "all" checked></input><label>All</label>'
												+'<input type = "radio" class = "checkbox-input voting-history-radio" value = "GE"></input><label>General</label>'
												+'<input type = "radio" class = "checkbox-input voting-history-radio" value = "PE"></input><label>Primary</label>'
												+'<input type = "radio" class = "checkbox-input voting-history-radio" value = "PP"></input><label>Presidential</label>'
											+'</div>'
											+'<div class = "checkbox-options-div voting-history-years-div">'
												+'<select class = "dropdown-select dynamic-dropdown-selection voting-history-dynamic-dropdown" id = "voting-history">'
													+'<option value = "">--Select Year--</option>'
												+'</select>'
											+'</div>'
											+'<div class = "checkbox-options-div voting-history-years-type-div">'
												+'<select class = "dropdown-select voting-history-years-type">'
													+'<option>Any</option>'
													+'<option>All</option>'
												+'</select>'
											+'</div>'
											+'<div class = "checkbox-options-div voting-history-chosen-list-div">'
												+'<ul class = "dynamic-chosen-list voting-history-dynamic-chosen-list">'
												+'</ul>'
											+'</div>');
	var d = new Date();
	var year = d.getFullYear();
	for(var i = year; i >= year - 23; i--){
		 $('.voting-history-dynamic-dropdown').append($('<option>', { 
			value: i,
			text : i 
		 }));
	}
}

/*Remove Voting History inputs*/
function removeVotingHistory(){
	$('.voting-history-election-selection-div').remove();
	$('.voting-history-years-div').remove();
	$('.voting-history-years-type-div').remove();
	$('.voting-history-chosen-list-div').remove();
}
//-------------------------------------------------------

/*=======================================================
 *================CREATE ASSOC INDEX=====================
 *=======================================================*/
 
 /*for generic single and multiple search types*/
 function addSearchIndex(column, match, value, type){
	 return {columnName: column, 
			 match: match, 
			 value: value, 
			 type: type
			};
 }
 
 /*For adding search criteria index for voting history. This is a special case*/
 function addHistorySearch(radio_selects, years_select, type_select){
	 var columnName = type_select;
	 var match = "";
	 var values = [];
	 var type = "";
	 if(radio_selects[0] == "all"){
		 match = "like";
		 for(var i = 0; i < years_select.length; i++){
			 values.push(years_select[i].slice(-2));
		 }
		 type = "like_history_years";
		 return addSearchIndex(columnName, match, values, type);
	 }
	 else if(years_select.length == 0){
		 match = "like";
		 for(var i = 0; i < radio_selects.length; i++){
			 values.push(radio_selects[i]);
		 }
		 type = "like_history_elections";
		 return addSearchIndex(columnName, match, values, type);
	 }
	 else{
		 match = "exact";
		 for(var i = 0; i < years_select.length; i++){
			 var temp_array = [];
			 for(var ii = 0; ii < radio_selects.length; ii++){
				 temp_array.push(radio_selects[ii] + years_select[i].slice(-2));
			 }
			 values.push(temp_array);
		 }
		 type = "in_history";
		 return addSearchIndex(columnName, match, values, type);
	 }
 }
 
 /*Check for duplicate names in queued statements object*/
 function checkQueueStatements(query_name){
	 var flag = false;
	 for(statement in queue_statements){
		 if(queue_statements[statement]["name"] == query_name){
			 flag = true;
		 }
	 }
	 return flag;
 }
 
 function submitForm(this_form){
	 $(this_form).submit();
 }