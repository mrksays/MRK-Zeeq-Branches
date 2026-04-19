<?php
/**
 * Plugin Name: MRK Zeeq Branches
 * Description: A WordPress plugin for filtering branches by city. Use shortcode anywhere in editor [zeeq_branches]
 * Version: 1.0
 * Author: Muhammad Rameez Khalid
 * Author URI: https://github.com/MuhammadRameezKhalid
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mrk-zeeq-branches
 */

// Enqueue custom styles
function zeeq_enqueue_styles() {
    wp_enqueue_style('zeeq-styles', plugin_dir_url(__FILE__) . 'css/style.css', array(), '1.0', 'all');
}
add_action('wp_enqueue_scripts', 'zeeq_enqueue_styles');

// Register custom post type for branches
function zeeq_custom_post_type() {
    $labels = array(
        'name'               => __('Zeeq Branches'),
        'singular_name'      => __('Zeeq Branch'),
        'add_new'            => __('Add New Branch'),
        'add_new_item'       => __('Add New Branch'),
        'edit_item'          => __('Edit Branch'),
        'new_item'           => __('New Branch'),
        'view_item'          => __('View Branch'),
        'view_items'         => __('View Branches'),
        'search_items'       => __('Search Branches'),
        'not_found'          => __('No branches found'),
        'not_found_in_trash' => __('No branches found in trash'),
        'parent_item_colon'  => '',
    );

    $args = array(
        'labels'      => $labels,
        'public'      => true,
        'has_archive' => true,
        'rewrite'     => array('slug' => 'zeeq-branches'),
        'supports'    => array('title', 'thumbnail'),
    );

    register_post_type('zeeq_branches', $args);
}
add_action('init', 'zeeq_custom_post_type');

// Add custom meta box for branch information
function zeeq_add_custom_fields() {
    add_meta_box('zeeq_branch_info', 'Branch Information', 'zeeq_branch_info_callback', 'zeeq_branches', 'normal', 'default');
}
add_action('add_meta_boxes', 'zeeq_add_custom_fields');

function zeeq_branch_info_callback($post) {
    $manager_name   = get_post_meta($post->ID, '_manager_name', true);
    $branch_address = get_post_meta($post->ID, '_branch_address', true);
    $phone_number   = get_post_meta($post->ID, '_phone_number', true);
    $location_link  = get_post_meta($post->ID, '_location_link', true);
    $city           = get_post_meta($post->ID, '_branch_city', true);

    wp_nonce_field('zeeq_save_branch_info', 'zeeq_branch_info_nonce');

    echo '<label for="manager_name" style="margin-right:20px;">Branch Manager Name</label>';
    echo '<input style="width:100%;" type="text" id="manager_name" name="manager_name" value="' . esc_attr($manager_name) . '" /><br>';

    echo '<label for="branch_address" style="margin-right:20px;">Branch Address</label>';
    echo '<input style="width:100%;" type="text" id="branch_address" name="branch_address" value="' . esc_attr($branch_address) . '" /><br>';

    echo '<label for="phone_number" style="margin-right:20px;">Phone Number</label>';
    echo '<input style="width:100%;" type="text" id="phone_number" name="phone_number" value="' . esc_attr($phone_number) . '" /><br>';

    echo '<label for="location_link" style="margin-right:20px;">Map Location Link</label>';
    echo '<input style="width:100%;" type="text" id="location_link" name="location_link" value="' . esc_attr($location_link) . '" /><br>';

    echo '<label for="branch_city" style="margin-right:20px;">City</label>';
    echo '<input style="width:100%;" type="text" id="branch_city" name="branch_city" value="' . esc_attr($city) . '" /><br>';
}

// Save custom field data
function zeeq_save_branch_info($post_id) {
    if (!isset($_POST['zeeq_branch_info_nonce']) || !wp_verify_nonce($_POST['zeeq_branch_info_nonce'], 'zeeq_save_branch_info')) {
        return $post_id;
    }

    $fields = array('manager_name', 'branch_address', 'phone_number', 'location_link');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }

    $city = isset($_POST['branch_city']) ? sanitize_text_field($_POST['branch_city']) : '';
    update_post_meta($post_id, '_branch_city', $city);
}
add_action('save_post', 'zeeq_save_branch_info');

