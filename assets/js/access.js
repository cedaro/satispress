import { data, element, html } from './utils/index.js';
import AccessTable from './components/access-table.js';
import './data/access.js';

const { useDispatch, useSelect } = data;
const { render } = element;

const { editedUserId } = _satispressAccessData;

function App( props ) {
	const { userId } = props;

	const {
		createApiKey,
		setUserId,
		revokeApiKey,
	} = useDispatch( 'satispress/access' );

	setUserId( userId );

	const apiKeys = useSelect( ( select ) => {
		return select( 'satispress/access' ).getApiKeys()
	} );

	return html`
		<${ AccessTable }
			apiKeys=${ apiKeys }
			userId=${ userId }
			onCreateApiKey=${ ( name ) => createApiKey( name, userId ) }
			onRevokeApiKey=${ revokeApiKey }
		/>
	`;
}

render(
	html`<${ App } userId=${ editedUserId } />`,
	document.getElementById( 'satispress-api-key-manager' )
);
