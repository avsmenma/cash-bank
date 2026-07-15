/**
 * CbInfiniteTable — muat data DataTables secara bertahap (chunk 100 baris)
 * tanpa pagination: chunk berikutnya di-fetch saat scroll mendekati dasar
 * .dataTables_scrollBody, lalu di-APPEND (baris lama tidak dibuang).
 *
 * Tabel harus diinisialisasi dalam mode klien (serverSide: false, paging: false).
 * Data tetap diambil dari endpoint server-side (protokol DataTables/Yajra:
 * draw, start, length, search[value], columns[i][search][value]) sehingga
 * pencarian & filter kolom tetap dieksekusi di server terhadap SELURUH data —
 * setiap perubahan filter mengosongkan akumulasi dan mulai lagi dari chunk 1.
 *
 * API:
 *   var loader = CbInfiniteTable.init('#tabel', {
 *     url: '...',                    // endpoint data (wajib)
 *     chunkSize: 100,
 *     extraParams: function () {},   // param tambahan per request (mis. filter tanggal)
 *     onResponse: function (json) {} // dipanggil tiap respons (mis. totals ke tfoot)
 *   });
 *   loader.setGlobalSearch(v); loader.setColumnSearch(idx, v);
 *   loader.applyFilters();  // reset akumulasi + fetch ulang chunk 1
 *   loader.reload();        // refetch semua baris yang sudah dimuat, posisi scroll dipertahankan
 *   CbInfiniteTable.get('#tabel'); window.cbTableReload('#tabel');
 */
