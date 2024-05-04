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
        usort($data, array(&$this, 'sort_data'));

        $perPage = 10;
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
            $data[] = array(
                'page_title' => '<a href="' . esc_url(get_edit_post_link($id)) . '">' . esc_html(get_the_title($id)) . '</a>',
                'slug'       => esc_html(get_post_field('post_name', $id)),
                'date'       => esc_html($info['date'])
            );
        }
        return $data;
    }

    public function column_default($item, $column_name) {
        return $item[$column_name];
    }

    protected function get_sortable_columns() {
        $sortable_columns = array(
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