// Shortcode: Display branches with city filter [zeeq_branches]
function zeeq_display_branches($atts) {
    $cities_query = new WP_Query(array(
        'post_type'      => 'zeeq_branches',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => '_branch_city',
        'fields'         => 'ids',
    ));

    $unique_cities = array();
    foreach ($cities_query->posts as $post_id) {
        $city = get_post_meta($post_id, '_branch_city', true);
        if (!empty($city)) {
            $unique_cities[] = $city;
        }
    }
    $unique_cities = array_unique($unique_cities);

    $items_per_page = 16;
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    echo '<div class="filter-main"><form action="" method="get" id="city-filter-form">';
    echo '<select name="city" id="city-filter">';
    echo '<option value="">All Cities</option>';
    foreach ($unique_cities as $city_name) {
        $selected = isset($_GET['city']) && $_GET['city'] === $city_name ? 'selected' : '';
        echo '<option value="' . esc_attr($city_name) . '" ' . $selected . '>' . esc_html($city_name) . '</option>';
    }
    echo '</select>';
    echo '</form></div><br>';

    echo '<script>
        document.getElementById("city-filter").addEventListener("change", function() {
            document.getElementById("city-filter-form").submit();
        });
    </script>';

    $args = array(
        'post_type'      => 'zeeq_branches',
        'posts_per_page' => $items_per_page,
        'paged'          => $paged,
        'post_status'    => 'publish',
    );

    if (isset($_GET['city']) && !empty($_GET['city'])) {
        $args['meta_query'] = array(
            array(
                'key'     => '_branch_city',
                'value'   => sanitize_text_field($_GET['city']),
                'compare' => '=',
            ),
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="branch-grid">';

        while ($query->have_posts()) {
            $query->the_post();

            $manager_name   = get_post_meta(get_the_ID(), '_manager_name', true);
            $branch_address = get_post_meta(get_the_ID(), '_branch_address', true);
            $phone_number   = get_post_meta(get_the_ID(), '_phone_number', true);
            $location_link  = get_post_meta(get_the_ID(), '_location_link', true);
            $branch_image   = get_the_post_thumbnail_url(get_the_ID(), 'full');

            echo '<div class="branch">';
            echo '<div class="branch-image">';
            echo '<img src="' . esc_url($branch_image) . '" alt="' . esc_attr(get_the_title()) . '" class="branch-thumbnail" onclick="openPopup(\'' . esc_js($branch_image) . '\')">';
            echo '</div>';
            echo '<h2>' . esc_html(get_the_title()) . '</h2>';
            echo '<p>Branch Manager: <b>' . esc_html($manager_name) . '</b></p>';
            echo '<p>Address: ' . esc_html($branch_address) . '</p>';
            echo '<p><b>' . esc_html($phone_number) . '</b></p>';
            echo '<a href="' . esc_url($location_link) . '" target="_blank" class="branch-btn">View on Map</a>';
            echo '</div>';
        }

        echo '</div>';

        echo '<div class="pagination">';
        echo paginate_links(array(
            'total'   => $query->max_num_pages,
            'current' => max(1, $paged),
        ));
        echo '</div>';

        wp_reset_postdata();
    } else {
        echo 'No branches found.';
    }
    ?>
    <script>
    function openPopup(imageUrl) {
        var popupWindow = window.open(imageUrl, 'popupWindow', 'width=800,height=600');
        popupWindow.focus();
    }
    </script>
    <?php
}
add_shortcode('zeeq_branches', 'zeeq_display_branches');

// Update admin labels
function zeeq_change_post_object_label() {
    global $wp_post_types;
    $labels = &$wp_post_types['zeeq_branches']->labels;
    $labels->name              = 'Zeeq Branches';
    $labels->singular_name     = 'Zeeq Branch';
    $labels->add_new           = 'Add New Branch';
    $labels->add_new_item      = 'Add New Branch';
    $labels->edit_item         = 'Edit Branch';
    $labels->new_item          = 'New Branch';
    $labels->view_item         = 'View Branch';
    $labels->search_items      = 'Search Branches';
    $labels->not_found         = 'No branches found';
    $labels->not_found_in_trash = 'No branches found in trash';
    $labels->all_items         = 'All Branches';
    $labels->menu_name         = 'Zeeq Branches';
    $labels->name_admin_bar    = 'Zeeq Branch';
}
add_action('init', 'zeeq_change_post_object_label');
