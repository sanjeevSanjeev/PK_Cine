<?php

/**
 * Plugin Name: Wedding Portfolio Manager
 * Description: A premium wedding portfolio manager for photographers and videographers to showcase wedding stories, films, and galleries.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wedding-portfolio-manager
 */

if (!defined('ABSPATH')) exit;

add_filter('block_categories_all', function($categories) {
    $categories[] = ['slug' => 'wedding-portfolio', 'title' => 'Wedding Portfolio'];
    return $categories;
});

add_filter('block_categories', function($categories) {
    $categories[] = ['slug' => 'wedding-portfolio', 'title' => 'Wedding Portfolio'];
    return $categories;
});

add_action('init', function() {
    wp_register_script(
        'wpm-block-editor',
        WPM_PLUGIN_URL . 'includes/assets/editor.js',
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor'],
        '1.0',
        true
    );
});

function wpm_register_blocks() {
    $blocks = [
        'cinematic-videos' => [
            'label'      => 'Cinematic Videos',
            'icon'       => 'video-alt3',
            'media_type' => 'video',
            'description' => 'Display all cinematic videos in a grid.',
        ],
        'vertical-shorts' => [
            'label'      => 'Vertical Shorts',
            'icon'       => 'smartphone',
            'media_type' => 'vertical',
            'description' => 'Display all vertical reels/shorts.',
        ],
        'photo-galleries' => [
            'label'      => 'Photo Galleries',
            'icon'       => 'camera',
            'media_type' => 'gallery',
            'description' => 'Display all photo galleries.',
        ],
        'portfolio-grid' => [
            'label'      => 'Portfolio Grid',
            'icon'       => 'grid-view',
            'media_type' => '',
            'description' => 'Display all portfolios in a grid.',
        ],
    ];

    foreach ($blocks as $slug => $config) {
        register_block_type("wpm/{$slug}", [
            'api_version'     => 3,
            'title'           => $config['label'],
            'category'        => 'wedding-portfolio',
            'icon'            => $config['icon'],
            'description'     => $config['description'],
            'editor_script'   => 'wpm-block-editor',
            'supports'        => [
                'html'  => false,
                'align' => ['wide', 'full'],
            ],
            'attributes'      => [
                'columns'            => ['type' => 'number', 'default' => 3],
                'perPage'            => ['type' => 'number', 'default' => 12],
                'showFilters'        => ['type' => 'boolean', 'default' => true],
                'thumbnailAspect'    => ['type' => 'string', 'default' => '3:4'],
            ],
            'render_callback' => function($attributes) use ($config) {
                $cols    = $attributes['columns'] ?? 3;
                $per     = $attributes['perPage'] ?? 12;
                $filters = $attributes['showFilters'] ?? true;
                $aspect  = $attributes['thumbnailAspect'] ?? '3:4';
                return do_shortcode(
                    sprintf(
                        '[wedding_portfolio media_type="%s" columns="%d" per_page="%d" show_filters="%s" thumbnail_aspect_ratio="%s"]',
                        $config['media_type'],
                        $cols,
                        $per,
                        $filters ? 'yes' : 'no',
                        $aspect
                    )
                );
            },
        ]);
    }
}
add_action('init', 'wpm_register_blocks', 99);