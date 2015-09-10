function addNewProject(event, obj) {
    if (event.which == 13) {
        var name = $(obj).val();

        if (name != "") {
            var toSend = {
                name: name
            }

            $.post(postUrlCreate, toSend, function(data) {
                var html = '<span class="delete-row"><a href="#!" onclick="deleteProject(\'' + data.id + '\')">Delete</a></span>';
                html += '<span class="is-completed-box"><input type="checkbox" onclick="toggleIsComplete(\'' + data.id + '\', this)" class="is-completed-check"></span>';
                html += '<span class="clickable-area" onclick="goToProject(\'/notebook/projects/'+data.id+'\');">';
                html += '<span class="name">' + data.name + '</span>';
                html += '<span class="date">' + data.date + '</span>';
                html += '</span>';

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

function toggleIsComplete(id, obj) {
    var isComplete = $(obj).prop('checked');

    var toSend = {
        id: id,
        isComplete: isComplete
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
            }
        }
        else {
            if (row.hasClass('row')) {
                row.removeClass('is-completed');
                row.find('.date').text(data.date);
            }
            else {
                $('.page-title').removeClass('grayed-out');
            }
        }
    });
}

function deleteProject(id) {
    if (confirm('Are you sure you want to remove this project? All data associated with the project will not be deleted. This action cannot be undone.')) {
        var toSend = {
            id: id
        }

        $.post(postUrlDelete, toSend, function (data) {
            window.location.href = projectsUrl;
        });
    }
}