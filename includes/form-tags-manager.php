<?php

/**
 * Wrapper function of WPCF7_FormTagsManager::add().
 */
function wpcf7_add_form_tag( $tag, $callback, $features = '' ) {
	$manager = WPCF7_FormTagsManager::get_instance();

	return $manager->add( $tag, $callback, $features );
}


/**
 * Wrapper function of WPCF7_FormTagsManager::remove().
 */
function wpcf7_remove_form_tag( $tag ) {
	$manager = WPCF7_FormTagsManager::get_instance();

	return $manager->remove( $tag );
}


/**
 * Wrapper function of WPCF7_FormTagsManager::replace_all().
 */
function wpcf7_replace_all_form_tags( $content ) {
	$manager = WPCF7_FormTagsManager::get_instance();

	return $manager->replace_all( $content );
}


/**
 * Wrapper function of WPCF7_ContactForm::scan_form_tags().
 */
function wpcf7_scan_form_tags( $cond = null ) {
	$contact_form = WPCF7_ContactForm::get_current();

	if ( $contact_form ) {
		return $contact_form->scan_form_tags( $cond );
	}

	return array();
}


/**
 * Wrapper function of WPCF7_FormTagsManager::tag_type_supports().
 */
function wpcf7_form_tag_supports( $tag, $feature ) {
	$manager = WPCF7_FormTagsManager::get_instance();

	return $manager->tag_type_supports( $tag, $feature );
}


/**
 * The singleton instance of this class manages the collection of form-tags.
 */
class WPCF7_FormTagsManager {

	private static $instance;

	private $tag_types = array();
	private $scanned_tags = null; // Tags scanned at the last time of scan()

	private function __construct() {}


	/**
	 * Returns the singleton instance.
	 *
	 * @return WPCF7_FormTagsManager The singleton manager.
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Returns scanned form-tags.
	 *
	 * @return array Array of WPCF7_FormTag objects.
	 */
	public function get_scanned_tags() {
		return $this->scanned_tags;
	}


	/**
	 * Registers form-tag types to the manager.
	 *
	 * @param string|array $tag_types The name of the form-tag type or
	 *                     an array of the names.
	 * @param callable $callback The callback to generates a form control HTML
	 *                 for a form-tag in this type.
	 * @param string|array $features Optional. Features a form-tag
	 *                     in this type supports.
	 */
	public function add( $tag_types, $callback, $features = '' ) {
		if ( ! is_callable( $callback ) ) {
			return;
		}

		if ( true === $features ) { // for back-compat
			$features = array( 'name-attr' => true );
		}

		$features = wp_parse_args( $features, array() );

		$tag_types = array_filter( array_unique( (array) $tag_types ) );

		foreach ( $tag_types as $tag_type ) {
			$tag_type = $this->sanitize_tag_type( $tag_type );

			if ( ! $this->tag_type_exists( $tag_type ) ) {
				$this->tag_types[$tag_type] = array(
					'function' => $callback,
					'features' => $features,
				);
			}
		}
	}

	public function tag_type_exists( $tag_type ) {
		return isset( $this->tag_types[$tag_type] );
	}

	public function tag_type_supports( $tag_type, $feature ) {
		$feature = array_filter( (array) $feature );

		if ( isset( $this->tag_types[$tag_type]['features'] ) ) {
			return (bool) array_intersect(
				array_keys( array_filter( $this->tag_types[$tag_type]['features'] ) ),
				$feature
			);
		}

		return false;
	}

	public function collect_tag_types( $feature = null, $invert = false ) {
		$tag_types = array_keys( $this->tag_types );

		if ( empty( $feature ) ) {
			return $tag_types;
		}

		$output = array();

		foreach ( $tag_types as $tag_type ) {
			if ( ! $invert && $this->tag_type_supports( $tag_type, $feature )
			|| $invert && ! $this->tag_type_supports( $tag_type, $feature ) ) {
				$output[] = $tag_type;
			}
		}

		return $output;
	}

