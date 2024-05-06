jQuery(document).ready(function($) {
    // Check if fields should be cleared
    if (dpcData.clearFields === 'true') {
        $('#dynamic_pages_creator_keyword_field').val('');  // Clear the page keywords field
        $('#dynamic_pages_creator_parent_field').prop('selectedIndex',0);  // Reset the parent page dropdown
    }
});


jQuery(document).ready(function($) {
    $('.quickedit-action').click(function() {
        var postId = $(this).data('id');
        $('#post-row-' + postId).hide();
        $('#quick-edit-' + postId).show();
        return false;
    });

    $('.save-quick-edit').click(function() {
        var postId = $(this).data('id');
        var rowData = {
            'action': 'save_quick_edit',
            'post_id': postId,
            'title': $('#quick-edit-' + postId + ' input[name="post_title"]').val(),
            'slug': $('#quick-edit-' + postId + ' input[name="post_name"]').val(),
            'parent': $('#quick-edit-' + postId + ' select[name="post_parent"]').val(),
            'status': $('#quick-edit-' + postId + ' select[name="_status"]').val(),
            'nonce': $('#quick-edit-' + postId + ' input[name="quick_edit_nonce"]').val()
        };

        $.post(ajaxurl, rowData, function(response) {
            if (response.success) {
                // Update the title link text, preserving any HTML structure around the title
                $('#post-row-' + postId + ' .column-page_title a.row-title').text(response.data.title);

                // Handle the status badge update
                var statusBadge = $('#post-row-' + postId + ' .post-state');
                if (response.data.status.toLowerCase() === 'draft') {
                    if (statusBadge.length === 0) { // If there's no badge, create one right after the title link
                        $('#post-row-' + postId + ' .column-page_title a.row-title').after('<strong><span class="post-state" style="font-size:14px;"> — Draft</span></strong>');
                    } else {
                        statusBadge.html(' — Draft'); // If there is already a badge, just update the text
                    }
                } else {
                    statusBadge.remove(); // Remove the badge if the status is not draft
                }

                // Update slug
                $('#post-row-' + postId + ' .column-slug').text(response.data.slug);

                // Optionally update parent and status columns if they're visible
                $('#post-row-' + postId + ' .column-parent').text(response.data.parent_name);
                $('#post-row-' + postId + ' .column-status').text(response.data.status_label);

                // Update the counts in the status filters
                $('.all .count').text('(' + response.data.counts.all + ')');
                $('.publish .count').text('(' + response.data.counts.publish + ')');
                $('.draft .count').text('(' + response.data.counts.draft + ')');
                $('.trash .count').text('(' + response.data.counts.trash + ')');

                // Hide the quick edit form and show the row
                $('#quick-edit-' + postId).hide();
                $('#post-row-' + postId).show();
            } else {
                alert('Error: ' + response.data.message);
            }
        });
        return false;
    });

    // Cancel Quick Edit
    $('.cancel-quick-edit').click(function() {
        var postId = $(this).data('id');
        $('#quick-edit-' + postId).hide();
        $('#post-row-' + postId).show();
        return false;
    });
});
