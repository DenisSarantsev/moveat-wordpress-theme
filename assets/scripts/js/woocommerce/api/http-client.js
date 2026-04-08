/* 
	Файл реализует HTTP-клиент для вызова Woo/API endpoint-ов с единым форматом ошибок. 
*/

// Класс ошибки API с кодом ответа и полезной нагрузкой.
export class WooApiError extends Error {
	constructor(message, status = 0, payload = null) {
		super(message);
		this.name = 'WooApiError';
		this.status = status;
		this.payload = payload;
	}
}

// Читает runtime-конфиг из глобальной переменной браузера.
function readRuntimeConfig() {
	const runtime = window.MOVEAT_WOO_API_CONFIG || {};
	return {
		baseUrl: runtime.baseUrl || window.location.origin,
		nonce: runtime.nonce || '',
		storeApiNonce: runtime.storeApiNonce || '',
		defaultHeaders: runtime.defaultHeaders || {},
	};
}

// Создает HTTP-клиент с методами GET/POST/PUT/PATCH/DELETE.
export function createWooHttpClient(config = {}) {
	const runtime = readRuntimeConfig();
	const merged = { ...runtime, ...config };

	// Выполняет запрос и возвращает JSON либо выбрасывает WooApiError.
	async function request(method, path, body, options = {}) {
		const isBodyDefined = typeof body !== 'undefined';
		const headers = {
			Accept: 'application/json',
			...merged.defaultHeaders,
			...(options.headers || {}),
		};

		if (merged.nonce) {
			headers['X-WP-Nonce'] = merged.nonce;
		}
		if (merged.storeApiNonce) {
			headers.Nonce = merged.storeApiNonce;
		}
		if (isBodyDefined && !(body instanceof FormData)) {
			headers['Content-Type'] = 'application/json';
		}

		const response = await fetch(`${merged.baseUrl}${path}`, {
			method,
			credentials: 'same-origin',
			headers,
			body: isBodyDefined ? (body instanceof FormData ? body : JSON.stringify(body)) : undefined,
		});

		const text = await response.text();
		let payload = null;
		try {
			payload = text ? JSON.parse(text) : null;
		} catch (error) {
			payload = text || null;
		}

		if (!response.ok) {
			const message =
				(payload && (payload.message || payload.error || payload.code)) ||
				`Request failed: ${method} ${path}`;
			throw new WooApiError(message, response.status, payload);
		}

		return payload;
	}

	return {
		// Выполняет GET-запрос.
		get(path, options) {
			return request('GET', path, undefined, options);
		},
		// Выполняет POST-запрос.
		post(path, body, options) {
			return request('POST', path, body, options);
		},
		// Выполняет PUT-запрос.
		put(path, body, options) {
			return request('PUT', path, body, options);
		},
		// Выполняет PATCH-запрос.
		patch(path, body, options) {
			return request('PATCH', path, body, options);
		},
		// Выполняет DELETE-запрос.
		delete(path, body, options) {
			return request('DELETE', path, body, options);
		},
	};
}
