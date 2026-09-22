<div class="wrap">
    <form method="GET" action="{{ route('home') }}" class="searchbar reveal show" id="searchForm">
        {{-- preserve active tag/sort filters --}}
        @if ($activeTag)                              <input type="hidden" name="tag"       value="{{ $activeTag }}"> @endif
        @if (($activeDirection ?? 'asc') !== 'asc')  <input type="hidden" name="direction" value="{{ $activeDirection }}"> @endif

        <div class="fld">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>
            </svg>
            <div style="flex:1">
                <label for="s-city">City</label>
                <select id="s-city" name="city">
                    <option value="">All Cities</option>
                    @foreach ($locations['cities'] ?? [] as $opt)
                        <option value="{{ $opt['value'] }}" {{ ($activeCity ?? '') === $opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="fld">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            <div style="flex:1">
                <label for="s-state">State</label>
                <select id="s-state" name="state">
                    <option value="">All States</option>
                    @foreach ($locations['states'] ?? [] as $opt)
                        <option value="{{ $opt['value'] }}" {{ ($activeState ?? '') === $opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="fld">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            <div style="flex:1">
                <label for="s-country">Country</label>
                <select id="s-country" name="country">
                    <option value="">All Countries</option>
                    @foreach ($locations['countries'] ?? [] as $opt)
                        <option value="{{ $opt['value'] }}" {{ ($activeCountry ?? '') === $opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button class="btn btn-navy go" type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/>
            </svg>
            Search
        </button>
    </form>
</div>

<script>
(function () {
    const FILTERS_URL = '{{ route('filters') }}';
    const selCountry  = document.getElementById('s-country');
    const selState    = document.getElementById('s-state');
    const selCity     = document.getElementById('s-city');

    function buildOpts(items, currentVal, allLabel) {
        let html = `<option value="">${allLabel}</option>`;
        items.forEach(function (opt) {
            const sel = opt.value === currentVal ? ' selected' : '';
            html += `<option value="${opt.value}"${sel}>${opt.label}</option>`;
        });
        return html;
    }

    function fetchFilters(params, cb) {
        const qs = new URLSearchParams(params).toString();
        fetch(FILTERS_URL + (qs ? '?' + qs : ''))
            .then(function (r) { return r.json(); })
            .then(cb)
            .catch(function () {});
    }

    selCountry.addEventListener('change', function () {
        const country = this.value;
        fetchFilters(country ? { country: country } : {}, function (data) {
            selState.innerHTML  = buildOpts(data.states  || [], '', 'All States');
            selCity.innerHTML   = buildOpts(data.cities  || [], '', 'All Cities');
        });
    });

    selState.addEventListener('change', function () {
        const country = selCountry.value;
        const state   = this.value;
        const params  = {};
        if (country) params.country = country;
        if (state)   params.state   = state;
        if (!country && !state) return;
        fetchFilters(params, function (data) {
            selCity.innerHTML = buildOpts(data.cities || [], '', 'All Cities');
        });
    });

    // ── AJAX submit: push location values into shared filter state, reload all tabs
    document.getElementById('searchForm').addEventListener('submit', function (e) {
        e.preventDefault();
        if (window.evFilters && window.evOnFilterChange) {
            window.evFilters.city    = selCity.value;
            window.evFilters.state   = selState.value;
            window.evFilters.country = selCountry.value;
            window.evOnFilterChange();
            var ev = document.getElementById('events');
            if (ev) ev.scrollIntoView({ behavior: 'smooth' });
        }
    });
})();
</script>
