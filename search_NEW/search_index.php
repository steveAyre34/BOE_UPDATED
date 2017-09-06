<?php
	require("connection.php");
	$first_name = $_POST["first_name"];
	$last_name = $_POST["last_name"];
	$street_no = $_POST["street_no"];
	$street_name = $_POST["street_name"];
	$apt_no = $_POST["apt_no"];
	$city = $_POST["city"];
	$zip = $_POST["zip"];
	$county = $_POST["county"];
?>
<html>
	<head>
		<title>BOE Search</title>
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
		<link rel="stylesheet" type="text/css" href="jquery.dataTables.css">
	</head>
<body>
	<h1>Search BOE Data</h1>
	<div style = "padding-bottom: 20px"></div>
	<table id="crm-table"  cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
			<thead>
				<tr>
				<th>Voter ID</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Residence</th>
				<th>Party</th>
				</tr>
			</thead>
		<tbody>
		</tbody>
	</table>
</body>
</html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="jquery.dataTables.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.12/js/dataTables.material.min.js"></script>
<script type="text/javascript" language="javascript">
    var dataTable = null;
    var myData = {function: 1};
    var FilteredRecords = 0;
	var county = <?php echo json_encode($county); ?>;
	var first_name = <?php echo json_encode($first_name); ?>;
	var last_name = <?php echo json_encode($last_name); ?>;
	var street_no = <?php echo json_encode($street_no); ?>;
	var street_name = <?php echo json_encode($street_name); ?>;
	var apt_no = <?php echo json_encode($apt_no); ?>;
	var city = <?php echo json_encode($city); ?>;
	var zip = <?php echo json_encode($zip); ?>;
	$(document).ready(function() {
    //on page load set the 'mark' column in database to 0
    

   //====================create datatable==========================================
  	dataTable = $('#crm-table').DataTable( {
  			dom: '<"toolbar">lfrtip',
        "order": [[ 1, "asc" ]],
  			"processing": true,
  			"serverSide": true,
        "deferRender": false,
  			"scrollX": true,
  			"ajax":{
  				url :"server-side-search-boe.php?county=" + county + "&first_name=" + first_name + "&last_name=" + last_name + "&street_no=" + street_no + "&street_name=" + street_name + "&apt_no=" + apt_no + "&city=" + city + "&zip=" + zip, // json datasource
  				type: "POST",  // method  , by default get
          data: function ( d ) {
                   return  $.extend(d, myData);
                },
  				error: function(){  // error handling
  					$(".crm-table-error").html("");
  					$("#crm-table").append('<tbody class="crm-table-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
  					$("#crm-table_processing").css("display","none");
  				}
  			},
  			"columnDefs": [ {
  			    "targets": 0,
  			    "render": function ( data, type, row) {
  			      return row[0]; //link for each client name
  			    }
  			  }
      ]
  	});
//buttons above table
  
  //==============================================================================


    $('#export').on('click', function() {
      if (FilteredRecords == 0) {
        swal({   title: "Warning",   text: "Cannot export an empty table. Please select some rows to export.",  text: "Your file will begin downloading if you choose to Export.", type: "warning",      confirmButtonColor: "#4FD8FC",   confirmButtonText: "OK",   closeOnConfirm: true },
    			function(){ saveNotClicked=false; $( ".store-btn" ).click();});
      }else{
        swal({
            title: "Are you sure you want to export "+FilteredRecords+" Records?",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Export it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
          },
          function(isConfirm){
            if (isConfirm) {
              var sqlsend = dataTable.ajax.json().sql;
              window.location.href="server-side-CSV.php?val="+sqlsend+"&NumRecords="+FilteredRecords;
              swal({title:"Nice!", text:"Saved as: ", type:"success"});
            } else {
              swal("Cancelled", "Export stopped", "error");
            }
        });
      }
    });


  //====Column Search ======
	$('.search-input-text').on( 'keyup click', function () {   // for text boxes
		var i =$(this).attr('data-column');  // getting column index
		var v =$(this).val();  // getting search input value
		dataTable.columns(i).search(v).draw();
	});

	$('.search-input-select').on( 'change', function () {   // for select box
			var i =$(this).attr('data-column');
			var v =$(this).val();
			dataTable.columns(i).search(v).draw();
	});
  //====!Column Search!======

	$('.buttons-select-all').on( 'click', function () {
    // Check checkboxes for all rows in the table
    var sqlsend = dataTable.ajax.json().sql;
    $.ajax({
      type:'POST',
      url: 'CRM_updateMarked.php',
      data: {
        'function': 2,
        'Select': 'all',
        'sql': sqlsend
      }
    });
    $('input[type="checkbox"]').each(function(){
        $(this).attr('checked', true);
    });
    FilteredRecords = dataTable.ajax.json().recordsFiltered;
    updateCounter();
    dataTable.ajax.reload(null, false);
  });

  //Select All button and select none button
  	$('.buttons-select-none').on( 'click', function (event) {
      console.log(this);
      var sqlsend = dataTable.ajax.json().sql;
      $.ajax({
        type:'POST',
        url: 'CRM_updateMarked.php',
        data: {
          'function': 2,
          'Select': 'none',
          'sql': sqlsend
        }
      });
      $('input[type="checkbox"]').each(function(){
          $(this).attr('checked', false);
      });
      $("#general i .counter").text('');

  	});

  //'show selected row' button
    $('.filterRow').on('click', function(event){
      // Check if the clicked button has class `btn_s`
      if ($(this).hasClass('buttons-showMarked')) {
        $(this).html('Display All').toggleClass('buttons-showMarked buttons-hideMarked');
        myData.function = 0;
        dataTable.ajax.reload();
      } else {
        console.log("in else");
        $(this).html('Show selected rows only').toggleClass('buttons-hideMarked buttons-showMarked ');
        myData.function = 1;
        dataTable.ajax.reload();
      }
    });


    function serialize (mixedValue) {
      //  discuss at: http://locutus.io/php/serialize/
      // original by: Arpad Ray (mailto:arpad@php.net)
      // improved by: Dino
      // improved by: Le Torbi (http://www.letorbi.de/)
      // improved by: Kevin van Zonneveld (http://kvz.io/)
      // bugfixed by: Andrej Pavlovic
      // bugfixed by: Garagoth
      // bugfixed by: Russell Walker (http://www.nbill.co.uk/)
      // bugfixed by: Jamie Beck (http://www.terabit.ca/)
      // bugfixed by: Kevin van Zonneveld (http://kvz.io/)
      // bugfixed by: Ben (http://benblume.co.uk/)
      // bugfixed by: Codestar (http://codestarlive.com/)
      //    input by: DtTvB (http://dt.in.th/2008-09-16.string-length-in-bytes.html)
      //    input by: Martin (http://www.erlenwiese.de/)
      //      note 1: We feel the main purpose of this function should be to ease
      //      note 1: the transport of data between php & js
      //      note 1: Aiming for PHP-compatibility, we have to translate objects to arrays
      //   example 1: serialize(['Kevin', 'van', 'Zonneveld'])
      //   returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
      //   example 2: serialize({firstName: 'Kevin', midName: 'van'})
      //   returns 2: 'a:2:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";}'

      var val, key, okey
      var ktype = ''
      var vals = ''
      var count = 0

      var _utf8Size = function (str) {
        var size = 0
        var i = 0
        var l = str.length
        var code = ''
        for (i = 0; i < l; i++) {
          code = str.charCodeAt(i)
          if (code < 0x0080) {
            size += 1
          } else if (code < 0x0800) {
            size += 2
          } else {
            size += 3
          }
        }
        return size
      }

      var _getType = function (inp) {
        var match
        var key
        var cons
        var types
        var type = typeof inp

        if (type === 'object' && !inp) {
          return 'null'
        }

        if (type === 'object') {
          if (!inp.constructor) {
            return 'object'
          }
          cons = inp.constructor.toString()
          match = cons.match(/(\w+)\(/)
          if (match) {
            cons = match[1].toLowerCase()
          }
          types = ['boolean', 'number', 'string', 'array']
          for (key in types) {
            if (cons === types[key]) {
              type = types[key]
              break
            }
          }
        }
        return type
      }

      var type = _getType(mixedValue)

      switch (type) {
        case 'function':
          val = ''
          break
        case 'boolean':
          val = 'b:' + (mixedValue ? '1' : '0')
          break
        case 'number':
          val = (Math.round(mixedValue) === mixedValue ? 'i' : 'd') + ':' + mixedValue
          break
        case 'string':
          val = 's:' + _utf8Size(mixedValue) + ':"' + mixedValue + '"'
          break
        case 'array':
        case 'object':
          val = 'a'
          /*
          if (type === 'object') {
            var objname = mixedValue.constructor.toString().match(/(\w+)\(\)/);
            if (objname === undefined) {
              return;
            }
            objname[1] = serialize(objname[1]);
            val = 'O' + objname[1].substring(1, objname[1].length - 1);
          }
          */

          for (key in mixedValue) {
            if (mixedValue.hasOwnProperty(key)) {
              ktype = _getType(mixedValue[key])
              if (ktype === 'function') {
                continue
              }

              okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key)
              vals += serialize(okey) + serialize(mixedValue[key])
              count++
            }
          }
          val += ':' + count + ':{' + vals + '}'
          break
        case 'undefined':
        default:
          // Fall-through
          // if the JS object has a property which contains a null value,
          // the string cannot be unserialized by PHP
          val = 'N'
          break
      }
      if (type !== 'object' && type !== 'array') {
        val += ';'
      }

      return val
    }

    function urlencode (str) {
      //       discuss at: http://locutus.io/php/urlencode/
      //      original by: Philip Peterson
      //      improved by: Kevin van Zonneveld (http://kvz.io)
      //      improved by: Kevin van Zonneveld (http://kvz.io)
      //      improved by: Brett Zamir (http://brett-zamir.me)
      //      improved by: Lars Fischer
      //         input by: AJ
      //         input by: travc
      //         input by: Brett Zamir (http://brett-zamir.me)
      //         input by: Ratheous
      //      bugfixed by: Kevin van Zonneveld (http://kvz.io)
      //      bugfixed by: Kevin van Zonneveld (http://kvz.io)
      //      bugfixed by: Joris
      // reimplemented by: Brett Zamir (http://brett-zamir.me)
      // reimplemented by: Brett Zamir (http://brett-zamir.me)
      //           note 1: This reflects PHP 5.3/6.0+ behavior
      //           note 1: Please be aware that this function
      //           note 1: expects to encode into UTF-8 encoded strings, as found on
      //           note 1: pages served as UTF-8
      //        example 1: urlencode('Kevin van Zonneveld!')
      //        returns 1: 'Kevin+van+Zonneveld%21'
      //        example 2: urlencode('http://kvz.io/')
      //        returns 2: 'http%3A%2F%2Fkvz.io%2F'
      //        example 3: urlencode('http://www.google.nl/search?q=Locutus&ie=utf-8')
      //        returns 3: 'http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3DLocutus%26ie%3Dutf-8'

      str = (str + '')

      // Tilde should be allowed unescaped in future versions of PHP (as reflected below),
      // but if you want to reflect current
      // PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
      return encodeURIComponent(str)
        .replace(/!/g, '%21')
        .replace(/"/g, '%22')
        .replace(/\(/g, '%28')
        .replace(/\)/g, '%29')
        .replace(/\*/g, '%2A')
        .replace(/%20/g, '+')
    }
    //quick search box
    $("#searchbox").on("keyup search input paste cut", function() {
   		dataTable.search(this.value).draw();
		});

  //save search button
    $('#save_button').on('click', function(){
      var val = '';
      var col_name ='';
      var search_name = '';
      $( '.search_col' ).each(function() {
        if ($(this).val()!=(null || '')) {    //when column search input field is not empty
            val =val+","+$(this).val();
            col_name = col_name+","+$(this).attr("text");
        }
      });
      if (val == '') {
        swal({   title: "Warning",   text: "You did not search anything. Please search for some input to save.",   type: "warning",      confirmButtonColor: "#4FD8FC",   confirmButtonText: "OK",   closeOnConfirm: true },
    		function(){ saveNotClicked=false; $( ".store-btn" ).click();});
      }
      else{
        swal({
            title: "Are you sure you want to save the searches you made?",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Save it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
          },
          function(isConfirm){
            if (isConfirm) {
              swal({
                title: "Search name",
                text: "Please enter a name for the search:",
                type: "input",
                showCancelButton: true,
                closeOnConfirm: false,
                animation: "slide-from-top",
                inputPlaceholder: "Write something"
              },
              function(inputValue){
                if (inputValue === false) return false;

                if (inputValue === "") {
                  //default name for the search
                  var d = new Date();

                  var month = d.getMonth()+1;
                  var day = d.getDate();

                  var output = d.getFullYear() + '/' +
                      (month<10 ? '0' : '') + month + '/' +
                      (day<10 ? '0' : '') + day;
                  search_name = "Saved Search "+output;
                }else
                  search_name = inputValue;
                 swal({title:"Nice!", text:"Saved as: " + search_name, type:"success"},
                 function(){
                    $.ajax({
                     type:'POST',
                     url: 'CRM_updateMarked.php',
                     data: {
                       'function': 3,
                       'search_name': search_name,
                       'val': val,
                       'col_name': col_name,
                     }
                   });
                   window.location.reload();
                 });
              });

            } else {
              swal("Cancelled", "Save stopped", "error");
            }
        });
      }
    });

    $('.delete_button').on('click', function(){
      var del_id = $(this).attr("id");
      var info = del_id;
      swal({
        title: "Are you sure you want to delete?",
        text: "You will not be able to recover this data!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel plx!",
        closeOnConfirm: false,
        closeOnCancel: false
      },
      function(isConfirm){
        if (isConfirm) {
          $.ajax({
        		url: 'CRM_updateMarked.php',
        		type: 'POST',
        		data: {
        			id: info,
              'function': 4
        		},
        		success: function(){
        			document.getElementById("cell" + del_id).style.display = "none";
        		}
        	});
          swal("Deleted!", "Your search has been deleted.", "success");
        } else {
          swal("Cancelled", "Your search is safe :)", "error");
        }
      });
    });
	});


  //when 'Show saved button' pressed
  function SavedSearch(field1, value1, field2, value2, field3, value3, field4, value4, field5, value5){
    var search_field = [field1, field2, field3, field4, field5];
    var search_value = [value1, value2, value3, value4, value5];
	var error_subtract = 0;
	var check_dup = 0;
	for(var j = 1; j <= 15; j++)
	{
		$(".minus_button" + j).hide();
		$(".add_button" + j).show();
	}
	$(".search-input-text").val("");
	$(".search-input-text").css("visibility", "hidden");
	$('.search_col').click();
    for (i = 0; i < search_field.length; i++) {
      if (search_field[i]!= "$$$") {
        $( '.search_col' ).each(function() {
          if ($(this).attr("text") == search_field[i]) {
            $(this).val(search_value[i]);
            $(this).css('visibility','visible');                          //input text field
            var plusbutton = $(this).siblings().nextAll().eq(0).attr('class');
            var minusbutton = $(this).siblings().attr('class');
            $('.'+plusbutton).hide();   //+ button
            $('.'+minusbutton).show();   //- button
			search_counter--;
			if(check_dup % 2 == 0){
				error_subtract++;
			}
			check_dup++;
          }
        });
      }
    }
	search_counter = search_counter + error_subtract;
    $( '.search_col' ).click();
  }

  function showSavedSearch(){
    if(document.getElementById('show_saved_search').innerHTML == "Show Saved Search"){
      document.getElementById('saved_search_table').style.display = "block";
      document.getElementById('show_saved_search').innerHTML = "Hide Saved Search";
    }
    else{
      document.getElementById('saved_search_table').style.display = "none";
      document.getElementById('show_saved_search').innerHTML = "Show Saved Search";
    }
  }

  //When input checkbox is clicked this function is called
  function rowMarkedClick(checked, clientName, Address){
    MarkChecked = (checked == 0)?true:false;
    $.ajax({
      type:'POST',
      url: 'CRM_updateMarked.php',
      data: {
        'function': 1,
        'checked': MarkChecked,
        'CName': clientName,
        'address': Address
      }
    });
    FilteredRecords = (MarkChecked)?FilteredRecords+=1:FilteredRecords-=1;
    updateCounter();
    dataTable.ajax.reload(null, false);
  }

  function updateCounter(){
		if(FilteredRecords>0){
			$("#general i .counter").text('('+FilteredRecords+')');
		}
		else{$("#general i .counter").text('');}
	}

  var search_counter = 5;
  function addSearchCounter(search, add_button, minus_button){
    	if(search_counter != 0){
    		$(add_button).hide();
    		$(search).css('visibility','visible');
        $(search).show().focus()
    		$(minus_button).show();
    		search_counter--;
    	}
    	else{
    		showErrorMessage();
    	}

    	function showErrorMessage(){
        swal({   title: "Limit",   text: "Only 5 search boxes allowed. Press '-' button to choose another column.",   type: "warning",      confirmButtonColor: "#4FD8FC",   confirmButtonText: "OK",   closeOnConfirm: true },
    			function(){ saveNotClicked=false; $( ".store-btn" ).click();});
    	};
  }
   function minusSearchCounter(search, add_button, minus_button){
		$(minus_button).hide();
		$(search).css('visibility','hidden');
		$(search).val("");
		$(add_button).show();
		search_counter++;
		$('.search_col').click();
}
$("#crm-table__filter").hide();
</script>