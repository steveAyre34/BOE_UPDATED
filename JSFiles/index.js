/*JAVASCRIPT FOR index.php*/
(function(){
	/*Returns a list of all updated counties on page load for the select option*/
	$.ajax({
		type: "POST",
		url: "./serverside/boe-serverside.php",
		data: JSON.stringify({get_counties: {}}),
		contentType: "application/json", // Set the data type so jQuery can parse it for you
		success: function (response){
			for(var i = 0; i < response.length; i++){
				$('.counties').append($('<option>', { 
					value: response[i],
					text : response[i] 
				}));
			}
		}
	});
})();