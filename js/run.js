jQuery(document).ready(function($){

	/* messages */
	$().message();

	/* iScroll */
	var timeline = new iScroll('wrapper',{
		bounce: false,
		scrollbarClass: 'scrollbar',
		vScroll: false,
		vScrollbar: false,
		onScrollEnd: function(){
			$('#current-view').css('left', -timeline.x/8);
		}
	});

	/* show mini map of the timeline */
	$('#timeline').minimap(timeline);

	var events = $('.event');

	/* show event details */
	events.each(function(){
		var event = $(this);

		// clone events
		var clone = event.parent().clone().css({
			'opacity': 0.2,
			'z-Index': 0
		});
		clone.find('.event').addClass('clone');
		clone.find('.event-details, .pin').remove(); // remove unneeded elements
		clone.attr('id', 'clone-' + clone.find('.event').attr('data-event'));
		event.parent().after(clone);	// insert clone after original event

		event.hovercard();
		event.parent().draggable({
			/*axis: 'y',*/
			/*handle: 'div',*/
			start: function(){
				var current = $(this);

				if (!current.data('origleft') && !current.data('origtop')) {
					current.attr('data-origleft', current.position().left);
					current.attr('data-origtop', current.position().top);
				}
				timeline.disable();
			},
			stop: function(){
				timeline.enable();
			}
		});
		event.parent().draggable('disable');	// disable on startup
	});

	// highlight event clone
	events.parent().hover(function() {
		var event = $(this).find('.event');
		var clone = $('#clone-' + event.data('event'));
		clone.stop().animate({'opacity': '1'}, 'slow');
	}, function() {
		var event = $(this).find('.event');
		var clone = $('#clone-' + event.data('event'));
		clone.stop().animate({'opacity': '0.3'}, 'slow');
	});


	/* toggle long event names */
	var eventsAndClones = $('.event');	// new selection of .events (with clones)
	$('#options-container').find('.button').click(function(){
		var button = $(this);
		var clones = $('.clones');

		// change button
		button.siblings().removeClass('selected');
		button.addClass('selected');

		// change events
		eventsAndClones.each(function(){
			var event = $(this);
			switch (button.data('type')) {
				case 'long':
				event
					.show()
					.width(event.data('width'))
					.html(event.data('title') + '<span class="pin"></span>');
				break;
				case 'short':
				event.parent().not('.sticky').find('.event')
					.show()
					.width('')
					.html('+');
				break;
				case 'hidden':
				event.hide();
				break;
			}
		});
	});

});
