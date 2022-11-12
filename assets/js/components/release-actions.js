import { components, element, html, i18n } from '../utils/index.js';

const { Button, TextControl } = components;
const { createElement, createInterpolateElement } = element;
const { __, sprintf } = i18n;

const selectField = ( e ) => e.nativeEvent.target.select();

function  ReleaseActions( props ) {
	const {
		composerName,
		name,
		url,
		version,
	} = props;

	const requireValue = `"${ composerName }": "${ version }"`;
	const cliCommandValue = `composer require ${ composerName }:${ version }`;

	/* translators: %s: version number */
	const buttonText = __( 'Download %s', 'satispress' );

	const copyPasteHtml = createInterpolateElement(
		__( 'Copy and paste into <code>composer.json</code>', 'satispress' ),
		{ code: createElement( 'code' ) }
	);

	return html`
		<div className="satispress-release-actions">
			<table>
				<tbody>
					<tr>
						<th scope="row">
							<label htmlFor="satispress-release-action-download-url-${ composerName }">${ __( 'Download URL', 'satispress' ) }</label>
						</th>
						<td>
							<${ TextControl }
								value=${ url }
								readOnly="readonly"
								id="satispress-release-action-download-url-${ composerName }"
								onClick=${ selectField }
							/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label htmlFor="satispress-release-action-require-${ composerName }">${ __( 'Require', 'satispress' ) }</label>
						</th>
						<td>
							<${ TextControl }
								value=${ requireValue }
								readOnly="readonly"
								id="satispress-release-action-require-${ composerName }"
								onClick=${ selectField }
							/>
							<span className="description">
								<em>${ copyPasteHtml }</em>
							</span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label htmlFor="satispress-release-action-cli-${ composerName }">${ __( 'CLI Command', 'satispress' ) }</label>
						</th>
						<td>
							<${ TextControl }
								value=${ cliCommandValue }
								readOnly="readonly"
								id="satispress-release-action-cli-${ composerName }"
								onClick=${ selectField }
							/>
						</td>
					</tr>
					<tr>
						<td colSpan="2">
							<${ Button }
								href=${ url }
								isPrimary
							>
								${ sprintf( buttonText, version ) }
							</${ Button }>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	`;
}

export default ReleaseActions;
