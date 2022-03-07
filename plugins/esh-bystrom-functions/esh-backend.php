<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
/**
 * Class to handle backend functionality
 */


if (!class_exists('Esh_backend')) {
	class Esh_backend {
		public function __construct() {
			require_once 'helper-functions.php';
			add_action('woocommerce_product_data_panels', array($this, 'remove_fields_multi'), 1);
			add_action('woocommerce_process_product_meta', array($this, 'remove_save_multi'), 1);
			add_action('woocommerce_product_data_panels', array($this, 'esh_add_wholesale_product_data_fields_multi'));
			add_action('woocommerce_process_product_meta', array($this, 'esh_woo_wholesale_fields_save_multi'), 99);
		}
		//action/filter removal functions
		public function remove_fields_multi() {
			remove_class_action('woocommerce_product_data_panels', '', 'wwp_add_wholesale_product_data_fields_multi', 10);
		}

		public function remove_save_multi() {
			remove_class_action('woocommerce_process_product_meta', '', 'wwp_woo_wholesale_fields_save_multi', 99);
		}



		public function esh_woo_wholesale_fields_save_multi($post_id) {
			if (!isset($_POST['wwp_product_wholesale_nonce']) || !wp_verify_nonce(wc_clean($_POST['wwp_product_wholesale_nonce']), 'wwp_product_wholesale_nonce')) {
				return;
			}
			// hide product for customer
			$_wwp_hide_for_customer = isset($_POST['_wwp_hide_for_customer']) ? wc_clean($_POST['_wwp_hide_for_customer']) : '';
			update_post_meta($post_id, '_wwp_hide_for_customer', esc_attr($_wwp_hide_for_customer));

			// hide product for visitor
			$_wwp_hide_for_visitor = isset($_POST['_wwp_hide_for_visitor']) ? wc_clean($_POST['_wwp_hide_for_visitor']) : '';
			update_post_meta($post_id, '_wwp_hide_for_visitor', esc_attr($_wwp_hide_for_visitor));

			// hide price and add-to-cart button for customer
			$_esh_hide_price_for_customer = isset($_POST['_esh_hide_price_for_customer']) ? wc_clean($_POST['_esh_hide_price_for_customer']) : '';
			update_post_meta($post_id, '_esh_hide_price_for_customer', esc_attr($_esh_hide_price_for_customer));

			/*	if ($_wwp_hide_for_customer == null) {
				$_wwp_hide_for_customer = 'null';
			}
			if ($_wwp_hide_for_visitor == null) {
				$_wwp_hide_for_visitor = 'null';
			}
			if ($_esh_hide_price_for_customer == null) {
				$_esh_hide_price_for_customer = 'null';
			}
			update_post_meta($post_id, 'HEJHEJ', $_wwp_hide_for_customer.$_wwp_hide_for_visitor.$_esh_hide_price_for_customer);*/
		}

		public function esh_add_wholesale_product_data_fields_multi() {
			// version 1.3.0
			global $post;
			$product_id = $post->ID;
			$roles      = array();
			$taxroles   = get_terms(
				'wholesale_user_roles',
				array(
					'hide_empty' => false,
				)
			);
			if (!empty($taxroles)) {
				foreach ($taxroles as $key => $role) {
					$roles[$role->slug] = $role->name;
				}
			}
?>
			<div id="wwp_wholesale_product_data" class="panel woocommerce_options_panel">
				<?php
				wp_nonce_field('wwp_product_wholesale_nonce', 'wwp_product_wholesale_nonce');

				woocommerce_wp_checkbox(
					array(
						'id'            => '_wwp_hide_for_customer',
						'wrapper_class' => '_wwp_hide_for_customer',
						'label'         => esc_html__('Hide Product', 'woocommerce-wholesale-pricing'),
						'description'   => esc_html__('Hide this product from users having customer role', 'woocommerce-wholesale-pricing'),
					)
				);

				woocommerce_wp_checkbox(
					array(
						'id'            => '_wwp_hide_for_visitor',
						'wrapper_class' => '_wwp_hide_for_visitor',
						'label'         => esc_html__('Hide Product', 'woocommerce-wholesale-pricing'),
						'description'   => esc_html__('Hide this product from visitors', 'woocommerce-wholesale-pricing'),
					)
				);

				$value = get_post_meta($product_id, 'wholesale_product_visibility_multi', true);
				woocommerce_wp_select(
					array(
						'id'                => 'wholesale_product_visibility_multi[]',
						'label'             => esc_html__('Hide Product for Wholesaler Roles', 'woocommerce-wholesale-pricing'),
						'type'              => 'select',
						'class'             => 'wc-enhanced-select',
						'style'             => 'min-width: 50%;',
						'desc_tip'          => 'true',
						'description'       => esc_html__('Choose specific user roles to hide the product.', 'woocommerce-wholesale-pricing'),
						'options'           => $roles,
						'value'             => $value,
						'custom_attributes' => array(
							'multiple' => 'multiple',
						),
					)
				); // ends version 1.3.0
				woocommerce_wp_checkbox(
					array(
						'id'            => '_esh_hide_price_for_customer',
						'wrapper_class' => '_esh_hide_price_for_customer',
						'label'         => esc_html__('Hide price', 'bystrom-functions'),
						'description'   => esc_html__('Hide price for this product from non-wholesale customer roles.', 'bystrom-functions'),
					)
				);
				/*?>
				<div id="wwp_wholesale_product_data" class="panel woocommerce_options_panel">
					<div id="variable_product_options" class=" wc-metaboxes-wrapper" style="display: block;">
						<div id="variable_product_options_inner">
							<div id="message" class="inline notice woocommerce-message">
								<p><?php echo sprintf('%1$s <strong>%2$s</strong> %3$s', esc_html__('For', 'woocommerce-wholesale-pricing'), esc_html__('Multi-user wholesale roles', 'woocommerce-wholesale-pricing'), esc_html__('manage price from wholesale metabox', 'woocommerce-wholesale-pricing')); ?></p>
								<p><a class="button-primary" id="wholesale-pricing-pro-multiuser-move"><?php esc_html_e('Move', 'woocommerce-wholesale-pricing'); ?></a></p>
							</div>
						</div>
					</div>
				</div>*/?>
			</div>
<?php
		}
	}
	$esh_backend = new Esh_backend();
}
