import { components, html, i18n } from '../utils/index.js';
import PackageTable from './package-table.js';

const { Button, Placeholder } = components;
const { __ } = i18n;

function RepositoryPlaceholder( props ) {
	return html`
		<${ Placeholder }
			label=${ __( 'Add Packages', 'satispress' ) }
			instructions=${ __( 'Get started by adding plugins and themes to your local repository. Packages in your repositority will be available for you to install with Composer.', 'satispress' ) }
		>
			<${ Button }
				isPrimary
				onClick=${ props.onButtonClick }
			>
				${ __( 'Add Packages', 'satispress' ) }
			</${ Button }>
		</${ Placeholder }>
	`;
}

function Repository( props ) {
	if ( ! props.packages.length ) {
		return html`
			<${ RepositoryPlaceholder } onButtonClick=${ props.onButtonClick } />
		`;
	}

	return props.packages.map( ( item, index ) =>
		html`<${ PackageTable } key=${ item.name } ...${ item } />`
	);
}

export default Repository;
