/** @jsx h */

/**
 * External dependencies
 */
import { h, Component } from 'preact';
import strip from 'strip';

/**
 * Internal dependencies
 */
import Gridicon from './gridicon';
import arrayOverlap from '../lib/array-overlap';
import { recordTrainTracksRender, recordTrainTracksInteract } from '../lib/tracks';

const ShortcodeTypes = {
	video: [
		'youtube',
		'ooyala',
		'anvplayer',
		'wpvideo',
		'bc_video',
		'video',
		'brightcove',
		'tp_video',
		'jwplayer',
		'tempo-video',
		'vimeo',
	],
	gallery: [ 'gallery', 'ione_media_gallery' ],
	audio: [ 'audio', 'soundcloud' ],
	code: [ 'code', 'sourcecode' ],
};

class SearchResultMinimal extends Component {
	componentDidMount() {
		recordTrainTracksRender( this.getCommonTrainTracksProps() );
	}

	getCommonTrainTracksProps() {
		return {
			fetch_algo: 'jetpack-instant-search-api/v1',
			fetch_position: this.props.index,
			fetch_query: this.props.query,
			railcar: this.props.railcarId,
			rec_blog_id: this.props.result.fields.blog_id,
			rec_post_id: this.props.result.fields.post_id,
			ui_algo: 'jetpack-instant-search-ui/v1',
			ui_position: this.props.index,
		};
	}

	onClick = event => {
		// User-triggered event
		if ( event.isTrusted ) {
			event.stopPropagation();
			event.preventDefault();
			// Send out analytics call
			recordTrainTracksInteract( { ...this.getCommonTrainTracksProps(), action: 'click' } );
			// Await next animation frame to ensure w.js processes the queue
			requestAnimationFrame( () => {
				// Re-dispatch click event
				const clonedEvent = new event.constructor( event.type, event );
				event.target.dispatchEvent( clonedEvent );
			} );
		} else {
			// Programmatically dispatched event from `dispatchEvent`
			return true;
		}
	};

	render() {
		const { result_type, fields, highlight } = this.props.result;
		const IconSize = 18;
		if ( result_type !== 'post' ) {
			return null;
		}
		const url = new URL( 'http://' + fields[ 'permalink.url.raw' ] );
		const path = url.pathname;
		const no_content = ! highlight.content || highlight.content[ 0 ] === '';

		let tags = fields[ 'tag.name.default' ];
		if ( ! tags ) {
			tags = [];
		}
		if ( ! Array.isArray( tags ) ) {
			tags = [ tags ];
		}

		let cats = fields[ 'category.name.default' ];
		if ( ! cats ) {
			cats = [];
		}
		if ( ! Array.isArray( cats ) ) {
			cats = [ cats ];
		}
		const noTags = tags.length === 0 && cats.length === 0;

		let hasVideo = arrayOverlap( fields.shortcode_types, ShortcodeTypes.video );
		let hasAudio = arrayOverlap( fields.shortcode_types, ShortcodeTypes.audio );
		const hasCode = arrayOverlap( fields.shortcode_types, ShortcodeTypes.code );

		let hasGallery =
			arrayOverlap( fields.shortcode_types, ShortcodeTypes.gallery ) || fields[ 'has.image' ] > 1;
		let hasImage = fields[ 'has.image' ] === 1;

		let postTypeIcon = null;
		switch ( fields.post_type ) {
			case 'product':
				postTypeIcon = <Gridicon icon="cart" size={ IconSize } />;
				hasImage = false;
				hasGallery = false;
				break;
			case 'page':
				postTypeIcon = <Gridicon icon="pages" size={ IconSize } />;
				break;
			case 'video':
				hasVideo = true;
				break;
			case 'gallery':
				hasGallery = true;
				break;
			case 'event':
			case 'events':
				postTypeIcon = <Gridicon icon="calendar" size={ IconSize } />;
				break;
		}

		//don't show too many icons
		if ( hasVideo ) {
			hasImage = false;
			hasGallery = false;
			hasAudio = false;
		}
		if ( hasAudio ) {
			hasImage = false;
			hasGallery = false;
		}
		if ( hasGallery ) {
			hasImage = false;
		}

		return (
			<div className="jetpack-instant-search__result-minimal">
				<span className="jetpack-instant-search__result-minimal-date">
					{ strip( fields.date ).split( ' ' )[ 0 ] }
				</span>
				<h3>
					{ postTypeIcon }
					<a
						href={ `//${ fields[ 'permalink.url.raw' ] }` }
						className="jetpack-instant-search__result-minimal-title"
						//eslint-disable-next-line react/no-danger
						dangerouslySetInnerHTML={ { __html: highlight.title } }
						onClick={ this.onClick }
					/>
					{ hasVideo && <Gridicon icon="video" size={ IconSize } /> }
					{ hasImage && <Gridicon icon="image" size={ IconSize } /> }
					{ hasGallery && <Gridicon icon="image-multiple" size={ IconSize } /> }
					{ hasAudio && <Gridicon icon="audio" size={ IconSize } /> }
					{ hasCode && <Gridicon icon="code" size={ IconSize } /> }
				</h3>

				{ no_content && (
					<div className="jetpack-instant-search__result-minimal-content">
						{ noTags && (
							<div className="jetpack-instant-search__result-minimal-path">{ path }</div>
						) }
						{ tags.length !== 0 && (
							<div className="jetpack-instant-search__result-minimal-tag">
								{ tags.map( tag => (
									<span>
										<Gridicon icon="tag" size={ IconSize } />
										{ tag }
									</span>
								) ) }
							</div>
						) }
						{ cats.length !== 0 && (
							<div className="jetpack-instant-search__result-minimal-cat">
								{ cats.map( cat => (
									<span>
										<Gridicon icon="folder" size={ IconSize } />
										{ cat }
									</span>
								) ) }
							</div>
						) }
					</div>
				) }
				{ ! no_content && (
					<div
						className="jetpack-instant-search__result-minimal-content"
						//eslint-disable-next-line react/no-danger
						dangerouslySetInnerHTML={ {
							__html: highlight.content.join( ' ... ' ),
						} }
					/>
				) }

				{ highlight.comments && (
					<div className="jetpack-instant-search__result-minimal-comment">
						<Gridicon icon="comment" size={ IconSize } />
						<span
							className="jetpack-instant-search__result-minimal-comment-span"
							//eslint-disable-next-line react/no-danger
							dangerouslySetInnerHTML={ {
								__html: highlight.comments.join( ' ... ' ),
							} }
						/>
					</div>
				) }
			</div>
		);
	}
}

export default SearchResultMinimal;
