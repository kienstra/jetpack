<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

/**
 * Makes the [tweet] Jetpack shortcode AMP-compatible.
 *
 * @see https://github.com/ampproject/amp-wp/blob/ea9e6fb9d262e699ea64978794840ba9868715f6/includes/embeds/class-amp-twitter-embed.php#L71
 */
class Jetpack_AMP_Tweet_Shortcode {

	/**
	 * URL pattern for a Tweet URL.
	 *
	 * @var string
	 */
	const URL_PATTERN = '#https?:\/\/twitter\.com(?:\/\#\!\/|\/)(?P<username>[a-zA-Z0-9_]{1,20})\/status(?:es)?\/(?P<tweet>\d+)#i';

	/**
	 * Add the shortcode filter.
	 */
	public static function init() {
		add_filter( 'do_shortcode_tag', array( 'Jetpack_AMP_Tweet_Shortcode', 'filter_shortcode' ), 10, 3 );
	}

	/**
	 * Filters the Tweet shortcode to be AMP-compatible.
	 *
	 * @param string $html The video player HTML.
	 * @param string $shortcode_tag The shortcode's tag (name).
	 * @param array  $attr The attributes of the shortcode.
	 * @return string The filtered HTML.
	 */
	public static function filter_shortcode( $html, $shortcode_tag, $attr ) {
		if ( ! Jetpack_AMP_Support::is_amp_request() || 'tweet' !== $shortcode_tag ) {
			return $html;
		}

		$attr = wp_parse_args( $attr, array( 'tweet' => false ) );
		if ( empty( $attr['tweet'] ) && ! empty( $attr[0] ) ) {
			$attr['tweet'] = $attr[0];
		}

		$id = false;
		if ( is_numeric( $attr['tweet'] ) ) {
			$id = $attr['tweet'];
		} else {
			preg_match( self::URL_PATTERN, $attr['tweet'], $matches );
			if ( isset( $matches['tweet'] ) && is_numeric( $matches['tweet'] ) ) {
				$id = $matches['tweet'];
			}
			if ( empty( $id ) ) {
				return '';
			}
		}

		return Jetpack_AMP_Support::build_tag(
			'amp-twitter',
			array(
				'data-tweetid' => $id,
				'layout'       => 'responsive',
				'width'        => isset( $attr['width'] ) ? $attr['width'] : 600,
				'height'       => isset( $attr['height'] ) ? $attr['height'] : 480,
			)
		);
	}
}

add_action( 'init', array( 'Jetpack_AMP_Tweet_Shortcode', 'init' ), 1 );
