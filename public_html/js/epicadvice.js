$(function() {
	$.offset.initialize();
	if ($.offset.supportsFixedPosition) $("body").addClass('supports-fixed');

	// Clear the search value on click
	$('#searchInput').one('click', function() {
		$(this).attr("value","")
	});
	
	$(".hide-info").each(function() {
		var el = $(this);
		el.closest('.question-summary').hover(function(e) {
			el.toggleClass('hide-info', e.type=="mouseleave");
			el.toggleClass('show-info', e.type=="mouseenter");
		});
		el.wrap('<div>');
		var wrapper = el.parent();
		wrapper.css(el.position());
		wrapper.css('position', 'absolute');
	});
	
	$(".epicdb-button").hover(function(e) {
	  var el = $(this);
	  el.toggleClass('ui-state-hover', e.type == 'mouseenter');
	})
});