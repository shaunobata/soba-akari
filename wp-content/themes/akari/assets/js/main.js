/**
 * akari main.js（詳細設計書9.6）
 * 役割はハンバーガーの開閉、固定バーの表示切り替え、スクロールでの表示、地図の遅延読み込み（＋よくある質問の初期状態）。
 * 計測はGTMが data-track を拾うので、ここでは何もしない。
 */
(function () {
	'use strict';

	/* ハンバーガーメニュー（U-03）
	   JSが動かない環境では #footer-nav へのページ内リンクとして働く。 */
	var toggle = document.querySelector('[data-menu-toggle]');
	var panel = document.querySelector('[data-menu-panel]');
	if (toggle && panel) {
		var closeBtn = panel.querySelector('[data-menu-close]');
		toggle.setAttribute('role', 'button');
		toggle.removeAttribute('href');
		toggle.setAttribute('tabindex', '0');

		var focusables = function () {
			return Array.prototype.slice.call(panel.querySelectorAll('a[href], button'));
		};
		var open = function () {
			panel.hidden = false;
			toggle.setAttribute('aria-expanded', 'true');
			document.body.classList.add('is-menu-open');
			(closeBtn || focusables()[0]).focus();
		};
		var close = function () {
			panel.hidden = true;
			toggle.setAttribute('aria-expanded', 'false');
			document.body.classList.remove('is-menu-open');
			toggle.focus();
		};

		toggle.addEventListener('click', function (e) {
			e.preventDefault();
			open();
		});
		toggle.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				open();
			}
		});
		if (closeBtn) {
			closeBtn.addEventListener('click', close);
		}
		panel.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				close();
				return;
			}
			if (e.key !== 'Tab') {
				return;
			}
			// フォーカスをパネルの中で循環させる
			var items = focusables();
			var first = items[0];
			var last = items[items.length - 1];
			if (e.shiftKey && document.activeElement === first) {
				e.preventDefault();
				last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault();
				first.focus();
			}
		});
		// パネル内のページ内リンクを押したら閉じる
		panel.addEventListener('click', function (e) {
			if (e.target.closest('a[href^="#"]')) {
				close();
			}
		});
		// 1024px以上に広げたら閉じる
		window.matchMedia('(min-width: 1024px)').addEventListener('change', function (mq) {
			if (mq.matches && !panel.hidden) {
				close();
			}
		});
	}

	/* 固定バー（U-04）：入力欄にフォーカスしている間と、トップでヒーローの電話・ネット予約が見えている間は隠す。
	   隠すのは transform だけで、body の下の余白は残す。 */
	var bar = document.querySelector('.fixed-cta');
	if (bar) {
		var hiddenBy = { field: false, hero: false };
		var updateBar = function () {
			bar.classList.toggle('is-hidden', hiddenBy.field || hiddenBy.hero);
		};
		var isField = function (el) {
			return el && /^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName);
		};
		document.addEventListener('focusin', function (e) {
			if (isField(e.target)) {
				hiddenBy.field = true;
				updateBar();
			}
		});
		document.addEventListener('focusout', function (e) {
			if (isField(e.target)) {
				hiddenBy.field = false;
				updateBar();
			}
		});
		var heroCta = document.querySelector('.hero .cta');
		if (heroCta && 'IntersectionObserver' in window) {
			new IntersectionObserver(function (entries) {
				hiddenBy.hero = entries[0].isIntersecting;
				updateBar();
			}).observe(heroCta);
		}
	}

	/* スクロールで一度だけ表示（見出し・写真）。最初から画面内にあるものと、動きを減らす設定では何もしない。 */
	if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		var revealIo = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-shown');
					revealIo.unobserve(entry.target);
				}
			});
		}, { rootMargin: '0px 0px -40px 0px' });
		document.querySelectorAll('.site-main .section-title, .site-main .split__media, .pick, .daynight__card, .course-card').forEach(function (el) {
			if (el.getBoundingClientRect().top > window.innerHeight) {
				el.classList.add('reveal');
				revealIo.observe(el);
			}
		});
	}

	/* 地図の遅延読み込み（F-07、9.7）。枠が画面の200px手前に来たら iframe を差し込む。 */
	var box = document.querySelector('[data-map]');
	if (box && 'IntersectionObserver' in window) {
		new IntersectionObserver(function (entries, io) {
			if (!entries[0].isIntersecting) {
				return;
			}
			io.disconnect();
			var iframe = document.createElement('iframe');
			iframe.src = box.getAttribute('data-map');
			iframe.title = '手打そば 燈りの地図';
			iframe.loading = 'lazy';
			iframe.referrerPolicy = 'no-referrer-when-downgrade';
			var loaded = false;
			iframe.addEventListener('load', function () {
				loaded = true;
			});
			box.appendChild(iframe);
			// 読み込めなかったら iframe を外し、文言とリンクを残す
			setTimeout(function () {
				if (loaded) {
					return;
				}
				iframe.remove();
				var err = box.querySelector('.map__error');
				if (err) {
					err.hidden = false;
				}
			}, 15000);
		}, { rootMargin: '200px' }).observe(box);
	}

	/* よくある質問：SPでは閉じた状態、PCでは開いた状態（JSが動かない環境ではすべて開いたまま） */
	if (window.matchMedia('(max-width: 767px)').matches) {
		document.querySelectorAll('[data-faq]').forEach(function (d) {
			d.open = false;
		});
	}
})();
