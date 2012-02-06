jQuery(document).ready(function($){

	/* messages */
	$().message();

	/* iScroll */
	var timeline = new iScroll('wrapper',{
		checkDOMChanges: true,
		scrollbarClass: 'scrollbar',
		vScroll: false,
		vScrollbar: false,
		onScrollEnd: function(){
			$('#current-view').css('left', -timeline.x/8);
		}
	});

	/* show mini map of the timeline */
	$('#timeline').minimap(timeline);

	/* show/hide event names */
	var events = $('.event');
	$('#options-container').find('.button').click(function(){

		// change button
		var button = $(this);
		button.siblings().removeClass('selected');
		button.addClass('selected');

		// change events
		events.each(function(){
			var event = $(this);
			switch (button.data('type')) {
				case 'long':
					event.show().width(event.attr('data-width')).html(event.attr('data-title') + '<span class="pin"></span>');
					var id = event.attr('data-event');
					break;
				case 'short':
					event.parent().not('.sticky').find('.event').show().width('').html('+');
					var id = event.attr('data-event');
					break;
				case 'hidden':
					event.hide();
					break;
			}
		});
	});

	/* show event details */
	events.each(function(){
		var event = $(this);
		var id = event.attr('data-event');

		event.hovercard({
			detailsHTML: $('#event-' + id).html()
		});
	});
});
