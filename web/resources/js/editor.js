function createPage() {
	openGeneralAjaxLoaderWithTimer();
	
	var folderId = $('.folders').find('.editor-folder.selected').attr('data-id');
	var projectId = $('.projects').find('.editor-folder.selected').attr('data-id');
    var syntax = $('.editor-page.selected').attr('data-syntax');

	if (folderId === undefined || folderId == "") {
		folderId = -1;
	}

	if (projectId === undefined || projectId == "") {
		projectId = -1;
	}

    if (syntax === undefined || syntax == "") {
        syntax = standardSyntax;
    }

    var toSend = {
        standardArea: standardArea,
        folder: folderId,
        project: projectId,
        syntax: syntax
    }
    
    $.post(editorUrl+"create/", toSend, function(data) {
    	var div = $("<div>");
    	div.addClass('editor-page');
    	div.attr('data-id', data.id);
    	div.attr('data-folder', data.folder);
    	div.attr('data-syntax', data.syntax);
		div.attr('data-project', data.project);
		div.attr('data-year', data.year);
    	div.html('<div class="preview"></div><div class="date">' + data.date + '</div><div class="content"></div>');
    	$('.editor-pages').append(div);
    	
    	div.click(function() {
    		selectPage($(this));
    	});
    	
    	div.trigger('click');
    	
    	setDraggableAndDroppable();
        closeGeneralAjaxLoader();
    }).fail(function() {
		showMessage('error', generalErrorMessage);
		closeGeneralAjaxLoader();
	});
}

function savePage() {
	openGeneralAjaxLoaderWithTimer();
	
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

        closeGeneralAjaxLoader();
    }).fail(function() {
		showMessage('error', generalErrorMessage);
		closeGeneralAjaxLoader();
	});
}

function removePage() {
	var selected = $('.editor-page.selected');

	if (selected.length) {
		confirmPopup('Are you sure you want to remove this page? <b>This action cannot be undone.</b>', removePageConfirmed);
	}
	else {
		showMessage('error', 'You must select a page to delete.');
	}
}

function removePageConfirmed() {
	openGeneralAjaxLoaderWithTimer();
	
	var selected = $('.editor-page.selected');

	var toSend = {
		id: selected.attr('data-id')
	}

	$.post(editorUrl + "remove/", toSend, function (data) {
		var previewSibling = selected.prev();
		selected.remove();
		previewSibling.trigger('click');
		
        closeGeneralAjaxLoader();
	}).fail(function() {
		showMessage('error', generalErrorMessage);
		closeGeneralAjaxLoader();
	});
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

function selectYear(obj) {
	$('.editor-folder').removeClass('selected');
	obj.addClass('selected');

	var id = $(obj).attr('data-id');

	$('.editor-page').hide();
	$('.editor-page[data-year=' + id + ']').show();

	if ($('.editor-page:visible').length <= 0) {
		editor.setValue("", -1);
	}

	$('.editor-page:visible:first').trigger('click');
}

function selectProject(obj) {
	$('.editor-folder').removeClass('selected');
	obj.addClass('selected');

	var id = $(obj).attr('data-id');

	$('.editor-page').hide();
	$('.editor-page[data-project=' + id + ']').show();

	if ($('.editor-page:visible').length <= 0) {
		editor.setValue("", -1);
	}

	$('.editor-page:visible:first').trigger('click');
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
			openGeneralAjaxLoaderWithTimer();
			
			var folder = $(this);
			var page = $(ui.draggable);

			var toSend = {
				folderId: folder.attr('data-id'),
				pageId: page.attr('data-id')
			}

			$.post(editorUrl+"movePageToFolder/", toSend, function(data) {
				page.attr('data-folder', data.folder);
				$('.editor-folder.selected').trigger('click');
				
		        closeGeneralAjaxLoader();
			}).fail(function() {
				showMessage('error', generalErrorMessage);
				closeGeneralAjaxLoader();
			});
		}
	});

	$('.editor-folder.all-projects-folders').droppable({
		hoverClass: "folder-hover",
		drop: function( event, ui ) {
			openGeneralAjaxLoaderWithTimer();
			
			var folder = $(this);
			var page = $(ui.draggable);

			var toSend = {
				folderId: -1,
				pageId: page.attr('data-id')
			}

			$.post(editorUrl+"removePageFromFolders/", toSend, function(data) {
				page.attr('data-folder', data.folder);
				$('.editor-folder.selected').trigger('click');
				
		        closeGeneralAjaxLoader();
			}).fail(function() {
				showMessage('error', generalErrorMessage);
				closeGeneralAjaxLoader();
			});
		}
	});

	$('.editor-folder.project').droppable({
		hoverClass: "folder-hover",
		drop: function( event, ui ) {
			openGeneralAjaxLoaderWithTimer();
			
			var project = $(this);
			var page = $(ui.draggable);

			var toSend = {
				projectId: project.attr('data-id'),
				pageId: page.attr('data-id')
			}

			$.post(editorUrl+"movePageToProject/", toSend, function(data) {
				page.attr('data-project', data.project);
				$('.editor-folder.selected').trigger('click');
				
		        closeGeneralAjaxLoader();
			}).fail(function() {
				showMessage('error', generalErrorMessage);
				closeGeneralAjaxLoader();
			});
		}
	});
}

function saveAllSettings() {
	openGeneralAjaxLoaderWithTimer();
	
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
        closeGeneralAjaxLoader();
	}).fail(function() {
		showMessage('error', generalErrorMessage);
		closeGeneralAjaxLoader();
	});
}

function saveFindReplaceOptions() {
	saveAllSettings();
}