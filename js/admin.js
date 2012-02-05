jQuery(document).ready(function($){

	// event list
	var events = $('#eventList').find('.eventContainer');
	var eventDetails = $('#eventDetails');

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
	});

	// looking for form submits...
	eventDetails.on('submit', 'form', function(e){
		e.preventDefault();
		var form = $(this);

		$.get('admin.inc.php?action=update&'+ form.serialize(), function(data){
			eventDetails.html(data);
		});
	});
});
