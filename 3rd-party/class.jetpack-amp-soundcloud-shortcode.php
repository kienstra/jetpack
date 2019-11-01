<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

/**
 * Makes the [soundcloud] Jetpack shortcode AMP-compatible.
 *
 * @see https://github.com/ampproject/amp-wp/blob/ea9e6fb9d262e699ea64978794840ba9868715f6/includes/embeds/class-amp-soundcloud-embed.php
 */
class Jetpack_AMP_Soundcloud_Shortcode {

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
		add_filter( 'do_shortcode_tag', array( __CLASS__, 'filter_shortcode' ), 10, 3 );
		add_filter( 'embed_oembed_html', array( __CLASS__, 'filter_embed_oembed_html' ), 10, 2 );
	}

	/**
	 * Renders the [soundcloud] shortcode.
	 *
	 * @param string $html The initial shortcode HTML.
	 * @param string $shortcode_tag The tag (name) of the shortcode.
	 * @param array  $attr Shortcode attributes.
	 * @return string Rendered shortcode.
	 */
	public static function filter_shortcode( $html, $shortcode_tag, $attr ) {
		if ( 'soundcloud' !== $shortcode_tag || ! Jetpack_AMP_Support::is_amp_request() ) {
			return $html;
		}

		$attr['height'] = isset( $attr['height'] ) ? $attr['height'] : 200;
		if ( isset( $attr['url'] ) ) {
			$url = $attr['url'];
		} elseif ( isset( $attr['id'] ) ) {
			$url = 'https://api.soundcloud.com/tracks/' . $attr['id'];
		} elseif ( isset( $attr[0] ) ) {
			$url = is_numeric( $attr[0] ) ? 'https://api.soundcloud.com/tracks/' . $attr[0] : $attr[0];
		} elseif ( function_exists( 'shortcode_new_to_old_params' ) ) {
			$url = shortcode_new_to_old_params( $attr );
		}

		// Defer to oEmbed if an oEmbeddable URL is provided.
		if ( isset( $url ) && 'api.soundcloud.com' !== wp_parse_url( $url, PHP_URL_HOST ) ) {
			return $GLOBALS['wp_embed']->shortcode( $attr, $url );
		}

		if ( isset( $url ) && ! isset( $attr['url'] ) ) {
			$attr['url'] = $url;
		}
		$output = soundcloud_shortcode( $attr, $html );

		return self::parse_amp_component_from_iframe( $output, null );
	}

	/**
	 * Filter oEmbed HTML for SoundCloud to convert to AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public static function filter_embed_oembed_html( $cache, $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! in_array( $host, array( 'soundcloud.com', 'www.soundcloud.com' ), true ) ) {
			return $cache;
		}

		return self::parse_amp_component_from_iframe( $cache, $url );
	}

	/**
	 * Parse AMP component from iframe.
	 *
	 * @param string      $html HTML.
	 * @param string|null $url  Embed URL, for fallback purposes.
	 * @return string AMP component or empty if unable to determine SoundCloud ID.
	 */
	private static function parse_amp_component_from_iframe( $html, $url ) {
		$embed = '';

		if ( preg_match( '#<iframe[^>]*?src="(?P<src>[^"]+)"#s', $html, $matches ) ) {
			$src   = html_entity_decode( $matches['src'], ENT_QUOTES );
			$query = [];
			parse_str( wp_parse_url( $src, PHP_URL_QUERY ), $query );
			if ( ! empty( $query['url'] ) ) {
				$props = self::extract_params_from_iframe_src( $query['url'] );
				if ( isset( $query['visual'] ) ) {
					$props['visual'] = $query['visual'];
				}

				if ( $url && preg_match( '#<iframe[^>]*?title="(?P<title>[^"]+)"#s', $html, $matches ) ) {
					$props['fallback'] = sprintf(
						'<a fallback href="%s">%s</a>',
						esc_url( $url ),
						esc_html( $matches['title'] )
					);
				}

				if ( preg_match( '#<iframe[^>]*?height="(?P<height>\d+)"#s', $html, $matches ) ) {
					$props['height'] = (int) $matches['height'];
				}

				if ( preg_match( '#<iframe[^>]*?width="(?P<width>\d+)"#s', $html, $matches ) ) {
					$props['width'] = (int) $matches['width'];
				}

				$embed = self::render( $props, $url );
			}
		}
		return $embed;
	}

	/**
	 * Renders the SoundCloud embed.
	 *
	 * @param array  $args Args.
	 * @param string $url  Embed URL for fallback purposes. Optional.
	 * @return string Rendered embed.
	 * @global WP_Embed $wp_embed
	 */
	public static function render( $args, $url ) {
		$args = wp_parse_args(
			$args,
			[
				'track_id'    => false,
				'playlist_id' => false,
				'height'      => null,
				'width'       => null,
				'visual'      => null,
				'fallback'    => '',
			]
		);

		$attributes = [];
		if ( ! empty( $args['track_id'] ) ) {
			$attributes['data-trackid'] = $args['track_id'];
		} elseif ( ! empty( $args['playlist_id'] ) ) {
			$attributes['data-playlistid'] = $args['playlist_id'];
		} elseif ( $url ) {
			return self::render_embed_fallback( $url );
		} else {
			return '';
		}

		if ( isset( $args['visual'] ) ) {
			$attributes['data-visual'] = rest_sanitize_boolean( $args['visual'] ) ? 'true' : 'false';
		}

		$attributes['height'] = $args['height'];
		if ( $args['width'] ) {
			$attributes['width']  = $args['width'];
			$attributes['layout'] = 'responsive';
		} else {
			$attributes['layout'] = 'fixed-height';
		}

		return Jetpack_AMP_Support::build_tag(
			'amp-soundcloud',
			$attributes,
			$args['fallback']
		);
	}

	/**
	 * Render embed fallback.
	 *
	 * @param string $url URL.
	 * @return string Fallback link.
	 */
	private static function render_embed_fallback( $url ) {
		return AMP_HTML_Utils::build_tag(
			'a',
			[
				'href'  => esc_url_raw( $url ),
				'class' => 'amp-wp-embed-fallback',
			],
			esc_html( $url )
		);
	}

	/**
	 * Get params from Soundcloud iframe src.
	 *
	 * @param string $url URL.
	 * @return array Params extracted from URL.
	 */
	private static function extract_params_from_iframe_src( $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( preg_match( '#tracks/(?P<track_id>\d+)#', $parsed_url['path'], $matches ) ) {
			return [
				'track_id' => $matches['track_id'],
			];
		}
		if ( preg_match( '#playlists/(?P<playlist_id>\d+)#', $parsed_url['path'], $matches ) ) {
			return [
				'playlist_id' => $matches['playlist_id'],
			];
		}
		return [];
	}
}

add_action( 'init', array( 'Jetpack_AMP_Soundcloud_Shortcode', 'init' ), 1 );
