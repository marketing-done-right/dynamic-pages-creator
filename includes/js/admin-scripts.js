jQuery(document).ready(function($) {
    // Check if fields should be cleared
    if (dpcData.clearFields === 'true') {
        $('#dynamic_pages_creator_keyword_field').val('');  // Clear the page keywords field
        $('#dynamic_pages_creator_parent_field').prop('selectedIndex',0);  // Reset the parent page dropdown
    }
});
