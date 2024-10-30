<?php

add_action('woocommerce_product_write_panel_tabs', 'c4d_woo_vs_tab_title');
add_action('woocommerce_product_data_panels', 'c4d_woo_vs_tab_panel');
add_action('woocommerce_admin_process_product_object', 'c4d_woo_vs_save_data');

function c4d_woo_vs_value($data, $default = '', $datas = array()) {
	if (isset($datas[$data])) {
		return $datas[$data];
	} else {
		return $default;
	}
}

function c4d_woo_vs_tab_title() {
	echo '<li class="c4d-woo-bundle-tab-title show_if_simple show_if_variable show_if_grouped show_if_external">
				<a href="#c4d-woo-vs-tab-panel"><span>'.esc_html__('Variation Swatches', 'c4d-woo-vs').'</span></a>
			</li>';
}

function c4d_woo_vs_tab_panel() {
	include_once (dirname(__FILE__). '/tab-panel.php');
}

function c4d_woo_vs_save_data($product) {
	update_post_meta( $product->get_id(), 'c4d_woo_vs', $_POST['c4d_woo_vs'] );
}