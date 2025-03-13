<?php
require_once dirname(__FILE__) . '/webspark-product.php';

class Webspark_My_Products_Table extends WP_Query
{

    function __construct()
    {
        parent::__construct([
            'post_type' => 'product',
            'post_status' => 'any',
            'posts_per_page' => 25,
            'paged' => get_query_var('paged') ?: 1,
            'author' => get_current_user_id(),
            'fields' => 'ids',
        ]);
    }

    public function render_table()
    { ?>

        <table class="webspark-products-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>QTY</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($this->posts as $product_id) : ?>
                    <?php 
                    $product = new Webspark_Product($product_id);
                    $product_data = $product->get_data_for_table();
                    ?>

                    <tr>
                        <td><?php echo $product_data['title']; ?></td>
                        <td><?php echo $product_data['qty']; ?></td>
                        <td><?php echo $product_data['price']; ?></td>
                        <td><?php echo $product_data['status']; ?></td>
                        <td><a class="button primary" href="<?php echo add_query_arg(['id' => $product_data['id']], wc_get_page_permalink('myaccount') . '/add-product'); ?>"><?php esc_html_e('Edit', 'wpmyproductspark'); ?></a></td>
                        <td><button class="button primary"><?php esc_html_e('Delete', 'myproductspark'); ?></button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

<?php
    }
}
