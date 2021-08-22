document.addEventListener( 'DOMContentLoaded', event => {

	if ( typeof window.wpcf7_stripe === 'undefined' ) {
		console.error( "window.wpcf7_stripe is not defined." );
		return;
	}

	if ( typeof window.Stripe !== 'function' ) {
		console.error( "window.Stripe is not defined." );
		return;
	}

	const stripe = Stripe( wpcf7_stripe.publishable_key );
	const elements = stripe.elements();

	document.addEventListener( 'wpcf7submit', event => {
		const unitTag = event.detail.unitTag;
		const form = document.querySelector( `#${ unitTag } form` );
		const errorId = `${ unitTag }-ve-stripe-card-element`;

		const wrap = form.querySelector( '.wpcf7-stripe .wpcf7-form-control-wrap' );
		const button1 = form.querySelector( '.wpcf7-stripe button.first' );
		const button2 = form.querySelector( '.wpcf7-stripe button.second' );

		const hiddenInput = form.querySelector(
			'[name="_wpcf7_stripe_payment_intent"]'
		);

		if ( ! hiddenInput ) {
			return;
		}

		hiddenInput.setAttribute( 'value', '' );

		const setScreenReaderValidationError = error => {
			const li = document.createElement( 'li' );

			li.setAttribute( 'id', errorId );
			li.insertAdjacentText( 'beforeend', error.message );

			form.closest( '.wpcf7' ).querySelector(
				'.screen-reader-response ul'
			).appendChild( li );
		};

		const setVisualValidationError = error => {
			const control = wrap.querySelector( '.wpcf7-form-control' );
			control.classList.add( 'wpcf7-not-valid' );
			control.setAttribute( 'aria-describedby', errorId );

			const tip = document.createElement( 'span' );
			tip.setAttribute( 'class', 'wpcf7-not-valid-tip' );
			tip.setAttribute( 'aria-hidden', 'true' );
			tip.insertAdjacentText( 'beforeend', error.message );
			wrap.appendChild( tip );

			wrap.querySelectorAll( '[aria-invalid]' ).forEach( elm => {
				elm.setAttribute( 'aria-invalid', 'true' );
			} );

			if ( control.closest( '.use-floating-validation-tip' ) ) {
				control.addEventListener( 'focus', event => {
					tip.setAttribute( 'style', 'display: none' );
				} );

				tip.addEventListener( 'mouseover', event => {
					tip.setAttribute( 'style', 'display: none' );
				} );
			}
		};

		const clearValidationErrors = () => {
			form.closest( '.wpcf7' ).querySelectorAll(
				`.screen-reader-response li#${ errorId }`
			).forEach( li => {
				li.remove();
			} );

			wrap.querySelectorAll( '.wpcf7-not-valid-tip' ).forEach( span => {
				span.remove();
			} );

			wrap.querySelectorAll( '[aria-invalid]' ).forEach( elm => {
				elm.setAttribute( 'aria-invalid', 'false' );
			} );

			wrap.querySelectorAll( '.wpcf7-form-control' ).forEach( control => {
				control.removeAttribute( 'aria-describedby' );
				control.classList.remove( 'wpcf7-not-valid' );
			} );
		};

		if ( 'payment_required' === event.detail.status ) {
			const paymentIntent = event.detail.apiResponse.stripe.payment_intent;

			if ( paymentIntent.id ) {
				hiddenInput.setAttribute( 'value', paymentIntent.id );
			}

			const card = elements.getElement( 'card' ) || elements.create( 'card' );

			card.mount( `#${ unitTag } .wpcf7-stripe .card-element` );
			card.clear();

			wrap.classList.remove( 'hidden' );
			button1.classList.add( 'hidden' );
			button2.classList.remove( 'hidden' );
			button2.disabled = true;

			card.addEventListener( 'change', event => {
				clearValidationErrors();

				if ( event.error ) {
					const error = {
						message: event.error.message,
					};

					setScreenReaderValidationError( error );
					setVisualValidationError( error );

					button2.disabled = true;
				} else {
					button2.disabled = false;
				}
			} );

			button2.addEventListener( 'click', event => {
				button2.disabled = true;
				form.classList.add( 'submitting' );

				stripe.confirmCardPayment( paymentIntent.client_secret, {
					payment_method: {
						card: card,
					}
				} ).then( result => {
					clearValidationErrors();
					form.classList.remove( 'submitting' );

					if ( result.error ) {
						const error = {
							message: result.error.message,
						};

						setScreenReaderValidationError( error );
						setVisualValidationError( error );
					} else if ( 'succeeded' === result.paymentIntent.status ) {
						wpcf7.submit( form );
					}
				} );
			} );
		} else {
			wrap.classList.add( 'hidden' );
			button1.classList.remove( 'hidden' );
			button2.classList.add( 'hidden' );
		}
	} );
} );
