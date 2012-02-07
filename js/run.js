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

	/* show/hide event names */
	var events = $('.event');
	$('#options-container').find('.button').click(function(){
		var button = $(this);

		// change button
		button.siblings().removeClass('selected');
		button.addClass('selected');

		// change events
		events.each(function(){
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

	/* show event details */
	events.each(function(){
		var event = $(this);

		event.hovercard();
		event.parent().draggable({
			axis: 'y',
			handle: 'div',
			/*snap: true,*/
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
		event.parent().draggable('disable');
	});
});
