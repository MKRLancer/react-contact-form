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

};


/**
 * Verifies URL fields have URL values.
 */
export const url = function ( formData ) {

};


/**
 * Verifies telephone number fields have telephone number values.
 */
export const tel = function ( formData ) {

};


/**
 * Verifies number fields have number values.
 */
export const number = function ( formData ) {

};


/**
 * Verifies date fields have date values.
 */
export const date = function ( formData ) {

};


/**
 * Verifies file fields have file values.
 */
export const file = function ( formData ) {

};


/**
 * Verifies string values are not shorter than threshold.
 */
export const minlength = function ( formData ) {

};


/**
 * Verifies string values are not longer than threshold.
 */
export const maxlength = function ( formData ) {

};


/**
 * Verifies numerical values are not smaller than threshold.
 */
export const minnumber = function ( formData ) {

};


/**
 * Verifies numerical values are not larger than threshold.
 */
export const maxnumber = function ( formData ) {

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
