var timeline = null;

/*************** FUNCTIONS **************/
$.fn.showEvent = function(){
	var el = $(this);
	el.show();
	$('#minimap-'+el.data('event')).show();
}
$.fn.hideEvent = function(){
	var el = $(this);//TODO stickies?
	el.hide();
	$('#minimap-'+el.data('event')).hide();
}

jumpToEvent = function(currentEventId, eventId) {
	var el = $('#event'+eventId);
	if(el.length) {
		//if hidden, make visible...
		if (el.filter(':not(:visible)').length) {
			el.showEvent();
		}
	
		var nx = el.offset().left + Math.round(el.width()/2) - Math.round($(window).width()/2);
		timeline.scrollTo(nx*-1, 300);

		//remove old back button if exists
		el.siblings().first().find('a.jump').remove();
		//show back button
		if (currentEventId) {
			var a = $("<a class=\"jump\"></a>");
			var oldel = $('#event'+currentEventId);
			hoverOutFunction(oldel.parent(), oldel);
			a.title = oldel.data('title');
			if (el.offset().left < oldel.offset().left)
				a.addClass('right-arrow');
			else
				a.addClass('left-arrow');
			a.click(function(e){
				e.preventDefault();
				hoverOutFunction(el.parent(), el);
				jumpBack(currentEventId);
				a.remove();
			});
			el.next().append(a);
		}
		hoverInFunction(el.parent(), el);
		el.parent().addClass('sticky').draggable('enable');
		Event.makeClone(el.parent());
		hoverOutFunction(el.parent(), el);
	}
};

jumpBack = function(el) {
	var x = $('#event'+el);
	if(x.length) {
		var nx = x.offset().left + Math.round(x.width()/2) - Math.round($(window).width()/2);
		timeline.scrollTo(nx*-1, 300);
	}
};

/* set wrapper height to current browser viewport */
setWrapperHeight = function() {
	var scroller = $('#scroller');
	var wrapper = $('#wrapper');
	var full_width = $(window).width();
	var full_height = $(window).height();
	scroller.css('height', full_height - parseInt(wrapper.css('top')) - 70 + 'px');
};

/* initial actions */
initialize = function(initialClass) {
	//highlight initial Category
	$('#colorclasses').find('li').each(function(){
		var a = $(this);
		if (a.data('colorclass') == initialClass)
			a.addClass('selected');
		else
			a.removeClass('selected');
	});

	//hide and show only initial events...
	$('.event').each(function(){
		var event = $(this);
		if (event && initialClass != event.data('colorclass')){
			event.hide();
			$('#minimap-'+event.data('event')).hide();
		}
		else {
			jumpToEvent(null, event.data('event'));
		}
	});
};

/************ PAGE LOAD *************/
jQuery(document).ready(function($){
	var events = $('.event');

	/*** internal links ***/
	$('.event-details').find('a').each(function() {
		var ab = $(this);
		var hr = ab.attr('href');
		if (hr && hr.substr(0,1) == '#') {
			var nid = hr.substr(1, hr.length-1);
			var oid = ab.parents('.event-preview').first().children().first().data('event');
			ab.click(function(e){ e.preventDefault(); jumpToEvent(oid, nid); });
		}
	});


	/*** iScroll ***/
	timeline = new iScroll('wrapper',{
		bounce: false,
		scrollbarClass: 'scrollbar',
		vScroll: false,
		vScrollbar: false
	});


	/*** mini map ***/
	$('#timeline').minimap(timeline, $(window).width()-4);

	// update timeline height
	setWrapperHeight();
	$(window).resize(function() {
		setWrapperHeight();
	});

	var timelineheight = $('#content').height();
	var maxElementHeight = Math.min(Math.max(timelineheight-100,100), 400);

	/*** events ***/
	events.each(function(){
		var event = $(this);
		var details = event.siblings().first();

		// show large images
		details.find('img').each(function(){
			var image = $(this);
			if (!image.attr('height') && !image.attr('width')) {
				image.attr('height', 150);
			}
			image.parents('a').fancybox();
		});

		// show event details
		event.hovercard(timeline);

		// make events draggable
		event.parent().draggable({
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
		
		var elementDetails = $(this).next();
		while (elementDetails.height() > maxElementHeight) {
			elementDetails.width(elementDetails.width()+100);
		}
	});

	/* toggle long event names */
	$('#options-container').on('click', '.button', function(e){
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
	
	/* legend is intelligent filter */
	$('#colorclasses').on('click', 'li', function(e){
		var button = $(this);
		button.toggleClass('selected');
		var activeFilters = button.parent().children().filter('.selected');

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
				event.showEvent();
			else
				event.hideEvent();
		});
	});

	// highlight timeline column on hover
	$('#content').on('mouseover mouseleave', 'td', function(e) {
		var index = $(this).index();
		if (e.type == 'mouseover') {
			$(this).parents('table').find('th:nth-child(' + (index + 1) + ')').addClass("hover");
		}
		else {
			$(this).parents('table').find('th:nth-child(' + (index + 1) + ')').removeClass("hover");
		}
	});

	// open extern links in modal window
	$('.extern').fancybox({
		width: '100%',
		height: '100%',
		autoScale: false,
	});

	// open video links in modal window
	$('.video').click(function() {
		$.fancybox({
			padding: 0,
			autoScale: false,
			transitionIn: 'none',
			transitionOut: 'none',
			title: this.title,
			width: 680,
			height: 495,
			href: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/') + '?rel=0',
			type: 'swf',
			swf: {
				wmode: 'transparent',
				allowfullscreen: 'true'
			}
		});
		return false;
	});

	// fancy switch
	var sources = $('.source');
	$('.toggle').Switch("off", function() {
		sources.slideDown();
	}, function() {
		sources.filter(":visible").slideUp();
		sources.filter(":not(:visible)").hide();
	});

	//show initial stuff
	initialize('semiotic');
});
