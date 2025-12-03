import htm from '../vendor/htm.module.js';

export const { apiFetch, components, data, element, i18n } = wp;

export const html = htm.bind( React.createElement );

export const noop = () => {};
