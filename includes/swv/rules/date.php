<?php

class WPCF7_SWV_DateRule extends WPCF7_SWV_Rule {

	const rule_name = 'date';

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
		$field = $this->get_property( 'field' );
		$input = isset( $_POST[$field] ) ? $_POST[$field] : '';
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		foreach ( $input as $i ) {
			if ( ! wpcf7_is_date( $i ) ) {
				if ( isset( $context['validity'] )
				and $context['validity'] instanceof WPCF7_Validation ) {
					$error = new WP_Error( 'wpcf7_invalid_date',
						$this->get_property( 'message' )
					);

					$context['validity']->invalidate( $field, $error );
				}

				return false;
			}
		}

		return true;
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
