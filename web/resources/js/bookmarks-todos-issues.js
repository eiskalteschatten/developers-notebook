$(document).ready(function() {
    $('.editor-folder').click(function() {
        $('.tab-section').hide();
        $('.editor-folder').removeClass('selected');

        var dataSection = $(this).attr('data-section');
        $('.'+dataSection).show();
        $(this).addClass('selected');
    });

    $('.editor-folder.all-projects-folders').click(function() {
        selectFolder($(this));
    });

    $('.editor-folder.folder').click(function() {
        selectFolder($(this));
    });

    $('.editor-folder.project').click(function() {
        selectProject($(this));
    });

    $('.all-projects-folders').trigger('click');

    var selectedItem = getUrlParameter("selectedItem");

    if (selectedItem !== undefined) {
        $('.row[data-id="'+selectedItem+'"]').trigger('click');
    }

    setDraggableAndDroppable();
});

function createItem() {
    var folderId = $('.folders').find('.editor-folder.selected').attr('data-id');
    var projectId = $('.projects').find('.editor-folder.selected').attr('data-id');

    if (folderId === undefined || folderId == "") {
        folderId = -1;
    }

    if (projectId === undefined || projectId == "") {
        projectId = -1;
    }

    var toSend = {
        folder: folderId,
        project: projectId,
    }

    $.post(createItemUrl, toSend, function(data) {
        createCallback(data);
    });
}

function removeItem() {
    var selected = $('.row.selected');

    if (selected.length) {
        confirmPopup('Are you sure you want to remove this item? <b>This action cannot be undone.</b>', removeItemConfirmed);
    }
    else {
        showMessage('error', 'You must select an item to delete.');
    }
}

function removeItemConfirmed() {
    var selected = $('.row.selected');

    var toSend = {
        id: selected.attr('data-id')
    }

    $.post(removeItemUrl, toSend, function(data) {
        var previewSibling = selected.prev();
        selected.remove();
        previewSibling.trigger('click');
    });
}

function selectItem(obj) {
    $('.row').removeClass('selected');
    $(obj).addClass('selected');
}

function closeEdit(obj) {
    var row = $(obj).parents('.row');
    row.find('.info').show();
    row.find('.edit').hide();
    row.removeClass('edit-mode');
}

function selectFolder(obj) {
    $('.editor-folder').removeClass('selected');
    obj.addClass('selected');

    var id = $(obj).attr('data-id');

    if (id == "-1") {
        if ($('#toggleIsCompleted').hasClass('selected')) {
            $('.row').show();
        }
        else {
            $('.row:not(.is-completed)').show();
        }
    }
    else {
        $('.row').hide();

        if ($('#toggleIsCompleted').hasClass('selected')) {
            $('.row[data-folder=' + id + ']').show();
        }
        else {
            $('.row[data-folder=' + id + ']:not(.is-completed)').show();
        }
    }
}

function selectProject(obj) {
    $('.editor-folder').removeClass('selected');
    obj.addClass('selected');

    var id = $(obj).attr('data-id');

    $('.row').hide();

    if ($('#toggleIsCompleted').hasClass('selected')) {
        $('.row[data-project=' + id + ']').show();
    }
    else {
        $('.row[data-project=' + id + ']:not(.is-completed)').show();
    }
}

function setDraggableAndDroppable() {
    var draggableHelper = function() {
        var div = $("<div>");

        var clone = $(this).find('.info').clone();
        clone.find('.edit-button').remove();
        clone.addClass('draggable-item');
        clone.find('input').remove();
        clone.find('.gray-info').remove();

        div.html(clone);

        return div;
    };

    $('.row').each(function() {
        if (!$(this).hasClass('to-be-cloned')) {
            $(this).draggable({
                revert: 'invalid',
                helper: draggableHelper,
                appendTo: 'body',
                containment: 'window'
            });
        }
    });

    $('.editor-folder.folder').droppable({
        hoverClass: "folder-hover",
        drop: function( event, ui ) {
            var folder = $(this);
            var page = $(ui.draggable);

            var toSend = {
                folderId: folder.attr('data-id'),
                pageId: page.attr('data-id')
            }

            $.post(movePageToFolderUrl, toSend, function(data) {
                page.attr('data-folder', data.folder);
                $('.editor-folder.selected').trigger('click');
            });
        }
    });

    $('.editor-folder.all-projects-folders').droppable({
        hoverClass: "folder-hover",
        drop: function( event, ui ) {
            var folder = $(this);
            var page = $(ui.draggable);

            var toSend = {
                folderId: -1,
                pageId: page.attr('data-id')
            }

            $.post(removePageFromFoldersUrl, toSend, function(data) {
                page.attr('data-folder', data.folder);
                $('.editor-folder.selected').trigger('click');
            });
        }
    });

    $('.editor-folder.project').droppable({
        hoverClass: "folder-hover",
        drop: function( event, ui ) {
            var project = $(this);
            var page = $(ui.draggable);

            var toSend = {
                projectId: project.attr('data-id'),
                pageId: page.attr('data-id')
            }

            $.post(movePageToProjectUrl, toSend, function(data) {
                page.attr('data-project', data.project);
                $('.editor-folder.selected').trigger('click');
            });
        }
    });
}