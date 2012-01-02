/* iScroll */
var myScroll;
function loaded() {
	myScroll = new iScroll('wrapper', {
		checkDOMChanges: true,
		scrollbarClass: 'scrollbar'
	});
}

document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
document.addEventListener('DOMContentLoaded', loaded, false);

jQuery(document).ready(function($) {
	$('.event').each(function(){
		var id = $(this).attr('data-event');
		$(this).hovercard({
			detailsHTML: $('#event-' + id).html()
		});
	});
});
