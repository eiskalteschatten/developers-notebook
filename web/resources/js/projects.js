function addNewProject(event, obj) {
    if (event.which == 13) {
        var name = $(obj).val();

        if (name != "") {
            var toSend = {
                name: name
            }

            $.post(postUrlCreate, toSend, function(data) {
	        	var div = $('.row:first').clone();
	            div.attr('data-id', data.id);
	            div.find('.to-empty').text('');
	            div.find('.name').text(data.name);
	            div.find('.date').text(data.date);
	            div.removeClass('to-be-cloned');
	            div.removeClass('is-completed');
	            
	            div.find('.delete-link').attr('onclick', '');
	            div.find('.delete-link').click(function () {
		            deleteProject(data.id);
	            });
	            
	            div.find('.is-completed-check').prop('checked', false);
	            div.find('.is-completed-check').attr('onclick', '');
	            div.find('.is-completed-check').click(function () {
					toggleIsComplete(data.id, $(this));
	            });
	            
	            div.find('.clickable-area').attr('href', data.url);
	            
	            $('#itemTable').prepend(div);

                $(obj).val('');
            });
        }
    }
}

function toggleCompletedProjects() {
    if ($('#showCompleted').prop('checked')) {
        $('.is-completed').show();
    }
    else {
        $('.is-completed').hide();
    }
}

function toggleIsComplete(id, obj) {
    var isComplete = $(obj).prop('checked');
    var allComplete = false;

    if (isComplete) {
        if (confirm("Would you like to mark all to dos and issues belonging to this project as completed?\n\nAll to dos and issues will remain marked as completed even if you mark this project as not completed again.\n\nThis action cannot be undone.")) {
            allComplete = true;
        }
    }

    var toSend = {
        id: id,
        isComplete: isComplete,
        allComplete: allComplete
    }

    $.post(postUrlToggle, toSend, function(data) {
        var row = $(obj).parents('.row');

        if (data.isCompleted) {
            if (row.hasClass('row')) {
                row.addClass('is-completed');
                row.find('.date').text("Completed: " + data.date);
            }
            else {
                $('.page-title').addClass('grayed-out');
                $('.is-completed').show();
            }
        }
        else {
            if (row.hasClass('row')) {
                row.removeClass('is-completed');
                row.find('.date').text(data.date);
            }
            else {
                $('.page-title').removeClass('grayed-out');
                $('.is-completed').hide();
            }
        }
    });
}

function deleteProject(id) {
    confirmPopupWithParam('Are you sure you want to remove this project? All data associated with the project will not be deleted. <b>This action cannot be undone.</b>', removeConfirmed, id);
}

function removeConfirmed(id) {
    var toSend = {
        id: id
    }

    $.post(postUrlDelete, toSend, function (data) {
        window.location.href = projectsUrl;
    });
}