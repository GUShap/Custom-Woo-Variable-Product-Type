<?php

function register_custom_product_type()
{
    class WC_Product_Custom_Product extends WC_Product_Variable
    {

        public function __construct($product)
        {
            parent::__construct($product);
        }

        public function get_type()
        {
            return 'custom_product';
        }
    }
}
add_action('init', 'register_custom_product_type');

function custom_product_class($class_name, $product_type)
{
    if ($product_type == 'custom_product') {
        $class_name = 'WC_Product_Custom_Product';
    }
    return $class_name;
}
add_filter('woocommerce_product_class', 'custom_product_class', 10, 2);

function add_custom_product_type_selector($types)
{
    $types['custom_product'] = __('custom Product', 'woocommerce');
    return $types;
}

add_filter('product_type_selector', 'add_custom_product_type_selector');

function custom_product_add_to_cart_handler($handler, $adding_to_cart)
{
    if ($handler == 'custom_product') {
        $handler = 'variable';
    }
    return $handler;
}
add_filter('woocommerce_add_to_cart_handler', 'custom_product_add_to_cart_handler', 10, 2);

function add_custom_product_store_data($stores)
{
    $stores['product-custom_product'] = 'WC_Product_Variable_Data_Store_CPT';
    return $stores;
}
add_filter('woocommerce_data_stores', 'add_custom_product_store_data', 10, 1);
