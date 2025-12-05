import { data, components, element, html, i18n } from '../utils/index.js';

const { Button, Flex, FlexItem, SelectControl, TextControl } = components;
const { useSelect } = data;
const { useState } = element;
const { __ } = i18n;

const { editedUserId } = _satispressAccessData;

function ApiKeyForm( { onSubmit } ) {
	const [ name, setName, userId, setUserId ] = useState( '' );

	const isEmpty = '' === name || '' === userId;

	const onClick = () => {
		onSubmit( name );
		setName( '' );
		setUserId( '' );
	};

	const getUsers = () => {
		let data = [{ value: '', label: 'Select a User', readonly: true }];

		const users = useSelect( ( select ) => {
			return select( 'core' ).getUsers();
		}, [] );

		if ( !users ) {
			return [];
		}

		Array.from(users).forEach( user => {
			data.push({ value: user.id, label: user.name, readonly: false });
		});

		return data;
	};

	return html`
		<${ Flex } justify="start">
			<${ FlexItem }>
				<${ TextControl }
					label=${ __( 'API Key Name', 'satispress' ) }
					hideLabelFromVision
					placeholder=${ __( 'Name', 'satispress' ) }
					onChange=${ setName }
					value=${ name }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			</${ FlexItem }>
			<${ FlexItem }>
				<${ SelectControl }
					label=${ __( 'User', 'satispress' ) }
					hideLabelFromVision
					value=${ editedUserId }
					options=${ getUsers() }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			</${ FlexItem }>
			<${ FlexItem }>
				<${ Button }
					isPrimary=${ ! isEmpty }
					isSecondary=${ isEmpty }
					disabled="${ isEmpty && 'disabled' }"
					onClick=${ onClick }
				>
					${ __( 'Create API Key', 'satispress' ) }
				</${ Button }>
			</${ FlexItem }>
		</${ Flex }>
	`;
}

export default ApiKeyForm;
