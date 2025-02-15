<?php
/**
 * Plugin Name: INESPAY payment
 * Plugin URI: https://www.transferenciabancariapsd2.com/comercios/
 * Description: This plugin allows to accept transfer payments through the Online banking.
 * Version: 1.5
 * Author: INESPAY
 * Author URI: https://www.inespay.com
 * License: GPLv2 or later
 *
 * Text Domain: inespay
 * Domain Path: /languages/
 */

// Your plugin code goes here

add_action('plugins_loaded', 'woocommerce_myplugin', 0);
function woocommerce_myplugin()
{
    if (!class_exists('WC_Payment_Gateway')){
        return; // if the WC payment gateway class
    }


    include(plugin_dir_path(__FILE__) . 'inespay.php');
}


add_filter('woocommerce_payment_gateways', 'add_my_custom_gateway');

function add_my_custom_gateway($gateways)
{
    $gateways[] = 'inespay';
    return $gateways;
}

/**
 * Custom function to declare compatibility with cart_checkout_blocks feature
 */
function declare_cart_checkout_blocks_compatibility()
{
    // Check if the required class exists
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Declare compatibility for 'cart_checkout_blocks'
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
}

// Hook the custom function to the 'before_woocommerce_init' action
add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');

// Hook the custom function to the 'woocommerce_blocks_loaded' action
add_action('woocommerce_blocks_loaded', 'oawoo_register_order_approval_payment_method_type');

/**
 * Custom function to register a payment method type
 */
function oawoo_register_order_approval_payment_method_type()
{
    // Check if the required class exists
    if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }

    // Include the custom Blocks Checkout class
    require_once plugin_dir_path(__FILE__) . 'inespay-block.php';

    // Hook the registration function to the 'woocommerce_blocks_payment_method_type_registration' action
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            // Register an instance of My_Custom_Gateway_Blocks
            $payment_method_registry->register(new Inespay_block);
        }
    );
}

?>