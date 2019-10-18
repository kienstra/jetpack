/**
 * External dependencies
 */
import React from 'react';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { PlanCard, PlanCardHeader, PlanCardBody } from './plan-card';
import PlanGrid from './plan-grid';
import QuerySite from 'components/data/query-site';
import { getAvailablePlans } from 'state/site/reducer';

function PlanPriceDisplay( props ) {
	const { price } = props;
	return (
		<span
			className="plan-price__yearly"
			// using dangerouslySetInnerHTML because formatting localized
			// currencies is best left to our server and it includes the <abbr> element
			/*eslint-disable react/no-danger*/
			dangerouslySetInnerHTML={ { __html: price.per.replace( 'per ', '/' ) } }
		/>
	); // TODO: don't use this replace
}

export class Plans extends React.Component {
	state = {
		period: 'yearly',
	};

	renderHeaderContent() {
		const { sitePlans } = this.props;

		return (
			<div>
				<h3>This is the header.</h3>
				{ // TODO: make sitePlans dynamic
				sitePlans && (
					<PlanPriceDisplay price={ sitePlans[ 'daily-backup' ].price[ this.state.period ] } />
				) }
			</div>
		);
	}

	render() {
		// TODO: remove
		// const planType = 'is-backup-daily-plan';
		// const className = classNames(
		// 	'plan-features__table-item',
		// 	'is-header',
		// 	'has-border-top',
		// 	`is-${ planType }-plan`
		// );
		return (
			<>
				<QuerySite />
				<h1 className="plans-section__header">Single Products</h1>
				<h2 className="plans-section__subheader">
					Just looking for backups? We've got you covered.
				</h2>
				{ /* TODO: remove
                    <div key={ 'plan-header-' + planType } className={ className }>
					<header className="plan-features__header">
						<h3 className="plan-features__header-title">TITLE</h3>
						<div className="plan-features__description">DESCRIPTION</div>
					</header>
                </div> */ }
				<div style={ { display: 'flex', justifyContent: 'center', marginBottom: '10px' } }>
					<PlanCard>
						{ {
							header: <PlanCardHeader>{ this.renderHeaderContent() }</PlanCardHeader>,
							body: <PlanCardBody>{ <p>This is the body.</p> }</PlanCardBody>,
						} }
					</PlanCard>
				</div>
				<PlanGrid />
			</>
		);
	}
}

export default connect( state => {
	const fakedSitePlans = getAvailablePlans( state );
	// TODO: this is faked data; remove before releasing
	if ( fakedSitePlans ) {
		fakedSitePlans[ 'daily-backup' ] = {
			price: {
				yearly: {
					html: '<abbr title="United States Dollars">$</abbr>39',
					text: '$39',
					amount: 39,
					symbol: '$',
					per: '<abbr title="United States Dollars">$</abbr>39 per year',
				},
				monthly: {
					html: '<abbr title="United States Dollars">$</abbr>3.50',
					text: '$3.50',
					amount: 3.5,
					symbol: '$',
					per: '<abbr title="United States Dollars">$</abbr>3.50 per month',
				},
			},
			features: [],
		};
		fakedSitePlans[ 'realtime-backup' ] = {
			price: {
				yearly: {
					html: '<abbr title="United States Dollars">$</abbr>39',
					text: '$39',
					amount: 39,
					symbol: '$',
					per: '<abbr title="United States Dollars">$</abbr>39 per year',
				},
				monthly: {
					html: '<abbr title="United States Dollars">$</abbr>3.50',
					text: '$3.50',
					amount: 3.5,
					symbol: '$',
					per: '<abbr title="United States Dollars">$</abbr>3.50 per month',
				},
			},
			features: [],
		};
	}

	return {
		// sitePlans: getAvailablePlans( state ),
		sitePlans: fakedSitePlans,
	};
} )( Plans );
