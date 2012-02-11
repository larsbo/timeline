jQuery(document).ready(function($){

	/* messages */
	$().message();

	/* datepicker options */
	$.datepicker.setDefaults($.datepicker.regional['de']); // make it german
	$.datepicker.setDefaults({
			dateFormat: 'yy-mm-dd', 
			showOtherMonths: true,
			selectOtherMonths: true,
			changeMonth: true,
			changeYear: true });

	var eventList = $('#eventList');
	var eventDetails = $('#eventDetails');

	// insert event
	$('#new').click(function(){
		$.getJSON('admin.inc.php?action=insert', function(data){
			eventDetails.html(data.result);
			eventDetails.find('.dateentry').datepicker();
		});
	});

	// show event
	eventList.on('click', '.event:not(.new)', function(){
		var id = $(this).parent().data('id');
		$.getJSON('admin.inc.php?action=show&id=' + id, function(data){
			eventDetails.html(data.result);
		});
	});

	// edit event
	eventList.on('click', '.edit', function(){
		var id = $(this).parent().data('id');
		$.getJSON('admin.inc.php?action=edit&id=' + id, function(data){
			eventDetails.html(data.result);
			eventDetails.find('.dateentry').datepicker();
		});
	});

	// delete event
	eventList.on('click', '.delete', function(){
		var id = $(this).parent().data('id');
		$(this).after("<p id=\"dialogConfirm\">Soll das Ereignis " +  id + " wirklich gel&ouml;scht werden?</p>");
		$('#dialogConfirm').dialog({
			resizable: false,
			height:140,
			modal: true,
			buttons: {
				Confirm: function() {
					//do deletion
					$.getJSON('admin.inc.php?action=deleteconfirmation&id=' + id, function(data){
						eventDetails.html(data.result);
						$('#eventList').find('[data-id="' + id + '"]').fadeOut('slow');
					});
					$( this ).dialog( "close" );
					$('#dialogConfirm').remove();
				},
				Cancel: function() {
				//do nothing
					eventDetails.html('Ereignis ' + id + ' wurde nicht geloescht.');
					$( this ).dialog( "close" );
					$('#dialogConfirm').remove();
				}
			}
		});

	});

	// looking for form submits...
	eventDetails.on('submit', 'form', function(e){
		e.preventDefault();
		var form = $(this);
		var action = form.data('action');

		$.getJSON('admin.inc.php?action=' + action + '&' + form.serialize(), function(data){
			eventDetails.html(data.result);
			$.getJSON('admin.inc.php?action=refresh', function(data){
				eventList.find('div:first').html(data.result);
			});
		});
	});

		// databaseupdate
	$('#databaseUpdate').click(function() {
		$.getJSON('admin.inc.php?action=databaseRefresh', function(data) {
			if (data.result)
				eventDetails.html("Datenbank Update war erfolgreich.");
			else {
				eventDetails.html("Das Datenbank Update ist fehlgeschlagen!");
			}
		});
	});
	
	//dropAllTables And restart with testdata
	$('#databaseRestart').click(function() {
		$('#databaseRestart').after("<p id=\"dialogConfirm\">Wirklich?</p>");
		$('#dialogConfirm').dialog({
			resizable: false,
			height:140,
			modal: true,
			buttons: {
				Confirm: function() {
					//do deletion
					$.getJSON('admin.inc.php?action=dropAndInsertTestData', function(data){
						if (data.result) {
							eventDetails.html("Datenbank Reset war erfolgreich.");
							$.getJSON('admin.inc.php?action=refresh', function(data){
								eventList.find('div:first').html(data.result);
							});
						}
						else {
							eventDetails.html("Das Datenbank Reset ist fehlgeschlagen!");
						}
					});
					$( this ).dialog( "close" );
					$('#dialogConfirm').remove();
				},
				Cancel: function() {
				//do nothing
					$( this ).dialog( "close" );
					$('#dialogConfirm').remove();
				}
			}
		});
	});

});
