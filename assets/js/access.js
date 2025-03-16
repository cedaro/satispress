import { data, element, html } from './utils/index.js';
import AccessTable from './components/access-table.js';
import './data/access.js';

const { useDispatch, useSelect } = data;
const { createRoot, useEffect } = element;

const { editedUserId } = _satispressAccessData;

function App( { userId } ) {
	const {
		createApiKey,
		setUserId,
		revokeApiKey,
	} = useDispatch( 'satispress/access' );

	const apiKeys = useSelect( ( select ) => {
		return select( 'satispress/access' ).getApiKeys()
	} );

	useEffect( () => {
		setUserId( userId );
	}, [ userId ] );

	return html`
		<${ AccessTable }
			apiKeys=${ apiKeys }
			userId=${ userId }
			onCreateApiKey=${ ( name ) => createApiKey( name, userId ) }
			onRevokeApiKey=${ revokeApiKey }
		/>
	`;
}

const root = createRoot( document.getElementById( 'satispress-api-key-manager' ) );
root.render( html`<${ App } userId=${ editedUserId } />` );
