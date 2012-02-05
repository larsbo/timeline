jQuery(document).ready(function($){

	// event list
	var eventList = $('#eventList');
	var eventDetails = $('#eventDetails');
	var events = eventList.find('.eventContainer');
	var insert = eventList.find('.new');

	events.each(function(){
		var event = $(this);
		var id = event.data('id');
		var edit = event.find('.edit');
		var del = event.find('.delete');

		edit.click(function(){
			$.get('admin.inc.php?action=edit&id=' + id, function(data){
				eventDetails.html(data);
			});
		});

		del.click(function(){
			$.get('admin.inc.php?action=delete&id=' + id, function(data){
				eventDetails.html(data);
			});
		});
	});

	insert.click(function(){
		$.get('admin.inc.php?action=insert', function(data){
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
		});
	});
	// button click >> yes or no?
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
});
