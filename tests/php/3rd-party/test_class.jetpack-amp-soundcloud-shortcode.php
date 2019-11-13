<?php

require_once JETPACK__PLUGIN_DIR . 'modules/shortcodes/soundcloud.php';

/**
 * Tests for class Jetpack_AMP_Soundcloud_Shortcode.
 */
class WP_Test_Jetpack_AMP_Soundcloud_Shortcode extends WP_UnitTestCase {

	/**
	 * Track URL.
	 *
	 * @var string
	 */
	protected $track_url = 'https://soundcloud.com/jack-villano-villano/mozart-requiem-in-d-minor';

	/**
	 * Playlist URL.
	 *
	 * @var string
	 */
	protected $playlist_url = 'https://soundcloud.com/classical-music-playlist/sets/classical-music-essential-collection';

	/**
	 * Response for track oEmbed request.
	 *
	 * @var string
	 */
	protected $track_oembed_response = '{"version":1.0,"type":"rich","provider_name":"SoundCloud","provider_url":"http://soundcloud.com","height":400,"width":500,"title":"Mozart - Requiem in D minor Complete Full by Jack Villano Villano","description":"mass in D Minor ","thumbnail_url":"http://i1.sndcdn.com/artworks-000046826426-o7i9ki-t500x500.jpg","html":"\u003Ciframe width=\"500\" height=\"400\" scrolling=\"no\" frameborder=\"no\" src=\"https://w.soundcloud.com/player/?visual=true\u0026url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F90097394\u0026show_artwork=true\u0026maxwidth=500\u0026maxheight=750\u0026dnt=1\"\u003E\u003C/iframe\u003E","author_name":"Jack Villano Villano","author_url":"https://soundcloud.com/jack-villano-villano"}';

	/**
	 * Response for playlist oEmbed request.
	 *
	 * @var string
	 */
	protected $playlist_oembed_response = '{"version":1.0,"type":"rich","provider_name":"SoundCloud","provider_url":"http://soundcloud.com","height":450,"width":500,"title":"Classical Music - The Essential Collection by Classical Music","description":"Classical Music - The Essential Collection features 50 of the finest Classical Masterpieces ever written. Definitely not to working to! ","thumbnail_url":"http://i1.sndcdn.com/artworks-000083473866-mno23j-t500x500.jpg","html":"\u003Ciframe width=\"500\" height=\"450\" scrolling=\"no\" frameborder=\"no\" src=\"https://w.soundcloud.com/player/?visual=true\u0026url=https%3A%2F%2Fapi.soundcloud.com%2Fplaylists%2F40936190\u0026show_artwork=true\u0026maxwidth=500\u0026maxheight=750\u0026dnt=1\"\u003E\u003C/iframe\u003E","author_name":"Classical Music","author_url":"https://soundcloud.com/classical-music-playlist"}';

