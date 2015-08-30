function savePost(button, publish) {
	$(button).stop().animate({opacity: 0}, 200, function() {
		var id = $('#postId').val();
		var title = $('#postTitle').val();
		var markdown = $('.markdown-editor').val();
		var html = $('.html-editor').val();
		var tags = $('.tags-input').val();
		var categories = new Array();
		
		$('input[name="category"]:checked').each(function() {
			categories.push($(this).val());
		});
		
		if (categories.length == 0) {
			categories.push('1');
		}
		
		var categoriesStr = categories.join();
		
		if (publish) {
			published = 1;		

			$('.draft-buttons').removeClass('visible');
			$('.published-buttons').addClass('visible');
		}
		else {
			published = 0;
			
			$('.published-buttons').removeClass('visible');
			$('.draft-buttons').addClass('visible');
		}

		$.ajax({
			url: '_app/ajax.php',
			data: {action: 'save', id: id, published: published, title: title, markdown: markdown, html: html, categories: categoriesStr, tags: tags},
			type: 'post',
			success: function(msg) {
				var values = JSON.parse(msg);
				$('#postId').val(values.id);
				$('.date-updated').show();
				$('.date-updated').find('.date').text(values.date);
		
				$(button).stop().animate({opacity: 1}, 200);
			},
			error: function(msg) {
				console.log("Error\n"+msg);	
				$(button).stop().animate({opacity: 1}, 200);	
			}
		});	
	});
}

function autoSavePost() {
    var button = document.getElementById("saveDraft");
    savePost(button, false);
}

function deletePost() {
    closePopup("confirmdelete-popup");
    
    var id = $('#postId').val();
    
    $('#deletePost').stop().animate({opacity: 0}, 200, function() {
        $.ajax({
			url: '_app/ajax.php',
			data: {action: 'delete-post', id: id},
			type: 'post',
			success: function(msg) {
				$('#deletePost').stop().animate({opacity: 1}, 200, function() {
					window.location.href = "index.php";
				});
			},
			error: function(msg) {
				alert("An error has occurred!\n\n"+msg);	
				$('#deletePost').stop().animate({opacity: 1}, 200);	
			}
        });
    });
}

function publish(button) {
	openPopup("confirmpublish-popup");

	$("#publishPost").click(function() {
		savePost(button, true);
		closePopup("confirmpublish-popup");
		clearInterval(autoSave);
	});
}

function unpublish(button) {
	openPopup("confirmunpublish-popup");

	$("#unpublishPost").click(function() {
		savePost(button, false);
		closePopup("confirmunpublish-popup");
		autoSave = setInterval(autoSavePost, autoSaveInterval);
	});
}


// Editor functions

function showmarkdownEditor(button) {
	$('.html-editor').removeClass('visible');
	$('.markdown-editor').addClass('visible');
	
	$('.editor-type').find('a').removeClass('selected');
	$(button).addClass('selected');
}

function showHtmlEditor(button) {
	$('.markdown-editor').removeClass('visible');
	$('.html-editor').addClass('visible');
	
	$('.editor-type').find('a').removeClass('selected');
	$(button).addClass('selected');
}


// Live preview functions

function updateTyping() {
	updateWordCount();
	updateHtml();
}

function updateWordCount() {
	var markdownContent = $('.markdown-editor').val();
	var contentArray = markdownContent.split(" ");
	var size = contentArray.length;
	
	for (var i = 0; i < size; i++) {
		if (contentArray[i] == "") {
			contentArray.remove(i);
		}
	}

	size = contentArray.length;
	
	$('.word-count').find('.count').text(size);
}

function updateHtml() {
	var markdownContent = $('.markdown-editor').val();
	
	marked(markdownContent, function (err, content) {
  		if (err) {
  			throw err;
	  		console.log(content);
	  	}
	  	else {
			$('.html-editor').val(content);
			updatePreview();
		}
	});
}

function updatePreview() {
	var html = $('.html-editor').val();
	$('.preview-content').html(html);
}


// Functions for the right panel

function showLivePreview(button) {
	closeAllRight();
	$('.preview-content').addClass('visible');
	
	$(button).addClass('selected');
}

function showMarkdownGuide(button) {
	closeAllRight();
	$('.markdown-guide').addClass('visible');
	
	$(button).addClass('selected');
}

function showPostOptions() {
	closeAllRight();
	$('.post-options').addClass('visible');
	
	$('#postOptions').addClass('selected');
}

function closeAllRight() {
    $('.preview-content').removeClass('visible');
    $('.markdown-guide').removeClass('visible');
    $('.post-options').removeClass('visible');
    
    $('.markdown-help').find('a').removeClass('selected');
}


// Category functions

function createCategory() {
    var name = $('#newCategory').val();
    var parent = $('#newCategoryParent').val();
    
    $.ajax({
        url: '_app/ajax.php',
        data: {action: 'create-category', name: name, parent: parent},
        type: 'post',
        success: function(msg) {
            if (msg == true) {
                updateCategories();
            }
            else {
                alert("Something went wrong while creating a new category! Please try again.");
            }
        },
        error: function(msg) {
            alert("Something went wrong while creating a new category! Please try again.\n\n"+msg);
        }
    });
}

function updateCategories() {
	var categories = new Array();
	
	$('input[name="category"]:checked').each(function() {
		categories.push($(this).val());
	});

    $.ajax({
        url: '_app/ajax.php',
        data: {action: 'update-categories', categories: categories},
        type: 'post',
        success: function(msg) {
            if (msg) {
                $('.all-categories').html(msg);
            }
            else {
                alert("Something went wrong while creating a new category! Please try again.");
            }
        },
        error: function(msg) {
            alert("Something went wrong while creating a new category! Please try again.\n\n"+msg);
        }
    });
}
