jQuery(document).ready(function($) {
    $('#lfdl_sync_listings').on('click', function() {
        var fd = new FormData();

        fd.append('lfdl_nonce', lfdl_ajax_admin_obj.nonce);
        fd.append('action', 'lfdl_sync_listings');

        var spinner = $('#lfdl_spinner_container');
        spinner.show();

        jQuery.ajax({
            url: lfdl_ajax_admin_obj.ajaxurl,
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            dataType: "JSON"
        })
        .done(function(results) {
            if (results.error) {
                showSyncNotice('Sync failed ' + results.error, true);
            } else {
                showSyncNotice('Sync completed successfully', false);
            }

            spinner.hide();
        })        
        .fail(function(xhr, textStatus, errorThrown) {
            console.log('Request Failed. Status - ' + textStatus);
            showSyncNotice('Sync failed: ' + errorThrown, true);
        });
    });

    function showSyncNotice(message, isError) {
        var noticeClass = isError ? 'notice-error' : 'notice-success';
        var notice = $('<div class="notice ' + noticeClass + ' lfdl_sync is-dismissible"><p>' + message + '</p></div>');
        
        if (!isError) {
            // Add the dismiss button for success notices
            notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');
            
            // Handle dismiss event for success notices
            notice.on('click', '.notice-dismiss', function() {
                notice.hide();
            });
        }
        
        // Insert the notice before the .wrap element
        $('.wrap').before(notice);
    }
});
