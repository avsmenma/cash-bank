# Design Specification: Smart Sync, Live SSE Progress, & Fix Selisih Saldo

- **Date**: 2026-10-19
- **Status**: Draft / Under Review
- **Authors**: Antigravity Assistant & Engineering Team
- **Target Systems**: Cash & Bank (`/var/www/cash-bank`) and Agenda Online (`/var/www/agenda-online-PTPN`)

---

## 1. Problem Statement & Motivation

1. **Redundant & Heavy Synchronization**:
   Currently, triggering manual sync in Bank Masuk or Bank Keluar unconditionally triggers Agenda Online synchronization. If a user syncs Bank Masuk and then syncs Bank Keluar, Agenda Online is parsed and processed twice back-to-back, wasting 10-15 seconds and server resources.
2. **Missing Progress & Percentage Feedback**:
   The current web UI only displays a spinning font-awesome icon with text `Menyinkronkan...` and no numerical percentage or step indications. Users waiting 30-70 seconds experience anxiety and lack clarity on whether the process is advancing or hung.
3. **Nominal Modification in Spreadsheet Causing Balance Discrepancy ("Selisih")**:
   When users edit the nominal (`debet` or `kredit`) of an existing document in Google Sheets (e.g., correcting an SPP or payment amount), the current reconciliation logic fails to match the row because all matching passes require the nominal to be identical. Consequently:
   - The existing record in the database retains the stale nominal.
   - The modified spreadsheet record is treated as a new row and inserted as an unwanted duplicate or left unmatched.
   - The Dashboard Bank balance (`/dashboard-bank`) diverges from the spreadsheet balance.

---

## 2. Proposed Architecture & Solutions

### A. Smart Sync & Cache Management
- Store `last_agenda_sync_at`, `last_masuk_sync_at`, and `last_keluar_sync_at` in application cache.
- Expose `GET /bank/sync-sheets/status` returning JSON:
  ```json
  {
    "agenda": {
      "last_synced_at": "2026-10-19T14:30:00+07:00",
      "human_time": "15 menit yang lalu",
      "is_fresh": true
    },
    "bank_masuk": {
      "last_synced_at": "2026-10-19T12:00:00+07:00",
      "human_time": "3 jam yang lalu"
    },
    "bank_keluar": {
      "last_synced_at": "2026-10-19T12:00:00+07:00",
      "human_time": "3 jam yang lalu"
    }
  }
  ```
- In the Sync Modal:
  - Checkboxes for `Bank Masuk` (default: checked), `Bank Keluar` (default: checked).
  - Checkbox for `Agenda Online`: If `is_fresh` (synced < 30 minutes ago), it defaults to **unchecked** with a badge `"Sudah Up-to-Date"`, but the user can easily check it if they wish to force an Agenda sync.

### B. Live Real-time SSE Progress Streaming
- Endpoint: `GET /bank/sync-sheets/stream`
- Query parameters:
  - `sync_agenda=1|0`
  - `sync_masuk=1|0`
  - `sync_keluar=1|0`
  - `fix_selisih=1|0`
- Response Headers:
  ```
  Content-Type: text/event-stream
  Cache-Control: no-cache
  Connection: keep-alive
  X-Accel-Buffering: no
  ```
- Event Payload Format:
  ```json
  {
    "status": "running",
    "step": 2,
    "total_steps": 3,
    "percent": 55,
    "step_name": "Bank Masuk",
    "message": "Mencocokkan baris data Bank Masuk (1.450 / 2.659 baris)...",
    "summary": null
  }
  ```
- Completion Event Payload:
  ```json
  {
    "status": "completed",
    "percent": 100,
    "message": "Sinkronisasi selesai dengan sukses!",
    "summary": {
      "agenda": { "status": "skipped" },
      "bank_masuk": { "processed": 2659, "updated": 0, "inserted": 0 },
      "bank_keluar": { "processed": 16780, "updated": 2, "inserted": 0, "fixed_selisih": 2 }
    }
  }
  ```

### C. "Fix Selisih" Discrepancy Resolution Engine
- Added to `/dashboard-bank` as a button: `<button id="btnFixSelisih" class="btn btn-warning btn-sm font-weight-bold"><i class="fas fa-tools mr-1"></i> Fix Selisih Saldo</button>`.
- Reconciles nominal differences:
  - **Bank Keluar**:
    1. Match on `agenda_tahun` (unique composite key, e.g. `5006_2026`). If `agenda_tahun` matches and DB `kredit != csv kredit`, update `kredit` and `nilai_rupiah` in database to match the spreadsheet value!
    2. Match on `no_bukti` where agenda is missing.
    3. Match on high-similarity date + account + description.
    4. Detect and delete duplicate rows sharing the same agenda where one is outdated.
  - **Bank Masuk**:
    1. Match on `no_bukti` or `agenda_tahun`. If nominal differs, update `debet`.
    2. Match on date + account + description.
- Summarizes all modified documents and recalculates VA and bank balances.

---

## 3. UI/UX Design

### Unified Modal Component: `resources/views/cash_bank/partials/syncModal.blade.php`
- **Modal Views**:
  1. **Config View** (when opening "Sync Google Sheets"):
     - Shows checkboxes and status indicators.
     - "Mulai Sinkronisasi" button.
  2. **Progress View** (active while streaming):
     - Large animated progress bar (`.progress-bar-striped .progress-bar-animated`).
     - Large percentage counter (e.g. `68%`).
     - Current step description.
  3. **Result View** (upon completion):
     - Green success banner / checkmark.
     - Summary table (documents updated, inserted, nominals fixed).
     - "Tutup & Muat Ulang" button.

---

## 4. Verification Plan

1. **Non-browser verification**:
   - Verify `GET /bank/sync-sheets/status` returns valid JSON with timestamps.
   - Verify `GET /bank/sync-sheets/stream` streams SSE events with `X-Accel-Buffering: no`.
2. **Playwright E2E verification**:
   - Test modal opening on Bank Masuk, Bank Keluar, and Dashboard Bank.
   - Verify progress bar moves from 0% to 100% with live text.
   - Test "Fix Selisih" on `/dashboard-bank` and verify balance updates after completion.
   - Verify clean state persistence upon `page.reload()`.
