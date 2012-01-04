jQuery(document).ready(function($){
	var events = $('.event');

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
		var mousePosition = e.pageX;
		var offset = $(this).offset();
		var viewSize = $('#current-view').width();
		var newPosition = (mousePosition - viewSize/2 + 5 - offset.left) * -8;

console.log('viewSize: ' + viewSize);
console.log('newPosition: ' + newPosition);

		myScroll.scrollTo(newPosition, 0, 200);
	});


	/* show/hide event names */
	$('#options').find('.button').click(function(){
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
					event.show().html(event.attr('data-title'));
					break;
				case 'short':
					event.show().html('+');
					break;
				case 'hidden':
					event.hide();
					break;
			}
		});
	});

	/* show event details */
	var end = 0;
	events.each(function(){
		var $this = $(this);
		var pos = $this.position();
		var id = $this.attr('data-event');

		if (pos.left < end) {
			$this.css('top', '40px');
		}
		end = pos.left + Math.round($this.width());

		$this.hovercard({
			detailsHTML: $('#event-' + id).html()
		});
	});
});
