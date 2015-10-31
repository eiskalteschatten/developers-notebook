var generalAjaxSpinner;
var generalAjaxSpinnerTimeout;

// General scripts

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

function getUrlParameter(sParam) {
	var sPageURL = decodeURIComponent(window.location.search.substring(1)),
		sURLVariables = sPageURL.split('&'),
		sParameterName,
		i;

	for (i = 0; i < sURLVariables.length; i++) {
		sParameterName = sURLVariables[i].split('=');

		if (sParameterName[0] === sParam) {
			return sParameterName[1] === undefined ? true : sParameterName[1];
		}
	}
};

// Functions for the search

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

function submitSearch(event) {
	if (event.which == 13) {
		var url = $('.command-search').attr('data-search-url');
		
		window.location.href = url + '?q=' + $('.command-search-field').val();
	}
}

// Functions for ajax loader

function openGeneralAjaxLoader() {
	var ajaxLoaderGeneralOpts = {
        lines: 13, // The number of lines to draw
        length: 4, // The length of each line
        width: 1, // The line thickness
        radius: 4, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000', // #rgb or #rrggbb or array of colors
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: true, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: '50%', // Top position relative to parent
        left: '13px' // Left position relative to parent
    };
    
    $('.general-ajax-loader-container').show();
    $('.general-ajax-loader-container').stop().animate({'top':'0px'}, 400, function() {
        var target = document.getElementById('general-ajax-loader');
		generalAjaxSpinner = new Spinner(ajaxLoaderGeneralOpts).spin(target);
    });
}

function closeGeneralAjaxLoader() {
	clearTimeout(generalAjaxSpinnerTimeout);
	
	setTimeout(function() {
		if (typeof generalAjaxSpinner != 'undefined') {
		    generalAjaxSpinner.stop();
		    
		    $('.general-ajax-loader-container').stop().animate({'top':'-50px'}, 400, function() {
				$(this).hide();
		    });
	    }
    }, 500);
}

function openGeneralAjaxLoaderWithTimer() {
	generalAjaxSpinnerTimeout = setTimeout(openGeneralAjaxLoader, 500);
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

function confirmPopup(msg, callback) {
	$('#popupConfirm').find('.confirm-message').html(msg);
	$('#popupConfirm').find('#confirmButton').click(function() {
		callback();
		closePopup();
	});

	openPopup('popupConfirm');
}

function confirmPopupWithParam(msg, callback, param1) {
	$('#popupConfirm').find('.confirm-message').html(msg);
	$('#popupConfirm').find('#confirmButton').click(function() {
		callback(param1);
		closePopup();
	});

	openPopup('popupConfirm');
}

// Error and success messages

var messageTimeout;

function showMessage(type, message) {
	clearTimeout(messageTimeout);

	var msgCenter = $('.message-center');

	var div = $("<div>");
	div.addClass(type);
	div.text(message);

	msgCenter.html(div);
	msgCenter.fadeIn(500);

	messageTimeout = setTimeout(hideMessage, 3000);
}

function hideMessage() {
	var msgCenter = $('.message-center');
	msgCenter.fadeOut(500, function() {
		msgCenter.html('');
	});
}

// Functions for Autocomplete with comma separated values

function autocompleteSplit(val) {
	return val.split(/,\s*/);
}

function autocompleteExtractLast(term) {
	return autocompleteSplit(term).pop();
}

function setAutocomplete(obj, url) {
	$(obj).autocomplete({
		source: function (request, response) {
			$.getJSON(url, {
				term: autocompleteExtractLast(request.term)
			}, response);
		},
		search: function () {
			var term = autocompleteExtractLast(this.value);
			if (term.length < 1) {
				return false;
			}
		},
		focus: function () {
			return false;
		},
		select: function (event, ui) {
			var terms = autocompleteSplit(this.value);
			terms.pop();
			terms.push(ui.item.value);
			terms.push("");
			this.value = terms.join(", ");
			return false;
		}
	});
}

// Functions for labels

function checkLabelsDarkLight() {
	$('.label-color').each(function() {
		var color = $(this).css('background-color');
		color = color.substring(4, color.length - 1);
		var rgb = color.split(',');
		var luma = 0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2];

		if (luma < 128) {
			$(this).addClass('dark-bg');
		}
	});
}

function loadLabelColorPicker() {
	$('.label-color').colorPicker({
		GPU: true,
		renderCallback: function ($elm, toggled) {
			var colorHex = '#' + this.color.colors.HEX;

			$($elm).text(colorHex);

			var colors = this.color.colors.RND,
				modes = {
					r: colors.rgb.r, g: colors.rgb.g, b: colors.rgb.b,
					h: colors.hsv.h, s: colors.hsv.s, v: colors.hsv.v,
					HEX: this.color.colors.HEX
				};

			$('input', '.cp-panel').each(function() {
				this.value = modes[this.className.substr(3)];
			});

			if (toggled === false) {
				var id = $($elm).attr('data-id');
				saveLabelColor(id, colorHex);
			}
		},
		buildCallback: function($elm) {
			var colorInstance = this.color,
				colorPicker = this;

			$elm.prepend('<div class="cp-panel">' +
				'R <input type="text" class="cp-r" /><br>' +
				'G <input type="text" class="cp-g" /><br>' +
				'B <input type="text" class="cp-b" /><hr>' +
				'H <input type="text" class="cp-h" /><br>' +
				'S <input type="text" class="cp-s" /><br>' +
				'B <input type="text" class="cp-v" /><hr>' +
				'<input type="text" class="cp-HEX" />' +
				'</div>').on('change', 'input', function(e) {
				var value = this.value,
					className = this.className,
					type = className.split('-')[1],
					color = {};

				color[type] = value;
				colorInstance.setColor(type === 'HEX' ? value : color,
					type === 'HEX' ? 'HEX' : /(?:r|g|b)/.test(type) ? 'rgb' : 'hsv');
				colorPicker.render();
				this.blur();
			});
		}
	});
}

function saveLabelColor(id, color) {
	openGeneralAjaxLoaderWithTimer();

	var toSend = {
		id: id,
		color: color
	}

	$.post(labelsSaveUrl, toSend, function(data) {
		closeGeneralAjaxLoader();
	}).fail(function() {
		showMessage('error', generalErrorMessage);
		closeGeneralAjaxLoader();
	});
}