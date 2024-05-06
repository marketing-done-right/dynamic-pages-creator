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
            'seo_template' => 'SEO Template',
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
                $seo_template = get_post_meta($id, '_dpc_seo_override', true); 
                $data[] = array(
                    'ID'          => $post->ID,
                    'page_title'  => esc_html(get_the_title($id)),
                    'slug'       => esc_html(get_post_field('post_name', $id)),
                    'date'       => $formatted_date,
                    'parent'      => $post->post_parent,
                    'status'      => $post->post_status,
                    'seo_template' => $seo_template
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

        // Title link
        $title_link = '<a class="row-title" href="' . esc_url(get_edit_post_link($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a>';

        // Append post state if necessary
        $post_state = '';
        if ($post_status === 'draft') {
            $post_state = ' <strong><span class="post-state" style="font-size:14px;">— Draft</span></strong>';
        }
    
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
    
        return sprintf('%1$s%2$s %3$s', $title_link, $post_state, $this->row_actions($actions));
    }

    protected function column_seo_template($item) {
        $seo_template = get_post_meta($item['ID'], '_dpc_seo_override', true);
        echo $seo_template === 'global' ? 'Global' : 'Default';
    }    
    
    public function single_row($item) {
        $seo_template = $item['seo_template']; // Fetch the SEO Template setting

        echo '<tr id="post-row-' . $item['ID'] . '">';
        $this->single_row_columns($item);
        echo '</tr>';

        // Prepare checked states for radio buttons based on the current SEO template setting
        $global_checked = $seo_template === 'global' ? ' checked' : '';
        $default_checked = $seo_template === 'default' ? ' checked' : '';
        if (empty($global_checked) && empty($default_checked)) {
            // Default to 'default' if no meta is set
            $default_checked = ' checked';
        }
    
        // Add Quick Edit form here with nonce
        echo '<tr class="inline-edit-row inline-edit-row-page quick-edit-row quick-edit-row-page inline-edit-page inline-editor quick-edit-row" id="quick-edit-' . $item['ID'] . '" style="display: none;">';
        echo '<td colspan="4">';  // Adjust colspan as per your table structure
        echo '
        <div class="inline-edit-wrapper" role="region" aria-labelledby="quick-edit-legend">
    <fieldset class="inline-edit-col-left">
      <legend class="inline-edit-legend">Quick Edit</legend>
      <div class="inline-edit-col">
        <label>
          <span class="title">Title</span>
          <span class="input-text-wrap">
            <input type="text" name="post_title" class="ptitle" value="' . esc_attr(strip_tags($item['page_title'])) . '">
          </span>
        </label>
        <label>
          <span class="title">Slug</span>
          <span class="input-text-wrap">
            <input type="text" name="post_name" value="' . esc_attr($item['slug']) . '" autocomplete="off" spellcheck="false">
          </span>
        </label>
        
        <br class="clear">
        
      </div>
    </fieldset>
    <fieldset class="inline-edit-col-right">
      <div class="inline-edit-col">
        <label>
          <span class="title">Parent</span>
          <select name="post_parent" id="post_parent">
          <option value="0"' . ($item['parent'] == 0 ? ' selected' : '') . '>Main Page (no parent)</option>';
            $pages = get_pages();
            foreach ($pages as $page) {
                $selected = ($item['parent'] == $page->ID) ? 'selected' : '';
                echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
            }
            echo '
          </select>
        </label>
        
        <div class="inline-edit-group wp-clearfix">
          <label class="inline-edit-status alignleft">
            <span class="title">Status</span>
            <select name="_status">';
                $statuses = get_post_statuses();
                foreach ($statuses as $status => $label) {
                    $selected = ($item['status'] == $status) ? 'selected' : '';  // Ensure you have 'status' in $item
                    echo '<option value="' . esc_attr($status) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                }
            echo '</select>
          </label>
        </div>
        <div class="inline-edit-group wp-clearfix">
            <label class="inline-edit-group">
                <span class="title">SEO</span>
                <div class="input-text-wrap">
                    <label for="seo_global"><input class="'. $global_checked .'" type="radio" name="seo_template" value="global" ' . $global_checked . '/> Global</label>
                    <label for="seo_default"><input class="'.  $default_checked .'" style="margin-left:15px;" type="radio" name="seo_template" value="default" ' . $default_checked . '/> Default</label>
                </div>
            </label>
        </div>
      </div>
    </fieldset>
    <div class="submit inline-edit-save">
      <input type="hidden" id="_inline_edit" name="_inline_edit" value="361eeddf9d">
      '. wp_nonce_field('quick_edit_action', 'quick_edit_nonce') .'
      <button class="button button-primary save save-quick-edit" data-id="' . $item['ID'] . '">Update</button>
      <button class="button cancel cancel-quick-edit" data-id="' . $item['ID'] . '">Cancel</button>
      <span class="spinner"></span>
      <input type="hidden" name="post_view" value="list">
      <input type="hidden" name="screen" value="edit-page">
      <div class="notice notice-error notice-alt inline hidden">
        <p class="error"></p>
      </div>
    </div>
  </div>
        ';
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
