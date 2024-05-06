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
            'seo_template': $('input[name="seo_template"]:checked').val(),
            'nonce': $('#quick-edit-' + postId + ' input[name="quick_edit_nonce"]').val()
        };

        $.post(ajaxurl, rowData, function(response) {
            if (response.success) {
                // Update the title link text, preserving any HTML structure around the title
                $('#post-row-' + postId + ' .column-page_title a.row-title').text(response.data.title);

                // Update the SEO Template column
                $('#post-row-' + postId + ' .column-seo_template').text(response.data.seo_template);

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

                // Reapply odd/even classes
                $('tr[id^="post-row-"]').each(function(index) {
                    $(this).removeClass('odd even');
                    var className = index % 2 === 0 ? 'even' : 'odd';
                    $(this).addClass(className);
                });
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

jQuery(document).ready(function($) {
    function stripeTable() {
        // Remove existing stripes
        $('.dynamic-pages-creator_page_dynamic-pages-view-pages tr').removeClass('odd even');

        // Add stripes only to visible rows
        $('.dynamic-pages-creator_page_dynamic-pages-view-pages tr:visible').each(function(index) {
            $(this).addClass((index % 2 == 0) ? 'odd' : 'even');
        });
    }

    // Initial striping
    stripeTable();

    // Reapply striping whenever a quick edit is toggled
    $('.quickedit-action, .cancel-quick-edit, .save-quick-edit').click(function() {
        stripeTable();
    });

    // Also reapply after any operations that might show or hide rows
    $('.save-quick-edit').click(function() {
        // Assuming you might be showing/hiding rows on save
        setTimeout(stripeTable, 100); // Delay to ensure rows are shown/hidden
    });
});

jQuery(document).ready(function($) {
    // Function to initialize radio buttons within a specific container
    function initializeRadioButtons(container) {
        // Only initialize radio buttons within the given container
        container.find('input[type="radio"]').each(function() {
            if ($(this).is(':checked')) {
                $(this).addClass('checked');
            }
        });
    }

    // Event handler for radio button changes within any Quick Edit row
    $('.quick-edit-row input[type="radio"]').on('change', function() {
        var $row = $(this).closest('.quick-edit-row');

        // Remove 'checked' class from all radios in the same row to ensure only one shows the effect
        $row.find('input[type="radio"]').removeClass('checked');

        // Add 'checked' class to the currently selected radio
        if ($(this).is(':checked')) {
            $(this).addClass('checked');
        }
    });

    // Reinitialize radio buttons when a Quick Edit is opened
    $('.quickedit-action').click(function() {
        var postId = $(this).data('id');

        // Find the Quick Edit row for the clicked action
        var $quickEditRow = $('#quick-edit-' + postId);

        // Initialize radio buttons only in the opened Quick Edit row
        initializeRadioButtons($quickEditRow);
    });

    // Initial setup for all radio buttons on page load
    initializeRadioButtons($('body'));
});
