<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              #
 * @since             1.0.0
 * @package           Multi_Purchase_Product
 *
 * @wordpress-plugin
 * Plugin Name:       Multi Purchase Product
 * Plugin URI:        #
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Mushinmao
 * Author URI:        #
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       multi-purchase-product
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

add_filter( 'woocommerce_product_data_tabs', 'add_multi_purchase_product_data_tab' , 99 , 1 );
function add_multi_purchase_product_data_tab( $product_data_tabs ) {
    $product_data_tabs['multi-product-tab'] = array(
        'label' => __( 'Multi purchase', 'my_text_domain' ),
        'target' => 'multi_purchase_product_data',
    );
    return $product_data_tabs;
}

add_action( 'woocommerce_product_data_panels', 'add_multi_purchase_product_data_fields' );
function add_multi_purchase_product_data_fields() {
    global $post;
    ?>

    <div id="multi_purchase_product_data" class="panel woocommerce_options_panel">
        <?php

        $value = get_post_meta($post->ID, '_purchase_qty', true) ?? '';
        woocommerce_wp_text_input( array(
            'id'            => '_multi_purchase_field',
            'wrapper_class' => 'multi-purchase',
            'label'         => __( 'Purchase quantity', 'my_text_domain' ),
            'description'   => __( 'Enter the number of items per add to cart', 'my_text_domain' ),
            'name'          => '_purchase_qty',
            'desc_tip'      => false,
            'value'         =>  $value,
        ) );
        ?>
    </div>
    <?php
}

add_action( 'woocommerce_process_product_meta', 'woocommerce_process_product_meta_fields_save' );
function woocommerce_process_product_meta_fields_save( $post_id ){
    $woo_checkbox = $_POST['_purchase_qty'] ?? '';
    update_post_meta( $post_id, '_purchase_qty', $woo_checkbox);
}

add_filter( 'woocommerce_quantity_input_args', 'multi_purchase_input_args', 10, 2 );
function multi_purchase_input_args( $args, $product ) {

    if( $product->get_meta('_purchase_qty') ){
        $args['step'] = $product->get_meta('_purchase_qty');
        $args['min_value'] = $product->get_meta('_purchase_qty');
    }

    return $args;
}

add_filter( 'woocommerce_add_to_cart_quantity','woocommerce_add_to_cart_quantity_callback', 10, 2 );
function woocommerce_add_to_cart_quantity_callback( $quantity, $product_id ) {
    $quantity = get_post_meta($product_id, '_purchase_qty', true);
    return $quantity;
}

add_filter( 'woocommerce_loop_add_to_cart_link', 'change_product_qty', 10, 2 );

function change_product_qty( $link, $product ){

    $quantity = get_post_meta($product->ID, '_purchase_qty', true);

    $link = sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>',
        esc_url( $product->add_to_cart_url() ),
        esc_attr( !empty( $quantity) ? $quantity : '1' ),
        esc_attr( $product->id ),
        esc_attr( $product->get_sku() ),
        implode(
            ' ',
            array_filter(
                array(
                    'button',
                    'product_type_' . $product->get_type(),
                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                    $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
                )
            )
        ),
        esc_html( $product->add_to_cart_text() )
    );
    return $link;

}