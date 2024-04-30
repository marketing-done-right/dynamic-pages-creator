// Intercept the form submission
jQuery('form').on('submit', function(e) {
    e.preventDefault();  // Prevent the normal form submission
    jQuery('#progress').show(); // Show the progress bar or indicator
    processNextBatch(0);  // Start the batch process
});

function processNextBatch(index) {
    jQuery.ajax({
        url: dpcAjax.ajax_url,
        type: 'POST',
        data: {
            action: 'process_batch_creation',
            nonce: dpcAjax.nonce,
            batch_index: index
        },
        success: function(response) {
            if (response.success) {
                updateProgressBar(response.data.progress);
                if (response.data.continue) {
                    console.log('Processed batch ' + index + ', Progress: ' + response.data.progress + '%');
                    processNextBatch(index + 1);
                } else {
                    jQuery('#batch-status').text('All batches processed');
                    console.log('All batches processed');
                }
            } else {
                console.error('Batch processing error:', response.data.message);
                jQuery('#batch-status').text('Error processing batch: ' + response.data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            jQuery('#batch-status').text('AJAX request failed: ' + error);
        }
    });
}


function updateProgressBar(progress) {
    var progressBar = jQuery('#progress-bar');
    progressBar.val(progress); // Assuming it's an input type="progress"
    progressBar.text(progress + '%'); // Update the text if you have a label
}

jQuery(document).ready(function($) {
    $('#start-batch-process').on('click', function() {
        processNextBatch(0);
    });
})
