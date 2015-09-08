function addNewProject(event, obj) {
    if (event.which == 13) {
        var name = $(obj).val();

        if (name != "") {
            var toSend = {
                name: name
            }

            $.post(postUrlCreate, toSend, function(data) {
                var html = '<span class="delete-row"><a href="#!" onclick="deleteProject(this)">Delete</a></span>';
                html += '<span class="is-completed-box"><input type="checkbox" onclick="toggleIsComplete(this)" class="is-completed-check"></span>';
                html += '<span class="name" onclick="goToProject(\'/notebook/projects/'+data.id+'\');">' + data.name + '</span>';
                html += '<span class="date" onclick="goToProject(\'/notebook/projects/'+data.id+'\');">' + data.date + '</span>';

                var div = $("<div>");
                div.addClass('row');
                div.attr('data-id', data.id);
                div.html(html);

                $('.full-size-table').prepend(div);

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

function toggleIsComplete(obj) {
    var row = $(obj).parents('.row');
    var id = row.attr('data-id');
    var isComplete = $(obj).prop('checked');

    var toSend = {
        id: id,
        isComplete: isComplete
    }

    $.post(postUrlToggle, toSend, function(data) {
        if (data.isCompleted) {
            row.addClass('is-completed');
            row.find('.date').text("Completed: " + data.date);
        }
        else {
            row.removeClass('is-completed');
            row.find('.date').text(data.date);
        }
    });
}

function toggleProjectIsComplete(id, obj) {
    var isComplete = $(obj).prop('checked');

    var toSend = {
        id: id,
        isComplete: isComplete
    }

    $.post(postUrlToggle, toSend, function(data) {
        if (data.isCompleted) {
            $('.page-title').addClass('grayed-out');
        }
        else {
            $('.page-title').removeClass('grayed-out');
        }
    });
}

function deleteProject(obj) {
    if (confirm('Are you sure you want to remove this project? All data associated with the project will not be deleted. This action cannot be undone.')) {
        var row = $(obj).parents('.row');
        var id = row.attr('data-id');

        var toSend = {
            id: id
        }

        $.post(postUrlDelete, toSend, function (data) {
            row.remove();
        });
    }
}

function deleteSingleProject(id) {
    if (confirm('Are you sure you want to remove this project? All data associated with the project will not be deleted. This action cannot be undone.')) {
        var toSend = {
            id: id
        }

        $.post(postUrlDelete, toSend, function (data) {
            window.location.href = projectsUrl;
        });
    }
}