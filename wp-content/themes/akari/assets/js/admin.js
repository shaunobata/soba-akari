/**
 * akari admin.js
 * 管理画面の入力補助：字数カウンタ、価格の全角→半角、画像の選択（メディアから1枚）。
 * 保存時の検証はサーバー側（akari-core/fields.php）で行う。ここは入力中の目安だけ。
 */
(function () {
	'use strict';

	var settings = window.akariAdmin || {};

	/* 字数カウンタ：data-max を持つ入力欄と、品名・タイトル（#title） */
	var attachCounter = function (input, max) {
		if (!input || !max || input.dataset.akariCounter) {
			return;
		}
		input.dataset.akariCounter = '1';
		var counter = document.createElement('p');
		counter.className = 'akari-counter';
		counter.setAttribute('aria-live', 'polite');
		input.insertAdjacentElement('afterend', counter);
		var update = function () {
			var length = Array.from(input.value.replace(/\n/g, '')).length;
			counter.textContent = length + ' / ' + max + '字';
			counter.classList.toggle('is-over', length > max);
		};
		input.addEventListener('input', update);
		update();
	};
	document.querySelectorAll('[data-max]').forEach(function (el) {
		attachCounter(el, parseInt(el.dataset.max, 10));
	});
	if (settings.titleMax) {
		attachCounter(document.getElementById('title'), settings.titleMax);
	}

	/* 価格：全角数字を半角に直し、「円」「,」などを削る */
	document.querySelectorAll('[data-price]').forEach(function (input) {
		input.addEventListener('blur', function () {
			input.value = input.value
				.replace(/[０-９]/g, function (c) {
					return String.fromCharCode(c.charCodeAt(0) - 0xfee0);
				})
				.replace(/[^0-9]/g, '');
		});
	});

	/* 画像の選択 */
	document.querySelectorAll('[data-akari-image]').forEach(function (wrap) {
		var input = wrap.querySelector('input[type="hidden"]');
		var preview = wrap.querySelector('.akari-image__preview');
		var removeBtn = wrap.querySelector('[data-akari-image-remove]');
		var frame = null;

		wrap.querySelector('[data-akari-image-select]').addEventListener('click', function () {
			if (!window.wp || !wp.media) {
				return;
			}
			if (!frame) {
				frame = wp.media({
					title: '画像を選ぶ',
					button: { text: 'この画像を使う' },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function () {
					var file = frame.state().get('selection').first().toJSON();
					var src = (file.sizes && file.sizes.thumbnail ? file.sizes.thumbnail : file).url;
					input.value = file.id;
					preview.innerHTML = '';
					var img = document.createElement('img');
					img.src = src;
					img.alt = '';
					preview.appendChild(img);
					removeBtn.hidden = false;
				});
			}
			frame.open();
		});

		removeBtn.addEventListener('click', function () {
			input.value = '';
			preview.innerHTML = '';
			removeBtn.hidden = true;
		});
	});
})();
