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
			var t = $('#eventList').find('div:first');
			t.html(data.result);
			$('.event', t).button();
			$('.edit', t).button({ icons: { primary: "ui-icon-wrench" }, text: false });
			$('.delete', t).button({ icons: { primary: "ui-icon-trash" }, text: false });
			if (data.debug) showDebugMsg(data.debug);
		}, 'json');
}

jQuery(document).ready(function($){

	refreshList();

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
	$('#new').button({ icons: { primary: "ui-icon-plusthick" } }).click(function(){
		$.post('admin.inc.php', { 'action': 'insert' }, function(data){
				eventDetails.html(data.result);
				if (data.debug) showDebugMsg(data.debug);
				eventDetails.find('.dateentry').datepicker();
				eventDetails.find('textarea').cleditor({width:'100%'});
			}, 'json');
	});

	// show event
	eventList.on('click', '.event', function(){
		var id = $(this).parent().data('id');
		$.post('admin.inc.php', { 
				'action': 'show',
				'id': id 
			}, function(data){
				eventDetails.html(data.result);
				if (data.debug) showDebugMsg(data.debug);
			}, 'json');
	});

	// edit event
	eventList.on('click', '.edit', function(){
		var id = $(this).parent().data('id');
		$.post('admin.inc.php', { 
				'action': 'edit',
				'id': id 
			}, function(data){
				eventDetails.html(data.result);
				if (data.debug) showDebugMsg(data.debug);
				eventDetails.find('.dateentry').datepicker();
				eventDetails.find('textarea').cleditor({width:'100%'});
			}, 'json');
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
			data: form.serialize()+"&action="+form.data('action'),
			dataType: 'json',
			type: 'POST',
			success: function(data){
				eventDetails.html(data.result);
				if (data.debug) showDebugMsg(data.debug);
				refreshList();
			}
		});
	});

		// databaseupdate
	$('#databaseUpdate').button().click(function() {
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
	$('#databaseRestart').button().click(function() {
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
	$('#refreshbutton').button({ icons: { primary: "ui-icon-refresh" } }).click(refreshList);

});