(function (window, $) {
    'use strict';

    var registry = {};

    function Loader(selector, options) {
        this.selector = selector;
        this.table = $(selector).DataTable();
        this.url = options.url;
        this.chunkSize = options.chunkSize || 100;
        this.prefetchPx = options.prefetchPx || 600;
        this.extraParams = options.extraParams || function () { return {}; };
        this.onResponse = options.onResponse || null;
        this.infoTarget = options.infoTarget || null;

        this.drawCounter = 0;
        this.loadedCount = 0;
        this.totalFiltered = null;
        this.loading = false;
        this.failed = false;
        this.globalSearch = '';
        this.columnSearches = {};

        this._bind();
    }

    Loader.prototype._scrollBody = function () {
        return $(this.table.table().container()).find('.dataTables_scrollBody').get(0);
    };

    Loader.prototype._processing = function (show) {
        $(this.table.table().container()).find('.dataTables_processing')
            .css('display', show ? 'block' : 'none');
    };

    Loader.prototype._infoElement = function () {
        if (this.infoTarget) return $(this.infoTarget);
        return $(this.table.table().container()).find('.dataTables_info');
    };

    Loader.prototype._infoText = function () {
        var $info = this._infoElement();
        if (!$info.length) return;
        if (this.totalFiltered === null) return;
        var text;
        if (this.totalFiltered === 0) {
            text = 'Tidak ada data.';
        } else if (this.loadedCount >= this.totalFiltered) {
            text = 'Semua ' + this.totalFiltered.toLocaleString('id-ID') + ' data telah dimuat.';
        } else {
            text = this.loadedCount.toLocaleString('id-ID') + ' dari ' +
                this.totalFiltered.toLocaleString('id-ID') +
                ' data dimuat — scroll ke bawah untuk memuat berikutnya.';
        }
        $info.text(text);
    };

    Loader.prototype._buildParams = function (start, length) {
        var params = {
            draw: ++this.drawCounter,
            start: start,
            length: length,
            search: { value: this.globalSearch || '', regex: false },
            columns: []
        };

        var self = this;
        var settings = this.table.settings()[0];
        this.table.columns().every(function (index) {
            var dataSrc = this.dataSrc();
            var col = settings && settings.aoColumns ? settings.aoColumns[index] : null;
            params.columns.push({
                data: typeof dataSrc === 'string' ? dataSrc : index,
                name: col && col.sName ? col.sName : '',
                searchable: col ? !!col.bSearchable : true,
                orderable: col ? !!col.bSortable : false,
                search: { value: self.columnSearches[index] || '', regex: false }
            });
        });

        return $.extend(params, this.extraParams() || {});
    };

    Loader.prototype._fetch = function (start, length) {
        var self = this;
        return $.ajax({
            url: this.url,
            method: 'GET',
            data: this._buildParams(start, length),
            dataType: 'json'
        }).done(function (json) {
            if (self.onResponse) self.onResponse(json);
        });
    };

    Loader.prototype._nearBottom = function () {
        var sb = this._scrollBody();
        if (!sb) return true;
        return sb.scrollTop + sb.clientHeight >= sb.scrollHeight - this.prefetchPx;
    };

    Loader.prototype.loadNext = function (force) {
        var self = this;
        if (this.loading) return;
        if (this.totalFiltered !== null && this.loadedCount >= this.totalFiltered) return;
        if (!force && !this._nearBottom()) return;

        this.loading = true;
        this.failed = false;
        this._processing(true);

        // Simpan posisi scroll: DataTables mereset scroll body ke atas saat
        // draw(), sehingga tanpa ini scrollbar "tertarik balik" ke atas tiap
        // kali chunk baru di-append saat user menggeser.
        var sb = this._scrollBody();
        var keepScroll = sb ? sb.scrollTop : 0;

        this._fetch(this.loadedCount, this.chunkSize)
            .done(function (json) {
                var rows = (json && json.data) || [];
                self.totalFiltered = json && typeof json.recordsFiltered !== 'undefined'
                    ? parseInt(json.recordsFiltered, 10)
                    : (rows.length < self.chunkSize ? self.loadedCount + rows.length : null);
                if (rows.length) {
                    self.table.rows.add(rows).draw(false);
                    self.loadedCount += rows.length;
                    if (sb) sb.scrollTop = keepScroll;   // kembalikan posisi setelah draw
                } else if (self.totalFiltered === null) {
                    self.totalFiltered = self.loadedCount;
                }
                self._infoText();
                // Chunk pertama bisa lebih pendek dari viewport — isi sampai penuh.
                window.requestAnimationFrame(function () { self.loadNext(false); });
            })
            .fail(function () {
                self.failed = true;
                var $info = self._infoElement();
                if ($info.length) $info.text('Gagal memuat data. Scroll lagi untuk mencoba ulang.');
            })
            .always(function () {
                self.loading = false;
                self._processing(false);
            });
    };

    Loader.prototype.applyFilters = function () {
        this.loadedCount = 0;
        this.totalFiltered = null;
        this.table.clear().draw(false);
        var sb = this._scrollBody();
        if (sb) sb.scrollTop = 0;
        this.loadNext(true);
    };

    Loader.prototype.setGlobalSearch = function (value) {
        this.globalSearch = value || '';
    };

    Loader.prototype.setColumnSearch = function (index, value) {
        this.columnSearches[index] = value || '';
    };

    Loader.prototype.clearColumnSearches = function () {
        this.columnSearches = {};
    };

    /** Refetch semua baris yang sudah dimuat dalam satu request; scroll dipertahankan. */
    Loader.prototype.reload = function () {
        var self = this;
        if (this.loading) return;
        var count = Math.max(this.loadedCount, this.chunkSize);
        var sb = this._scrollBody();
        var keepScroll = sb ? sb.scrollTop : 0;

        this.loading = true;
        this._processing(true);
        this._fetch(0, count)
            .done(function (json) {
                var rows = (json && json.data) || [];
                self.table.clear();
                self.table.rows.add(rows).draw(false);
                self.loadedCount = rows.length;
                self.totalFiltered = json && typeof json.recordsFiltered !== 'undefined'
                    ? parseInt(json.recordsFiltered, 10)
                    : rows.length;
                self._infoText();
                if (sb) sb.scrollTop = keepScroll;
            })
            .always(function () {
                self.loading = false;
                self._processing(false);
            });
    };

    Loader.prototype._bind = function () {
        var self = this;
        var sb = this._scrollBody();
        if (sb) {
            sb.addEventListener('scroll', function () {
                self.loadNext(false);
            }, { passive: true });
        }

        // Kotak pencarian bawaan DataTables: alihkan ke pencarian server
        // (pencarian klien hanya melihat baris yang sudah dimuat — tidak lengkap).
        var $searchInput = $(this.table.table().container()).find('.dataTables_filter input');
        if ($searchInput.length) {
            $searchInput.off();
            var timer = null;
            $searchInput.on('input', function () {
                var value = this.value;
                if (timer) clearTimeout(timer);
                timer = setTimeout(function () {
                    self.setGlobalSearch(value);
                    self.applyFilters();
                }, 400);
            });
        }
    };

    var CbInfiniteTable = {
        init: function (selector, options) {
            var loader = new Loader(selector, options);
            registry[selector] = loader;
            loader.loadNext(true);
            return loader;
        },
        get: function (selector) {
            return registry[selector] || null;
        }
    };

    window.CbInfiniteTable = CbInfiniteTable;
    window.cbTableReload = function (selector) {
        var loader = registry[selector];
        if (loader) loader.reload();
    };
})(window, window.jQuery);
