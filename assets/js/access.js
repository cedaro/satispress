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
		return select( 'satispress/access' ).getApiKeys();
	} );

	const selectedUserId = useSelect( ( select ) => {
		return select( 'satispress/access' ).getUserId();
	} );

	useEffect( () => {
		setUserId( userId );
	}, [ userId ] );

	return html`
		<${ AccessTable }
			apiKeys=${ apiKeys }
			userId=${ selectedUserId || userId }
			onCreateApiKey=${ ( name, selected ) => createApiKey( name, selected ) }
			onRevokeApiKey=${ revokeApiKey }
			onChangeUser=${ ( id ) => setUserId( id ) }
		/>
	`;
}

const root = createRoot( document.getElementById( 'satispress-api-key-manager' ) );
root.render( html`<${ App } userId=${ editedUserId } />` );
