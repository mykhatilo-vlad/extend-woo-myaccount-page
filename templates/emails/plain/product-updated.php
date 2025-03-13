<?php
/**
 * Product Updated email template (plain text)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$product_post = get_post($product->get_id());
$user_edit_link = add_query_arg( 'user_id', $product_post->post_author, self_admin_url( 'user-edit.php' ) );

echo $email_heading . "\n\n";

echo sprintf( __( 'Product: %s', 'woocommerce' ), $product->get_title() ) . "\n\n";
echo sprintf( __( 'See the Author: %s', 'woocommerce' ), $user_edit_link ) . "\n\n";
echo sprintf( __( 'See the Product: %s', 'woocommerce' ), get_edit_post_link($product->get_id()) ) . "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
