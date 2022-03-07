<?php
defined( 'ABSPATH' ) || exit;

require_once 'helper-functions.php';

add_filter( 'woocommerce_price_filter_sql', 'esh_price_widget_filter', 10, 3 );
function esh_price_widget_filter( $sql, $meta_query_sql, $tax_query_sql ){
    if (!esh_is_current_user(array("administrator","wholesaler"))) {
        global $wpdb;

        $sql = $sql . " AND product_id NOT IN (
            SELECT post_id FROM {$wpdb->postmeta}
        WHERE {$wpdb->postmeta}.meta_key = '_esh_hide_price_for_customer' AND {$wpdb->postmeta}.meta_value='yes'
        )";    }
	return $sql;
}