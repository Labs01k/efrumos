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

    /*
       Блоки, которые целиком принадлежат товару и меняются вместе с оттенком.
       Берём КРУПНЫЕ контейнеры, а не отдельные поля: у оттенка без остатка
       нет кнопок покупки, и точечные селекторы (.pb-cta-row и т.п.) не
       находились — подмена срывалась в полную перезагрузку.
    */
    var SWAP_TARGETS = [
        '[data-pb-gallery]',
        '.product-end-content',   // заголовок, объём, оттенок, цена, кнопки, акции
        '.pb-product-info',       // табы, характеристики, доставка, преимущества
        '.pb-product-stock',      // наличие по магазинам
        '.pb-product-set',        // «С этим покупают» — иначе комплект кладёт старый оттенок
        '.pb-product-similar',
        '.breadcrumbs-wrapper',
        '.pb-bar--top'
    ];

    /* Мобильная панель покупки: у оттенка без остатка её нет вовсе. */
    var OPTIONAL_TARGETS = ['.pb-bar--bottom'];

    /* Теги в <head>, которые обязаны указывать на текущий оттенок. */
    function swapHead(doc) {
        var pairs = [
            ['link[rel="canonical"]', 'href'],
            ['meta[property="og:url"]', 'content'],
            ['meta[property="og:title"]', 'content'],
            ['meta[property="og:image"]', 'content']
        ];

        pairs.forEach(function (pair) {
            var cur = document.querySelector(pair[0]);
            var next = doc.querySelector(pair[0]);
            if (cur && next) cur.setAttribute(pair[1], next.getAttribute(pair[1]));
        });

        // структурированные данные товара (sku, цена, оттенок)
        var cur_ld = document.querySelector('script[type="application/ld+json"]');
        var next_ld = doc.querySelector('script[type="application/ld+json"]');
        if (cur_ld && next_ld) cur_ld.textContent = next_ld.textContent;
        else if (cur_ld && !next_ld) cur_ld.remove();
    }

    function restoreScroll(y) {
        if (!y) return;

        window.scrollTo(0, y);
        requestAnimationFrame(function () { window.scrollTo(0, y); });
        setTimeout(function () { window.scrollTo(0, y); }, 250);
    }

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

                // страница не должна прыгать: положение прокрутки сохраняем
                var scroll_y = window.scrollY;

                pairs.forEach(function (pair) {
                    pair[0].replaceWith(document.importNode(pair[1], true));
                });

                // необязательные блоки: появляются и исчезают вместе с наличием
                OPTIONAL_TARGETS.forEach(function (selector) {
                    var cur = document.querySelector(selector);
                    var next = doc.querySelector(selector);
                    var page = document.querySelector('.pb-page');

                    if (cur && next) cur.replaceWith(document.importNode(next, true));
                    else if (cur && !next) cur.remove();
                    else if (!cur && next && page) page.appendChild(document.importNode(next, true));
                });

                swapHead(doc);
                document.title = doc.title || document.title;
                if (push) window.history.pushState({ pbShade: true }, '', url);
                lastPath = window.location.pathname;

                initGallery();
                initShadeSelect();
                initTopBar(true);

                // блоки подменились целиком — обработчики вешаем заново
                initCitySelect();
                initShopsToggle();
                initNearestShop();
                initSetAdd();
                initSimilarSliders();

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

                // высота страницы после подмены набирается не сразу (ленивые
                // картинки), поэтому возвращаем позицию ещё раз в следующем
                // кадре и после догрузки — иначе браузер упирается в короткий
                // документ и прокрутка «прыгает» вверх
                restoreScroll(scroll_y);
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
            if (!track || section.dataset.pbReady) return;
            section.dataset.pbReady = '1';

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
            if (button.dataset.pbReady) return;
            button.dataset.pbReady = '1';

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
        if (!root || root.dataset.pbReady) return;
        root.dataset.pbReady = '1';

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
     * Подсветка ближайшего магазина с наличием (п.5 ТЗ). Расстояние и выбор
     * магазина считает сервер — GET ajaxNearestShopWithStock (frontend-spec.md,
     * Epic 5): он единственный видит реальные остатки по складам, а на клиенте
     * в разметке только «есть/нет».
     *
     * Геолокацию сами не запрашиваем: подсветка — приятная мелочь, ради неё
     * дёргать разрешение навязчиво. Используем, только если посетитель уже
     * разрешил её раньше (например, на странице «Магазины»).
     */
    function initNearestShop() {
        var shops = document.querySelector('[data-shops]');
        if (!shops || !('geolocation' in navigator)) return;

        var goods_item_id = shops.dataset.goodsItemId;
        if (!goods_item_id || shops.dataset.pbNearestDone) return;
        shops.dataset.pbNearestDone = '1';

        function highlight(position) {
            var params = new URLSearchParams({
                goods_item_id: goods_item_id,
                lat: position.coords.latitude,
                lng: position.coords.longitude
            });

            fetch('/' + document.documentElement.lang + '/ajaxNearestShopWithStock?' + params, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (!data || data.status !== true) return;

                    var row = shops.querySelector('.pb-shop-item[data-shop-id="' + data.shop_id + '"]');
                    if (row) row.classList.add('is-nearest');
                })
                .catch(function () {});
        }

        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then(function (status) {
                if (status.state === 'granted') {
                    navigator.geolocation.getCurrentPosition(highlight, function () {}, { maximumAge: 600000 });
                }
            }).catch(function () {});
        }
    }

    /** Раскрытие магазинов, где товара нет. */
    function initShopsToggle() {
        var shops = document.querySelector('[data-shops]');
        if (!shops) return;

        var toggle = shops.querySelector('.pb-shops-toggle');
        if (!toggle || toggle.dataset.pbReady) return;
        toggle.dataset.pbReady = '1';

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
