import './modules/carousel-init.js';
import './modules/spinner.js';
import './modules/system-message.js';
import './woocommerce/add-to-cart.js';
import { createWooApiLayer } from './woocommerce/api/index.js';

window.MOVEAT_API = window.MOVEAT_API || {};
window.MOVEAT_API.woocommerce = createWooApiLayer({
	checkoutUrl: (window.MOVEAT_THEME && window.MOVEAT_THEME.checkoutUrl) || '/checkout/',
});