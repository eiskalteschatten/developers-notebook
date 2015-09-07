// General scripts

function expandSearch(field) {
	$(field).stop().animate({width: '500px'}, 100, function() {
		$('body').click(function() {
			shrinkSearch(field);
		});
	});
}

function shrinkSearch(field) {
	$(field).stop().animate({width: '300px'}, 100);
	$('body').unbind('click');
}

function showConsole() {
	$('.console-tab').stop().animate({bottom: '200px'}, 300, function() {
		$(this).attr('onclick', '');
		$(this).click(hideConsole);
		var label = $(this).attr('data-close');
		$(this).html(label);
	});
	
	$('.console').stop().animate({top: '-=200px'}, 300);
}

function hideConsole() {
	$('.console-tab').stop().animate({bottom: '0'}, 200, function() {
		$(this).click(showConsole);
		var label = $(this).attr('data-open');
		$(this).html(label);
	});
	
	$('.console').stop().animate({top: '100%'}, 200);	
}

function openAccountDropdown(button) {
	var dropdown = $(button).siblings('.dropdown');
	dropdown.show();
	dropdown.stop().animate({opacity: 1}, 100, function() {
		$(button).addClass('open');
	
		$('.popup-close-layer').click(function() {
			closeAccountDropdown(button);
		})
		
		$('.popup-close-layer').show();	
	});
}

function closeAccountDropdown(button) {
	var dropdown = $(button).siblings('.dropdown');
	dropdown.stop().animate({opacity: 0}, 100, function() {
		$(this).hide();
		$(button).removeClass('open');	
		$('.popup-close-layer').hide();	
	});
}

// Functions for popups

function openPopup(id) {
	var obj = $('#'+id);
	obj.show();
	
	var content = obj.find('.popup-content');
	var width = content.outerWidth();
	var height = content.outerHeight();
	
	content.css('margin-left', Math.round((width/2)) * -1 + 'px');
	content.css('margin-top', Math.round((height/2)) * -1 + 'px');
	
	obj.stop().animate({opacity: 1}, 200);
	
	$('.popup-close-layer').click(function() {
		closePopup();
	})
	$('.popup-close-layer').addClass('background');
	$('.popup-close-layer').show();
}

function closePopup() {
	var obj = $('.popup');
	
	obj.stop().animate({opacity: 0}, 200, function() {
		$(this).hide();
		$('.popup-close-layer').hide();
		$('.popup-close-layer').removeClass('background');
	});
}

// Error and success messages

function showMessage(type, message) {
	var msgCenter = $('.message-center');

	var div = $("<div>");
	div.addClass(type);
	div.text(message);

	msgCenter.html(div);
	msgCenter.show();
}