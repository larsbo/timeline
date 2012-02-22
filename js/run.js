var timeline = null;

/*************** FUNCTIONS **************/
jumpToEvent = function(currentEventId, eventId) {
	var el = $('#event'+eventId);
	if(el.length) {
		var nx = el.offset().left + Math.round(el.width()/2) - Math.round($(window).width()/2);
		timeline.scrollTo(nx*-1, 300);

		//remove old back button if exists
		el.siblings().first().find('a.back').remove();

		//show back button
		var a = $("<a class=\"back\"></a>");
		a.title = $('#event'+currentEventId).data('title');
		a.click(function(e){
			e.preventDefault();
			jumpBack(currentEventId);
			a.remove();
		});
		el.next().append(a);
		el.parent().addClass('sticky').draggable('enable');
		el.next().stop(true, true).fadeIn();
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
			console.log('hiding: '+event.data('colorclass'));
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


	/*** events ***/
	events.each(function(){
		var event = $(this);

		// show large images
		event.siblings().first().find('img').each(function(){
			var image = $(this);
			if (!image.attr('height') && !image.attr('width')) {
				image.attr('height', 150);
			}
			image.parents('a').fancybox();
		});

		// clone events
		var clone = event.parent().clone().css({
			'opacity': 0.2,
			'z-Index': 0
		});
		clone.find('.event').addClass('clone');
		clone.find('.event-details, .pin').remove(); // remove unneeded elements
		clone.attr('id', 'clone-' + clone.find('.event').attr('data-event'));
		event.parent().after(clone);	// insert clone after original event

		// show event details
		event.hovercard();

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

		// highlight event clone
		event.parent().hover(function() {
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
	
	/* legend is intelligent filter */
	$('#colorclasses').find('li').click(function(){
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
			//TODO what about stickies??
			if (flag) {
				event.show();
				$('#minimap-'+event.data('event')).show();
			}
			else {
				event.hide();
				$('#minimap-'+event.data('event')).hide();
			}
		});
	});

	// highlight timeline column on hover
	$('#content').delegate('td','mouseover mouseleave', function(e) {
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
		sources.slideUp();
	});

	//show initial stuff
	initialize('semiotic');
});