	private function sanitize_tag_type( $tag_type ) {
		$tag_type = preg_replace( '/[^a-zA-Z0-9_*]+/', '_', $tag_type );
		$tag_type = rtrim( $tag_type, '_' );
		$tag_type = strtolower( $tag_type );
		return $tag_type;
	}

	public function remove( $tag_type ) {
		unset( $this->tag_types[$tag_type] );
	}

	public function normalize( $content ) {
		if ( empty( $this->tag_types ) ) {
			return $content;
		}

		$content = preg_replace_callback(
			'/' . $this->tag_regex() . '/s',
			array( $this, 'normalize_callback' ),
			$content
		);

		return $content;
	}

	private function normalize_callback( $matches ) {
		// allow [[foo]] syntax for escaping a tag
		if ( $matches[1] == '['
		and $matches[6] == ']' ) {
			return $matches[0];
		}

		$tag = $matches[2];

		$attr = trim( preg_replace( '/[\r\n\t ]+/', ' ', $matches[3] ) );
		$attr = strtr( $attr, array( '<' => '&lt;', '>' => '&gt;' ) );

		$content = trim( $matches[5] );
		$content = str_replace( "\n", '<WPPreserveNewline />', $content );

		$result = $matches[1] . '[' . $tag
			. ( $attr ? ' ' . $attr : '' )
			. ( $matches[4] ? ' ' . $matches[4] : '' )
			. ']'
			. ( $content ? $content . '[/' . $tag . ']' : '' )
			. $matches[6];

		return $result;
	}

	public function replace_all( $content ) {
		return $this->scan( $content, true );
	}

	public function scan( $content, $replace = false ) {
		$this->scanned_tags = array();

		if ( empty( $this->tag_types ) ) {
			if ( $replace ) {
				return $content;
			} else {
				return $this->scanned_tags;
			}
		}

		if ( $replace ) {
			$content = preg_replace_callback(
				'/' . $this->tag_regex() . '/s',
				array( $this, 'replace_callback' ),
				$content
			);

			return $content;
		} else {
			preg_replace_callback(
				'/' . $this->tag_regex() . '/s',
				array( $this, 'scan_callback' ),
				$content
			);

			return $this->scanned_tags;
		}
	}

	public function filter( $input, $cond ) {
		if ( is_array( $input ) ) {
			$tags = $input;
		} elseif ( is_string( $input ) ) {
			$tags = $this->scan( $input );
		} else {
			$tags = $this->scanned_tags;
		}

		$cond = wp_parse_args( $cond, array(
			'type' => array(),
			'basetype' => array(),
			'name' => array(),
			'feature' => array(),
		) );

		$cond = array_map( function ( $c ) {
			return array_filter( array_map( 'trim', (array) $c ) );
		}, $cond );

		$tags = array_filter(
			(array) $tags,
			function ( $tag ) use ( $cond ) {
				$tag = new WPCF7_FormTag( $tag );

				if ( $cond['type']
				and ! in_array( $tag->type, $cond['type'], true ) ) {
					return false;
				}

				if ( $cond['basetype']
				and ! in_array( $tag->basetype, $cond['basetype'], true ) ) {
					return false;
				}

				if ( $cond['name']
				and ! in_array( $tag->name, $cond['name'], true ) ) {
					return false;
				}

				foreach ( $cond['feature'] as $feature ) {
					if ( '!' === substr( $feature, 0, 1 ) ) { // Negation
						$feature = trim( substr( $feature, 1 ) );

						if ( $this->tag_type_supports( $tag->type, $feature ) ) {
							return false;
						}
					} else {
						if ( ! $this->tag_type_supports( $tag->type, $feature ) ) {
							return false;
						}
					}
				}

				return true;
			}
		);

		return array_values( $tags );
	}

	private function tag_regex() {
		$tagnames = array_keys( $this->tag_types );
		$tagregexp = implode( '|', array_map( 'preg_quote', $tagnames ) );

		return '(\[?)'
			. '\[(' . $tagregexp . ')(?:[\r\n\t ](.*?))?(?:[\r\n\t ](\/))?\]'
			. '(?:([^[]*?)\[\/\2\])?'
			. '(\]?)';
	}

