<?php
/**
 * Plugin Name: Wedding Portfolio Manager
 * Description: A premium wedding portfolio manager for photographers and videographers.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: wedding-portfolio-manager-6
 */

if (!defined('ABSPATH')) exit;

define('WPM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPM_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WPM_PLUGIN_DIR . 'includes/post-types.php';
require_once WPM_PLUGIN_DIR . 'includes/rest-api.php';
require_once WPM_PLUGIN_DIR . 'includes/admin-page.php';
require_once WPM_PLUGIN_DIR . 'includes/shortcode.php';
require_once WPM_PLUGIN_DIR . 'includes/blocks.php';

register_activation_hook(__FILE__, 'wpm_activate');
function wpm_activate() {
    wpm_register_post_types();
    wpm_register_taxonomies();
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'wpm_deactivate');
function wpm_deactivate() {
    flush_rewrite_rules();
}

// Ensure our frontend script loads as a module
add_filter('script_loader_tag', function($tag, $handle) {
    if ($handle === 'wpm-frontend-js') {
        // Replace any existing type attribute with module
        $tag = str_replace("type='text/javascript'", "type='module'", $tag);
        $tag = str_replace('type="text/javascript"', 'type="module"', $tag);
        // If there is no type attribute, add it
        if (strpos($tag, 'type=') === false) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
    }
    return $tag;
}, 10, 2);

// ==========================================
// Single Portfolio Template Override (with debug)
// ==========================================
// Inject React app into single portfolio content
add_filter('the_content', function($content) {
    if (is_singular('portfolio') && in_the_loop() && is_main_query() && !wp_is_json_request()) {
        // Enqueue assets
        wp_enqueue_style('wpm-frontend-css', WPM_PLUGIN_URL . 'includes/assets/App.bundle.css', [], '1.0');
        wp_enqueue_script('wpm-frontend-js', WPM_PLUGIN_URL . 'includes/assets/frontend.bundle.js', [], '1.0', true);
        
        global $post;
        $data = [
            'apiRoot'    => esc_url_raw(rest_url('wp/v2/portfolios')),
            'nonce'      => wp_create_nonce('wp_rest'),
            'atts'       => ['id' => (int) $post->ID],
            'taxonomies' => wpm_get_taxonomy_terms_shortcode(),
        ];
        
        // Pass data using wp_add_inline_script (safe, no echo)
        wp_add_inline_script('wpm-frontend-js', 'window.wpmFrontend = ' . wp_json_encode($data) . ';', 'before');
        
        // Return only the container
        return '<div id="wpm-frontend-root"></div>';
    }
    return $content;
}, 10, 1);

function wpm_insert_default_terms() {
    $taxonomies = [
        'portfolio_type' => [
            'Wedding Story',
            'Cinematic Film',
            'Highlight Film',
            'Instagram Reel',
            'Engagement Session',
            'Elopement',
            'Wedding Trailer',
        ],
        'wedding_type' => [
            'Traditional',
            'Destination',
            'Hindu',
            'Christian',
            'Muslim',
            'Multicultural / Fusion',
            'Civil / Non‑Religious',
            'Interfaith',
            'Spiritual',
            'Jewish',
        ],
        'style' => [
            'Editorial',
            'Fine Art',
            'Documentary',
            'Modern',
            'Vintage',
            'Rustic',
            'Bohemian',
            'Dramatic',
            'Natural / Lifestyle',
        ],
        'location' => [
            'Kathmandu',
            'Pokhara',
            'Bali',
            'Tuscany',
            'Amalfi Coast',
            'Toronto',
            'Ottawa',
            'Niagara‑on‑the‑Lake',
            'Muskoka',
            'Prince Edward County',
            'Blue Mountains',
            'Kingston',
            'Hamilton',
            'London (ON)',
            'Stratford',
            'Collingwood',
            'Algonquin Park',
            'Thousand Islands',
            'Georgian Bay',
            'Lake Superior',
            'Quebec City',
            'Vancouver',
            'Banff',
        ],
    ];

    foreach ($taxonomies as $tax => $terms) {
        foreach ($terms as $term_name) {
            if (!term_exists($term_name, $tax)) {
                wp_insert_term($term_name, $tax);
            }
        }
    }
}
add_action('init', 'wpm_insert_default_terms');

// Force ES module for our scripts
add_filter('script_loader_tag', function($tag, $handle) {
    $handles = ['wpm-admin-js', 'wpm-frontend-js'];
    if (in_array($handle, $handles)) {
        // Replace any existing type attribute with module
        $tag = str_replace("type='text/javascript'", "type='module'", $tag);
        $tag = str_replace('type="text/javascript"', 'type="module"', $tag);
        // If there is no type attribute, add it
        if (strpos($tag, 'type=') === false) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
    }
    return $tag;
}, 10, 2);