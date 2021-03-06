/**
 * External dependencies
 */
import React from 'react';
import { translate as __ } from 'i18n-calypso';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import analytics from 'lib/analytics';
import ChecklistCta from './checklist-cta';
import ChecklistProgress from './checklist-progress-card';
import { getPlanClass } from 'lib/plans/constants';
import { getUpgradeUrl, getSiteRawUrl, showBackups } from 'state/initial-state';
import { imagePath } from 'constants/urls';
import UpgradeLink from 'components/upgrade-link';

class MyPlanHeader extends React.Component {
	trackChecklistCtaClick = () =>
		void analytics.tracks.recordEvent(
			'jetpack_myplan_headerchecklistcta_click',
			this.props.plan
				? {
						plan: this.props.plan,
				  }
				: undefined
		);

	render() {
		const { plan, siteSlug } = this.props;

		const PlanHeaderCard = props => {
			const { title, text, imgSrc, imgAlt } = props;

			return (
				<div className="jp-landing__plan-card">
					<div className="jp-landing__plan-card-img">
						<img src={ imgSrc } className="jp-landing__plan-icon" alt={ imgAlt } />
					</div>
					<div className="jp-landing__plan-card-current">
						<h3 className="jp-landing__plan-features-title">{ title }</h3>
						<p className="jp-landing__plan-features-text">{ text }</p>
						<ChecklistCta onClick={ this.trackChecklistCtaClick } siteSlug={ siteSlug } />
					</div>
				</div>
			);
		};

		let planCard = '';
		switch ( getPlanClass( plan ) ) {
			case 'is-free-plan':
				planCard = (
					<div className="jp-landing__plan-card">
						<div className="jp-landing__plan-card-img">
							<img
								src={ imagePath + '/plans/plan-free.svg' }
								className="jp-landing__plan-icon"
								alt={ __( 'Jetpack Free Plan' ) }
							/>
						</div>
						<div className="jp-landing__plan-card-current">
							<h3 className="jp-landing__plan-features-title">
								{ __( 'Your plan: Jetpack Free' ) }
							</h3>
							<p className="jp-landing__plan-features-text">
								{ __(
									'Worried about security? Get backups, automated security fixes and more: {{a}}Upgrade now{{/a}}',
									{
										components: {
											a: (
												<UpgradeLink
													source="my-plan-header-free-plan-text-link"
													target="upgrade-now"
													feature="my-plan-header-free-upgrade"
												/>
											),
										},
									}
								) }
							</p>
							<ChecklistCta onClick={ this.trackChecklistCtaClick } siteSlug={ siteSlug } />
						</div>
					</div>
				);
				break;

			case 'is-personal-plan':
				planCard = (
					<div className="jp-landing__plan-card">
						<div className="jp-landing__plan-card-img">
							<img
								src={ imagePath + '/plans/plan-personal.svg' }
								className="jp-landing__plan-icon"
								alt={ __( 'Jetpack Personal Plan' ) }
							/>
						</div>
						<div className="jp-landing__plan-card-current">
							<h3 className="jp-landing__plan-features-title">
								{ __( 'Your plan: Jetpack Personal' ) }
							</h3>
							{ this.props.showBackups ? (
								<p className="jp-landing__plan-features-text">
									{ __( 'Daily backups, spam filtering, and priority support.' ) }
								</p>
							) : (
								<p className="jp-landing__plan-features-text">
									{ __( 'Spam filtering and priority support.' ) }
								</p>
							) }
							<ChecklistCta onClick={ this.trackChecklistCtaClick } siteSlug={ siteSlug } />
						</div>
					</div>
				);
				break;

			case 'is-premium-plan':
				planCard = (
					<div className="jp-landing__plan-card">
						<div className="jp-landing__plan-card-img">
							<img
								src={ imagePath + '/plans/plan-premium.svg' }
								className="jp-landing__plan-icon"
								alt={ __( 'Jetpack Premium Plan' ) }
							/>
						</div>
						<div className="jp-landing__plan-card-current">
							<h3 className="jp-landing__plan-features-title">
								{ __( 'Your plan: Jetpack Premium' ) }
							</h3>
							<p className="jp-landing__plan-features-text">
								{ __(
									'Full security suite, marketing and revenue automation tools, unlimited video hosting, and priority support.'
								) }
							</p>
							<ChecklistCta onClick={ this.trackChecklistCtaClick } siteSlug={ siteSlug } />
						</div>
					</div>
				);
				break;

			case 'is-business-plan':
				planCard = (
					<div className="jp-landing__plan-card">
						<div className="jp-landing__plan-card-img">
							<img
								src={ imagePath + '/plans/plan-business.svg' }
								className="jp-landing__plan-icon"
								alt={ __( 'Jetpack Business Plan' ) }
							/>
						</div>
						<div className="jp-landing__plan-card-current">
							<h3 className="jp-landing__plan-features-title">
								{ __( 'Your plan: Jetpack Professional' ) }
							</h3>
							<p className="jp-landing__plan-features-text">
								{ __(
									'Full security suite, marketing and revenue automation tools, unlimited video hosting, unlimited themes, enhanced search, and priority support.'
								) }
							</p>
							<ChecklistCta onClick={ this.trackChecklistCtaClick } siteSlug={ siteSlug } />
						</div>
					</div>
				);
				break;

			case 'is-daily-backup-plan':
				planCard = (
					<PlanHeaderCard
						title={ __( 'Your plan: Jetpack Free + Daily Backup' ) }
						text={ __(
							'Worried about security? Get automated security fixes and more: {{a}}Upgrade now{{/a}}',
							{
								components: {
									a: (
										<UpgradeLink
											source="my-plan-header-daily-backup-text-link"
											target="upgrade-now"
											feature="my-plan-header-daily-backup-upgrade"
										/>
									),
								},
							}
						) }
						imgSrc={ imagePath + '/plans/plan-free.svg' }
						imgAlt={ __( 'Jetpack Daily Backup Plan' ) }
					/>
				);
				break;

			case 'is-realtime-backup-plan':
				planCard = (
					<PlanHeaderCard
						title={ __( 'Your plan: Jetpack Free + Real-time Backup' ) }
						text={ __(
							'Worried about security? Get automated security fixes and more: {{a}}Upgrade now{{/a}}',
							{
								components: {
									a: (
										<UpgradeLink
											source="my-plan-header-real-time-backup-text-link"
											target="upgrade-now"
											feature="my-plan-header-real-time-backup-upgrade"
										/>
									),
								},
							}
						) }
						imgSrc={ imagePath + '/plans/plan-free.svg' }
						imgAlt={ __( 'Jetpack Real-time Backup Plan' ) }
					/>
				);
				break;

			default:
				planCard = (
					<div className="jp-landing__plan-card">
						<div className="jp-landing__plan-card-img is-placeholder" />
						<div className="jp-landing__plan-card-current">
							<h3 className="jp-landing__plan-features-title is-placeholder"> </h3>
							<p className="jp-landing__plan-features-text is-placeholder"> </p>
						</div>
					</div>
				);
				break;
		}
		return (
			<>
				<div>{ planCard }</div>
				<ChecklistProgress plan={ plan } />
			</>
		);
	}
}
export default connect( state => {
	return {
		siteSlug: getSiteRawUrl( state ),
		showBackups: showBackups( state ),
		plansMainTopUpgradeUrl: getUpgradeUrl( state, 'plans-main-top' ),
	};
} )( MyPlanHeader );
