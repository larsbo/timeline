var timeline = null;

jumpToEvent = function(currentEventId, eventId) {
	timeline.scrollToElement('#event'+eventId, 300);
	var el = $('#event'+eventId);
	if(el.length) {
		//show back button
		if (el.find('a.back').length == 0 && currentEventId) {
			var a = $("<a class=\"back\"></a>");
			a.title = $('#event'+currentEventId).data('title');
			a.click(function(e){
				e.preventDefault();
				jumpBack(currentEventId);
				a.remove();
			});
			el.next().append(a);
		}
		else if (currentEventId) {
			var a = el.find('a.back');
			a.click(function(e){
				e.preventDefault();
				jumpBack(currentEventId);
				a.remove();
			});
			a.title = $('#event'+currentEventId).data('title');
		}
		el.parent().addClass('sticky');
		el.next().stop(true, true).fadeIn();
	}
};

jumpBack = function(el) {
	timeline.scrollToElement('#event'+el, 300);
};

/* set wrapper height to current browser viewport */
setWrapperHeight = function() {
	var scroller = $('#scroller');
	var wrapper = $('#wrapper');
	var full_width = $(window).width();
	var full_height = $(window).height();
	scroller.css('height', full_height - parseInt(wrapper.css('top')) - 70 + 'px');
};

jQuery(document).ready(function($){
	//make internal links
	$('.event-details').find('a').each(function() {
		var ab = $(this);
		var hr = ab.attr('href');
		if (hr && hr.substr(0,1) == '#') {
			var nid = hr.substr(1, hr.length-1);
			var oid = ab.parents('.event-preview').first().children().first().data('event');
			ab.click(function(e){ e.preventDefault(); jumpToEvent(oid, nid); });
		}
	});



	/* iScroll */
	timeline = new iScroll('wrapper',{
		bounce: false,
		scrollbarClass: 'scrollbar',
		vScroll: false,
		vScrollbar: false
	});

	/* show mini map of the timeline */
	$('#timeline').minimap(timeline, $(window).width()-10);

	// update timeline height
	setWrapperHeight();
	$(window).resize(function() {
		setWrapperHeight();
	});

	var events = $('.event');

	/* show event details */
	events.each(function(){
		var event = $(this);

		// show large image
		event.siblings().first().find('img').parent().fancybox();

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
		$('#minimap-' + event.data('event')).addClass('hovered');
		$('.event').not(event).not(clone.find('.clone')).stop().animate({'opacity': '0.2'}, 'slow');
		clone.stop().animate({'opacity': '1'}, 'slow');
	}, function() {
		var event = $(this).find('.event');
		var clone = $('#clone-' + event.data('event'));
		$('#minimap-' + event.data('event')).removeClass('hovered');
		$('.event').not(event).not(clone.find('.clone')).stop().animate({'opacity': '1'}, 'slow');
		clone.stop().animate({'opacity': '0.2'}, 'slow');
	});


	/* toggle long event names */
	$('#options-container').find('.button').click(function(){
		var button = $(this);
		var clones = $('.clones');

		// change button
		button.siblings().removeClass('selected');
		button.addClass('selected');

		// change events
		if (button.data('type') == 'hidden') {
			var events = $('.event').not('.clone');
			events.each(function() {
				var event = $(this);
				var id = event.data('event');
				if (event.parent().hasClass('sticky')) {
					event.parent().next().find('.event').show();
					event.show();
				}
				else {
					event.hide();
					event.parent().next().find('.event').hide();
				}
			});
		}
		else {
			// new selection of .events (with clones)
			$('.event').each(function(){
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
				}
			});
		}
	});
	
	/* legende is intelligent filter */
	$('#colorclasses').find('li').click(function(){
		var button = $(this);
		button.toggleClass('selected');
		var activeFilters = button.parent().children().filter('.selected');

		if (activeFilters.length == 0) {
			//no filter, show all
			$('.event').each(function(){
				var event = $(this);
				event.show();
			});
		}
		else {
			//show only objects of activeFilters-categories
			$('.event').each(function(){
				var event = $(this);
				var value = event.data('colorclass');
				var flag = false;
				activeFilters.each(function() {
					if ($(this).data('colorclass') == value)
						flag = true;
				});
				if (flag)
					event.show();
				else {
					event.hide();
				}
			});
		}
	});

	// highlight timeline column on hover
	$('#content').find('td').hover(function(){
		$(this).parents('table').find('th:nth-child(' + ($(this).index() + 1) + ')').addClass("hover");
	}, function(){
		$(this).parents('table').find('th:nth-child(' + ($(this).index() + 1) + ')').removeClass("hover");
 });

});
