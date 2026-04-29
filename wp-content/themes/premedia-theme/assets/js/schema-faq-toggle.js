const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;


// 1. Add the attribute to the block
addFilter(
    'blocks.registerBlockType',
    'myplugin/accordion-faq-attribute',
    ( settings, name ) => {
        if ( name !== 'core/accordion' ) return settings; // confirm actual block name

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                enableFaqSchema: {
                    type: 'boolean',
                    default: false,
                },
            },
        };
    }
);

// 2. Add the toggle to the block Inspector sidebar
const withFaqSchemaToggle = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        if ( props.name !== 'core/accordion' ) {
            return wp.element.createElement( BlockEdit, props );
        }

        const { attributes, setAttributes } = props;
        const { enableFaqSchema } = attributes;

        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement( BlockEdit, props ),
            wp.element.createElement(
                wp.blockEditor.InspectorControls,
                null,
                wp.element.createElement(
                    wp.components.PanelBody,
                    { title: 'FAQ Schema', initialOpen: true },
                    wp.element.createElement(
                        wp.components.ToggleControl,
                        {
                            label: 'Enable FAQ Schema Markup',
                            help: enableFaqSchema
                                ? 'JSON-LD FAQPage schema will be output with this block.'
                                : 'No schema markup will be added.',
                            checked: enableFaqSchema,
                            onChange: ( val ) => setAttributes({ enableFaqSchema: val }),
                        }
                    )
                )
            )
        );
    };
}, 'withFaqSchemaToggle' );


addFilter(
    'editor.BlockEdit',
    'myplugin/accordion-faq-toggle',
    withFaqSchemaToggle
);