import { components, data, element, html, i18n } from '../utils/index.js';
import { closeSmallIcon } from './icons.js';

const { Button, CheckboxControl, Panel, PanelHeader, TabPanel } = components;
const { useSelect } = data;
const { Component } = element;
const { __ } = i18n;

function PackageSelector( props ) {
	const {
		onAddPackage,
		onRemovePackage,
		onClose,
		packages,
	} = props;

	const installedPackages = {
		plugins: useSelect( ( select ) => select( 'satispress/packages' ).getPlugins() ),
		themes: useSelect( ( select ) => select( 'satispress/packages' ).getThemes() ),
	};

	const packageExists = ( slug, type ) => {
		return !! packages.filter( item => slug === item.slug && type === item.type ).length;
	}

	const togglePackage = ( slug, type, add ) => {
		if ( add ) {
			onAddPackage( slug, type );
		} else {
			onRemovePackage( slug, type );
		}
	}

	const tabs = [
		{
			name: 'plugins',
			title: __( 'Plugins', 'satispress' ),
			className: 'tab-plugins',
		},
		{
			name: 'themes',
			title: __( 'Themes', 'satispress' ),
			className: 'tab-themes',
		}
	];

	const tabContent = ( tab ) => {
		const items = installedPackages[ tab.name ];

		const listItems = items.map( item => {
			const { name, slug, type } = item;

			return html`
				<li key=${ item.slug }>
					<${ CheckboxControl }
						checked=${ packageExists( slug, type ) }
						onChange=${ checked => togglePackage( slug, type, checked ) }
						label=${ name }
					/>
				</li>
			`;
		} );

		return html`<ul>${ listItems }</ul>`;
	};

	return html`
		<${ Panel }>
			<${ PanelHeader } label=${ __( 'Manage Packages', 'satispress' ) }>
				<${ Button }
					label=${ __( 'Close package inserter', 'satispress' ) }
					icon=${ closeSmallIcon }
					onClick=${ onClose }
				/>
			</${ PanelHeader }>
			<${ TabPanel } tabs=${ tabs }>
				${ tabContent }
			</${ TabPanel }>
		</${ Panel }>
	`;
}

export default PackageSelector;
