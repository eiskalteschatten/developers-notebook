function registerUser() {
	var password = $('input[name="password"]').val();
	var confirmPassword = $('input[name="confirm-password"]').val();
	
	if (password == confirmPassword) {
		document.forms["registerForm"].submit();	
	}
	else {
		$('.message').text("The passwords you entered don't match.");
		showMessage();
	}
}

function showMessage() {
	$('.login-box').stop().animate({height: '360px', marginTop: '-205px'}, 200, function() {
		$('.message').show();
		$('.message').stop().animate({opacity: 1}, 150);
	});
}