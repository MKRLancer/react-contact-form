<?php
/**
 * Schema-Woven Validation API
 */


function wpcf7_swv_generate_schema( WPCF7_ContactForm $contact_form ) {
	$schema = new WPCF7_SWV_Schema();

	$tags = $contact_form->scan_form_tags();

	do_action(
		'wpcf7_swv_pre_add_rules',
		$schema,
		$tags
	);

	foreach ( $tags as $tag ) {
		$type = $tag->type;

		do_action(
			"wpcf7_swv_add_rules_for_{$type}",
			$schema,
			$tag
		);
	}

	do_action(
		'wpcf7_swv_add_rules',
		$schema,
		$tags
	);

	return $schema;
}


add_action(
	'wpcf7_swv_pre_add_rules',
	'wpcf7_swv_add_common_rules',
	10, 2
);

function wpcf7_swv_add_common_rules( $schema, $tags ) {
	foreach ( $tags as $tag ) {

		if ( $tag->is_required() ) {
			$schema->add_rule( $tag->name, 'required', array(
				'message' => wpcf7_get_message( 'invalid_required' ),
			) );
		}
	}
}


add_filter(
	'wpcf7_validate',
	'wpcf7_swv_validate',
	10, 2
);

function wpcf7_swv_validate( $result, $tags ) {
	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission ) {
		return $result;
	}

	$contact_form = $submission->get_contact_form();
	$schema = $contact_form->get_schema();

	foreach ( $schema->validate( $_POST ) as $error ) {
		$result->invalidate( $error['field'], $error['message'] );
	}

	return $result;
}


class WPCF7_SWV_Schema {

	private $rules = array();

	public function __construct() {
	}

	public function add_rule( $field, $rule, $args = '' ) {
		$args = wp_parse_args( $args, array(
			'message' => __( "Invalid value.", 'contact-form-7' ),
		) );

		$this->rules[] = array(
			'field' => $field,
			'rule' => sanitize_key( $rule ),
		) + $args;
	}

	public function validate( $input ) {
		$invalid_fields = array();

		foreach ( $this->rules as $rule ) {
			if ( isset( $rule['field'] )
			and in_array( $rule['field'], $invalid_fields, true ) ) {
				continue;
			}

			if ( ! isset( $rule['rule'] ) ) {
				continue;
			}

			// Todo: Implement error creation
			$error = $rule;

			if ( ! empty( $error['field'] ) ) {
				$invalid_fields[] = $error['field'];
			}

			yield $error;
		}
	}

	public function to_array() {
		return array(
			'rules' => $this->rules,
		);
	}
}
