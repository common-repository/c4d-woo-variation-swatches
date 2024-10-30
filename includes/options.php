<?php
$c4d_woo_vs_params = array(
    "c4d-woo-vs-global-zoom-text" => "+",
    "c4d-woo-vs-listing-display" => "1",
    "c4d-woo-vs-listing-flip-thumbnail" => "1",
    "c4d-woo-vs-listing-variation-align" => "align-left",
    "c4d-woo-vs-single-gallery-default" => "no",
    "c4d-woo-vs-single-gallery-replace" => "yes",
    "c4d-woo-vs-single-gallery-replace-class" => ".woocommerce-product-gallery",
    "c4d-woo-vs-single-sort-order" => "no",
    "c4d-woo-vs-single-variation-popup" => "no",
    "c4d-woo-vs-single-zoom-out-box" => "no",
    "c4d-woo-vs-single-nav-show" => "yes",
    "c4d-woo-vs-single-nav-direction" => "hoz",
    "c4d-woo-vs-single-nav-item-show" => "3",
    "c4d-woo-vs-multi-order-border-rounded" => "no",
);

if (!isset($c4d_plugin_manager)) {
  $c4d_plugin_manager = $c4d_woo_vs_params;
} else {
  $c4d_plugin_manager = array_merge($c4d_woo_vs_params, $c4d_plugin_manager);
}

function c4d_woo_vs_admin_notice__success() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <h3><?php _e( 'Thank you for using our!', 'sample-text-domain' ); ?></h3>

        </p>
    </div>
    <?php
}
//add_action( 'admin_notices', 'c4d_woo_vs_admin_notice__success' );
