<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Class to handle frontend functionality
 */

if (!class_exists('Esh_frontend')) {
    class Esh_frontend {
        public function __construct() {
            add_action('init', array($this, 'hide_price_and_button'), 9);
        }

        public function hide_price_and_button() {
            //Remove wholesale-for-woocommerce behaviour
            require_once 'helper-functions.php';
            remove_class_action('init', '', 'wwp_hide_price_add_cart_not_logged_in');

            //Remove woocommerce original behaviour
            /*add_action('woocommerce_after_shop_loop_item', array($this, 'remove_loop_item'), 9);
            add_action('woocommerce_single_product_summary', array($this, 'remove_single_button'), 9);
            add_action('woocommerce_single_product_summary', array($this, 'remove_single_price'), 9);*/
            remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);

            remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
            remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);

            //Add esh pricing
            add_action('woocommerce_after_shop_loop_item', array($this, 'esh_template_loop_add_to_cart'), 10);
            add_action('woocommerce_single_product_summary', array($this, 'esh_template_single_add_to_cart'), 10);
            add_action('woocommerce_single_product_summary', array($this, 'esh_template_single_price'), 10);
            add_action('woocommerce_after_shop_loop_item_title', array($this, 'esh_template_loop_price'), 10);
            add_action('woocommerce_single_variation', array($this, 'esh_woocommerce_get_variation_price_html'), 20);

            add_action('woocommerce_single_product_summary', array($this, 'esh_retail_prices'), 10);
            add_action('woocommerce_after_shop_loop_item', array($this, 'esh_retail_prices'), 11);
            add_filter('woocommerce_get_price_html', array($this, 'esh_woocommerce_get_price_html'), 10, 2);
            add_filter('woocommerce_is_purchasable', array($this, 'filter_woocommerce_is_purchasable'), 10, 2);
        }

        private function esh_is_price_visible_for_current_customer($current_product = null) {
            global $product;
            if (!is_object($current_product)) {
                $current_product = $product;
            }
            $price_hidden = get_post_meta($current_product->id, '_esh_hide_price_for_customer', true);
            if ($price_hidden == 'yes') {
                if (esh_is_current_user(array("administrator", "wholesaler"))) {
                    return true;
                }
                return false;
            }
            return true;
        }

        public function esh_template_loop_add_to_cart() {
            if ($this->esh_is_price_visible_for_current_customer()) {
                woocommerce_template_loop_add_to_cart();
            }
        }

        public function esh_template_single_add_to_cart() {
            global $product;
            if ($this->esh_is_price_visible_for_current_customer()) {
                woocommerce_template_single_add_to_cart();
            } else if ($product->get_type() == 'variable') {
                do_action('woocommerce_variable_add_to_cart');
            } //Else-if statetment because simple.php has a hard-coded button, as opposed to variable.php 
        }

        public function esh_template_single_price() {
            if ($this->esh_is_price_visible_for_current_customer()) {
                woocommerce_template_single_price();
            }
        }

        public function esh_template_loop_price() {
            if ($this->esh_is_price_visible_for_current_customer()) {
                woocommerce_template_loop_price();
            }
        }

        public function esh_woocommerce_get_variation_price_html() {
            if ($this->esh_is_price_visible_for_current_customer()) {
                woocommerce_single_variation_add_to_cart_button();
            }
        }

        public function filter_woocommerce_is_purchasable($this_exists_publish, $product) {
            return $this->esh_is_price_visible_for_current_customer($product);
        }

        public function esh_retail_prices() {
            $price_hidden = get_post_meta(get_the_ID(), '_esh_hide_price_for_customer', true);
            $settings = get_option('wwp_wholesale_pricing_options', true);

            if ($price_hidden == 'yes' && !$this->esh_is_price_visible_for_current_customer()) {
                if (isset($settings['display_link_text']) && !empty($settings['display_link_text'])) {
                    $link_text = $settings['display_link_text'];
                } else {
                    $link_text = 'Logga in för att se pris';
                }
                echo '<a class="login-to-upgrade" href="' . esc_url(get_permalink(wc_get_page_id('myaccount'))) . '">' . esc_html__($link_text, 'woocommerce-wholesale-pricing') . '</a>';
            }
        }

        public function esh_woocommerce_get_price_html($price, $product) {
            if ($this->esh_is_price_visible_for_current_customer($product)) {
                return $price;
            }
            return '';
        }
    }
    $esh_frontend = new Esh_frontend();
}
