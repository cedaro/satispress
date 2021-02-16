import { element, html } from '../utils/index.js';

const { createPortal } = element;

// @todo https://developer.wordpress.org/block-editor/components/scroll-lock/

function Sidebar( props ) {
	return createPortal(
		props.children,
		props.root
	);
}

export default Sidebar;
