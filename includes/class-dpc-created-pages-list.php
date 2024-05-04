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

    private function table_data() {
        $data = [];
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        foreach ($existing_pages_ids as $id => $info) {
            $formatted_date = date('Y/m/d \a\t g:i a', strtotime($info['date'])); 
            $data[] = array(
                'page_title' => '<a class="row-title" href="' . esc_url(get_edit_post_link($id)) . '">' . esc_html(get_the_title($id)) . '</a>',
                'slug'       => esc_html(get_post_field('post_name', $id)),
                'date'       => $formatted_date
            );
        }
        return $data;
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

    private function sort_data($a, $b) {
        // Set default to no sorting
        return 0;
    }
}
