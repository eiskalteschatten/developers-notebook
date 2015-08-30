// Scripts for the "Edit categories" page

// Basic category functions

function createCategory() {
    var name = $('#newCategory').val();
    var parent = $('#newCategoryParent').val();
    
    $.ajax({
        url: '_app/ajax.php',
        data: {action: 'create-category', name: name, parent: parent},
        type: 'post',
        success: function(msg) {
            if (msg == true) {
				location.reload(false);
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

function openEditCategory(catId) {
	$('#categoryRow'+catId).addClass('edit-opened');
	$('#categoryRow'+catId).attr('onclick', '');
	$('#categoryRow'+catId).click(function() {
		closeEditCategory(catId);
	});
	
	$('#edit-area'+catId).show();
	$('#edit-area'+catId).find('.toggle-div').css('height', '');
	$('#edit-area'+catId).find('.toggle-div').stop().animate({height: 'show', opacity: 1}, 300);
}

function closeEditCategory(catId) {
	$('#edit-area'+catId).find('.toggle-div').stop().animate({height: 'hide', opacity: 0}, 200, function() {
		$(this).css('height', '');
		$('#edit-area'+catId).hide();
		$('#categoryRow'+catId).removeClass('edit-opened');
		$('#categoryRow'+catId).click(function() {
			openEditCategory(catId);
		});
	});
}

function saveCategory(catId) {
	var editArea = $('#edit-area'+catId);
	var name = editArea.find('input[name="categoryName"]').val();
	var parent = editArea.find('select').val();

    $.ajax({
        url: '_app/ajax.php',
        data: {action: 'save-category', id: catId, name: name, parent: parent},
        type: 'post',
        success: function(msg) {
            if (msg == true) {
				$('#categoryRow'+catId).find('.allcategories-title').text(name);
				closeEditCategory(catId);
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