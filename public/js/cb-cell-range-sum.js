/**
 * Blok sel ala spreadsheet + popup Jumlah untuk tabel DataTables.
 * Pakai: CbCellRangeSum.init('#example3');
 *
 * - Drag mouse memblok banyak sel (Shift+klik juga bisa memperluas blok).
 * - Setelah blok, muncul popup kecil berisi Jumlah & banyak data angka.
 * - Esc menghapus blok; redraw tabel otomatis membatalkan blok.
 * - Tidak mengganggu inline edit (dobel klik) maupun tombol aksi.
 */
(function (global) {
    'use strict';

    var STYLE_ID = 'cb-cell-range-sum-style';

    function ensureStyle() {
        if (document.getElementById(STYLE_ID)) return;
        var st = document.createElement('style');
        st.id = STYLE_ID;
        st.textContent =
            'td.crs-range-cell { background: #dbeafe !important; }' +
            'table.crs-noselect, table.crs-noselect * { user-select: none !important; }' +
            '.crs-sum-pop { position: fixed; z-index: 2000; display: none;' +
            '  background: #0d3b6e; color: #fff; font-size: 12px; line-height: 1;' +
            '  padding: 8px 12px; border-radius: 6px; box-shadow: 0 4px 14px rgba(0,0,0,.28);' +
            '  pointer-events: none; white-space: nowrap; }' +
            '.crs-sum-pop b { color: #ffd166; }';
        document.head.appendChild(st);
    }

    // Angka format Indonesia: "1.039.538.838", "(2.500)", "12,5". Tanggal,
    // nomor agenda (mis. "4947_2026"), dan teks lain ditolak agar tidak terjumlah.
    function parseNum(v) {
        if (v === null || v === undefined) return null;
        var s = String(v).replace(/<[^>]*>/g, '').trim();
        if (!s || s === '-') return null;
        var neg = /^\(.*\)$/.test(s);
        s = s.replace(/[()\s]/g, '').replace(/^Rp/i, '');
        if (!/^-?\d{1,3}(\.\d{3})*(,\d+)?$/.test(s) && !/^-?\d+(,\d+)?$/.test(s)) return null;
        var n = parseFloat(s.replace(/\./g, '').replace(',', '.'));
        if (isNaN(n)) return null;
        return neg ? -n : n;
    }

    function init(tableSel) {
        ensureStyle();
        var tbl = document.querySelector(tableSel);
        if (!tbl) return;

        var pop = document.createElement('div');
        pop.className = 'crs-sum-pop';
        document.body.appendChild(pop);

        var anchor = null;    // { r, c } — titik awal blok
        var range = null;     // { r1, c1, r2, c2 } — blok ternormalisasi
        var dragging = false;

        function tbody() { return tbl.tBodies[0]; }

        function cellPos(td) {
            var tr = td.parentElement;
            if (!tr || !tbody() || tr.parentElement !== tbody()) return null;
            return { r: tr.sectionRowIndex, c: td.cellIndex };
        }

        function clearPaint() {
            tbl.querySelectorAll('td.crs-range-cell').forEach(function (n) {
                n.classList.remove('crs-range-cell');
            });
        }

        function paint() {
            clearPaint();
            if (!range || !tbody()) return;
            var rows = tbody().rows;
            for (var r = range.r1; r <= range.r2 && r < rows.length; r++) {
                var cells = rows[r].cells;
                for (var c = range.c1; c <= range.c2 && c < cells.length; c++) {
                    cells[c].classList.add('crs-range-cell');
                }
            }
        }

        function setRange(a, b) {
            range = {
                r1: Math.min(a.r, b.r), r2: Math.max(a.r, b.r),
                c1: Math.min(a.c, b.c), c2: Math.max(a.c, b.c)
            };
            paint();
        }

        function clearAll() {
            range = null;
            clearPaint();
            pop.style.display = 'none';
        }

        function showPop(mx, my) {
            if (!range || !tbody() || (range.r1 === range.r2 && range.c1 === range.c2)) {
                pop.style.display = 'none';
                return;
            }
            var rows = tbody().rows;
            var sum = 0, n = 0;
            for (var r = range.r1; r <= range.r2 && r < rows.length; r++) {
                var cells = rows[r].cells;
                for (var c = range.c1; c <= range.c2 && c < cells.length; c++) {
                    var v = parseNum(cells[c].textContent);
                    if (v !== null) { sum += v; n++; }
                }
            }
            if (!n) { pop.style.display = 'none'; return; }
            pop.textContent = '';
            pop.appendChild(document.createTextNode('Jumlah: '));
            var bSum = document.createElement('b');
            bSum.textContent = sum.toLocaleString('id-ID', { maximumFractionDigits: 2 });
            pop.appendChild(bSum);
            if (n > 1) {
                pop.appendChild(document.createTextNode('  ·  Data angka: '));
                var bN = document.createElement('b');
                bN.textContent = String(n);
                pop.appendChild(bN);
            }
            pop.style.display = 'block';

            // Tempel dekat sel kanan-bawah blok; bila tidak terlihat pakai posisi mouse.
            var x = mx || 0, y = my || 0;
            var lastRow = tbody().rows[range.r2];
            var ce = lastRow && lastRow.cells[Math.min(range.c2, lastRow.cells.length - 1)];
            if (ce) {
                var rc = ce.getBoundingClientRect();
                if (rc.width || rc.height) { x = rc.right + 8; y = rc.bottom + 8; }
            }
            var pr = pop.getBoundingClientRect();
            x = Math.min(Math.max(8, x), global.innerWidth - pr.width - 8);
            y = Math.min(Math.max(8, y), global.innerHeight - pr.height - 8);
            pop.style.left = x + 'px';
            pop.style.top = y + 'px';
        }

        // capture:true supaya tetap jalan walau handler lain memanggil stopPropagation
        tbl.addEventListener('mousedown', function (e) {
            if (e.button !== 0) return;
            var td = e.target.closest ? e.target.closest('td') : null;
            if (!td || !tbl.contains(td)) return;
            if (e.target.closest('button, a, input, select, textarea, label')) return;
            if (td.classList.contains('cb-editing-cell')) return;
            var p = cellPos(td);
            if (!p) return;
            pop.style.display = 'none';
            if (e.shiftKey && anchor) {            // Shift+klik: perluas blok dari anchor
                setRange(anchor, p);
                showPop(e.clientX, e.clientY);
                return;
            }
            anchor = p;
            dragging = true;
            range = null;
            clearPaint();
        }, true);

        tbl.addEventListener('mouseover', function (e) {
            if (!dragging || !anchor) return;
            var td = e.target.closest ? e.target.closest('td') : null;
            if (!td || !tbl.contains(td)) return;
            var p = cellPos(td);
            if (!p) return;
            if (!range && p.r === anchor.r && p.c === anchor.c) return; // belum bergeser
            tbl.classList.add('crs-noselect');
            var sel = global.getSelection && global.getSelection();
            if (sel && sel.removeAllRanges) sel.removeAllRanges();
            setRange(anchor, p);
        }, true);

        document.addEventListener('mouseup', function (e) {
            if (!dragging) return;
            dragging = false;
            tbl.classList.remove('crs-noselect');
            if (!range || (range.r1 === range.r2 && range.c1 === range.c2)) {
                clearPaint();                       // klik tunggal: bukan blok
                range = null;
                return;
            }
            showPop(e.clientX, e.clientY);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var tag = (document.activeElement && document.activeElement.tagName) || '';
            if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA') return;
            clearAll();
        });

        // Popup posisinya fixed — saat area tabel di-scroll, sembunyikan agar
        // tidak "melayang" jauh dari blok selnya.
        document.addEventListener('scroll', function () {
            pop.style.display = 'none';
        }, true);

        // Redraw DataTables mengganti isi tbody — blok lama tidak berlaku lagi.
        if (global.jQuery) {
            global.jQuery(tbl).on('draw.dt', function () {
                range = null;
                pop.style.display = 'none';
            });
        }
    }

    global.CbCellRangeSum = { init: init };
})(window);
