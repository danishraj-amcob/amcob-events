<section class="blk" id="calendar" style="padding-top:10px">
    <div class="wrap">
        <div class="sec-head">
            <div>
                <span class="eyebrow">Plan ahead</span>
                <h2 class="sec-t">Events calendar</h2>
                <p class="sec-s" style="font-size:14.5px">The month at a glance — marked dates have AMCOB events. Click a date to see what's on.</p>
            </div>
        </div>
        <div class="calwrap">
            <div class="cal">
                <div class="cal-top">
                    <h3 id="calTitle">Month YYYY</h3>
                    <div class="cal-nav">
                        <button id="calPrev" aria-label="Previous month"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg></button>
                        <button id="calNext" aria-label="Next month"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M9 6l6 6-6 6"/></svg></button>
                    </div>
                </div>
                <div class="cal-dow">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                </div>
                <div class="cal-grid" id="calGrid"></div>
                <div class="cal-legend">
                    <span><i style="background:var(--gold)"></i> Upcoming event</span>
                    <span><i style="background:var(--navy)"></i> Past event</span>
                </div>
            </div>
            <div class="cal-side">
                <h4 id="calSideTitle">This month</h4>
                <div class="sub" id="calSideSub"></div>
                <div id="calSideList"></div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    const CAL = @json($allCalEvents);

    const MNAMES = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];

    const keyOf  = (y, mi, d) => `${y}-${mi}-${d}`;
    const calMap = {};
    CAL.forEach(ev => {
        const k = keyOf(ev.y, ev.mi, ev.day);
        (calMap[k] = calMap[k] || []).push(ev);
    });

    const now = new Date();
    let vY = now.getFullYear(), vM = now.getMonth(), selDay = null;

    const firstUpcoming = CAL.find(e => !e.past);
    if (firstUpcoming) { vY = firstUpcoming.y; vM = firstUpcoming.mi; }

    const pinSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>';

    function thumb(e) {
        return e.gradient
            ? `<div class="cthumb" style="background:${e.gradient};border-radius:6px;flex-shrink:0"></div>`
            : '';
    }

    function monthEvents(y, m) {
        return CAL.filter(e => e.y === y && e.mi === m).sort((a, b) => a.day - b.day);
    }

    function renderSide() {
        const st   = document.getElementById('calSideTitle');
        const sub  = document.getElementById('calSideSub');
        const list = document.getElementById('calSideList');
        let evs, label;
        if (selDay) {
            evs            = calMap[keyOf(vY, vM, selDay)] || [];
            st.textContent = `${MNAMES[vM]} ${selDay}, ${vY}`;
            label          = `${evs.length} event${evs.length !== 1 ? 's' : ''} on this day`;
        } else {
            evs            = monthEvents(vY, vM);
            st.textContent = `${MNAMES[vM]} ${vY}`;
            label          = evs.length
                ? `${evs.length} event${evs.length !== 1 ? 's' : ''} this month`
                : 'No events this month';
        }
        sub.textContent = label;
        if (!evs.length) {
            list.innerHTML = '<div class="cal-empty">Nothing scheduled — try another month.</div>';
            return;
        }
        list.innerHTML = evs.map(e => `
            <a class="cal-ev${e.past ? ' past' : ''}" href="${e.url}">
                ${thumb(e)}
                <div class="cdate"><b>${String(e.day).padStart(2, '0')}</b><span>${MNAMES[e.mi].slice(0, 3)}</span></div>
                <div class="cbody">
                    <span class="tagmini">${e.cat}${e.past ? ' · Held' : ''}</span>
                    <b>${e.title}</b>
                    <span class="loc2">${pinSvg}${e.loc}</span>
                </div>
            </a>`).join('');
    }

    function renderCal() {
        document.getElementById('calTitle').textContent = `${MNAMES[vM]} ${vY}`;
        const first = new Date(vY, vM, 1).getDay();
        const days  = new Date(vY, vM + 1, 0).getDate();
        let html = '';
        for (let i = 0; i < first; i++) html += '<div class="cal-cell blank"></div>';
        for (let d = 1; d <= days; d++) {
            const evs = calMap[keyOf(vY, vM, d)];
            if (evs) {
                const isPast = evs.every(e => e.past);
                const dots   = '<i></i>'.repeat(Math.min(evs.length, 3));
                const sel    = selDay === d ? ' sel' : '';
                html += `<div class="cal-cell has${isPast ? ' past' : ''}${sel}" data-day="${d}">${d}<span class="evdot">${dots}</span></div>`;
            } else {
                html += `<div class="cal-cell">${d}</div>`;
            }
        }
        document.getElementById('calGrid').innerHTML = html;
        document.querySelectorAll('#calGrid .cal-cell.has').forEach(c => {
            c.addEventListener('click', () => {
                const d = +c.dataset.day;
                selDay  = selDay === d ? null : d;
                renderCal();
                renderSide();
            });
        });
        renderSide();
    }

    document.getElementById('calPrev').addEventListener('click', () => {
        selDay = null; vM--;
        if (vM < 0) { vM = 11; vY--; }
        renderCal();
    });
    document.getElementById('calNext').addEventListener('click', () => {
        selDay = null; vM++;
        if (vM > 11) { vM = 0; vY++; }
        renderCal();
    });

    renderCal();
})();
</script>
@endpush
