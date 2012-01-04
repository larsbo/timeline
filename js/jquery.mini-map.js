/* 
jQuery Mini-Map plugin copyright Sam Croft <samcroft@gmail.com>
Licensed like jQuery - http://docs.jquery.com/License
*/
(function($){
	$.fn.minimap = function(){
		var miniMap = $('#mini-map');
		var miniMapCurrentView = $('#current-view');
		var offsetX, offsetY;
		var mapIconX, mapIconY;

		var el = this;
		var elPosition = el.offset();
		var events = el.find('.event');

		miniMap.width(el.width()/8);
		miniMap.height(el.height()/8);

		miniMapCurrentView.height(el.height()/8);
		miniMapCurrentView.width($(window).width()/8);

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
})(jQuery);