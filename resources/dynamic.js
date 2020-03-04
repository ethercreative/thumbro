window.thumbro = function () {
	// Images
	function visible (p) {
		const b = p.getBoundingClientRect();

		return b.top <= (window.innerHeight || document.documentElement.clientHeight);
	}

	function showImage (p) {
		const l = p.lastElementChild;
		l.setAttribute('src', l.dataset.src);
		l.setAttribute('srcset', l.dataset.srcset);
		l.removeAttribute('data-src');
		l.removeAttribute('data-srcset');
	}

	const images = document.querySelectorAll('picture');

	const ioImages = new IntersectionObserver((entries, observer) => {
		for (let p of entries) {
			if (p.intersectionRatio <= 0)
				continue;

			const t = p.target;

			observer.unobserve(t);
			showImage(t);
		}
	}, {
		rootMargin: '20px 0px',
		threshold: 0.01,
	});

	for (let i = 0, l = images.length; i < l; i++) {
		const p = images[i];

		const o = document.createElement('div');
		const n = p.querySelector('noscript');

		if (!n)
			continue;

		o.innerHTML = n.textContent;

		const g = o.firstElementChild;
		g.style.opacity = 0;
		g.setAttribute('data-src', g.getAttribute('src'));
		g.setAttribute('data-srcset', g.getAttribute('srcset'));
		g.removeAttribute('src');
		g.removeAttribute('srcset');

		g.addEventListener('load', () => {
			g.removeAttribute('style');
		});

		p.appendChild(g);

		// Delay immediate load to allow fonts to load first
		if (visible(p)) setTimeout(() => showImage(p), 150);
		else ioImages.observe(p);
	}
};
window.thumbro();
