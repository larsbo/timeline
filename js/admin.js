showDebugMsg = function(msgs){
	if($('#debug').length){
		$.each(msgs, function() {
			noty({
				layout: 'topRight',
				text: '<h3>debug</h3><p>'+this+'</p>',
				timeout: false,
				type: 'debug'
			});
		});
	}
}
refreshList = function(){
	$.post('admin.inc.php', { 'action': 'refresh' }, function(data){
			$('#eventList').find('div:first').html(data.result);
			if (data.debug) showDebugMsg(data.debug);
		}, 'json');
}

jQuery(document).ready(function($){

	/* messages */
	$('.message').each(function(){
		var msg = $(this);
		noty({
			layout: 'topRight',
			text: msg.html(),
			timeout: false,
			type: msg.data('type')
		});
		msg.remove();
	});

	/* datepicker options */
	$.datepicker.setDefaults($.datepicker.regional['de']); // make it german
	$.datepicker.setDefaults({
			dateFormat: 'yy-mm-dd', 
			showOtherMonths: true,
			selectOtherMonths: true,
			changeMonth: true,
			changeYear: true });

	var eventList = $('.eventList');
	var eventDetails = $('#eventDetails');

	// insert event
	$('#new').click(function(){
		showDetails($(this), 'insert');
	});

	// show event
	eventList.on('click', '.show', function(){
		showDetails($(this), 'show');
	});

	// edit event
	eventList.on('click', '.edit', function(){
		showDetails($(this), 'edit');
	});

	function showDetails(event, action) {
		var id = event.parents('tr').data('id');

		// delete old modal form
		event.dialog("destroy");
		eventDetails.html('');

		// load event data
		$.post('admin.inc.php', { 
			'action': action,
			'id': id 
		}, function(data){
			eventDetails.html(data.result);
			if (data.debug) showDebugMsg(data.debug);
			if (action == 'edit' || action == 'insert') {
				eventDetails.find('.dateentry').datepicker();
				eventDetails.find('#details').cleditor({width:'100%'});
			}
		}, 'json');

		if (action == 'edit') {
			// create new modal form
			var title = $("#title"),
					start = $("#start"),
					end = $("#end"),
					colorclass = $("#colorclass"),
					type = $("#type"),
					image = $("#image"),
					details = $("#details"),
					source = $("#source");

			// create new event
			eventDetails.dialog({
				autoOpen: false,
				width: '80%',
				modal: true,
				position: 'top',
				title: 'Ereignis ' + id + ' bearbeiten',
				buttons: {
					"Speichern": function() {
						var form = $('#eventDetails').find('form');
						//do update
						$.ajax({
							url: 'admin.inc.php',
							data: form.serialize()+"&action="+form.data('action'),
							dataType: 'json',
							type: 'POST',
							success: function(data){
								eventDetails.html(data.result);
								if (data.debug) showDebugMsg(data.debug);
								refreshList();
							}
						});

						$(this).dialog("close");
					},
					"Abbrechen": function() {
						$(this).dialog("close");
					}
				}
			});
		} else if (action == 'insert') {
			// create new modal form
			var title = $("#title"),
					start = $("#start"),
					end = $("#end"),
					colorclass = $("#colorclass"),
					type = $("#type"),
					image = $("#image"),
					details = $("#details"),
					source = $("#source");

			// create new event
			eventDetails.dialog({
				autoOpen: false,
				width: '80%',
				modal: true,
				position: 'top',
				title: 'Neues Ereignis erstellen',
				buttons: {
					"Ereignis erstellen": function() {
						var form = $('#eventDetails').find('form');

						//do insert
						$.ajax({
							url: 'admin.inc.php',
							data: form.serialize()+"&action="+form.data('action'),
							dataType: 'json',
							type: 'POST',
							success: function(data){
								eventDetails.html(data.result);
								if (data.debug) showDebugMsg(data.debug);
								refreshList();
							}
						});

						$(this).dialog("close");
					},
					"Abbrechen": function() {
						$(this).dialog("close");
					}
				}
			});
		} else {
			// show event
			eventDetails.dialog({
				autoOpen: false,
				width: '80%',
				modal: true,
				position: 'top',
				title: 'Ereignis ' + id,
				buttons: {
					Close: function() {
						$(this).dialog("close");
					}
				}
			});
		}

		// show modal form
		eventDetails.dialog("open");
	}

	// delete event
	eventList.on('click', '.delete', function(){
		var id = $(this).parents('tr').data('id');
		$(this).after("<p id=\"dialogConfirm\">Soll das Ereignis " +  id + " wirklich gel&ouml;scht werden?</p>");
		$('#dialogConfirm').dialog({
			resizable: false,
			height:140,
			modal: true,
			buttons: {
				"Ja": function() {
					//do deletion
					$.post('admin.inc.php', { 
							'action': 'deleteconfirmation',
							'id': id 
						}, function(data){
							eventDetails.html(data.result);
							if (data.debug) showDebugMsg(data.debug);
							$('#eventList').find('[data-id="' + id + '"]').fadeOut('slow');
						}, 'json');
					$( this ).dialog( "close" );
					$('#dialogConfirm').remove();
				},
				"Nein": function() {
				//do nothing
					eventDetails.html('Ereignis ' + id + ' wurde nicht geloescht.');
					$( this ).dialog( "close" );
					$('#dialogConfirm').remove();
				}
			}
		});

	});

		// databaseupdate
	$('#databaseUpdate').click(function() {
		$.post('admin.inc.php',{'action':'databaseRefresh'}, function(data) {
			if (data.result)
				eventDetails.html("Datenbank Update war erfolgreich.");
			else {
				eventDetails.html("Das Datenbank Update ist fehlgeschlagen!");
			}
			if (data.debug) showDebugMsg(data.debug);
		},'json');
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
					$.post('admin.inc.php', {'action':'dropAndInsertTestData'}, function(data){
						if (data.result) {
							eventDetails.html("Datenbank Reset war erfolgreich.");
							if (data.debug) showDebugMsg(data.debug);
							refreshList();
						}
						else {
							eventDetails.html("Das Datenbank Reset ist fehlgeschlagen!");
						}
					},'json');
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

		// refreshbutton
	$('#refreshbutton').click(refreshList);

});
