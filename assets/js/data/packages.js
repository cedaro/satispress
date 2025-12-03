import { apiFetch, data } from '../utils/index.js';

const { createReduxStore, register } = data;

const STORE_KEY = 'satispress/packages';

const DEFAULT_STATE = {
	packages: [],
	plugins: [],
	themes: [],
};

const packageExists = ( packages, slug, type ) => {
	return !! packages.filter( item => slug === item.slug && type === item.type ).length;
}

const compareByName = ( a, b ) => {
	if ( a.name < b.name ) {
		return -1;
	}

	if ( a.name > b.name ) {
		return 1;
	}

	return 0;
};

const addPackage = ( slug, type ) => async ( { dispatch, select } ) => {
	const packages = select.getPackages();

	if ( packageExists( packages, slug, type ) ) {
		return;
	}

	const result = await apiFetch( {
		path: '/satispress/v1/packages',
		method: 'POST',
		data: {
			slug,
			type,
		},
	} );

	dispatch.setPackages(
		[
			...packages,
			result
		]
	);
}

const removePackage = ( slug, type ) => async ( { dispatch, select } ) => {
	const packages = select.getPackages();

	await apiFetch( {
		path: `/satispress/v1/packages/${ slug }?type=${ type }`,
		method: 'DELETE',
	} );

	dispatch.setPackages(
		packages.filter( item => {
			return slug !== item.slug || type !== item.type;
		} )
	);
}

function setPackages( packages ) {
	return {
		type: 'SET_PACKAGES',
		packages: packages.sort( compareByName )
	};
}

function setPlugins( plugins ) {
	return {
		type: 'SET_PLUGINS',
		plugins: plugins.sort( compareByName )
	};
}

function setThemes( themes ) {
	return {
		type: 'SET_THEMES',
		themes: themes.sort( compareByName )
	};
}

const getPackages = () => async ( { dispatch, select } ) => {
	const packages = await apiFetch( { path: '/satispress/v1/packages' } );
	dispatch.setPackages( packages );
}

const getPlugins = () => async ( { dispatch, select } ) => {
	const plugins = await apiFetch( { path: '/satispress/v1/plugins?_fields=slug,name,type' } );
	dispatch.setPlugins( plugins );
}

const getThemes = () => async ( { dispatch, select } ) => {
	const themes = await apiFetch( { path: '/satispress/v1/themes?_fields=slug,name,type' } );
	dispatch.setThemes( themes );
}

const store = createReduxStore( STORE_KEY, {
	reducer( state = DEFAULT_STATE, action ) {
		switch ( action.type ) {
			case 'SET_PACKAGES' :
				return {
					...state,
					packages: action.packages,
				};

			case 'SET_PLUGINS' :
				return {
					...state,
					plugins: action.plugins,
				};

			case 'SET_THEMES' :
				return {
					...state,
					themes: action.themes,
				};
		}

		return state;
	},
	actions: {
		addPackage,
		removePackage,
		setPackages,
		setPlugins,
		setThemes,
	},
	selectors: {
		getPackages( state ) {
			return state.packages || [];
		},
		getPlugins( state ) {
			return state.plugins || [];
		},
		getThemes( state ) {
			return state.themes || [];
		},
	},
	resolvers: {
		getPackages,
		getPlugins,
		getThemes,
	}
} );

register( store );
