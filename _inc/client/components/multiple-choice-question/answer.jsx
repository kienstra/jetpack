/**
 * External dependencies
 */
import React, { Component } from 'react';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { FormTextInput, FormLabel, FormRadio } from 'components/forms';

class MultipleChoiceAnswer extends Component {
	static propTypes = {
		disabled: PropTypes.bool,
		isSelected: PropTypes.bool,
		onAnswerChange: PropTypes.func,
		answer: PropTypes.shape( {
			id: PropTypes.string.isRequired,
			answerText: PropTypes.string.isRequired,
			textInput: PropTypes.bool,
			textInputPrompt: PropTypes.string,
			children: PropTypes.object,
		} ).isRequired,
		selectedAnswerText: PropTypes.string,
	};

	static defaultProps = {
		disabled: false,
		selectedAnswerText: '',
	};

	constructor( props ) {
		super( props );

		this.handleRadioChange = this.handleRadioChange.bind( this );
		this.handleTextChange = this.handleTextChange.bind( this );
		this.state = {
			textResponse: props.selectedAnswerText,
		};
	}

	handleRadioChange() {
		const {
			onAnswerChange,
			answer: { id },
		} = this.props;
		const { textResponse } = this.state;

		onAnswerChange( id, textResponse );
	}

	handleTextChange( target ) {
		const {
			onAnswerChange,
			answer: { id },
		} = this.props;
		const { value } = target;

		onAnswerChange( id, value );
		this.setState( {
			textResponse: value,
		} );
	}

	render() {
		const {
			disabled,
			answer: { id, answerText, textInput, textInputPrompt, children },
			isSelected,
		} = this.props;

		const { value } = this.state;

		return (
			<FormLabel>
				<FormRadio
					value={ id }
					onChange={ this.handleRadioChange }
					checked={ isSelected }
					disabled={ disabled }
				/>
				<span>{ answerText }</span>
				<div className="multiple-choice-question__answer-item-content">
					{ textInput && (
						<FormTextInput
							className="multiple-choice-question__answer-item-text-input"
							value={ value }
							onChange={ this.handleTextChange }
							placeholder={ textInputPrompt ? textInputPrompt : '' }
							disabled={ disabled }
						/>
					) }
					{ children }
				</div>
			</FormLabel>
		);
	}
}

export default MultipleChoiceAnswer;
