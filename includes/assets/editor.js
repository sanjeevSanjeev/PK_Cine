( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var __ = wp.i18n.__;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var RangeControl = wp.components.RangeControl;
    var ToggleControl = wp.components.ToggleControl;      // <-- added
    var SelectControl = wp.components.SelectControl;      // <-- added

    var blockConfigs = [
        {
            name: 'wpm/cinematic-videos',
            title: 'Cinematic Videos',
            icon: 'video-alt3',
            category: 'wedding-portfolio',
            description: 'Display all cinematic videos in a grid.',
            mediaType: 'video',
        },
        {
            name: 'wpm/vertical-shorts',
            title: 'Vertical Shorts',
            icon: 'smartphone',
            category: 'wedding-portfolio',
            description: 'Display all vertical reels/shorts.',
            mediaType: 'vertical',
        },
        {
            name: 'wpm/photo-galleries',
            title: 'Photo Galleries',
            icon: 'camera',
            category: 'wedding-portfolio',
            description: 'Display all photo galleries.',
            mediaType: 'gallery',
        },
        {
            name: 'wpm/portfolio-grid',
            title: 'Portfolio Grid',
            icon: 'grid-view',
            category: 'wedding-portfolio',
            description: 'Display all portfolios in a grid.',
            mediaType: '',
        }
    ];

    blockConfigs.forEach( function( config ) {
        registerBlockType( config.name, {
            apiVersion: 3,
            title: config.title,
            icon: config.icon,
            category: config.category,
            description: config.description,
            supports: {
                html: false,
                align: [ 'wide', 'full' ],
            },
            attributes: {
                columns: { type: 'number', default: 3 },
                perPage: { type: 'number', default: 12 },
                showFilters: { type: 'boolean', default: true },      // <-- new
                thumbnailAspect: { type: 'string', default: '3:4' },  // <-- new
            },
            edit: function( props ) {
                var attributes = props.attributes;
                var setAttributes = props.setAttributes;

                var inspector = el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: 'Display Settings', initialOpen: true },
                        el(
                            RangeControl,
                            {
                                label: 'Columns',
                                value: attributes.columns,
                                onChange: function( value ) {
                                    setAttributes( { columns: value } );
                                },
                                min: 1,
                                max: 4,
                                step: 1,
                            }
                        ),
                        el(
                            RangeControl,
                            {
                                label: 'Items Per Page',
                                value: attributes.perPage,
                                onChange: function( value ) {
                                    setAttributes( { perPage: value } );
                                },
                                min: 1,
                                max: 48,
                                step: 1,
                            }
                        ),
                        el(
                            ToggleControl,                          // <-- new control
                            {
                                label: 'Show Filters',
                                checked: attributes.showFilters,
                                onChange: function( value ) {
                                    setAttributes( { showFilters: value } );
                                },
                            }
                        ),
                        el(
                            SelectControl,                         // <-- new control
                            {
                                label: 'Thumbnail Aspect Ratio',
                                value: attributes.thumbnailAspect,
                                options: [
                                    { label: '3:4 (Portrait)', value: '3:4' },
                                    { label: '4:3 (Landscape)', value: '4:3' },
                                    { label: '16:9 (Widescreen)', value: '16:9' },
                                    { label: '1:1 (Square)', value: '1:1' },
                                ],
                                onChange: function( value ) {
                                    setAttributes( { thumbnailAspect: value } );
                                },
                            }
                        )
                    )
                );

                var preview = el(
                    'div',
                    {
                        className: 'wp-block-wpm-placeholder',
                        style: { padding: '20px', background: '#f1f1f1', borderRadius: '4px', textAlign: 'center' }
                    },
                    el( 'h3', {}, config.title ),
                    el( 'p', {}, config.description ),
                    el( 'p', { style: { fontSize: '12px', color: '#666' } },
                        'Columns: ' + attributes.columns +
                        ' | Per Page: ' + attributes.perPage +
                        ' | Filters: ' + (attributes.showFilters ? 'Yes' : 'No') +
                        ' | Aspect: ' + attributes.thumbnailAspect
                    )
                );

                return el( 'div', {}, inspector, preview );
            },
            save: function() {
                return null;
            }
        } );
    } );
} )( window.wp );