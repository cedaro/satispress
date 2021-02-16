import { components, element, html } from '../utils/index.js';
import ReleaseActions from './release-actions.js';

const { Button } = components;
const { Fragment, useState } = element;

const defaultRelease = {
	url: '',
	version: '',
};

function Releases( props ) {
	const {
		author,
		author_url,
		composer,
		description,
		name,
		homepage,
		releases,
		type,
	} = props;

	const [ selectedRelease, setSelectedRelease ] = useState( defaultRelease );

	const clearSelectedRelease = () => setSelectedRelease( defaultRelease );

	const { version: selectedVersion } = selectedRelease;

	const releaseButtons = releases.map( ( release, index ) => {
		const isSelected = selectedVersion === release.version;

		let className = 'button satispress-release';
		if ( isSelected ) {
			className += ' active';
		}

		const onClick = () => {
			if ( selectedVersion === release.version ) {
				clearSelectedRelease();
			} else {
				setSelectedRelease( release );
			}
		};

		return html`
			<${ Button }
				key=${ release.version }
				className=${ className }
				aria-expanded=${ isSelected }
				onClick=${ onClick }
			>
				${ release.version }
			</${ Button }>
			${ ' ' }
		`
	} );

	const releaseActions = '' !== selectedVersion && html`
		<${ ReleaseActions }
			name=${ name }
			composerName=${ composer.name }
			...${ selectedRelease }
		/>
	`;

	return html`
		<${ Fragment }>
			${ releaseButtons }
			${ releaseActions }
		</${ Fragment }
	`;
}

export default Releases;
