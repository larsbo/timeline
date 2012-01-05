jQuery(document).ready(function($){

	/* messages */
	$().message();


	/* iScroll */
	var myScroll = new iScroll('wrapper',{
		checkDOMChanges: true,
		scrollbarClass: 'scrollbar',
		vScroll: false,
		vScrollbar: false,
		onScrollEnd: function(){
			$('#current-view').css('left', -myScroll.x/8);
		}
	});

	/* show mini map of the timeline */
	$('#timeline').minimap();

	$('#mini-map').click(function(e){
		var map = $(this);
		var mousePosition = e.pageX;
		var offset = map.offset();
		var view = $('#current-view').outerWidth() / 2 - 5;
		var newPosition = (mousePosition - view - offset.left) * -8;
		myScroll.scrollTo(newPosition, 0, 200);
	});


	/* show/hide event names */
	var events = $('.event');
	$('#options-container').find('.button').click(function(){
		var elem = $(this);
		var input = elem.next();
		var li = elem.parent();

		// check radio button
		input.attr("checked","checked");

		// change button
		li.siblings().removeClass('selected');
		li.addClass('selected');

		// change events
		events.each(function(){
			var event = $(this);
			switch (input.val()) {
				case 'long':
					event.show().width(event.attr('data-width')).html(event.attr('data-title'));
					break;
				case 'short':
					event.show().width('').html('+');
					break;
				case 'hidden':
					event.hide();
					break;
			}
		});
	});

	/* show event details */
	events.each(function(){
		var $this = $(this);
		var id = $this.attr('data-event');

		$this.hovercard({
			detailsHTML: $('#event-' + id).html()
		});
	});
});
