/* 
jQuery Mini-Map plugin copyright Sam Croft <samcroft@gmail.com>
Licensed like jQuery - http://docs.jquery.com/License
*/
(function($){
	$.fn.minimap = function(){
		var miniMap = $('#mini-map');
		var miniMapCurrentView = $('#current-view');
		var offsetX, offsetY;
		var mapIconX, mapIconY, yearIconLeft;

		var el = this;
		var elPosition = el.offset();
		var events = el.find('.event');
		var years = el.find('th');

		miniMap.height(el.height()/8 + 10);
		miniMap.width(el.width()/8);
		miniMapCurrentView.height(el.height()/8 + 10);
		miniMapCurrentView.width($(window).width()/8);

		years.each(function(i,t){
			if (i % 5 == 0) {	// show every 10th year
				var year = $(this);
				var yearCoords = year.offset();
				yearIconLeft = (yearCoords.left/8);

				var mapIcon = $('<div>' + year.text() + '</div>');
				mapIcon
				.css({
					'width': 18, 
					'left': yearIconLeft
				})
				.addClass(t.tagName.toLowerCase())
				.appendTo(miniMap);

			}
		});

		events.each(function(i,t){
			var event = $(this);
			var eventCoords = event.offset();
			var mapIconHeight = event.height()/8;
			var mapIconWidth = event.width()/8;
			var mapIconMarginLeft = parseInt(event.css('margin-left'))/8;
			var mapIconMarginRight = parseInt(event.css('margin-right'))/8;
			var mapIconMarginTop = parseInt(event.css('margin-top'))/8;
			var mapIconMarginBottom = parseInt(event.css('margin-bottom'))/8;

			if (i == 0) {
				mapIconX = (eventCoords.left/8) - mapIconMarginLeft;
				mapIconY = (eventCoords.top/8) - mapIconMarginTop;
			} else {
				mapIconX = (eventCoords.left/8) + offsetX;
				mapIconY = (eventCoords.top/8) + offsetY;
			}
			offsetX = mapIconMarginLeft + mapIconMarginRight;
			offsetY = mapIconMarginTop + mapIconMarginBottom;

			var mapIcon = $('<div>');
			mapIcon
			.css({
				'height': mapIconHeight, 
				'width': mapIconWidth, 
				'margin-left': mapIconMarginLeft, 
				'margin-right': mapIconMarginRight, 
				'left': mapIconX,
				'top': mapIconY
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