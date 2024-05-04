<?php
// Function to render the list table
function dpc_render_list_table() {
    $list_table = new DPC_Created_Pages_List();
    $list_table->prepare_items();
    echo '<div class="wrap"><h1>Created Pages</h1>';
    $list_table->views();
    $list_table->display();
    echo '</div>';
}

dpc_render_list_table();