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
            'title': $('#quick-edit-' + postId + ' input[name="title"]').val(),
            'slug': $('#quick-edit-' + postId + ' input[name="slug"]').val(),
            'nonce': $('#quick-edit-' + postId + ' input[name="quick_edit_nonce"]').val()
        };

        $.post(ajaxurl, rowData, function(response) {
            if (response.success) {
                // Update the title within the link to maintain the hyperlink and actions
                $('#post-row-' + postId + ' .column-page_title a.row-title').text(response.data.title);
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
