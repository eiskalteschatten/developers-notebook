$(document).ready(function() {
    $('.date-picker').datepicker({
        dateFormat: "yy-mm-dd"
    });
});

function toggleIsComplete(id, obj) {
	openGeneralAjaxLoaderWithTimer();
	
    var isComplete = $(obj).prop('checked');

    var toSend = {
        id: id,
        isComplete: isComplete
    }

    $.post(issuesIsCompleteUrl, toSend, function(data) {
        var row = $(obj).parents('.row');

        if (data.isCompleted) {
            if (row.hasClass('row')) {
                row.addClass('is-completed');
                row.removeClass('selected');
                row.find('.date').text("Completed: " + data.date);
                
                if (!$('#toggleIsCompleted').hasClass('selected')) {
	                row.hide();	                
                }
            }
            else {
                $('.page-title').addClass('grayed-out');
            }
        }
        else {
            if (row.hasClass('row')) {
                row.removeClass('is-completed');
                row.find('.date').text(data.date);
                row.show();
            }
            else {
                $('.page-title').removeClass('grayed-out');
            }
        }
        
        closeGeneralAjaxLoader();
    }).fail(function() {
        showMessage('error', generalErrorMessage);
        closeGeneralAjaxLoader();
    });
}