<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class DPC_Created_Pages_List extends WP_List_Table {

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        
        // Capture sort and order inputs from the URL
        $orderBy = !empty($_GET["orderby"]) ? $_GET["orderby"] : 'date'; // Default sort by 'date'
        $order = !empty($_GET["order"]) ? $_GET["order"] : 'desc'; // Default order

        // Sort the data
        usort($data, function($a, $b) use ($orderBy, $order) {
            if ($orderBy === 'page_title') {
                // Compare titles without HTML tags
                $valA = strip_tags($a[$orderBy]);
                $valB = strip_tags($b[$orderBy]);
            } else {
                $valA = $a[$orderBy];
                $valB = $b[$orderBy];
            }
            if ($order === 'asc') {
                return strcmp($valA, $valB);
            } else {
                return strcmp($valB, $valA);
            }
        });

        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ));

        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    public function get_columns() {
        $columns = array(
            'page_title' => 'Page Title',
            'slug'       => 'Slug',
            'date'       => 'Date Created'
        );
        return $columns;
    }

    public function views() {
        $status_links = array();
        $current = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'all';
    
        // Calculate the number of items for each status
        $num_posts = $this->count_posts();
        $base_link = esc_url(remove_query_arg(array('action', 'status')));
    
        // Prepare status links
        $total_non_trash = $num_posts->publish + $num_posts->draft; // Sum only non-trashed statuses
        $status_links['all'] = $this->get_status_link('All', $base_link, $current, 'all', $total_non_trash);
        $status_links['publish'] = $this->get_status_link('Published', add_query_arg('status', 'publish', $base_link), $current, 'publish', $num_posts->publish);
        if ($num_posts->draft > 0) {
            $status_links['draft'] = $this->get_status_link('Draft', add_query_arg('status', 'draft', $base_link), $current, 'draft', $num_posts->draft);
        }
        if ($num_posts->trash > 0) {
            $status_links['trash'] = $this->get_status_link('Trash', add_query_arg('status', 'trash', $base_link), $current, 'trash', $num_posts->trash);
        }
    
        echo '<ul class="subsubsub">';
        foreach ($status_links as $key => $link) {
            echo "<li class='$key'>$link</li>";
        }
        echo '</ul>';
    }
    
    private function get_status_link($name, $url, $current, $status, $count) {
        $class = ($current === $status) ? ' class="current"' : '';
        $full_url = esc_url(add_query_arg('status', $status, $url));
        $link = sprintf('<a href="%s"%s>%s <span class="count">(%d)</span></a>', $full_url, $class, $name, $count);
        return $link;
    }
    
    private function count_posts() {
        $counts = (object) array(
            'publish' => 0,
            'draft' => 0,
            'trash' => 0
        );
    
        $all_posts = get_option('dynamic_pages_creator_existing_pages_ids', []);
        foreach ($all_posts as $id => $info) {
            $post_status = get_post_status($id);
            if ($post_status) {
                if (isset($counts->$post_status)) {
                    $counts->$post_status++;
                }
            }
        }
    
        return $counts;
    }    
    
    private function table_data() {
        $data = [];
        $current_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'all';
        $search_query = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';  // Retrieve the search term from the request
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        foreach ($existing_pages_ids as $id => $info) {
            $post = get_post($id);

            // Exclude trashed posts from the 'all' view
            if ($current_status == 'all' && $post->post_status == 'trash') {
                continue;
            }

            if ($current_status == 'all' || $post->post_status == $current_status) {
                $post_state = '';
                if (!empty($search_query) && stripos($post->post_title, $search_query) === false) {
                    continue;  // Skip posts that do not match the search query
                }
                // Append post state for the 'all' filter
            if ($current_status == 'all' && $post->post_status !== 'publish') {
                $post_state .= " — " . ucfirst($post->post_status);
            }
                $formatted_date = date('Y/m/d \a\t g:i a', strtotime($info['date'])); 
                $data[] = array(
                    'ID'          => $post->ID,
                    'page_title' => '<a class="row-title" href="' . esc_url(get_edit_post_link($id)) . '">' . esc_html(get_the_title($id)) . '</a><strong><span style="font-size:14px; class="post-state">'. $post_state .'</strong></span>',
                    'slug'       => esc_html(get_post_field('post_name', $id)),
                    'date'       => $formatted_date
                );
            }
        }
        return $data;
    }

    public function search_box($text, $input_id) {
        if (empty($_REQUEST['s']) && !$this->has_items()) {
            return;
        }
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '"/>';
        parent::search_box($text, $input_id);
        echo '</form>';
    }

    protected function column_page_title($item) {
        $post_id = $item['ID'];
        $post_status = get_post_status($post_id);
    
        // Generate edit, trash, restore, and delete links with security nonces
        $edit_link = admin_url(sprintf('post.php?post=%s&action=edit', $post_id));

        // Ensure the base URL is correct and add the nonce.
        $base_url = admin_url('admin.php');
        $trash_link = wp_nonce_url("$base_url?page=dynamic-pages-view-pages&action=trash&post=$post_id", 'trash-post_' . $post_id);
        $restore_link = wp_nonce_url("$base_url?page=dynamic-pages-view-pages&action=restore&post=$post_id", 'restore-post_' . $post_id);
        $delete_link = wp_nonce_url("$base_url?page=dynamic-pages-view-pages&action=delete&post=$post_id", 'delete-post_' . $post_id);


    
        // Determine the correct action link based on the post status
        if ($post_status === 'publish') {
            $view_preview_link = sprintf('<a href="%s" target="_blank">View</a>', get_permalink($post_id));
        } elseif (in_array($post_status, ['draft', 'pending', 'auto-draft'])) {
            $view_preview_link = sprintf('<a href="%s" target="_blank">Preview</a>', get_preview_post_link($post_id));
        } else {
            $view_preview_link = ''; // No link if the status is not one that allows viewing or previewing
        }
    
        // Set up action array
        $actions = [
            'edit' => '<a href="' . esc_url($edit_link) . '">Edit</a>',
            'quickedit' => '<a href="#" class="quickedit-action" data-id="' . $post_id . '">Quick Edit</a>',
            'view_preview' => $view_preview_link
        ];
    
        if ($post_status !== 'trash') {
            $actions['trash'] = '<a href="' . esc_url($trash_link) . '">Trash</a>';
        } else {
            $actions = [
                'restore' => '<a href="' . esc_url($restore_link) . '">Restore</a>',
                'delete' => '<a href="' . esc_url($delete_link) . '">Delete Permanently</a>'
            ];
        }
    
        return sprintf('%1$s %2$s', $item['page_title'], $this->row_actions($actions));
    }
    
    public function single_row($item) {
        echo '<tr id="post-row-' . $item['ID'] . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    
        // Add Quick Edit form here with nonce
        echo '<tr class="quick-edit-row" id="quick-edit-' . $item['ID'] . '" style="display: none;">';
        echo '<td colspan="3">';  // Adjust colspan as per your table structure
        echo '<div><label>Title:</label><input type="text" name="title" value="' . esc_attr(strip_tags($item['page_title'])) . '" /></div>';
        echo '<div><label>Slug:</label><input type="text" name="slug" value="' . esc_attr($item['slug']) . '" /></div>';
        // Include a nonce field for security
        wp_nonce_field('quick_edit_action', 'quick_edit_nonce');
        echo '<button class="button button-primary save-quick-edit" data-id="' . $item['ID'] . '">Update</button>';
        echo '<button class="button cancel-quick-edit" data-id="' . $item['ID'] . '">Cancel</button>';
        echo '</td>';
        echo '</tr>';
    }           
    
    public function column_default($item, $column_name) {
        return $item[$column_name];
    }

    protected function get_sortable_columns() {
        $sortable_columns = array(
            'page_title' => array('page_title', false),  // False indicates the initial sort direction is not ascending
            'slug'       => array('slug', false),
            'date' => array('date', false)
        );
        return $sortable_columns;
    }

    private function get_hidden_columns() {
        return array();
    }

}
