// Progressive enhancement: native text inputs remain usable without JS or API.
export function initLocationFields() {
    document.querySelectorAll('[data-location-fields]').forEach(root => {
        const province = root.querySelector('[data-location-province]');
        const city = root.querySelector('[data-location-city]');
        const provinceManual = root.querySelector('[data-location-province-manual]');
        const cityManual = root.querySelector('[data-location-city-manual]');
        const status = root.querySelector('[data-location-status]');
        const retry = root.querySelector('[data-location-retry]');
        if (!province || !city || !provinceManual || !cityManual || !status || !retry) return;

        const normalize = value => String(value || '').trim().replaceAll('ي','ی').replaceAll('ك','ک');
        const sort = rows => [...rows].sort((a,b) => a.name.localeCompare(b.name,'fa'));
        let sequence = 0;
        let controller;
        let retryAction = null;
        const switchField = (select, manual, useSelect) => {
            select.hidden = !useSelect;
            select.disabled = !useSelect;
            manual.hidden = useSelect;
            manual.disabled = useSelect;
        };
        const feedback = (message, action = null) => {
            status.textContent = message;
            retryAction = action;
            retry.hidden = !action;
        };
        const options = (select, items, placeholder) => {
            select.replaceChildren(new Option(placeholder, ''));
            sort(items).forEach(item => {
                const option = new Option(item.name, item.name);
                if (item.id != null) option.dataset.id = String(item.id);
                select.add(option);
            });
        };
        const request = async (url, signal) => {
            const response = await fetch(url, {headers:{Accept:'application/json'}, signal, credentials:'same-origin'});
            if (!response.ok) throw new Error('locations unavailable');
            const result = await response.json();
            if (!Array.isArray(result) || !result.length || !result.every(row => typeof row.name === 'string')) throw new Error('invalid locations');
            return result;
        };
        const cancelPrevious = () => {
            controller?.abort();
            controller = new AbortController();
            return {token: ++sequence, signal: controller.signal};
        };
        const loadCities = async (preserve = '') => {
            const id = province.selectedOptions[0]?.dataset.id;
            const {token, signal} = cancelPrevious();
            cityManual.value = preserve;
            city.value = '';
            if (!id) {
                options(city, [], 'ابتدا استان را انتخاب کنید');
                switchField(city, cityManual, true);
                feedback('');
                return;
            }
            options(city, [], 'در حال دریافت شهرها…');
            switchField(city, cityManual, !preserve);
            feedback('در حال دریافت شهرها…');
            try {
                const url = new URL(root.dataset.citiesUrl, window.location.origin);
                url.searchParams.set('province', id);
                const rows = await request(url, signal);
                if (token !== sequence) return;
                options(city, rows, 'انتخاب شهر');
                city.add(new Option('ورود دستی نام شهر', '__manual__'));
                const pendingCity = cityManual.value;
                const previous = rows.find(row => normalize(row.name) === normalize(pendingCity));
                if (pendingCity && !previous) {
                    switchField(city, cityManual, false);
                    feedback('شهر قبلی در فهرست یافت نشد؛ می‌توانید آن را دستی وارد کنید.', () => loadCities(cityManual.value));
                } else {
                    city.value = previous?.name || '';
                    switchField(city, cityManual, true);
                    feedback('');
                }
            } catch (error) {
                if (token !== sequence) return;
                switchField(city, cityManual, false);
                feedback('فهرست شهرها در دسترس نیست؛ نام شهر را دستی وارد کنید.', () => loadCities(cityManual.value));
            }
        };
        const loadProvinces = async () => {
            const {token,signal} = cancelPrevious();
            feedback('در حال دریافت استان‌ها…');
            try {
                const rows = await request(root.dataset.provincesUrl, signal);
                if (token !== sequence) return;
                const oldProvince = provinceManual.value;
                const oldCity = cityManual.value;
                options(province, rows, 'انتخاب استان');
                const previous = rows.find(row => normalize(row.name) === normalize(oldProvince));
                if (oldProvince && !previous) {
                    feedback('استان قبلی در فهرست یافت نشد؛ ورود دستی فعال است.', loadProvinces);
                    return;
                }
                province.value = previous?.name || '';
                switchField(province, provinceManual, true);
                if (!previous && oldCity) {
                    feedback('برای انتخاب شهر، ابتدا استان را مشخص کنید.');
                    return;
                }
                await loadCities(oldCity);
            } catch (error) {
                if (token !== sequence) return;
                switchField(province, provinceManual, false);
                switchField(city, cityManual, false);
                feedback('فهرست استان‌ها در دسترس نیست؛ استان و شهر را دستی وارد کنید.', loadProvinces);
            }
        };
        province.addEventListener('change', () => { provinceManual.value = province.value; loadCities(); });
        city.addEventListener('change', () => {
            if (city.value !== '__manual__') return;
            cityManual.value = '';
            switchField(city, cityManual, false);
            feedback('نام شهر را وارد کنید.', () => loadCities(cityManual.value));
            cityManual.focus();
        });
        retry.addEventListener('click', () => retryAction?.());
        loadProvinces();
    });
}
