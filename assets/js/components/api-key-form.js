import { components, data, element, html, i18n } from '../utils/index.js';

const { Button, Flex, FlexItem, SelectControl, TextControl } = components;
const { useSelect } = data;
const { useState } = element;
const { __ } = i18n;

const { capabilities, editedUserId } = _satispressAccessData;

function ApiKeyForm( { onSubmit, userId: selectedUserId, onChangeUser } ) {
	const [ name, setName ] = useState('');
	const [ userId, setUserId ] = useState('');

	// Only show the user selector on the plugin settings page, not the user edit page
	const isUserEditPage = ( typeof window !== 'undefined' ) && window.location && window.location.href.includes( 'user-edit.php' );

	const effectiveUserId = userId || selectedUserId || editedUserId || '';
	const isEmpty = '' === name || '' === effectiveUserId;

	const onClick = () => {
		onSubmit( name, effectiveUserId );
		setName( '' );
		setUserId( '' );
	};

	const getAdminUsers = () => {
		let data = [{ value: '', label: 'Select a User', readonly: true }];

		const users = useSelect( ( select ) => {
			const query = {
				_fields: "id,name",
				capabilities: capabilities,
				context: "view",
				per_page: 100,
			};
			const { getUsers } = select( 'core' );
			return getUsers( query );
		}, [] );

		if ( !users ) {
			return [{ value: '', label: __( 'Loading...', 'satispress' ), readonly: true }];
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
					data-1p-ignore
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			</${ FlexItem }>
			${ ! isUserEditPage && html`
				<${ FlexItem }>
					<${ SelectControl }
						label=${ __( 'User', 'satispress' ) }
						hideLabelFromVision
						value=${ effectiveUserId }
						options=${ getAdminUsers() }
						onChange=${ ( val ) => { setUserId( val ); onChangeUser && onChangeUser( val ); } }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				</${ FlexItem }>
			` }
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
