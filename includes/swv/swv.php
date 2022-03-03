<?php
/**
 * Schema-Woven Validation API
 */

require_once WPCF7_PLUGIN_DIR . '/includes/swv/schema-holder.php';


function wpcf7_swv_load_rules() {
	$rules = array(
		'required',
		'requiredfile',
		'email',
		'url',
		'tel',
		'number',
		'date',
		'file',
		'minlength',
		'maxlength',
		'minnumber',
		'maxnumber',
		'mindate',
		'maxdate',
		'maxfilesize',
	);

	foreach ( $rules as $rule ) {
		$file = sprintf( '%s.php', $rule );
		$path = path_join( WPCF7_PLUGIN_DIR . '/includes/swv/rules', $file );

		if ( file_exists( $path ) ) {
			include_once $path;
		}
	}
}


function wpcf7_swv_create_rule( $rule_name, $properties = '' ) {
	wpcf7_swv_load_rules();

	switch ( $rule_name ) {
		case 'required':
			return new WPCF7_SWV_RequiredRule( $properties );
		case 'requiredfile':
			return new WPCF7_SWV_RequiredFileRule( $properties );
		case 'email':
			return new WPCF7_SWV_EmailRule( $properties );
		case 'url':
			return new WPCF7_SWV_URLRule( $properties );
		case 'tel':
			return new WPCF7_SWV_TelRule( $properties );
		case 'number':
			return new WPCF7_SWV_NumberRule( $properties );
		case 'date':
			return new WPCF7_SWV_DateRule( $properties );
		case 'file':
			return new WPCF7_SWV_FileRule( $properties );
		case 'minlength':
			return new WPCF7_SWV_MinLengthRule( $properties );
		case 'maxlength':
			return new WPCF7_SWV_MaxLengthRule( $properties );
		case 'minnumber':
			return new WPCF7_SWV_MinNumberRule( $properties );
		case 'maxnumber':
			return new WPCF7_SWV_MaxNumberRule( $properties );
		case 'mindate':
			return new WPCF7_SWV_MinDateRule( $properties );
		case 'maxdate':
			return new WPCF7_SWV_MaxDateRule( $properties );
		case 'maxfilesize':
			return new WPCF7_SWV_MaxFileSizeRule( $properties );
	}
}


abstract class WPCF7_SWV_Rule {

	protected $properties = array();

	public function __construct( $properties = '' ) {
		$this->properties = wp_parse_args( $properties, array() );
	}

	public function match( $context ) {
		$field = $this->get_property( 'field' );

		if ( ! empty( $context['field'] ) ) {
			if ( $field and ! in_array( $field, (array) $context['field'], true ) ) {
				return false;
			}
		}

		return true;
	}

	public function validate( $context ) {
		return true;
	}

	public function to_array() {
		return (array) $this->properties;
	}

	public function get_property( $name ) {
		if ( isset( $this->properties[$name] ) ) {
			return $this->properties[$name];
		}
	}

}


abstract class WPCF7_SWV_CompositeRule extends WPCF7_SWV_Rule {

	protected $rules = array();

	public function add_rule( $rule ) {
		if ( $rule instanceof WPCF7_SWV_Rule ) {
			$this->rules[] = $rule;
		}
	}

	public function rules() {
		foreach ( $this->rules as $rule ) {
			yield $rule;
		}
	}

	public function match( $context ) {
		return true;
	}

	public function validate( $context ) {
		foreach ( $this->rules() as $rule ) {
			if ( $rule->match( $context ) ) {
				$result = $rule->validate( $context );

				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}
		}

		return true;
	}

	public function to_array() {
		$rules_arrays = array_map(
			function ( $rule ) {
				return $rule->to_array();
			},
			$this->rules
		);

		return array_merge(
			parent::to_array(),
			array(
				'rules' => $rules_arrays,
			)
		);
	}

}


class WPCF7_SWV_Schema extends WPCF7_SWV_CompositeRule {

	const version = 'CIOS v20220302';

	public function __construct( $properties = '' ) {
		$this->properties = wp_parse_args( $properties, array(
			'version' => self::version,
		) );
	}

}
