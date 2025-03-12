<?php

/**
 * Plugin Name:       WP My Product Webspark
 * Version:           0.1
 * Author:            Vlad Mykhatilo
 * Author URI:        https://www.linkedin.com/in/vlad-mykhatilo/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpmyproductwebspark
 */

if (!defined('ABSPATH')) exit;

require_once dirname(__FILE__) . '\wepspark-add-product-form.php';
require_once dirname(__FILE__) . '\webspark-my-products-table.php';
require_once dirname(__FILE__) . '\webspark-product.php';


class Webspark_My_Porduct
{
    function __construct()
    {
        add_action('init', [$this, 'register_new_item_endpoint']);

        add_filter('query_vars', [$this, 'new_item_query_vars']);
        add_filter('ajax_query_attachments_args', [$this, 'filter_media']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_filter('woocommerce_account_menu_items', [$this, 'add_more_menu_items'], 10, 2);

        add_action('woocommerce_account_add-product_endpoint', [$this, 'content_for_add_product_page']);
        add_action('woocommerce_account_my-products_endpoint', [$this, 'content_for_my_products_page']);

        add_filter('woocommerce_endpoint_add-product_title', [$this, 'title_for_add_product_page']);
        add_filter('woocommerce_endpoint_my-products_title', [$this, 'title_for_my_products_page']);

        add_action('template_redirect', [$this, 'save_product']);
    }

    function enqueue_scripts()
    {
        wp_enqueue_style(
            'webspark-frontend',
            plugins_url('/', __FILE__) . 'css/frontend.css',
            [],
            '1.0.0'
        );

        wp_enqueue_media();
        wp_enqueue_script(
            'webspark-frontend',
            plugins_url('/', __FILE__) . 'js/frontend.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    function add_more_menu_items($items, $endpoints)
    {
        $endpoints['add-product'] = 'add-product';
        $endpoints['my-products'] = 'my-products';

        $customer_logout = $items['customer-logout'];
        unset($items['customer-logout']);

        $items['add-product'] = __('Add Product', 'wpmyproductwebspark');
        $items['my-products'] = __('My Products', 'wpmyproductwebspark');
        $items['customer-logout'] = $customer_logout;

        return $items;
    }

    function register_new_item_endpoint()
    {
        add_rewrite_endpoint('add-product', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('my-products', EP_ROOT | EP_PAGES);
    }

    function new_item_query_vars($vars)
    {
        $vars[] = 'add-product';
        $vars[] = 'my-products';

        return $vars;
    }

    function filter_media($query)
    {
        if (! current_user_can('manage_options')) {
            $query['author'] = get_current_user_id();
        }

        return $query;
    }

    function content_for_add_product_page()
    {
        $product_id = $_GET['id'] ?? null;
        $form = new Webspark_Add_Product_Form( $product_id );
        $form->render_form();
    }

    function content_for_my_products_page()
    {
        $products = new Webspark_My_Products_Table();
        $products->render_table();
    }

    function title_for_add_product_page()
    {
        return __('Add Product', 'wpmyproductwebspark');
    }

    function title_for_my_products_page()
    {
        return __('My Products', 'wpmyproductwebspark');
    }

    function save_product()
    {
        $nonce_value = wc_get_var($_REQUEST['save-product-form-nonce'], wc_get_var($_REQUEST['_wpnonce'], ''));

        if (! wp_verify_nonce($nonce_value, 'save_product_form')) {
            return;
        }

        if (empty($_POST['action']) || 'save_product_form' !== $_POST['action']) {
            return;
        }

        wc_nocache_headers();

        $user_id = get_current_user_id();

        if ($user_id <= 0) {
            return;
        }

        $title = $_POST['product_title'] ?? '';
        $price = $_POST['product_price'] ?? '';
        $qty = $_POST['stock_qty'] ?? '';
        $desc = $_POST['product_description'] ?? '';
        $media = $_POST['media_ids'] ?? '';

        if (!$title && !$price && !$qty) {
            return;
        }

        $new_product = wp_insert_post([
            'post_status' => 'pending',
            'post_type' => 'product',
            'post_title' => $title,
            'post_content' => $desc,
        ]);

        if (is_wp_error($new_product)) {
            return;
        }

        $product = new Webspark_Product($new_product);
        $product->update_data([
            'price' => $price,
            'qty' => $qty,
            'media' => $media
        ]);
    }
}

new Webspark_My_Porduct;
