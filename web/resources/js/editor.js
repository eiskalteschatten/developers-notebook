function createPage() {
	var folderId = $('.editor-folder.selected').attr('data-id');
    var syntax = $('.editor-page.selected').attr('data-syntax');

    if (folderId == "") {
        folderId = -1;
    }

    if (syntax === undefined || syntax == "") {
        syntax = standardSyntax;
    }

    var toSend = {
        standardArea: standardArea,
        folder: folderId,
        project: 0,
        syntax: syntax
    }
    
    $.post(editorUrl+"create/", toSend, function(data) {
    	var div = $("<div>");
    	div.addClass('editor-page');
    	div.attr('data-id', data.id);
    	div.attr('data-folder', data.folder);
    	div.attr('data-syntax', data.syntax);
    	div.attr('data-project', data.project);	        	
    	div.html('<div class="preview"></div><div class="date">' + data.date + '</div><div class="content"></div>');
    	$('.editor-pages').append(div);
    	
    	div.click(function() {
    		selectPage($(this));
    	});
    	
    	div.trigger('click');
    	
    	setDraggableAndDroppable();
    });
}

function savePage() {
    var selected = $('.editor-page.selected');
    
    var toSend = {
        id: selected.attr('data-id'),
        syntax: selected.attr('data-syntax'),
        content: editor.getValue()
    }
    
    $.post(editorUrl+"save/", toSend, function(data) {
        updatePagePreview(data.previewContent);
		var content = editor.getValue();
		$('.editor-page.selected').find('.content').text(content);
    });
}

function removePage() {
    if (confirm('Are you sure you want to remove this page?')) {
	    var selected = $('.editor-page.selected');
    
        var toSend = {
	        id: selected.attr('data-id')
        }
        
        $.post(editorUrl+"remove/", toSend, function(data) {
            var previewSibling = selected.prev();
            selected.remove();
			previewSibling.trigger('click');
        });
	} 
}

function selectPage(obj) {
	$('.editor-page').removeClass('selected');
	obj.addClass('selected');
	
	var syntax = obj.attr('data-syntax');
	$('#mode').val(syntax);
	changeMode('#mode');
	
	var content = obj.find('.content').text();
	editor.setValue(content, -1);
}

function updatePagePreview(content) {
    $('.editor-page.selected').find('.preview').text(content);
}

function createFolder() {
	var div = $("<div>");
	div.addClass('editor-folder');
	div.addClass('folder');
	div.addClass('temp-new');
	div.html('<input type="text" onkeyup="checkCreateFolderKeyDown(event, this)" id="newFolder" placeholder="Folder Name">');
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
    
    if (name != "") {
	    var toSend = {
            standardArea: standardArea,
	        name: name
        }
	    
        $.post(editorUrl+"createFolder/", toSend, function(data) {
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

function selectFolder(obj) {
	$('.editor-folder').removeClass('selected');
	obj.addClass('selected');
	
	var id = $(obj).attr('data-id');
	
	if (id == "-1") {
		$('.editor-page').show();
	}
	else {
		$('.editor-page').hide();
		$('.editor-page[data-folder=' + id + ']').show();
	}

	if ($('.editor-page:visible').length <= 0) {
		editor.setValue("", -1);
	}

	$('.editor-page:visible:first').trigger('click');
}

function removeFolder() {
    var selected = $('.editor-folder.selected');
    
    if (selected.hasClass('folder')) {
        if (confirm('Are you sure you want to remove this folder? Its contents will not be deleted.')) {
		    var toSend = {
		        id: selected.attr('data-id')
	        }
	        
            $.post(editorUrl+"removeFolder/", toSend, function(data) {
				var previewSibling = selected.prev();
	            selected.remove();
				previewSibling.trigger('click');
            	//console.log(data);
            });
	    }
	} 
	else {
		alert("You must selected a folder to remove it.");
	}
}

function setDraggableAndDroppable() {
    $('.editor-page').draggable({
		revert: 'invalid', 
		helper: "clone",
		appendTo: 'body',
		containment: 'window'
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

			$.post(editorUrl+"movePageToFolder/", toSend, function(data) {
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

			$.post(editorUrl+"removePageFromFolders/", toSend, function(data) {
				page.attr('data-folder', data.folder);
				$('.editor-folder.selected').trigger('click');
			});
		}
	});
}

function setSettings() {
	$('#theme').val(defaultTheme);
	$('#defaultMode').val(standardSyntax);
}

function saveAllSettings() {
	var toSend = {
		defaultTheme: $('#theme').val(),
		defaultSyntax: $('#defaultMode').val(),
		highlightActiveLine: $('#highlightActiveLine').prop('checked'),
		wrapSearch: $('#wrapSearch').prop('checked'),
		caseSensitive: $('#caseSensitive').prop('checked'),
		wholeWord: $('#wholeWord').prop('checked'),
		regExp: $('#regExp').prop('checked'),
		skipCurrent: $('#skipCurrent').prop('checked'),
		area: standardArea
	}

	$.post(saveSettingsUrl, toSend, function(data) {
		closePopup();
	});
}

function saveFindReplaceOptions() {
	// Apply settings here too

	saveAllSettings();
}