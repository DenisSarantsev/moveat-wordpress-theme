/* 
	Модуль показывает системные уведомления в едином контейнере с разными статусами. 
	Шаблон сообщения для отображения берется из верстки файла header.php
	Через 5 секунд после показа сообщение автоматически скрывается.
	Сама функция закрывания сообщения по клику находится в проекте на Vite с версткой проекта.
	Функция универсальная и не требует размещения в теме WordPress.
*/

const CONTAINER_SELECTOR = '.messages-container';
const MESSAGE_SELECTOR = '.messages-container__message';
const MESSAGE_TEXT_SELECTOR = '.messages-container__message-text';
const AUTO_HIDE_DELAY = 5000; // Время задержки перед автоматическим пропаданием
const STATUS_CLASSES = [
	'messages-container__message--success',
	'messages-container__message--error',
	'messages-container__message--info',
	'messages-container__message--warning',
];

let messageTemplate = null;

// Нормализует входной статус и возвращает допустимое значение.
const normalizeStatus = (status) => {
	const allowed = ['success', 'error', 'info', 'warning'];
	return allowed.includes(status) ? status : 'info';
};

// Возвращает шаблон сообщения и удаляет статическое демо-сообщение из контейнера.
const getMessageTemplate = () => {
	if (messageTemplate) return messageTemplate;

	const container = document.querySelector(CONTAINER_SELECTOR);
	if (!container) return null;

	const initialMessage = container.querySelector(MESSAGE_SELECTOR);
	if (!initialMessage) return null;

	messageTemplate = initialMessage.cloneNode(true);
	initialMessage.remove();
	return messageTemplate;
};

// Плавно скрывает сообщение и удаляет его из DOM.
const removeMessage = (messageEl) => {
	if (!messageEl) return;
	messageEl.classList.remove('is-visible');
	window.setTimeout(() => {
		if (messageEl && messageEl.parentNode) {
			messageEl.remove();
		}
	}, 250);
};

// Показывает уведомление с заданным статусом и текстом.
export const showSystemMessage = (status, text) => {
	const container = document.querySelector(CONTAINER_SELECTOR);
	const template = getMessageTemplate();
	if (!container || !template) return;

	const messageEl = template.cloneNode(true);
	const messageTextEl = messageEl.querySelector(MESSAGE_TEXT_SELECTOR);
	const normalizedStatus = normalizeStatus(String(status || '').toLowerCase());
	messageEl.classList.remove('is-template');

	STATUS_CLASSES.forEach((className) => messageEl.classList.remove(className));
	messageEl.classList.add(`messages-container__message--${normalizedStatus}`);

	if (messageTextEl) {
		messageTextEl.textContent = String(text || '');
	}

	// Новые сообщения добавляются вниз списка, старые остаются сверху.
	container.appendChild(messageEl);
	messageEl.classList.add('is-visible');

	window.setTimeout(() => removeMessage(messageEl), AUTO_HIDE_DELAY);
};

window.MOVEAT_SYSTEM_MESSAGE = {
	show: showSystemMessage,
};