	private function replace_callback( $matches ) {
		return $this->scan_callback( $matches, true );
	}

	private function scan_callback( $matches, $replace = false ) {
		// allow [[foo]] syntax for escaping a tag
		if ( $matches[1] == '['
		and $matches[6] == ']' ) {
			return substr( $matches[0], 1, -1 );
		}

		$tag_type = $matches[2];
		$attr = $this->parse_atts( $matches[3] );

		$scanned_tag = array(
			'type' => $tag_type,
			'basetype' => trim( $tag_type, '*' ),
			'raw_name' => '',
			'name' => '',
			'options' => array(),
			'raw_values' => array(),
			'values' => array(),
			'pipes' => null,
			'labels' => array(),
			'attr' => '',
			'content' => '',
		);

		if ( $this->tag_type_supports( $tag_type, 'singular' )
		and $this->filter( $this->scanned_tags, array( 'type' => $tag_type ) ) ) {
			// Another tag in the same type already exists. Ignore this one.
			return $matches[0];
		}

		if ( is_array( $attr ) ) {
			if ( is_array( $attr['options'] ) ) {
				if ( $this->tag_type_supports( $tag_type, 'name-attr' )
				and ! empty( $attr['options'] ) ) {
					$scanned_tag['raw_name'] = array_shift( $attr['options'] );

					if ( ! wpcf7_is_name( $scanned_tag['raw_name'] ) ) {
						return $matches[0]; // Invalid name is used. Ignore this tag.
					}

					$scanned_tag['name'] = strtr( $scanned_tag['raw_name'], '.', '_' );
				}

				$scanned_tag['options'] = (array) $attr['options'];
			}

			$scanned_tag['raw_values'] = (array) $attr['values'];

			if ( WPCF7_USE_PIPE ) {
				$pipes = new WPCF7_Pipes( $scanned_tag['raw_values'] );
				$scanned_tag['values'] = $pipes->collect_befores();
				$scanned_tag['pipes'] = $pipes;
			} else {
				$scanned_tag['values'] = $scanned_tag['raw_values'];
			}

			$scanned_tag['labels'] = $scanned_tag['values'];

		} else {
			$scanned_tag['attr'] = $attr;
		}

		$scanned_tag['values'] = array_map( 'trim', $scanned_tag['values'] );
		$scanned_tag['labels'] = array_map( 'trim', $scanned_tag['labels'] );

		$content = trim( $matches[5] );
		$content = preg_replace( "/<br[\r\n\t ]*\/?>$/m", '', $content );
		$scanned_tag['content'] = $content;

		$scanned_tag = apply_filters( 'wpcf7_form_tag', $scanned_tag, $replace );

		$scanned_tag = new WPCF7_FormTag( $scanned_tag );

		$this->scanned_tags[] = $scanned_tag;

		if ( $replace ) {
			$callback = $this->tag_types[$tag_type]['function'];
			return $matches[1] . call_user_func( $callback, $scanned_tag ) . $matches[6];
		} else {
			return $matches[0];
		}
	}

	private function parse_atts( $text ) {
		$atts = array( 'options' => array(), 'values' => array() );
		$text = preg_replace( "/[\x{00a0}\x{200b}]+/u", " ", $text );
		$text = trim( $text );

		$pattern = '%^([-+*=0-9a-zA-Z:.!?#$&@_/|\%\r\n\t ]*?)((?:[\r\n\t ]*"[^"]*"|[\r\n\t ]*\'[^\']*\')*)$%';

		if ( preg_match( $pattern, $text, $matches ) ) {
			if ( ! empty( $matches[1] ) ) {
				$atts['options'] = preg_split( '/[\r\n\t ]+/', trim( $matches[1] ) );
			}

			if ( ! empty( $matches[2] ) ) {
				preg_match_all( '/"[^"]*"|\'[^\']*\'/', $matches[2], $matched_values );
				$atts['values'] = wpcf7_strip_quote_deep( $matched_values[0] );
			}
		} else {
			$atts = $text;
		}

		return $atts;
	}
}
