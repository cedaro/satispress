import { html, i18n } from '../utils/index.js';
import Releases from './releases.js';

const { __ } = i18n;

function PackageTable( props ) {
	const {
		author,
		author_url,
		composer,
		description,
		name,
		homepage,
		releases,
		slug,
		type,
	} = props;

	return html`
		<table className="satispress-package widefat">
			<thead>
				<tr>
					<th colSpan="2">${ composer.name }</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colSpan="2">${ description }</td>
				</tr>
				<tr>
					<th>${ __( 'Name', 'satispress' ) }</th>
					<td>${ name }</td>
				</tr>
				<tr>
					<th>${ __( 'Homepage', 'satispress' ) }</th>
					<td><a href="${ homepage }" target="_blank" rel="noopener noreferer">${ homepage }</a></td>
				</tr>
				<tr>
					<th>${ __( 'Authors', 'satispress' ) }</th>
					<td><a href="${ author_url }" target="_blank" rel="noopener noreferer">${ author }</a></td>
				</tr>
				<tr>
					<th>${ __( 'Releases', 'satispress' ) }</th>
					<td className="satispress-releases">
						<${ Releases } releases=${ releases } ...${ props } />
					</td>
				</tr>
				<tr>
					<th>${ __( 'Package Type', 'satispress' ) }</th>
					<td><code>${ composer.type }</code></td>
				</tr>
			</tbody>
		</table>
	`;
};

export default PackageTable;
