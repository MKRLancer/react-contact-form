<?php

add_action( 'wpcf7_init', 'wpcf7_add_form_tag_reflection', 10, 0 );

function wpcf7_add_form_tag_reflection() {
	wpcf7_add_form_tag( 'reflection',
		'wpcf7_reflection_form_tag_handler',
		array(
			'name-attr' => true,
			'display-block' => true,
			'not-for-mail' => true,
		)
	);
}

function wpcf7_reflection_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$html = sprintf(
		'<fieldset %1$s></fieldset>',
		wpcf7_format_atts( array(
			'data-reflection-of' => $tag->name,
			'class' => $tag->get_class_option(
				wpcf7_form_controls_class( $tag->type )
			),
			'id' => $tag->get_id_option(),
		) )
	);

	return $html;
}
