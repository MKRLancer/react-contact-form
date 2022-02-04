<?php

class WPCF7_SWV_MinNumberRule extends WPCF7_SWV_Rule {

	const rule_name = 'minnumber';

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

		$threshold = $this->get_property( 'threshold' );

		if ( ! wpcf7_is_number( $threshold ) ) {
			return true;
		}

		foreach ( $input as $i ) {
			if ( wpcf7_is_number( $i ) and (float) $i < (float) $threshold ) {
				return new WP_Error( 'wpcf7_invalid_minnumber',
					$this->get_property( 'message' )
				);
			}
		}

		return true;
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
