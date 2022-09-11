import init from './init';
import submit from './submit';
import reset from './reset';
import validate from './validate';

document.addEventListener( 'DOMContentLoaded', event => {

	if ( typeof wpcf7 === 'undefined' ) {
		console.error( "wpcf7 is not defined." );
		return;
	}

	if ( typeof wpcf7.api === 'undefined' ) {
		console.error( "wpcf7.api is not defined." );
		return;
	}

	if ( typeof window.fetch !== 'function' ) {
		console.error( "Your browser does not support window.fetch()." );
		return;
	}

	if ( typeof window.FormData !== 'function' ) {
		console.error( "Your browser does not support window.FormData()." );
		return;
	}

	const forms = document.querySelectorAll( '.wpcf7 > form' );

	if ( typeof forms.forEach !== 'function' ) {
		console.error( "Your browser does not support NodeList.forEach()." );
		return;
	}

	wpcf7 = {
		init,
		submit,
		reset,
		validate,
		...( wpcf7 ?? {} ),
	};

	forms.forEach( form => {
		wpcf7.init( form );
		form.closest( '.wpcf7' ).classList.replace( 'no-js', 'js' );
	} );

} );
