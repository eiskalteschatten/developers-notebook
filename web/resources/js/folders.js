function createFolder() {
    var div = $("<div>");
    div.addClass('editor-folder');
    div.addClass('folder');
    div.addClass('temp-new');
    div.html('<input type="text" onkeyup="checkCreateFolderKeyDown(event, this)" id="newFolder" placeholder="Folder Name" autocomplete="off">');
    $('.editor-folders.folders').append(div);

    $("#newFolder").focus();
}

function checkCreateFolderKeyDown(event, obj) {
    if (event.which == 13) {
        sendCreateFolder(obj);
    }
    else if (event.which == 27) {
        $('.editor-folder.temp-new').remove();
    }
}

function sendCreateFolder(obj) {
    var div = $('.editor-folder.temp-new');
    var name = $(obj).val();
    var url = $('.editor-folders.folders').attr('data-url-create');

    if (name != "") {
        var toSend = {
            standardArea: standardArea,
            name: name
        }

        $.post(url, toSend, function(data) {
            $(obj).remove();

            div.attr('data-id', data.id);
            div.text(data.name);

            div.click(function() {
                selectFolder($(this));
            });

            div.removeClass('temp-new');

            setDraggableAndDroppable();
        });
    }
    else {
        div.remove();
    }
}

function removeFolder(url) {
    var selected = $('.editor-folder.selected');

    if (selected.hasClass('folder')) {
        if (confirm('Are you sure you want to remove this folder? Its contents will not be deleted. This action cannot be undone.')) {
            var toSend = {
                id: selected.attr('data-id')
            }

            $.post(url, toSend, function(data) {
                var previewSibling = selected.prev();
                selected.remove();
                previewSibling.trigger('click');
            });
        }
    }
    else {
        alert("You must selected a folder to remove it.");
    }
}