{{-- Shared, presentation-only controls for the financial report tables. --}}
<style>
    .report-scan-toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:8px 16px; padding:12px 0; font-size:13px; }
    .report-scan-toolbar label { display:flex; align-items:center; gap:8px; margin:0; font-weight:600; white-space:nowrap; }
    .report-scan-toolbar select { max-width:220px; }
    .report-scan-note { color:#475569; }
    .report-scan-scroll { overflow-x:auto; overflow-y:hidden; height:18px; }
    .report-scan-scroll > div { height:1px; }
    .report-scan-table.tabulator, .report-scan-table.tabulator .tabulator-header .tabulator-col { font-size:13px !important; }
    .report-scan-table .tabulator-cell { padding:7px 8px; }
    @media print { .report-scan-toolbar, .report-scan-scroll { display:none !important; } }
</style>
<script>
window.cbReportScan = window.cbReportScan || function (table, options) {
    var el = table.element;
    el.classList.add('report-scan-table');
    var toolbar = document.createElement('div');
    toolbar.className = 'report-scan-toolbar';
    var modeLabel = document.createElement('label');
    modeLabel.textContent = 'Baris';
    var mode = document.createElement('select');
    mode.className = 'form-control form-control-sm';
    mode.setAttribute('aria-label', 'Baris laporan');
    mode.add(new Option('Ringkasan', 'summary'));
    mode.add(new Option('Semua detail', 'detail'));
    modeLabel.appendChild(mode);
    toolbar.appendChild(modeLabel);
    var month, weeks;
    if (options.months) {
        var monthLabel = document.createElement('label');
        monthLabel.textContent = 'Kolom bulan';
        month = document.createElement('select');
        month.className = 'form-control form-control-sm';
        month.setAttribute('aria-label', 'Kolom bulan');
        month.add(new Option('Semua bulan terpilih', ''));
        options.months.forEach(function (m) { month.add(new Option(m.label, String(m.id))); });
        monthLabel.appendChild(month);
        toolbar.appendChild(monthLabel);
    }
    if (options.weekPattern) {
        var weekLabel = document.createElement('label');
        weeks = document.createElement('input');
        weeks.type = 'checkbox';
        weekLabel.appendChild(weeks);
        weekLabel.appendChild(document.createTextNode('Detail mingguan'));
        toolbar.appendChild(weekLabel);
    }
    var note = document.createElement('span');
    note.className = 'report-scan-note';
    note.setAttribute('aria-live', 'polite');
    toolbar.appendChild(note);
    el.before(toolbar);
    var scroller = document.createElement('div');
    scroller.className = 'report-scan-scroll';
    scroller.tabIndex = 0;
    scroller.setAttribute('role', 'region');
    scroller.setAttribute('aria-label', 'Geser kolom laporan secara horizontal');
    var spacer = document.createElement('div');
    scroller.appendChild(spacer);
    el.before(scroller);
    var holder = el.querySelector('.tabulator-tableholder');
    var ignoredTopScroll = null;
    function syncPosition() {
        if (scroller.scrollLeft !== holder.scrollLeft) {
            ignoredTopScroll = holder.scrollLeft;
            scroller.scrollLeft = holder.scrollLeft;
        }
    }
    function syncWidth() {
        if (!el.isConnected) {
            if (observer) observer.disconnect();
            return;
        }
        scroller.hidden = holder.scrollWidth <= holder.clientWidth;
        spacer.style.width = (holder.scrollWidth + scroller.clientWidth - holder.clientWidth) + 'px';
        syncPosition();
    }
    scroller.addEventListener('scroll', function () {
        if (ignoredTopScroll !== null && scroller.scrollLeft === ignoredTopScroll) {
            ignoredTopScroll = null;
            return;
        }
        ignoredTopScroll = null;
        holder.scrollLeft = scroller.scrollLeft;
    });
    holder.addEventListener('scroll', syncPosition);
    table.on('renderComplete', syncWidth);
    table.on('columnResized', syncWidth);
    var observer = new ResizeObserver(syncWidth);
    observer.observe(el);
    table.on('tableDestroyed', function () { observer.disconnect(); toolbar.remove(); scroller.remove(); });
    function summaryFilter(data) { return (options.detailTypes || ['item']).indexOf(data.type) === -1; }
    var summaryActive = false;
    function updateRows() {
        if (summaryActive) table.removeFilter(summaryFilter);
        if (mode.value === 'summary') table.addFilter(summaryFilter);
        summaryActive = mode.value === 'summary';
        note.textContent = mode.value === 'summary'
            ? 'Rincian disembunyikan; subtotal dan total tetap utuh.'
            : 'Seluruh rincian ditampilkan; total mengikuti periode filter.';
    }
    function updateColumns() {
        table.blockRedraw();
        table.getColumns().forEach(function (column) {
            var field = column.getField();
            if (!field) return;
            var match = /^m(\d+)_/.exec(field);
            var show = !month || !month.value || !match || match[1] === month.value;
            if (weeks && options.weekPattern.test(field) && !weeks.checked) show = false;
            if (show !== column.isVisible()) {
                if (show) column.show(); else column.hide();
            }
        });
        table.restoreRedraw();
        syncWidth();
    }
    mode.addEventListener('change', updateRows);
    if (month) month.addEventListener('change', updateColumns);
    if (weeks) weeks.addEventListener('change', updateColumns);
    updateRows();
    updateColumns();
};
</script>
