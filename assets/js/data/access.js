import { data, dataControls } from '../utils/index.js';

const { dispatch, registerStore, select } = data;
const { apiFetch, controls } = dataControls;

const STORE_KEY = 'satispress/access';

const DEFAULT_STATE = {
	apiKeys: [],
	userId: null,
};

function* createApiKey( name, userId ) {
	const apiKeys = select( STORE_KEY ).getApiKeys();

	const result = yield apiFetch( {
		path: '/satispress/v1/apikeys',
		method: 'POST',
		data: {
			name,
			user: userId,
		},
	} );

	if ( result ) {
		return {
			type: 'SET_API_KEYS',
			apiKeys: [
				...apiKeys,
				result
			]
		};
	}
}

function* revokeApiKey( token, userId ) {
	const apiKeys = select( STORE_KEY ).getApiKeys();

	const result = yield apiFetch( {
		path: `/satispress/v1/apikeys/${ token }?user=${ userId }`,
		method: 'DELETE',
	} );

	return {
		type: 'SET_API_KEYS',
		apiKeys: apiKeys.filter( item => {
			return token !== item.token;
		} )
	};
}

function setApiKeys( apiKeys ) {
	return {
		type: 'SET_API_KEYS',
		apiKeys: apiKeys,
	};
}

function setUserId( userId ) {
	return {
		type: 'SET_USER_ID',
		userId: userId,
	};
}

function* getApiKeys() {
	const userId = select( STORE_KEY ).getUserId();
	const apiKeys = yield apiFetch( { path: `/satispress/v1/apikeys?user=${ userId }` } );
	dispatch( STORE_KEY ).setApiKeys( apiKeys );
}

const store = {
	reducer( state = DEFAULT_STATE, action ) {
		switch ( action.type ) {
			case 'SET_API_KEYS' :
				return {
					...state,
					apiKeys: action.apiKeys,
				};

			case 'SET_USER_ID' :
				return {
					...state,
					userId: action.userId,
				};
		}

		return state;
	},
	actions: {
		createApiKey,
		revokeApiKey,
		setApiKeys,
		setUserId,
	},
	selectors: {
		getApiKeys( state ) {
			return state.apiKeys || [];
		},
		getUserId( state ) {
			return state.userId || null;
		},
	},
	resolvers: {
		getApiKeys,
	},
	controls,
};

registerStore( STORE_KEY, store );
