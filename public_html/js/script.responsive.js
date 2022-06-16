$(document).ready(function(){
	enquire.register("screen and (max-width: 950px)", {
		match : function() {
			// menu_mobile();
			// $('#main-menu nav').append($('#menu-header .menu'));
		},  
		unmatch : function() {
			// $('#menu-header nav').append($('#menu-header .menu'));
			$(document).off(eventTouch, '#main-menu nav >ul >li >a');
			$(document).off(eventTouch, '#main-menu nav >ul >li >ul >li >a');
			$(document).off(eventTouch, '#main-menu nav >ul >li >ul >li >a');
		}
	});
});

