/* ==================================================================
   Страница «Магазины» (макет 758:168): единая интерактивная карта,
   синхронизация с панелью списка, фильтр городов, геолокация
   и сортировка «ближайшие», мобильные карта/список/шторка.

   Карта — адаптер: Google Maps JS API при наличии ключа
   (config custom.front.google_maps_key), иначе Leaflet + OSM.
   ================================================================== */
(function () {
    'use strict';

    var CFG = window.PB_SHOPS;
    var root = document.querySelector('[data-shp]');
    if (!CFG || !root || !CFG.shops.length) return;

    var state = {
        user: null,        // {lat, lng} после разрешения геолокации
        city: '',          // '' = все магазины
        selected: null,    // id выбранного магазина
        sort: 'default',   // default = группировка по городам | nearest
    };

    var shopsById = {};
    CFG.shops.forEach(function (s) { shopsById[s.id] = s; });

    var list = root.querySelector('[data-shp-list]');
    var cards = {};
    root.querySelectorAll('[data-shp-card]').forEach(function (card) {
        cards[card.dataset.shpCard] = card;
    });

    function isMobile() { return window.matchMedia('(max-width: 768px)').matches; }

    /* ------------------------------------------------------------ гео-математика */

    function distanceKm(aLat, aLng, bLat, bLng) {
        var rad = Math.PI / 180;
        var h = Math.sin((bLat - aLat) * rad / 2) * Math.sin((bLat - aLat) * rad / 2) +
            Math.cos(aLat * rad) * Math.cos(bLat * rad) *
            Math.sin((bLng - aLng) * rad / 2) * Math.sin((bLng - aLng) * rad / 2);
        return 6371 * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
    }

    function shopDistanceKm(shop) {
        if (!state.user) return null;
        return distanceKm(state.user.lat, state.user.lng, shop.lat, shop.lng);
    }

    function formatKm(km) {
        if (km < 1) return Math.round(km * 1000) + ' ' + CFG.texts.m;
        var value = km < 10 ? Math.round(km * 10) / 10 : Math.round(km);
        return value + ' ' + CFG.texts.km;
    }

    /* ------------------------------------------------------------------- пины */

    function pinSvg(selected) {
        var fill = selected ? '#db6e97' : '#db6e97';
        var opacity = selected ? '1' : '.24';
        var letter = selected ? '#ffffff' : '#db6e97';
        return '<svg width="48" height="58" viewBox="0 0 48 58" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M24 1C11.3 1 1 11.3 1 24c0 15.8 23 33 23 33s23-17.2 23-33C47 11.3 36.7 1 24 1z" fill="' + fill + '" fill-opacity="' + opacity + '"/>' +
            '<text x="24" y="32" text-anchor="middle" font-family="Georgia, serif" font-style="italic" font-weight="700" font-size="26" fill="' + letter + '">E</text>' +
            '</svg>';
    }

    /* ------------------------------------------------------- адаптер карты: Leaflet */

    function createLeafletMap(el, onSelect) {
        var map = L.map(el, { zoomControl: true, scrollWheelZoom: true });
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var markers = {};

        function icon(selected) {
            return L.divIcon({
                className: 'shp-pin',
                html: pinSvg(selected),
                iconSize: [48, 58],
                iconAnchor: [24, 58],
            });
        }

        CFG.shops.forEach(function (shop) {
            var marker = L.marker([shop.lat, shop.lng], { icon: icon(false), riseOnHover: true }).addTo(map);
            marker.on('click', function () { onSelect(shop.id); });
            markers[shop.id] = marker;
        });

        return {
            select: function (id) {
                Object.keys(markers).forEach(function (mid) {
                    markers[mid].setIcon(icon(String(mid) === String(id)));
                    if (String(mid) === String(id)) markers[mid].setZIndexOffset(1000);
                    else markers[mid].setZIndexOffset(0);
                });
            },
            panTo: function (id) {
                var shop = shopsById[id];
                if (!shop) return;
                map.setView([shop.lat, shop.lng], Math.max(map.getZoom(), 14), { animate: true });
            },
            fit: function (ids) {
                var points = ids.map(function (id) { return [shopsById[id].lat, shopsById[id].lng]; });
                if (!points.length) return;
                var pad_right = !isMobile() ? 440 : 40;
                map.fitBounds(L.latLngBounds(points), {
                    paddingTopLeft: [40, 40],
                    paddingBottomRight: [pad_right, 40],
                    maxZoom: 15,
                });
            },
            refresh: function () { map.invalidateSize(); },
        };
    }

    /* -------------------------------------------------- адаптер карты: Google Maps */

    function createGoogleMap(el, onSelect) {
        var adapter = { _queue: [] };
        var map = null;
        var markers = {};

        function iconFor(selected) {
            return {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(pinSvg(selected)),
                scaledSize: new google.maps.Size(48, 58),
                anchor: new google.maps.Point(24, 58),
            };
        }

        function boot() {
            map = new google.maps.Map(el, {
                center: { lat: 47.02, lng: 28.84 },
                zoom: 8,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            CFG.shops.forEach(function (shop) {
                var marker = new google.maps.Marker({
                    position: { lat: shop.lat, lng: shop.lng },
                    map: map,
                    icon: iconFor(false),
                });
                marker.addListener('click', function () { onSelect(shop.id); });
                markers[shop.id] = marker;
            });

            adapter._queue.forEach(function (fn) { fn(); });
            adapter._queue = [];
        }

        function whenReady(fn) {
            if (map) fn(); else adapter._queue.push(fn);
        }

        adapter.select = function (id) {
            whenReady(function () {
                Object.keys(markers).forEach(function (mid) {
                    markers[mid].setIcon(iconFor(String(mid) === String(id)));
                    markers[mid].setZIndex(String(mid) === String(id) ? 1000 : 1);
                });
            });
        };
        adapter.panTo = function (id) {
            whenReady(function () {
                var shop = shopsById[id];
                if (!shop) return;
                map.panTo({ lat: shop.lat, lng: shop.lng });
                if (map.getZoom() < 14) map.setZoom(14);
            });
        };
        adapter.fit = function (ids) {
            whenReady(function () {
                var bounds = new google.maps.LatLngBounds();
                ids.forEach(function (id) { bounds.extend({ lat: shopsById[id].lat, lng: shopsById[id].lng }); });
                var pad_right = !isMobile() ? 440 : 40;
                map.fitBounds(bounds, { top: 40, left: 40, bottom: 40, right: pad_right });
            });
        };
        adapter.refresh = function () {
            whenReady(function () { google.maps.event.trigger(map, 'resize'); });
        };

        window.__shpGoogleBoot = boot;
        var script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(CFG.googleKey) + '&callback=__shpGoogleBoot';
        script.async = true;
        document.head.appendChild(script);

        return adapter;
    }

    /* ------------------------------------------------------------------ список */

    function visibleShops() {
        return CFG.shops.filter(function (shop) {
            return !state.city || shop.city === state.city;
        });
    }

    function orderedShops() {
        var arr = visibleShops().slice();

        if (state.sort === 'nearest' && state.user) {
            arr.sort(function (a, b) { return shopDistanceKm(a) - shopDistanceKm(b); });
        } else {
            // «обычный порядок» = группировка по городам: Кишинёв первым (решение тикета)
            var order = {};
            CFG.cities.forEach(function (city, index) { order[city] = index; });
            arr.sort(function (a, b) { return (order[a.city] || 0) - (order[b.city] || 0); });
        }

        return arr;
    }

    function renderList() {
        var ordered = orderedShops();
        var group_by_city = state.sort !== 'nearest' && !state.city;
        var last_city = null;

        Object.keys(cards).forEach(function (id) { cards[id].hidden = true; });
        list.querySelectorAll('.shp-city-group').forEach(function (heading) { heading.remove(); });

        ordered.forEach(function (shop) {
            var card = cards[shop.id];
            if (!card) return;

            if (group_by_city && shop.city !== last_city) {
                var heading = document.createElement('div');
                heading.className = 'shp-city-group';
                heading.textContent = shop.city;
                list.appendChild(heading);
                last_city = shop.city;
            }

            list.appendChild(card);
            card.hidden = false;

            var chip = card.querySelector('[data-shp-distance]');
            var km = shopDistanceKm(shop);
            if (chip) {
                chip.hidden = km === null;
                if (km !== null) chip.textContent = formatKm(km);
            }
        });
    }

    /* ------------------------------------------------- мобильные карусель и шторка */

    var carousel = root.querySelector('[data-shp-carousel]');
    var sheet = root.querySelector('[data-shp-sheet]');

    function renderCarousel() {
        if (!carousel) return;
        carousel.innerHTML = '';

        orderedShops().forEach(function (shop) {
            var km = shopDistanceKm(shop);
            var mini = document.createElement('div');
            mini.className = 'shp-mini';
            mini.dataset.shpMini = shop.id;
            mini.innerHTML =
                '<div class="shp-mini-head"><span class="shp-mini-name"></span>' +
                (km !== null ? '<span class="shp-chip shp-chip-distance">' + formatKm(km) + '</span>' : '') +
                '</div><div class="shp-mini-address"></div>';
            mini.querySelector('.shp-mini-name').textContent = shop.name;
            mini.querySelector('.shp-mini-address').textContent = shop.address;
            carousel.appendChild(mini);
        });
    }

    function routeUrl(shop) {
        var url = 'https://www.google.com/maps/dir/?api=1&destination=' + shop.lat + ',' + shop.lng;
        if (state.user) url += '&origin=' + state.user.lat + ',' + state.user.lng;
        return url;
    }

    function renderSheet(id) {
        if (!sheet) return;
        var shop = shopsById[id];
        if (!shop) { sheet.hidden = true; return; }

        var km = shopDistanceKm(shop);
        sheet.innerHTML =
            '<div class="shp-sheet-head"><span class="shp-sheet-name"></span>' +
            '<button type="button" class="shp-sheet-close" data-shp-sheet-close aria-label="✕">' +
            '<svg viewBox="0 0 16 16"><path d="M4 6l4 4 4-4"/></svg></button></div>' +
            '<p class="shp-sheet-address"></p>' +
            '<div class="shp-card-contacts">' +
            (shop.phone ? '<p class="shp-card-row"><svg viewBox="0 0 16 16"><path d="M3.2 1.8h2.4l1.2 3-1.5 1.2a10 10 0 0 0 4.7 4.7l1.2-1.5 3 1.2v2.4a1.3 1.3 0 0 1-1.4 1.3A12.7 12.7 0 0 1 1.9 3.2a1.3 1.3 0 0 1 1.3-1.4z"/></svg><a class="shp-sheet-phone" href="tel:' + shop.phone.replace(/[^+\d]/g, '') + '"></a></p>' : '') +
            (shop.schedule ? '<p class="shp-card-row"><svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6.3"/><path d="M8 4.5V8L5.8 9.6"/></svg><span class="shp-sheet-schedule"></span></p>' : '') +
            '</div>' +
            '<div class="shp-sheet-actions">' +
            '<a class="shp-sheet-route" href="' + routeUrl(shop) + '" target="_blank" rel="noopener"><span>' + CFG.texts.buildRoute + '</span>' +
            (km !== null ? '<span>' + formatKm(km) + '</span>' : '') + '</a>' +
            '<button type="button" class="shp-sheet-locate" data-shp-sheet-locate>' +
            '<svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="2.6"/><path d="M8 1.5V4M8 12v2.5M1.5 8H4M12 8h2.5"/></svg></button>' +
            '</div>';

        sheet.querySelector('.shp-sheet-name').textContent = shop.name;
        sheet.querySelector('.shp-sheet-address').textContent = shop.address;
        if (shop.phone) sheet.querySelector('.shp-sheet-phone').textContent = shop.phone;
        if (shop.schedule) sheet.querySelector('.shp-sheet-schedule').textContent = shop.schedule;

        sheet.hidden = false;
        if (carousel) carousel.style.display = 'none';
    }

    function closeSheet() {
        if (!sheet) return;
        sheet.hidden = true;
        if (carousel) carousel.style.display = '';
    }

    /* -------------------------------------------------------------- выбор магазина */

    var map = CFG.googleKey
        ? createGoogleMap(document.getElementById('shp-map'), select)
        : createLeafletMap(document.getElementById('shp-map'), select);

    function select(id, options) {
        options = options || {};
        state.selected = id;

        Object.keys(cards).forEach(function (cid) {
            cards[cid].classList.toggle('is-selected', String(cid) === String(id));
        });

        map.select(id);
        if (options.pan !== false) map.panTo(id);

        if (isMobile()) {
            renderSheet(id);
        } else {
            var card = cards[id];
            if (card && options.scroll !== false) card.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    /* -------------------------------------------------------------------- события */

    // карточки списка: клик — выбрать (на мобиле — перейти на карту), locate — к пину
    list.addEventListener('click', function (event) {
        var card = event.target.closest('[data-shp-card]');
        if (!card) return;
        if (event.target.closest('a')) return; // маршрут/телефон/фото работают как ссылки

        var id = card.dataset.shpCard;

        if (isMobile()) {
            setListView(false);
            select(id);
            map.refresh();
            return;
        }

        select(id, { scroll: event.target.closest('[data-shp-locate]') ? false : true });
    });

    // карусель и шторка (мобайл)
    if (carousel) {
        carousel.addEventListener('click', function (event) {
            var mini = event.target.closest('[data-shp-mini]');
            if (mini) select(mini.dataset.shpMini);
        });

        // свайп карусели: на снепе карта сразу перелетает к магазину карточки
        var snap_timer = null;
        var last_snapped = null;

        carousel.addEventListener('scroll', function () {
            if (carousel.style.display === 'none') return;
            clearTimeout(snap_timer);
            snap_timer = setTimeout(function () {
                var minis = carousel.querySelectorAll('[data-shp-mini]');
                if (minis.length < 2) return;

                var step = minis[1].offsetLeft - minis[0].offsetLeft;
                var index = Math.max(0, Math.min(minis.length - 1, Math.round(carousel.scrollLeft / step)));
                var id = minis[index].dataset.shpMini;
                if (id === last_snapped) return;
                last_snapped = id;

                minis.forEach(function (one) { one.classList.toggle('is-active', one === minis[index]); });

                state.selected = id;
                map.select(id);
                map.panTo(id);
            }, 140);
        });
    }
    if (sheet) {
        sheet.addEventListener('click', function (event) {
            if (event.target.closest('[data-shp-sheet-close]')) { closeSheet(); return; }
            if (event.target.closest('[data-shp-sheet-locate]') && state.selected) map.panTo(state.selected);
        });
    }

    // табы сортировки: «ближайшие» без геолокации открывает модалку запроса
    root.querySelectorAll('[data-shp-sort]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            var sort = tab.dataset.shpSort;

            if (sort === 'nearest' && !state.user) { openGeoModal(); return; }

            state.sort = sort;
            root.querySelectorAll('[data-shp-sort]').forEach(function (one) {
                one.classList.toggle('is-active', one === tab);
            });
            renderList();
            renderCarousel();
        });
    });

    // фильтр города: два синхронных дропдауна (панель и мобильный топбар)
    root.querySelectorAll('[data-shp-city]').forEach(function (city_root) {
        var trigger = city_root.querySelector('.shp-city-trigger');

        trigger.addEventListener('click', function () {
            city_root.classList.toggle('is-open');
        });

        city_root.querySelectorAll('[data-shp-city-option]').forEach(function (option) {
            option.addEventListener('click', function () {
                state.city = option.dataset.shpCityOption;
                city_root.classList.remove('is-open');

                root.querySelectorAll('[data-shp-city]').forEach(function (one_root) {
                    one_root.querySelectorAll('[data-shp-city-option]').forEach(function (one_option) {
                        one_option.classList.toggle('is-selected', one_option.dataset.shpCityOption === state.city);
                    });
                    one_root.querySelector('[data-shp-city-value]').textContent = state.city || CFG.texts.all;
                });

                renderList();
                renderCarousel();
                closeSheet();
                map.fit(visibleShops().map(function (shop) { return shop.id; }));
            });
        });
    });

    document.addEventListener('click', function (event) {
        root.querySelectorAll('[data-shp-city].is-open').forEach(function (city_root) {
            if (!city_root.contains(event.target)) city_root.classList.remove('is-open');
        });
    });

    // мобильный топбар: переключатель карта/список
    var view_toggle = root.querySelector('[data-shp-view-toggle]');

    function setListView(on) {
        root.classList.toggle('is-list-view', on);
        if (!on) map.refresh();
    }

    if (view_toggle) view_toggle.addEventListener('click', function () {
        setListView(!root.classList.contains('is-list-view'));
    });

    /* ---------------------------------------------------------------- геолокация */

    var geo_modal = root.querySelector('[data-shp-geo-modal]');

    function openGeoModal() { if (geo_modal) geo_modal.hidden = false; }
    function closeGeoModal() {
        if (geo_modal) geo_modal.hidden = true;
        try { localStorage.setItem('shpGeoAsked', '1'); } catch (e) {}
    }

    function applyGeo(position) {
        state.user = { lat: position.coords.latitude, lng: position.coords.longitude };
        state.sort = 'nearest';
        root.querySelectorAll('[data-shp-sort]').forEach(function (tab) {
            tab.classList.toggle('is-active', tab.dataset.shpSort === 'nearest');
        });
        renderList();
        renderCarousel();
        if (state.selected) renderSheetIfOpen();
    }

    function renderSheetIfOpen() {
        if (sheet && !sheet.hidden && state.selected) renderSheet(state.selected);
    }

    function requestGeo() {
        if (!('geolocation' in navigator)) { closeGeoModal(); return; }
        navigator.geolocation.getCurrentPosition(
            function (position) { closeGeoModal(); applyGeo(position); },
            function () { closeGeoModal(); },
            { maximumAge: 600000, timeout: 10000 }
        );
    }

    if (geo_modal) {
        geo_modal.querySelectorAll('[data-shp-geo-close]').forEach(function (btn) {
            btn.addEventListener('click', closeGeoModal);
        });
        geo_modal.querySelector('[data-shp-geo-share]').addEventListener('click', requestGeo);
    }

    // на старте: разрешение уже есть — берём молча; нет и не спрашивали — модалка
    if ('geolocation' in navigator && navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' }).then(function (status) {
            if (status.state === 'granted') {
                navigator.geolocation.getCurrentPosition(applyGeo, function () {}, { maximumAge: 600000 });
            } else if (status.state === 'prompt') {
                var asked = null;
                try { asked = localStorage.getItem('shpGeoAsked'); } catch (e) {}
                if (!asked) openGeoModal();
            }
        }).catch(function () {});
    }

    /* --------------------------------------------------------------------- старт */

    renderList();
    renderCarousel();
    map.fit(CFG.shops.map(function (shop) { return shop.id; }));
})();
