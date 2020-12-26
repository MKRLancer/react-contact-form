import apiFetch from '@wordpress/api-fetch';

import { clearResponse } from './submit';
import { initSubmitButton } from './acceptance';
import { initCharacterCount } from './character-count';

export default function reset( form ) {
	const formData = new FormData( form );

	const detail = {
		contactFormId: form.wpcf7.id,
		pluginVersion: form.wpcf7.pluginVersion,
		contactFormLocale: form.wpcf7.locale,
		unitTag: form.wpcf7.unitTag,
		containerPostId: form.wpcf7.containerPost,
		status: form.wpcf7.status,
		inputs: Array.from(
			formData,
			val => {
				const name = val[0], value = val[1];
				return name.match( /^_/ ) ? false : { name, value };
			}
		).filter( val => false !== val ),
		formData,
	};

	apiFetch( {
		path: `contact-form-7/v1/contact-forms/${ form.wpcf7.id }/refill`,
		method: 'GET',
		wpcf7: {
			endpoint: 'refill',
			form,
			detail,
		},
	} ).then( response => {

		wpcf7.setStatus( form, 'init' );

		detail.apiResponse = response;

		wpcf7.triggerEvent( form.wpcf7.parent, 'refill', detail );

		return response;

	} ).then( response => {

		initSubmitButton( form );
		initCharacterCount( form );

	} ).catch( error => console.error( error ) );
}

apiFetch.use( ( options, next ) => {
	if ( options.wpcf7 && 'refill' === options.wpcf7.endpoint ) {
		const { form, detail } = options.wpcf7;

		clearResponse( form );
		wpcf7.setStatus( form, 'resetting' );
	}

	return next( options );
} );

// Refill for Really Simple CAPTCHA
export const refillCaptcha = ( form, refill ) => {
	for ( const name in refill ) {
		const url = refill[ name ];

		form.querySelectorAll( `input[name="${ name }"]` ).forEach( input => {
			input.value = '';
		} );

		form.querySelectorAll( `img.wpcf7-captcha-${ name }` ).forEach( img => {
			img.setAttribute( 'src', url );
		} );

		const match = /([0-9]+)\.(png|gif|jpeg)$/.exec( url );

		if ( match ) {
			form.querySelectorAll(
				`input[name="_wpcf7_captcha_challenge_${ name }"]`
			).forEach( input => {
				input.value = match[ 1 ];
			} );
		}
	}
};

// Refill for quiz fields
export const refillQuiz = ( form, refill ) => {
	for ( const name in refill ) {
		const question = refill[ name ][ 0 ];
		const hashedAnswer = refill[ name ][ 1 ];

		form.querySelectorAll(
			`.wpcf7-form-control-wrap.${ name }`
		).forEach( wrap => {
			wrap.querySelector( `input[name="${ name }"]` ).value = '';
			wrap.querySelector( '.wpcf7-quiz-label' ).textContent = question;

			wrap.querySelector(
				`input[name="_wpcf7_quiz_answer_${ name }"]`
			).value = hashedAnswer;
		} );
	}
};
