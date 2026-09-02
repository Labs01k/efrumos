/* ==================================================================
   Новая карточка товара: интерактив блоков по макету Figma.
   Галерея, палитра оттенков (с поиском и сменой без перезагрузки),
   выбор города, наличие по магазинам, комплект, слайдер похожих,
   мобильная верхняя панель.
   ================================================================== */
(function () {
    'use strict';

    /* ------------------------------------------------------------ галерея */

    function initGallery(scope) {
        var galleries = (scope || document).querySelectorAll('[data-pb-gallery]');

        Array.prototype.forEach.call(galleries, function (root) {
            if (root.dataset.pbReady) return;
            root.dataset.pbReady = '1';

            var track = root.querySelector('[data-pb-track]');
            if (!track) return;

            var slides = track.querySelectorAll('.pb-gallery-slide');
            var thumbs = root.querySelectorAll('.pb-gallery-thumb');
            var thumbsList = root.querySelector('[data-pb-thumbs]');
            var dotsBox = root.querySelector('[data-pb-dots]');
            var current = 0;

            if (dotsBox) {
                for (var i = 0; i < slides.length; i++) {
                    var dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'pb-gallery-dot' + (i === 0 ? ' is-active' : '');
                    dot.dataset.pbGo = i;
                    dotsBox.appendChild(dot);
                }
            }

            // стрелки прокрутки превью нужны, только если превью больше четырёх
            if (thumbs.length <= 4) {
                Array.prototype.forEach.call(
                    root.querySelectorAll('[data-pb-thumbs-prev], [data-pb-thumbs-next]'),
                    function (nav) { nav.hidden = true; }
                );
            }

            function setActive(index) {
                current = Math.max(0, Math.min(index, slides.length - 1));

                Array.prototype.forEach.call(thumbs, function (thumb, i) {
                    thumb.classList.toggle('is-active', i === current);
                });
                if (dotsBox) {
                    Array.prototype.forEach.call(dotsBox.children, function (dot, i) {
                        dot.classList.toggle('is-active', i === current);
                    });
                }
                if (thumbsList && thumbs[current]) {
                    var top = thumbs[current].offsetTop - thumbsList.offsetTop;
                    if (top < thumbsList.scrollTop || top + thumbs[current].offsetHeight > thumbsList.scrollTop + thumbsList.clientHeight) {
                        thumbsList.scrollTo({ top: top, behavior: 'smooth' });
                    }
                }
            }

            function goTo(index) {
                index = Math.max(0, Math.min(index, slides.length - 1));
                track.scrollTo({ left: index * track.clientWidth, behavior: 'smooth' });
                setActive(index);
            }

            var scrollRaf = null;
            track.addEventListener('scroll', function () {
                if (scrollRaf) return;
                scrollRaf = requestAnimationFrame(function () {
                    scrollRaf = null;
                    setActive(Math.round(track.scrollLeft / track.clientWidth));
                });
            });

            root.addEventListener('click', function (event) {
                var go = event.target.closest('[data-pb-go]');
                if (go) { goTo(parseInt(go.dataset.pbGo, 10)); return; }
                if (event.target.closest('[data-pb-prev]')) { goTo(current - 1); return; }
                if (event.target.closest('[data-pb-next]')) { goTo(current + 1); return; }
                if (event.target.closest('[data-pb-thumbs-prev]') && thumbsList) {
                    thumbsList.scrollTo({ top: thumbsList.scrollTop - 92, behavior: 'smooth' });
                    return;
                }
                if (event.target.closest('[data-pb-thumbs-next]') && thumbsList) {
                    thumbsList.scrollTo({ top: thumbsList.scrollTop + 92, behavior: 'smooth' });
                }
            });
        });
    }

    /* ------------------------------------- палитра оттенков (п.6 ТЗ) */

    function initShadeSelect(scope) {
        var roots = (scope || document).querySelectorAll('[data-shade-select]');

        Array.prototype.forEach.call(roots, function (root) {
            if (root.dataset.pbReady) return;
            root.dataset.pbReady = '1';

            var trigger = root.querySelector('.pb-shade-trigger');
            var search = root.querySelector('.pb-shade-search');
            var rows = root.querySelectorAll('.pb-dropdown > li');
            var empty = root.querySelector('.pb-dropdown-empty');

            function open() {
                root.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                if (search) {
                    search.value = '';
                    filter('');
                    search.focus();
                }
            }

            function close() {
                root.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }

            function filter(query) {
                var q = query.trim().toLowerCase();
                var found = 0;

                Array.prototype.forEach.call(rows, function (row) {
                    var item = row.querySelector('.pb-dropdown-item');
                    if (!item) return; // строка «не найдено»
                    var haystack = ((item.dataset.code || '') + ', ' + (item.dataset.name || '')).toLowerCase();
                    var match = !q || haystack.indexOf(q) !== -1;
                    row.hidden = !match;
                    if (match) found++;
                });
                if (empty) empty.hidden = found > 0;
            }

            trigger.addEventListener('click', function (event) {
                if (event.target === search) return;
                root.classList.contains('is-open') ? close() : open();
            });

            trigger.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    if (event.target === search) return;
                    event.preventDefault();
                    root.classList.contains('is-open') ? close() : open();
                }
            });

            if (search) {
                search.addEventListener('input', function () { filter(search.value); });
            }

            root.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') close();
            });

            document.addEventListener('click', function (event) {
                if (!root.contains(event.target)) close();
            });

            // выбор оттенка: подменяем данные товара без перезагрузки (п.6 ТЗ)
            Array.prototype.forEach.call(root.querySelectorAll('.pb-dropdown-item'), function (item) {
                item.addEventListener('click', function (event) {
                    if (item.classList.contains('is-selected')) { event.preventDefault(); close(); return; }
                    event.preventDefault();
                    close();
                    swapProduct(item.href, true);
                });
            });
        });
    }

    /* ------------------------------------------------------------------
       Смена оттенка без перезагрузки: тянем страницу оттенка, подменяем
       галерею, заголовок, цену, кнопки, характеристики и адрес страницы.
       Любое расхождение разметки — честный переход по ссылке.
       ------------------------------------------------------------------ */

    var SWAP_TARGETS = [
        '[data-pb-gallery]',
        '.product-end-content-inner',
        '.pb-volume-field',
        '.pb-shade',
        '.product-end-price',
        '.pb-cta-row',
        '.product-end-link',
        '.pb-product-info',
        '.pb-product-stock',
        '.pb-bar--top',
        '.pb-bar--bottom',
        '.breadcrumbs-wrapper'
    ];

    var lastPath = window.location.pathname;

    function swapProduct(url, push) {
        var gallery = document.querySelector('[data-pb-gallery]');
        if (gallery) gallery.classList.add('is-switching');

        fetch(url, { headers: { 'X-Requested-With': 'fetch' } })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var pairs = [];

                for (var i = 0; i < SWAP_TARGETS.length; i++) {
                    var cur = document.querySelector(SWAP_TARGETS[i]);
                    var next = doc.querySelector(SWAP_TARGETS[i]);
                    if (!cur && !next) continue;
                    if (!cur || !next) throw new Error('markup mismatch: ' + SWAP_TARGETS[i]);
                    pairs.push([cur, next]);
                }

                pairs.forEach(function (pair) {
                    pair[0].replaceWith(document.importNode(pair[1], true));
                });

                document.title = doc.title || document.title;
                if (push) window.history.pushState({ pbShade: true }, '', url);
                lastPath = window.location.pathname;

                initGallery();
                initShadeSelect();
                initTopBar(true);

                // блок наличия у оттенков может отличаться — перевешиваем обработчики
                // и модификатор пустого блока на сетке
                initCitySelect();
                initShopsToggle();
                initNearestShop();

                var pb_product = document.querySelector('.pb-product');
                var stock_block = document.querySelector('.pb-product-stock');
                if (pb_product && stock_block) {
                    pb_product.classList.toggle(
                        'pb-product--no-stock',
                        !stock_block.querySelector('[data-city-select], [data-shops]')
                    );
                }

                // штатный main.js активирует первую вкладку только на загрузке
                var firstTab = document.querySelector('.product-end-tabs .product-tab');
                if (firstTab) firstTab.click();
            })
            .catch(function () {
                window.location.href = url;
            });
    }

    window.addEventListener('popstate', function () {
        // fancybox тоже дёргает историю хешами — реагируем только на смену пути
        if (window.location.pathname !== lastPath) {
            swapProduct(window.location.href, false);
        }
    });

    /* --------------------------------------- мобильная верхняя панель */

    var topBarObserver = null;

    function initTopBar(reinit) {
        var bar = document.querySelector('[data-pb-topbar]');
        var title = document.querySelector('.pb-product .product-end-content-inner h1');
        if (!bar || !title) return;

        if (topBarObserver) {
            if (!reinit) return;
            topBarObserver.disconnect();
        }

        if (!('IntersectionObserver' in window)) return;

        topBarObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                var passed = !entry.isIntersecting && entry.boundingClientRect.top < 0;
                bar.classList.toggle('is-visible', passed);
            });
        });
        topBarObserver.observe(title);
    }

    /* --------------------------------------------- лента «Похожие товары» */

    function initSimilarSliders() {
        var sections = document.querySelectorAll('[data-rec-slider]');

        Array.prototype.forEach.call(sections, function (section) {
            var track = section.querySelector('.rec-similar-track');
            if (!track) return;

            function step() {
                var card = track.querySelector('.rec-card');
                // ширина карточки плюс отступ между карточками из макета
                return card ? card.offsetWidth + 24 : 296;
            }

            var prev = section.querySelector('[data-rec-prev]');
            var next = section.querySelector('[data-rec-next]');

            if (prev) prev.addEventListener('click', function () { track.scrollLeft -= step(); });
            if (next) next.addEventListener('click', function () { track.scrollLeft += step(); });
        });
    }

    /**
     * «Добавить весь комплект»: позиции добавляются ПОСЛЕДОВАТЕЛЬНО — параллельные
     * запросы гонялись бы за создание корзины и дублировали строки. Отказ по одной
     * позиции (кончилась между загрузкой и кликом) не прерывает остальные: в конце
     * показываем итог и обновляем шапку корзины из последнего успешного ответа.
     */
    function initSetAdd() {
        var buttons = document.querySelectorAll('.add-set-to-basket');

        function addOne(id) {
            var body = new FormData();
            body.append('goods_item_id', id);
            body.append('number', 1);

            return fetch('/' + document.documentElement.lang + '/ajaxAddToCart', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="_token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body
            }).then(function (response) { return response.json(); });
        }

        function applyCartResponse(data) {
            var count = document.querySelector('.header-basket-count');
            if (count) { count.style.display = ''; count.innerHTML = data.basket_count; }
            var price = document.querySelector('.header-basket-price');
            if (price && window.getDefaultPriceFormat) price.innerHTML = getDefaultPriceFormat(data.total_price);
            var header_items = document.querySelector('.render-header-basket-items');
            if (header_items) header_items.innerHTML = data.header_basket_items_view;
            var modal = document.querySelector('.render-modal-add-to-basket');
            if (modal) modal.innerHTML = data.modal_add_to_basket;
            var right = document.querySelector('.render-right-header-basket');
            if (right) right.innerHTML = data.modal_show_basket;
        }

        Array.prototype.forEach.call(buttons, function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                if (button.dataset.pbBusy) return;
                button.dataset.pbBusy = '1';

                var ids = (button.dataset.goodsIds || '').split(',').filter(Boolean);
                var added = 0;
                var refused = 0;
                var last_ok = null;

                var queue = ids.reduce(function (chain, id) {
                    return chain.then(function () {
                        return addOne(id).then(function (data) {
                            if (data && data.status === true) { added++; last_ok = data; }
                            else refused++;
                        }).catch(function () { refused++; });
                    });
                }, Promise.resolve());

                queue.then(function () {
                    delete button.dataset.pbBusy;
                    if (last_ok) applyCartResponse(last_ok);

                    if (window.Notiflix) {
                        if (refused === 0) {
                            Notiflix.Notify.success((button.dataset.labelAdded || 'OK') + ' (' + added + ')', { position: 'center-top', timeout: 3000 });
                        } else {
                            Notiflix.Notify.warning((button.dataset.labelPartial || '!') + ' (' + added + '/' + ids.length + ')', { position: 'center-top', timeout: 4000 });
                        }
                    }
                });
            });
        });
    }

    /** Селектор города над списком магазинов (п.5 ТЗ). */
    function initCitySelect() {
        var root = document.querySelector('[data-city-select]');
        if (!root) return;

        var trigger = root.querySelector('.pb-city-trigger');
        var value = root.querySelector('.pb-city-value');
        var items = root.querySelectorAll('.pb-dropdown-item');
        var shops = document.querySelector('[data-shops]');

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            root.classList.toggle('is-open');
        });

        Array.prototype.forEach.call(items, function (item) {
            item.addEventListener('click', function (event) {
                event.preventDefault();
                Array.prototype.forEach.call(items, function (one) { one.classList.remove('is-selected'); });
                item.classList.add('is-selected');
                value.textContent = item.dataset.city;
                root.classList.remove('is-open');
                filterShops(shops, item.dataset.city, items[0].dataset.city);
            });
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) root.classList.remove('is-open');
        });
    }

    function filterShops(shops, city, all_label) {
        if (!shops) return;

        var rows = shops.querySelectorAll('.pb-shop-item');
        Array.prototype.forEach.call(rows, function (row) {
            row.hidden = city !== all_label && row.dataset.city !== city;
        });
    }

    /**
     * Подсветка ближайшего магазина с наличием (п.5 ТЗ). Геолокацию не запрашиваем
     * сами — используем, только если посетитель уже дал разрешение (например, на
     * странице магазинов): молча подсвечиваем ближайшую точку с товаром.
     */
    function initNearestShop() {
        var shops = document.querySelector('[data-shops]');
        if (!shops || !('geolocation' in navigator)) return;

        function distance(lat1, lng1, lat2, lng2) {
            var rad = Math.PI / 180;
            var a = Math.sin((lat2 - lat1) * rad / 2) * Math.sin((lat2 - lat1) * rad / 2) +
                Math.cos(lat1 * rad) * Math.cos(lat2 * rad) *
                Math.sin((lng2 - lng1) * rad / 2) * Math.sin((lng2 - lng1) * rad / 2);
            return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function highlight(position) {
            var best = null;
            var bestDistance = Infinity;

            Array.prototype.forEach.call(shops.querySelectorAll('.pb-shop-item:not(.is-out)'), function (row) {
                var lat = parseFloat(row.dataset.lat);
                var lng = parseFloat(row.dataset.lng);
                if (isNaN(lat) || isNaN(lng)) return;

                var d = distance(position.coords.latitude, position.coords.longitude, lat, lng);
                if (d < bestDistance) { bestDistance = d; best = row; }
            });

            if (best) best.classList.add('is-nearest');
        }

        function locate() {
            navigator.geolocation.getCurrentPosition(highlight, function () {}, { maximumAge: 600000 });
        }

        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then(function (status) {
                if (status.state === 'granted') locate();
            }).catch(function () {});
        }
    }

    /** Раскрытие магазинов, где товара нет. */
    function initShopsToggle() {
        var shops = document.querySelector('[data-shops]');
        if (!shops) return;

        var toggle = shops.querySelector('.pb-shops-toggle');
        if (!toggle) return;

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            shops.classList.toggle('is-expanded');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initGallery();
        initShadeSelect();
        initTopBar();
        initSimilarSliders();
        initSetAdd();
        initCitySelect();
        initShopsToggle();
        initNearestShop();
    });
})();
