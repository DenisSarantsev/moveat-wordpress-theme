document.addEventListener('DOMContentLoaded', () => {

	// ------------------- Галерея фото
	const galleryEl = document.querySelector('[data-product-gallery]');
	if (!galleryEl) return;

	const mainContainer = galleryEl.querySelector('[data-gallery-main]');
	let mainImg = mainContainer ? mainContainer.querySelector('img, picture, video') : null;
	const refreshMainRef = () => {
		mainImg = mainContainer ? mainContainer.querySelector('img, picture, video') : null;
	};
	const thumbs = Array.from(galleryEl.querySelectorAll('[data-gallery-thumb]'));
	let currentIndex = 0;

	// Helper: derive a "full" image URL from a thumbnail button
	function getFullUrlFromThumb(btn) {
		if (!btn) return '';
		// 1) data-full on button
		let full = btn.getAttribute('data-full');
		if (full) return full;
		// 2) data-full on img
		const timg = btn.querySelector('img');
		if (!timg) return '';
		full = timg.getAttribute('data-full') || timg.getAttribute('data-large_image') || timg.getAttribute('data-src');
		if (full) return full;
		// 3) parent link href (if exists)
		const parentLink = timg.closest('a');
		if (parentLink && parentLink.href) return parentLink.href;
		// 4) fallback to currentSrc/src
		return (timg.currentSrc || timg.src || '');
	}

		if (mainContainer) {
			const link = mainContainer.querySelector('a');
			if (link && (link.querySelector('img') || link.querySelector('picture') || link.querySelector('video'))) {
				mainContainer.innerHTML = link.innerHTML;
				refreshMainRef();
			}
		}

		function setActive(index) {
			if (!thumbs[index]) return;
			thumbs.forEach(btn => btn.classList.remove('is-active'));
			thumbs[index].classList.add('is-active');
			const src = getFullUrlFromThumb(thumbs[index]);
			if (src && mainContainer) {
				// Prefer directly updating an <img>, otherwise replace innerHTML with a plain img
				if (mainImg && mainImg.tagName && mainImg.tagName.toLowerCase() === 'img') {
					mainImg.removeAttribute('srcset');
					mainImg.removeAttribute('sizes');
					mainImg.src = src;
				} else {
					mainContainer.innerHTML = `<img src="${src}" alt="">`;
					refreshMainRef();
				}
				// Persist current full image URL for lightbox
				mainContainer.setAttribute('data-current-full', src);
			}
			currentIndex = index;
		}

		thumbs.forEach((btn, idx) => {
			btn.addEventListener('click', () => setActive(idx));
		});

		const prevBtn = galleryEl.querySelector('[data-gallery-prev]');
		const nextBtn = galleryEl.querySelector('[data-gallery-next]');
		if (prevBtn) prevBtn.addEventListener('click', () => setActive((currentIndex - 1 + thumbs.length) % thumbs.length));
		if (nextBtn) nextBtn.addEventListener('click', () => setActive((currentIndex + 1) % thumbs.length));

		// Lightbox
 		function openLightbox() {
 			// Resolve close icon src robustly
 			const themeUri = (window.MOVEAT_THEME && window.MOVEAT_THEME.themeUri) || '';
 			let closeSrc = themeUri ? `${themeUri}/assets/images/icons/cross.png` : '';
 			if (!closeSrc) {
 				const existing = document.querySelector('.header__cross-icon');
 				if (existing && existing.src) closeSrc = existing.src;
 			}
 			if (!closeSrc) {
 				// Derive from current script path
 				const scripts = document.getElementsByTagName('script');
 				const last = scripts[scripts.length - 1];
 				if (last && last.src) {
 					try {
 						const u = new URL(last.src, window.location.href);
 						const ix = u.pathname.indexOf('/assets/');
 						if (ix !== -1) {
 							closeSrc = `${u.origin}${u.pathname.slice(0, ix)}/assets/images/icons/cross.png`;
 						}
 					} catch (e) {}
 				}
 			}
 			const closeInner = closeSrc ? `<img src="${closeSrc}" alt="Закрыть">` : '×';
			const overlay = document.createElement('div');
			overlay.className = 'product-lightbox';
			overlay.setAttribute('data-product-lightbox', '');
			overlay.setAttribute('aria-hidden', 'false');
			overlay.innerHTML = `
 				<button class="product-lightbox__close" type="button" aria-label="Закрыть" data-lightbox-close>${closeInner}</button>
				<button class="product-lightbox__arrow product-lightbox__arrow--prev" type="button" aria-label="Предыдущее фото" data-lightbox-prev>‹</button>
				<img class="product-lightbox__image" alt="Изображение товара" data-lightbox-image>
				<button class="product-lightbox__arrow product-lightbox__arrow--next" type="button" aria-label="Следующее фото" data-lightbox-next>›</button>
			`;
			document.body.appendChild(overlay);
			const lightboxImg = overlay.querySelector('[data-lightbox-image]');
			const update = () => {
				// 0) Stored current full url
				let src = mainContainer.getAttribute('data-current-full') || '';
				// 1) Try current main media
				if (!src) {
					const currentMainImg = mainContainer.querySelector('img, picture source, picture img, video, a[href]');
					if (currentMainImg) {
						const tag = currentMainImg.tagName.toLowerCase();
						if (tag === 'img') {
							src = currentMainImg.currentSrc || currentMainImg.src || '';
						} else if (tag === 'source') {
							src = currentMainImg.srcset ? currentMainImg.srcset.split(' ')[0] : '';
						} else if (tag === 'video') {
							src = currentMainImg.currentSrc || currentMainImg.src || '';
						} else if (tag === 'a') {
							src = currentMainImg.href || '';
						}
					}
				}
				// 2) Fallback to full url from active thumb
				if (!src) src = getFullUrlFromThumb(thumbs[currentIndex]);
				if (src) lightboxImg.src = src;
			};
			update();
			overlay.querySelector('[data-lightbox-close]').addEventListener('click', () => overlay.remove());
			overlay.querySelector('[data-lightbox-prev]').addEventListener('click', () => { setActive((currentIndex - 1 + thumbs.length) % thumbs.length); update(); });
			overlay.querySelector('[data-lightbox-next]').addEventListener('click', () => { setActive((currentIndex + 1) % thumbs.length); update(); });
		}
		if (mainContainer) {
			mainContainer.addEventListener('click', (e) => {
				// Prevent navigating if any anchor remains
				const a = e.target.closest('a');
				if (a) {
					e.preventDefault();
					e.stopPropagation();
				}
				openLightbox();
			});
		}
	// Инициализация первого активного
	if (thumbs.length) setActive(0);

	// ------------------- Кнопки купить сейчас и добавить в корзину
	
});

