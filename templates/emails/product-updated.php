<?php
/**
 * Product Updated email template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
$product_post = get_post($product->get_id());
$user_edit_link = add_query_arg( 'user_id', $product_post->post_author, self_admin_url( 'user-edit.php' ) );
?>

    <p><?php _e('Product:', 'wpmyproductwebspark'); ?> <?php echo $product->get_title(); ?></p>
    <p><a href="<?php echo $user_edit_link; ?>" target="_blank"><?php _e('See the Author', 'wpmyproductwebspark'); ?></a></p>
    <p><a href="<?php echo get_edit_post_link($product->get_id()); ?>" target="_blank"><?php _e('See the Product', 'wpmyproductwebspark'); ?></a></p>

<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
