// cf inc/button-aria-label.php

(function() {
    const { createHigherOrderComponent } = wp.compose;
    const { Fragment, createElement } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, TextControl } = wp.components;
    const { addFilter } = wp.hooks;

    // Add aria-label attribute to button block
    function addAriaLabelAttribute(settings, name) {
        if (name !== 'core/button') {
            return settings;
        }

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                ariaLabel: {
                    type: 'string',
                    default: ''
                }
            }
        };
    }

    addFilter(
        'blocks.registerBlockType',
        'button-aria-label/add-attribute',
        addAriaLabelAttribute
    );

    // Add control to sidebar
    const withAriaLabelControl = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            if (props.name !== 'core/button') {
                return createElement(BlockEdit, props);
            }

            const { attributes, setAttributes } = props;

            return createElement(
                Fragment,
                null,
                createElement(BlockEdit, props),
                createElement(
                    InspectorControls,
                    null,
                    createElement(
                        PanelBody,
                        { title: 'Accessibility', initialOpen: true },
                        createElement(TextControl, {
                            label: 'ARIA Label',
                            value: attributes.ariaLabel || '',
                            onChange: (value) => setAttributes({ ariaLabel: value }),
                            help: 'Optional. Provides an accessible label for screen readers.'
                        })
                    )
                )
            );
        };
    }, 'withAriaLabelControl');

    addFilter(
        'editor.BlockEdit',
        'button-aria-label/with-control',
        withAriaLabelControl
    );
})();