/*
	Проверка незавершённой оплаты при возврате через «Назад» (bfcache).
	Серверный template_redirect в этом случае не вызывается — дублируем логику на клиенте.
*/

const PENDING_COOKIE_NAME = "moveat_pending_order";

const getConfig = () => window.MOVEAT_WOO_API_CONFIG ?? {};

const parsePendingOrderCookie = () => {
	const escaped = PENDING_COOKIE_NAME.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
	const match = document.cookie.match(
		new RegExp(`(?:^|;\\s*)${escaped}=([^;]*)`),
	);

	if (!match?.[1]) {
		return null;
	}

	try {
		const data = JSON.parse(decodeURIComponent(match[1].trim()));
		const orderId = Number.parseInt(String(data?.order_id ?? ""), 10);
		const orderKey = String(data?.order_key ?? "").trim();

		if (!orderId || !orderKey) {
			return null;
		}

		return { order_id: orderId, order_key: orderKey };
	} catch {
		return null;
	}
};

const normalizePath = (pathname) => {
	const path = pathname.replace(/\/+$/, "") || "/";
	return path;
};

const pathEndsWithSlug = (pathname, slug) => {
	const path = normalizePath(pathname);
	const normalizedSlug = slug.replace(/^\/+|\/+$/g, "");

	return path === `/${normalizedSlug}` || path.endsWith(`/${normalizedSlug}`);
};

const buildUrlWithOrderArgs = (baseUrl, pending) => {
	const url = new URL(baseUrl, window.location.origin);
	url.searchParams.set("order_id", String(pending.order_id));
	url.searchParams.set("order_key", pending.order_key);
	return url.toString();
};

const runPendingOrderRedirect = () => {
	const pending = parsePendingOrderCookie();
	if (!pending) {
		return;
	}

	const config = getConfig();
	const thankYouUrl =
		config.thankYouUrl ?? `${window.location.origin}/stranicza-spasibo/`;
	const payProblemSlug = "pay-problem";
	const thankYouSlug = "stranicza-spasibo";
	const pathname = window.location.pathname;

	if (pathEndsWithSlug(pathname, payProblemSlug)) {
		return;
	}

	if (pathEndsWithSlug(pathname, thankYouSlug)) {
		window.location.reload();
		return;
	}

	window.location.replace(buildUrlWithOrderArgs(thankYouUrl, pending));
};

export const initPendingOrderRedirect = () => {
	let redirectQueued = false;

	const queuePendingOrderRedirect = () => {
		if (redirectQueued) {
			return;
		}

		redirectQueued = true;
		requestAnimationFrame(() => {
			redirectQueued = false;
			runPendingOrderRedirect();
		});
	};

	window.addEventListener("pageshow", (event) => {
		if (event.persisted) {
			queuePendingOrderRedirect();
		}
	});

	// «Назад»/«Вперёд» — в части браузеров без event.persisted
	window.addEventListener("popstate", () => {
		queuePendingOrderRedirect();
	});
};
