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

	var eventList = $('#eventList');
	var eventDetails = $('#eventDetails');

	// insert event
	$('#new').click(function(){
		$.ajax({
			url: 'admin.inc.php',
			data: { 'action': 'insert' },
			dataType: 'json',
			success: function(data){
				eventDetails.html(data.result);
				if (data.debug) showDebugMsg(data.debug);
				eventDetails.find('.dateentry').datepicker();
				eventDetails.find('textarea').cleditor({width:'100%'});
			}
		});
	});

	// show event
	eventList.on('click', '.event:not(.new)', function(){
		var id = $(this).parent().data('id');
		$.ajax({
			url: 'admin.inc.php',
			data: { 'action': 'show',
				'id': id },
			dataType: 'json',
			success: function(data){
				eventDetails.html(data.result);
				if (data.debug) showDebugMsg(data.debug);
			}
		});
	});

	// edit event
	eventList.on('click', '.edit', function(){
		var id = $(this).parent().data('id');
		$.ajax({
			url: 'admin.inc.php',
			data: { 'action': 'edit',
				'id': id },
			dataType: 'json',
			success: function(data){
				eventDetails.html(data.result);
				if (data.debug) showDebugMsg(data.debug);
				eventDetails.find('.dateentry').datepicker();
				eventDetails.find('textarea').cleditor({width:'100%'});
			}
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
					$.ajax({
						url: 'admin.inc.php',
						data: { 'action': 'delete',
							'id': id },
						dataType: 'json',
						success: function(data){
							eventDetails.html(data.result);
							if (data.debug) showDebugMsg(data.debug);
							$('#eventList').find('[data-id="' + id + '"]').fadeOut('slow');
						}
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

		$.ajax({
			url: 'admin.inc.php',
			data: $.merge(form, { 'action': form.data('action'),
				'id': id }),
			dataType: 'json',
			success: function(data){
				eventDetails.html(data.result);
				if (data.debug) showDebugMsg(data.debug);
				$.ajax({
					url: 'admin.inc.php',
					data: { 'action': 'refresh' },
					dataType: 'json',
					success: function(data){
						eventList.find('div:first').html(data.result);
						if (data.debug) showDebugMsg(data.debug);
					}
				});
			}
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
			if (data.debug) showDebugMsg(data.debug);
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
							if (data.debug) showDebugMsg(data.debug);
							$.getJSON('admin.inc.php?action=refresh', function(data){
								eventList.find('div:first').html(data.result);
								if (data.debug) showDebugMsg(data.debug);
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
