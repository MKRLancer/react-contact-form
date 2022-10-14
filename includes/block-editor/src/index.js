import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

window.wpcf7 = window.wpcf7 ?? {
	contactForms: [],
};

import icon from './icon';
import edit from './edit';
import transforms from './transforms';

registerBlockType( 'contact-form-7/contact-form-selector', {
	icon,

	transforms,

	edit,

	save: ( { attributes } ) => {
		let shortcode = `[contact-form-7]`;

		if ( attributes.id ) {
			shortcode = shortcode.replace( /\]$/,
				` id="${ attributes.id }"]`
			);
		}

		if ( attributes.title ) {
			shortcode = shortcode.replace( /\]$/,
				` title="${ attributes.title }"]`
			);
		}

		if ( attributes.htmlId ) {
			shortcode = shortcode.replace( /\]$/,
				` html_id="${ attributes.htmlId }"]`
			);
		}

		if ( attributes.htmlName ) {
			shortcode = shortcode.replace( /\]$/,
				` html_name="${ attributes.htmlName }"]`
			);
		}

		if ( attributes.htmlTitle ) {
			shortcode = shortcode.replace( /\]$/,
				` html_title="${ attributes.htmlTitle }"]`
			);
		}

		if ( attributes.htmlClass ) {
			shortcode = shortcode.replace( /\]$/,
				` html_class="${ attributes.htmlClass }"]`
			);
		}

		if ( 'raw_form' === attributes.output ) {
			shortcode = shortcode.replace( /\]$/,
				` output="${ attributes.output }"]`
			);
		}

		return(
			<div { ...useBlockProps.save() }>
				{ shortcode }
			</div>
		);
	},
} );
