import { data, dataControls } from '../utils/index.js';

const { dispatch, registerStore, select } = data;
const { apiFetch, controls } = dataControls;

const STORE_KEY = 'satispress/packages';

const DEFAULT_STATE = {
	packages: [],
	plugins: [],
	themes: [],
};

const packageExists = ( slug, type ) => {
	const packages = select( STORE_KEY ).getPackages();

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

function* addPackage( slug, type ) {
	const packages = select( STORE_KEY ).getPackages();

	if ( packageExists( slug, type ) ) {
		return;
	}

	const result = yield apiFetch( {
		path: '/satispress/v1/packages',
		method: 'POST',
		data: {
			slug,
			type,
		},
	} );

	if ( result ) {
		return {
			type: 'SET_PACKAGES',
			packages: [
				...packages,
				result
			].sort( compareByName )
		};
	}
}

function* removePackage( slug, type ) {
	const packages = select( STORE_KEY ).getPackages();

	const result = yield apiFetch( {
		path: `/satispress/v1/packages/${ slug }?type=${ type }`,
		method: 'DELETE',
	} );

	return {
		type: 'SET_PACKAGES',
		packages: packages.filter( item => {
			return slug !== item.slug || type !== item.type;
		} )
	};
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

function* getPackages() {
	const packages = yield apiFetch( { path: '/satispress/v1/packages' } );
	dispatch( STORE_KEY ).setPackages( packages.sort( compareByName ) );
}

function* getPlugins() {
	const plugins = yield apiFetch( { path: '/satispress/v1/plugins?_fields=slug,name,type' } );
	dispatch( STORE_KEY ).setPlugins( plugins );
}

function* getThemes() {
	const themes = yield apiFetch( { path: '/satispress/v1/themes?_fields=slug,name,type' } );
	dispatch( STORE_KEY ).setThemes( themes );
}

const store = {
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
	},
	controls,
};

registerStore( STORE_KEY, store );