	/**
	 * Set up.
	 *
	 * @global WP_Post $post
	 */
	public function setUp() {
		parent::setUp();

		/*
		 * As #34115 in 4.9 a post is not needed for context to run oEmbeds. Prior ot 4.9, the WP_Embed::shortcode()
		 * method would short-circuit when this is the case:
		 * https://github.com/WordPress/wordpress-develop/blob/4.8.4/src/wp-includes/class-wp-embed.php#L192-L193
		 * So on WP<4.9 we set a post global to ensure oEmbeds get processed.
		 */
		if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '4.9', '<' ) ) {
			$GLOBALS['post'] = $this->factory()->post->create_and_get();
		}

		add_shortcode( 'soundcloud', 'soundcloud_shortcode' );
		add_filter( 'pre_http_request', array( $this, 'mock_http_request' ), 10, 3 );
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		remove_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ) );
		remove_all_filters( 'jetpack_is_amp_request' );
		parent::tearDown();
	}

	/**
	 * Mock HTTP request.
	 *
	 * @param mixed  $preempt Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r       HTTP request arguments.
	 * @param string $url     The request URL.
	 * @return array Response data.
	 */
	public function mock_http_request( $preempt, $r, $url ) {
		unset( $r );
		if ( false === strpos( $url, 'soundcloud.com' ) ) {
			return $preempt;
		}

		if ( false !== strpos( $url, 'sets' ) ) {
			$body = $this->playlist_oembed_response;
		} else {
			$body = $this->track_oembed_response;
		}

		return array(
			'body'          => $body,
			'headers'       => array(),
			'response'      => array(
				'code'    => 200,
				'message' => 'ok',
			),
			'cookies'       => array(),
			'http_response' => null,
		);
	}

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		return array(
			'no_embed'        => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			),

			'track_simple'    => array(
				$this->track_url . PHP_EOL,
				'<p><amp-soundcloud data-trackid="90097394" data-visual="true" height="400" width="500" layout="responsive">' . ( function_exists( 'wp_filter_oembed_iframe_title_attribute' ) ? '<a fallback href="https://soundcloud.com/jack-villano-villano/mozart-requiem-in-d-minor">Mozart &#8211; Requiem in D minor Complete Full by Jack Villano Villano</a>' : '' ) . '</amp-soundcloud></p>' . PHP_EOL,
			),

			'playlist_simple' => array(
				$this->playlist_url . PHP_EOL,
				'<p><amp-soundcloud data-playlistid="40936190" data-visual="true" height="450" width="500" layout="responsive">' . ( function_exists( 'wp_filter_oembed_iframe_title_attribute' ) ? '<a fallback href="https://soundcloud.com/classical-music-playlist/sets/classical-music-essential-collection">Classical Music &#8211; The Essential Collection by Classical Music</a>' : '' ) . '</amp-soundcloud></p>' . PHP_EOL,
			),

			'shortcode_with_bare_track_api_url'   => array(
				'[soundcloud https://api.soundcloud.com/tracks/90097394]' . PHP_EOL,
				'<amp-soundcloud data-trackid="90097394" data-visual="false" height="166" layout="fixed-height"></amp-soundcloud>' . PHP_EOL,
			),

			'shortcode_with_track_api_url'        => array(
				'[soundcloud url=https://api.soundcloud.com/tracks/90097394]' . PHP_EOL,
				'<amp-soundcloud data-trackid="90097394" data-visual="false" width="auto" height="166" layout="fixed-height"></amp-soundcloud>' . PHP_EOL,
			),

			'shortcode_with_track_permalink'      => array(
				"[soundcloud url=$this->track_url]",
				'<amp-soundcloud data-trackid="90097394" data-visual="true" height="400" width="500" layout="responsive">' . ( function_exists( 'wp_filter_oembed_iframe_title_attribute' ) ? '<a fallback href="https://soundcloud.com/jack-villano-villano/mozart-requiem-in-d-minor">Mozart - Requiem in D minor Complete Full by Jack Villano Villano</a>' : '' ) . '</amp-soundcloud>' . PHP_EOL,
			),

			'shortcode_with_bare_track_permalink' => array(
				"[soundcloud {$this->track_url}]",
				'<amp-soundcloud data-trackid="90097394" data-visual="true" height="400" width="500" layout="responsive">' . ( function_exists( 'wp_filter_oembed_iframe_title_attribute' ) ? '<a fallback href="https://soundcloud.com/jack-villano-villano/mozart-requiem-in-d-minor">Mozart - Requiem in D minor Complete Full by Jack Villano Villano</a>' : '' ) . '</amp-soundcloud>' . PHP_EOL,
			),

			'shortcode_with_playlist_permalink'   => array(
				"[soundcloud url={$this->playlist_url}]",
				'<amp-soundcloud data-playlistid="40936190" data-visual="true" height="450" width="500" layout="responsive">' . ( function_exists( 'wp_filter_oembed_iframe_title_attribute' ) ? '<a fallback href="https://soundcloud.com/classical-music-playlist/sets/classical-music-essential-collection">Classical Music - The Essential Collection by Classical Music</a>' : '' ) . '</amp-soundcloud>' . PHP_EOL,
			),

			// This apparently only works on WordPress.com.
			'shortcode_with_id'                   => array(
				'[soundcloud id=90097394]' . PHP_EOL,
				'<amp-soundcloud data-trackid="90097394" data-visual="false" height="166" layout="fixed-height"></amp-soundcloud>' . PHP_EOL,
			),
		);
	}

	/**
	 * Test conversion.
	 *
	 * @covers Jetpack_AMP_Soundcloud_Shortcode::filter_embed_oembed_html()
	 * @covers Jetpack_AMP_Soundcloud_Shortcode::shortcode()
	 * @covers Jetpack_AMP_Soundcloud_Shortcode::render()
	 * @dataProvider get_conversion_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__conversion( $source, $expected ) {
		add_filter( 'jetpack_is_amp_request', '__return_true' );
		add_shortcode( 'soundcloud', 'soundcloud_shortcode' );
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}
}
