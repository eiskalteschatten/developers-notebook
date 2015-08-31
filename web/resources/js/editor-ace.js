function changeTheme(select) {
    var theme = $(select).val();
    editor.setTheme(theme);
}

function changeMode(select) {
    var mode = $(select).val();
    editor.getSession().setMode("ace/mode/"+mode);
    
	$('.editor-page.selected').attr('data-syntax', mode);
}

function toggleToolbarOptions(button, closeButton) {
    if (!closeButton) {
        var options = $(button).parents().siblings('.toolbar-options');
    }
    else {
        var options = $(button).parents('.toolbar-options');
    }

    if (options.css('display') == "none") {
        var position = $(button).position();

        options.css('left', position.left - 510);
        options.css('top', position.top + 35);

        options.show();
        options.stop().animate({opacity: 1}, 200, function() {
            $('.popup-close-layer').click(function() {
                toggleToolbarOptions(button, closeButton);
            })
            $('.popup-close-layer').show();
        });
    }
    else {
        $('.popup-close-layer').hide();
        options.stop().animate({opacity: 0}, 200, function() {
            $(this).hide();
        });
    }
}

function getCursorPosition() {
    var position = editor.getCursorPosition();
    $('#rowPos').text(position.row);
    $('#colPos').text(position.column);
}

function toggleFullScreen(button) {
    if ($('.editor-container').hasClass('full-screen')) {
        $('.editor-container').removeClass('full-screen');
        $(button).html('&#8598;');
    }
    else {
        $('.editor-container').addClass('full-screen');
        $(button).html('&#8600;');
    }
}

function toggleInvisibles(button) {
    if (editor.getShowInvisibles()) {
        editor.setShowInvisibles(false);
        $(button).removeClass('selected');
    }
    else {
        editor.setShowInvisibles(true);
        $(button).addClass('selected');
    }
}

function undo() {
    editor.undo();
}

function redo() {
    editor.redo();
}

function toggleIndentGuides(button) {
    if (editor.getDisplayIndentGuides()) {
        editor.setDisplayIndentGuides(false);
        $(button).removeClass('selected');
    }
    else {
        editor.setDisplayIndentGuides(true);
        $(button).addClass('selected');
    }
}

function toggleReadOnly(button) {
    if (editor.getReadOnly()) {
        editor.setReadOnly(false);
        $(button).removeClass('selected');
    }
    else {
        editor.setReadOnly(true);
        $(button).addClass('selected');
    }
}

function scrollToLine(textbox) {
    editor.scrollToLine(parseInt($(textbox).val()) - 1);
}

function toggleFindReplace(button) {
    if ($('#findReplaceToolbar').css('display') != "none") {
        $('#findReplaceToolbar').hide();
        $(button).removeClass('selected');
    }
    else {
        $('#findReplaceToolbar').show();
        $(button).addClass('selected');
    }
}

function toggleHighlightActiveLine() {
    if (editor.getHighlightActiveLine()) {
        editor.setHighlightActiveLine(false);
    }
    else {
        editor.setHighlightActiveLine(true);
    }
}

var findOptions = {};

function setFindOptions(needle) {
    findOptions = {
        needle: needle,
        wrap: (($('#wrapSearch:checked').length > 0) ? true:false),
        caseSensitive: (($('#caseSensitive:checked').length > 0) ? true:false),
        wholeWord: (($('#wholeWord:checked').length > 0) ? true:false),
        regExp: (($('#regExp:checked').length > 0) ? true:false),
        skipCurrent: (($('#skipCurrent:checked').length > 0) ? true:false)
    };
}

function findPrev() {
    setFindOptions($('#findText').val());
    editor.findPrevious(findOptions, true);
}

function findNext() {
    setFindOptions($('#findText').val());
    editor.findNext(findOptions, true);
}

function replaceText() {
    setFindOptions($('#findText').val());
    editor.replace($('#replaceText').val(), findOptions);
}

function replaceAllText() {
    setFindOptions($('#findText').val());
    editor.replaceAll($('#replaceText').val(), findOptions);
}

function cancelOptions(button) {

}

function saveEditorOptions() {

}

function saveFindReplaceOptions() {

}