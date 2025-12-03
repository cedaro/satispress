import { apiFetch, data } from '../utils/index.js';

const { createReduxStore, register } = data;

const STORE_KEY = 'satispress/access';

const DEFAULT_STATE = {
	apiKeys: [],
	userId: null,
};

const createApiKey = ( name, userId ) => async ( { dispatch, select } ) => {
	const apiKeys = select.getApiKeys();

	const result = await apiFetch( {
		path: '/satispress/v1/apikeys',
		method: 'POST',
		data: {
			name,
			user: userId,
		},
	} )

	dispatch.setApiKeys( [
		...apiKeys,
		result
	] );
}

const revokeApiKey = ( token, userId ) => async ( { dispatch, select } ) => {
	apiFetch( {
		path: `/satispress/v1/apikeys/${ token }?user=${ userId }`,
		method: 'DELETE',
	} );

	const apiKeys = select.getApiKeys().filter( item => {
		return token !== item.token;
	} );

	dispatch.setApiKeys( apiKeys );
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

const store = createReduxStore( STORE_KEY, {
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
		getApiKeys: () => async ( { dispatch, select } ) => {
			const userId = select.getUserId();
			const apiKeys = await apiFetch( { path: `/satispress/v1/apikeys?user=${ userId }` } );
			dispatch.setApiKeys( apiKeys );
		},
	},
} );

register( store );
