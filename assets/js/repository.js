import { components, data, element, html, i18n } from './utils/index.js';
import Repository from './components/repository.js';
import PackageSelector from './components/package-selector.js';
import Sidebar from './components/sidebar.js';
import { closeSmallIcon } from './components/icons.js';
import './data/packages.js';

const { Button } = components;
const { dispatch, useSelect } = data;
const { Fragment, render, useEffect, useState } = element;
const { __ } = i18n;

const { addPackage, removePackage } = dispatch( 'satispress/packages' );

const bodyEl = document.body;
const sidebarEl = document.getElementById( 'satispress-screen-sidebar' );

function App() {
	const packages = useSelect( select => select( 'satispress/packages' ).getPackages() );

	const [ isSidebarOpen, setSidebarStatus ] = useState( false );
	const closeSidebar = () => setSidebarStatus( false );
	const openSidebar = () => setSidebarStatus( true );
	const toggleSidebar = () => setSidebarStatus( ! isSidebarOpen );

	useEffect( () => {
		window.addEventListener( 'hashchange', closeSidebar );
		bodyEl.classList.toggle( 'sidebar-is-open', isSidebarOpen );

		return () => {
			window.removeEventListener( 'hashchange', closeSidebar );
		};
	} );

	return html`
		<${ Fragment }>
			${ !! packages.length && html`
				<${ Button }
					isSecondary
					isPressed=${ isSidebarOpen }
					icon=${ isSidebarOpen && closeSmallIcon }
					onClick=${ toggleSidebar }
				>
					${ __( 'Manage Packages', 'satispress' ) }
				</${ Button }>`
			}
			<${ Repository }
				packages=${ packages }
				onButtonClick=${ openSidebar }
			/>
			${ isSidebarOpen && html`
				<${ Sidebar } root=${ sidebarEl }>
					<${ PackageSelector }
						packages=${ packages }
						onAddPackage=${ addPackage }
						onRemovePackage=${ removePackage }
						onClose=${ closeSidebar }
					/>
				</${ Sidebar }>`
			}
		</${ Fragment }>
	`;
}

render(
	html`<${ App } />`,
	document.getElementById( 'satispress-repository' )
);
