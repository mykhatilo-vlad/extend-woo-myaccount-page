<?php
require_once dirname(__FILE__) . '/webspark-product.php';

class Webspark_My_Products_Table extends WP_Query
{

    function __construct()
    {
        parent::__construct([
            'post_type' => 'product',
            'post_status' => 'any',
            'posts_per_page' => 10,
            'paged' => $this->get_current_page(),
            'author' => get_current_user_id(),
            'fields' => 'ids',
        ]);

        if (!$this->have_posts() && $this->get_current_page() > 1) {
            wp_redirect(add_query_arg([], wc_get_account_endpoint_url('my-products')));
            exit;
        }
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
                    <td><a class="button primary"
                           href="<?php echo add_query_arg(['id' => $product_id], wc_get_page_permalink('myaccount') . 'add-product'); ?>"><?php esc_html_e('Edit', 'wpmyproductspark'); ?></a>
                    </td>
                    <td>
                        <?php $this->delete_form($product_id); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php $this->pagination(); ?>

        <?php
    }

    public function delete_form($product_id) { ?>
        <form action="" method="post" data-webspark-delete-form>
            <?php
            $action = 'webspark_delete_product';
            wp_nonce_field($action, "{$action}-nonce");
            ?>
            <button class="button" name="<?php echo $action; ?>">Delete</button>
            <input type="hidden" name="action" value="<?php echo $action; ?>" />
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        </form>
    <?php }


    function pagination($echo = true, $args = [])
    {
        $big = 999999999;
        $pagi_args = array(
            'base' => str_replace($big, '%#%', esc_url(explode('?', get_pagenum_link($big), 2)[0])),
            'format' => 'page/%#%',
            'prev_next' => true,
            'current' => max(1, $this->query_vars['paged']),
            'total' => $this->max_num_pages,
            'type' => 'array',
        );

        $args = !empty($_GET) ? array_merge($args, $_GET) : $args;
        if ($args) {
            foreach ($args as $key => $val) {
                $pagi_args['add_args'][$key] = $val;
            }
        }
        $links = paginate_links($pagi_args);
        $pagination = '';

        if ($links) {
            $pagination = "<ul class='webspark-pagination'>\n\t<li>";
            $pagination .= implode("</li>\n\t<li>", $links);
            $pagination .= "</li>\n</ul>\n";
        }

        if ($echo) {
            echo $pagination;
        } else {
            return $pagination;
        }
    }

    function get_current_page() {
        global $wp;

        $segments = explode('/', $wp->request);
        $paged_index = array_search('page', $segments);
        return ($paged_index !== false && isset($segments[$paged_index + 1])) ? intval($segments[$paged_index + 1]) : 1;
    }
}
