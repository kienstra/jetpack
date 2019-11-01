<?php

require_once JETPACK__PLUGIN_DIR . '3rd-party/class.jetpack-amp-tweet-shortcode.php';

/**
 * Tests for class Jetpack_AMP_Tweet_Shortcode.
 */
class WP_Test_Jetpack_AMP_Tweet_Shortcode extends WP_UnitTestCase {

	/**
	 * Tear down each test.
	 *
	 * @inheritDoc
	 */
	public function tearDown() {
		remove_all_filters( 'jetpack_is_amp_request' );
	}

	/**
	 * Tests init.
	 *
	 * @covers Jetpack_AMP_Tweet_Shortcode::init()
	 */
	public function test_init() {
		Jetpack_AMP_Tweet_Shortcode::init();
		$this->assertEquals( 10, has_filter( 'do_shortcode_tag', array( 'Jetpack_AMP_Tweet_Shortcode', 'filter_shortcode' ) ) );
	}

	/**
	 * Gets the test data for the [tweet] shortcodes.
	 *
	 * @return array An associative array of test data.
	 */
	public function get_filter_shortcode_data() {
		return array(
			'not_a_tweet_shortcode'               => array(
				'<amp-vimeo></amp-vimeo>',
				'vimeo',
				array(
					'id' => '62245'
				),
				null,
			),
			'empty_attr_array'                    => array(
				'<div>Initial shortcode</div>',
				'tweet',
				array(),
				'',
			),
			'correct_tweet_attribute_present'     => array(
				'<div>Initial shortcode</div>',
				'tweet',
				array(
					'tweet' => '24246'
				),
				'<amp-twitter data-tweetid="24246" layout="responsive" width="600" height="480"></amp-twitter>'
			),
			'wrong_non_numeric_tweet_attribute'   => array(
				'<div>Initial shortcode</div>',
				'tweet',
				array(
					'tweet' => 'notanumber'
				),
				'',
			),
			'id_in_0_index'                       => array(
				'<div>Initial shortcode</div>',
				'tweet',
				array(
					'0' => '62345',
				),
				'<amp-twitter data-tweetid="62345" layout="responsive" width="600" height="480"></amp-twitter>'
			),
			'id_in_0_index_with_width_and_height' => array(
				'<div>Initial shortcode</div>',
				'tweet',
				array(
					'0'      => '62345',
					'width'  => '400',
					'height' => '300'
				),
				'<amp-twitter data-tweetid="62345" layout="responsive" width="400" height="300"></amp-twitter>'
			),
			'correct_tweet_url_in_0_index'        => array(
				'<div>Initial shortcode</div>',
				'tweet',
				array(
					'0'      => 'https://twitter.com/exampleuser/status/26134134',
					'width'  => '400',
					'height' => '300'
				),
				'<amp-twitter data-tweetid="26134134" layout="responsive" width="400" height="300"></amp-twitter>'
			),
			'wrong_tweet_url_in_0_index'          => array(
				'<div>A Shortcode</div>',
				'tweet',
				array(
					'0'      => 'https://youtube.com/exampleuser/status/26134134',
					'width'  => '400',
					'height' => '300'
				),
				'',
			),
		);
	}

	/**
	 * Tests that the [tweet] shortcode filter produces the right HTML.
	 *
	 * @dataProvider get_filter_shortcode_data
	 * @covers Jetpack_AMP_Tweet_Shortcode::filter_shortcode()
	 *
	 * @param string $html The html passed to the filter.
	 * @param string $shortcode_tag The tag (name) of the shortcode, like 'tweet'.
	 * @param array  $attr The shortcode attributes.
	 * @param string $expected The expected return value.
	 */
	public function test_filter_shortcode( $html, $shortcode_tag, $attr, $expected ) {
		unset( $GLOBALS['content_width'] );
		add_filter( 'jetpack_is_amp_request', '__return_true' );

		if ( null === $expected ) {
			$expected = $html;
		}

		$this->assertEquals( $expected, Jetpack_AMP_Tweet_Shortcode::filter_shortcode( $html, $shortcode_tag, $attr ) );
	}

	/**
	 * Tests that the [tweet] shortcode filter does not filter the markup on non-AMP endpoints.
	 *
	 * @covers Jetpack_AMP_Tweet_Shortcode::filter_shortcode()
	 */
	public function test_filter_shortcode_non_amp() {
		$initial_shortcode_markup = '<div><span>This is the shortcode markup</span></div>';

		$this->assertEquals(
			$initial_shortcode_markup,
			Jetpack_AMP_Tweet_Shortcode::filter_shortcode(
				$initial_shortcode_markup,
				'youtube',
				array(
					'id'     => '6234',
					'width'  => '800',
					'height' => '400',
				)
			)
		);
	}
}
