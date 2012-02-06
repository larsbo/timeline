jQuery(document).ready(function($){

	/* messages */
	$().message();

	var eventList = $('#eventList');
	var eventDetails = $('#eventDetails');

	// insert event
	eventList.on('click', '.new', function(){
		$.get('admin.inc.php?action=insert', function(data){
			eventDetails.html(data);
		});
	});

	// show event
	eventList.on('click', '.event:not(.new)', function(){
		var id = $(this).parent().data('id');
		$.get('admin.inc.php?action=show&id=' + id, function(data){
			eventDetails.html(data);
		});
	});

	// edit event
	eventList.on('click', '.edit', function(){
		var id = $(this).parent().data('id');
		$.get('admin.inc.php?action=edit&id=' + id, function(data){
			eventDetails.html(data);
		});
	});

	// delete event
	eventList.on('click', '.delete', function(){
		var id = $(this).parent().data('id');
		$.get('admin.inc.php?action=delete&id=' + id, function(data){
			eventDetails.html(data);
		});
	});

	// looking for form submits...
	eventDetails.on('submit', 'form', function(e){
		e.preventDefault();
		var form = $(this);
		var action = form.data('action');

		$.get('admin.inc.php?action=' + action + '&' + form.serialize(), function(data){
			eventDetails.html(data);
			$.get('admin.inc.php?action=refresh', function(data){
				eventList.html(data);
			});
		});
	});

	// confirmation >> yes or no?
	eventDetails.on('click', 'input[type="button"]', function(e){
		var button = $(this);
		var id = button.parent().data('id');
		if (button.attr('name') == 'yes') {
			$.get('admin.inc.php?action=deleteconfirmation&id=' + id, function(data){
				eventDetails.html(data);
				$('#eventList').find('[data-id="' + id + '"]').fadeOut('slow');
			});
		} else {
			eventDetails.html('Ereignis ' + id + ' wurde nicht geloescht.');
		}
	});
	
		// databaseupdate
	$('#databaseUpdate').click(function() {
		$.get('admin.inc.php?action=databaseRefresh', function(data){
			eventDetails.html(data);
		});
	});

});
