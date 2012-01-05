/* 
jQuery Mini-Map plugin copyright Sam Croft <samcroft@gmail.com>
extended for timeline use copyright Lars Borchert <borchert.lars@gmail.com>
Licensed like jQuery - http://docs.jquery.com/License
*/
(function($){
	$.fn.minimap = function(){
		var miniMap = $('#mini-map');
		var miniMapCurrentView = $('#current-view');

		var el = this;
		var years = el.find('th');
		var events = el.find('.event');

		miniMap.height(Math.round(el.height()/8) + 10);
		miniMap.width(Math.round(el.width()/8));
		miniMapCurrentView.height(Math.round(el.height()/8) + 12);
		miniMapCurrentView.width(Math.round($(window).width()/8));

		years.each(function(i,t){
			if (i % 5 == 0) {	// show every 5th year
				var year = $(this);
				var yearCoords = year.offset();

				var mapIcon = $('<div>' + year.text() + '</div>');
				mapIcon
				.css({
					'width': 18, 
					'left': Math.round(yearCoords.left/8)
				})
				.addClass(t.tagName.toLowerCase())
				.appendTo(miniMap);
			}
		});

		events.each(function(i,t){
			var event = $(this);
			var eventCoords = event.offset();

			var mapIcon = $('<div>');
			mapIcon
			.css({
				'height': Math.round(event.height()/8), 
				'width': Math.round(event.width()/8), 
				'left': Math.round(eventCoords.left/8),
				'top': Math.round(eventCoords.top/8) + 3
			})
			.addClass(t.tagName.toLowerCase())
			.appendTo(miniMap);
		});
	};


	/* messages */
	$.fn.message = function(){
		var myMessages = ['info','warning','error','success'];
		var immediateMessage = $('.immediate');
		var page = $('#page');

		function hideAllMessages(){
			var messagesHeights = new Array(); // this array will store height for each
			for (i=0; i<myMessages.length; i++) {
				messagesHeights[i] = $('.' + myMessages[i]).outerHeight(); // fill array
				$('.' + myMessages[i]).css('top', -messagesHeights[i]); //move element outside viewport
			}
		}

		function showImmediateMessage(){
			if (immediateMessage.length){
				immediateMessage.animate({top:0}, 500);
				page.animate({
					top: immediateMessage.outerHeight(),
					height: page.outerHeight() - immediateMessage.outerHeight()
				}, 500);
			}
		}

		function showMessage(){
			for (i=0; i<myMessages.length; i++) {
				$('.'+ myMessages[i] +'-trigger').click(function(){
					hideAllMessages();
					$('.' + myMessages[i]).animate({top:"0"}, 500);
				});
			}
		}

		$('.msg').click(function(){
			$(this).animate({top: -$(this).outerHeight()}, 500);
			page.animate({
				top: 0,
				height: page.outerHeight() + immediateMessage.outerHeight()
			}, 500);
		});

		hideAllMessages();
		showImmediateMessage();
	};
})(jQuery);