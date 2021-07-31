import { components, data, element, html, i18n } from '../utils/index.js';
import { closeSmallIcon } from './icons.js';

const { Button, CheckboxControl, Panel, PanelHeader, TabPanel, TextControl } = components;
const { useSelect } = data;
const { useState } = element;
const { __ } = i18n;

function PackageSelector( props ) {
	const {
		onAddPackage,
		onRemovePackage,
		onClose,
		packages,
	} = props;

	const [ searchTerm, setSearchTerm ] = useState( '' );

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
		const searchTerms = searchTerm.toLowerCase().split(' ').filter( searchTerm => searchTerm );
		const filteredItems = searchTerms.length === 0 ? items : items.filter( item => {
			return searchTerms.some( searchTerm => {
				const slugMatch = item.slug.toLowerCase().includes( searchTerm );
				const nameMatch = item.name.toLowerCase().includes( searchTerm );
				const authorMatch = item.author.toLowerCase().includes( searchTerm );
				return slugMatch || nameMatch || authorMatch;
			} );
		} );

		const listItems = filteredItems.map( item => {
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

		return html`<div>
				<${ TextControl }
					label=${ __( 'Search', 'satispress' ) + ' ' + tab.title }
					hideLabelFromVision
					placeholder=${ __( 'Search', 'satispress' ) + ' ' + tab.title }
					onChange=${ setSearchTerm }
					value=${ searchTerm }
				/>
			<ul>${ listItems }</ul>
		</div>`;
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
