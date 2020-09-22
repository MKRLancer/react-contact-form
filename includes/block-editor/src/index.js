import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

import edit from './edit';

registerBlockType( 'contact-form-7/contact-form-selector', {

	title: __( 'Contact Form 7', 'contact-form-7' ),

	description: __( "Insert a contact form you have created with Contact Form 7.", 'contact-form-7' ),

	icon: 'email',

	category: 'widgets',

	attributes: {
		id: {
			type: 'integer',
		},
		title: {
			type: 'string',
		},
	},

	edit,

	save: ( { attributes } ) => {
		return(
			<div>
				[contact-form-7 id="{ attributes.id }" title="{ attributes.title }"]
			</div>
		);
	},
} );
