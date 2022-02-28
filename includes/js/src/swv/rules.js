import { getFieldValues } from './utils';
import { ValidationError } from './error';


/**
 * Verifies required fields are filled in.
 */
export const required = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	if ( 0 === values.length ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies required file fields are filled in.
 */
export const requiredfile = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	if ( 0 === values.length ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies email fields have email values.
 */
export const email = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	// https://html.spec.whatwg.org/multipage/input.html#email-state-(type=email)
	const regExp = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;

	if ( ! values.every( text => regExp.test( text.trim() ) ) ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies URL fields have URL values.
 */
export const url = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	// https://html.spec.whatwg.org/multipage/input.html#url-state-(type=url)
	// Intentionally applying a loose validation
	// for consistency with implementation in current major browsers.
	const isAbsoluteUrl = text => {
		const found = text.trim().match(
			/^([a-z][a-z0-9.+-]*):([^\p{C}\p{Z}]+)$/iu
		);

		if ( ! found ) {
			return false;
		} else if ( /^(?:ftp|http|https|ws|wss)$/i.test( found[1] ) ) {
			return /^\/\/.+$/iu.test( found[2] );
		} else if ( /^file$/i.test( found[1] ) ) {
			return /^\/\/.+$/iu.test( found[2] );
		} else {
			return /^.+$/iu.test( found[2] );
		}
	};

	if ( ! values.every( isAbsoluteUrl ) ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies telephone number fields have telephone number values.
 */
export const tel = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	// https://html.spec.whatwg.org/multipage/input.html#telephone-state-(type=tel)
	const isTelephoneNumber = text => {
		text = text.trim();

		if ( /[\r\n\p{C}]/.test( text ) ) {
			return false;
		}

		return /[0-9]/.test( text );
	};

	if ( ! values.every( isTelephoneNumber ) ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies number fields have number values.
 */
export const number = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	// https://html.spec.whatwg.org/multipage/input.html#number-state-(type=number)
	const isValidFloatingPointNumber = text => {
		text = text.trim();

		if ( /^[-]?[0-9]+(?:[eE][+-]?[0-9]+)?$/.test( text ) ) {
			return true;
		}

		if ( /^[-]?(?:[0-9]+)?[.][0-9]+(?:[eE][+-]?[0-9]+)?$/.test( text ) ) {
			return true;
		}

		return false;
	};

	if ( ! values.every( isValidFloatingPointNumber ) ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies date fields have date values.
 */
export const date = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	// https://html.spec.whatwg.org/multipage/input.html#date-state-(type=date)
	const isValidDateString = text => {
		return /^[0-9]{4,}-[0-9]{2}-[0-9]{2}$/.test( text.trim() );
	};

	if ( ! values.every( isValidDateString ) ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies file fields have file values.
 */
export const file = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	const isAcceptableFile = file => {
		if ( file instanceof File ) {
			return this.accept?.some( fileType => {
				if ( /^\.[a-z0-9]+$/i.test( fileType ) ) {
					return file.name.toLowerCase().endsWith( fileType.toLowerCase() );
				}

				// Todo: MIME type check

				return false;
			} );
		}

		return false;
	};

	if ( ! values.every( isAcceptableFile ) ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies string values are not shorter than threshold.
 */
export const minlength = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	let totalLength = 0;

	values.forEach( val => {
		if ( 'string' === typeof val ) {
			totalLength += val.length;
		}
	} );

	if ( totalLength < parseInt( this.threshold ) ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies string values are not longer than threshold.
 */
export const maxlength = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	let totalLength = 0;

	values.forEach( val => {
		if ( 'string' === typeof val ) {
			totalLength += val.length;
		}
	} );

	if ( parseInt( this.threshold ) < totalLength ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies numerical values are not smaller than threshold.
 */
export const minnumber = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	const isAcceptableNumber = text => {
		if ( parseFloat( text ) < parseFloat( this.threshold ) ) {
			return false;
		}

		return true;
	};

	if ( ! values.every( isAcceptableNumber ) ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies numerical values are not larger than threshold.
 */
export const maxnumber = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	const isAcceptableNumber = text => {
		if ( parseFloat( this.threshold ) < parseFloat( text ) ) {
			return false;
		}

		return true;
	};

	if ( ! values.every( isAcceptableNumber ) ) {
		throw new ValidationError( this );
	}
};


/**
 * Verifies date values are not earlier than threshold.
 */
export const mindate = function ( formData ) {

};


/**
 * Verifies date values are not later than threshold.
 */
export const maxdate = function ( formData ) {

};


/**
 * Verifies file values are not larger in file size than threshold.
 */
export const maxfilesize = function ( formData ) {

};
