# Report Integrations

A dashboard for generating reports in multiple formats — **PDF** (3 different
engines), **Excel**, **CSV**, **DOCX**, and **fillable PDF forms** — from one
place.

- **`backend/`** — Laravel (PHP) REST API that does the actual file generation.
- **`frontend/`** — React + TypeScript (TSX) single-page app: dashboard,
  sidebar, light/dark mode, and one page per report type.

They run as two separate servers and talk to each other over HTTP. This
document explains how to set both up, written so that someone who has never
touched this project before can follow it step by step.

---

## 1. What you need installed first

| Tool | Why you need it | Check you have it |
|---|---|---|
| PHP 8.2+ | Runs the Laravel backend | `php -v` |
| Composer | Installs PHP packages | `composer -V` |
| Node.js 18+ and npm | Runs/builds the React frontend | `node -v` |
| MySQL (or another DB) | Optional — only needed if you add database features later | — |

Two extra things are **optional**, and only needed if you want to use the
matching PDF engine:

- **wkhtmltopdf** — needed only for the "Snappy" PDF engine.
  Download: https://wkhtmltopdf.org/downloads.html
- **pdftk** — needed only for the "Fillable PDF" feature.
  Download: https://www.pdflabs.com/tools/pdftk-server/

The default PDF engine (**DomPDF**) needs nothing extra — it's pure PHP.
The "Puppeteer" PDF engine needs Node.js (which you already have from the
table above) plus one npm package, installed in step 2.4 below.

---

## 2. Setting up the backend (`backend/`)

### 2.1 Create the actual Laravel project

This folder ships the **custom application code** (controllers, services,
routes, config, views) for the report-generation features — it is not a
full Laravel installation by itself (that would mean shipping thousands of
framework files). So the first step is to generate a fresh Laravel project
and then drop this code into it:

```bash
# From the folder that contains this README:
composer create-project laravel/laravel backend-fresh

# Copy this project's custom files into the fresh install, overwriting
# the matching default files:
cp -r backend/app/. backend-fresh/app/
cp -r backend/config/. backend-fresh/config/
cp -r backend/routes/. backend-fresh/routes/
cp -r backend/resources/. backend-fresh/resources/
cp backend/bootstrap/app.php backend-fresh/bootstrap/app.php
cp backend/.env.example backend-fresh/.env.example
mkdir -p backend-fresh/storage/app/reports backend-fresh/storage/app/templates

# Swap the folders so everything lives under backend/
rm -rf backend
mv backend-fresh backend
```

> If you already know Laravel, you can instead just run
> `composer create-project laravel/laravel backend` and merge the files
> yourself — the instructions above do the same thing.

### 2.2 Install the PHP packages

```bash
cd backend
composer require barryvdh/laravel-dompdf barryvdh/laravel-snappy \
    spatie/browsershot maatwebsite/excel phpoffice/phpword \
    mikehaertl/php-pdftk laravel/sanctum
```

(These are already listed in `backend/composer.json` — running
`composer install` inside a project created from that file will also work.)

### 2.3 Configure your environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set:
- `FRONTEND_URL` — where the React app runs (default `http://localhost:5173`)
- `DB_*` — your database credentials (a database is required now — see 2.3b)
- `WKHTMLTOPDF_BINARY` — full path to wkhtmltopdf, only if you installed it
- `PDFTK_PATH` — full path to pdftk, only if you installed it

### 2.3b Create the database and run the migration

Every generated report is logged as a row in a **`reports`** table, so the
dashboard and history page can list what's been made without scanning the
filesystem.

Create an empty database matching what you put in `.env` (`DB_DATABASE`),
then run:

```bash
php artisan migrate
```

This creates the `reports` table with these columns: `type` (pdf, excel,
csv, docx, or fillable_pdf), `driver` (which PDF engine, when type=pdf),
`title`, `filename`, `path`, `size_bytes`, `metadata` (JSON), and
timestamps.

Don't have MySQL installed and just want to try the app quickly? SQLite
needs no setup:

```bash
touch database/database.sqlite
```
Then in `.env`, replace the `DB_*` lines with just:
```
DB_CONNECTION=sqlite
```

Optional — seed a few example rows so the dashboard isn't empty:
```bash
php artisan db:seed
```

### 2.4 (Optional) Enable the Puppeteer PDF engine

```bash
npm install puppeteer --prefix .
```

This downloads a bundled Chromium browser the first time — it can take a
few minutes.

### 2.5 Run the backend

```bash
php artisan serve
```

The API is now live at **http://localhost:8000/api**. Visit
`http://localhost:8000` in a browser — you should see a small JSON status
message confirming the app is running.

---

## 3. Setting up the frontend (`frontend/`)

```bash
cd frontend
npm install
npm run dev
```

Open **http://localhost:5173** in your browser. The dev server automatically
forwards `/api/...` calls to the Laravel backend on port 8000 (configured in
`frontend/vite.config.ts`), so both servers just need to be running at the
same time — no extra setup required.

To build the frontend for production (a static `dist/` folder you can host
anywhere):

```bash
npm run build
```

---

## 4. Using the app

Once both servers are running:

1. Open http://localhost:5173 — you'll land on the **Dashboard**, showing
   counts of previously generated reports.
2. In the sidebar, open the **Reports** dropdown to see the four report
   types: **PDF, Excel, CSV, DOCX**.
3. Click a type, fill in the small form, and click **Generate**. A
   download link appears once the file is ready.
4. Use the sun/moon button in the top-right corner to switch between
   **light and dark mode** — your choice is remembered on your next visit.

On the **PDF** page specifically, you can pick which rendering engine to
use:
- **DomPDF** — fastest, works out of the box, best for simple documents.
- **Snappy (wkhtmltopdf)** — better CSS support, needs the wkhtmltopdf
  binary installed.
- **Puppeteer** — best visual accuracy (real Chrome), needs Node + the
  puppeteer package.

---

## 5. Where things live (folder guide)

```
backend/
  app/Http/Controllers/Api/   → one controller per report type (Pdf, Excel, Csv, Docx, FillablePdf)
  app/Services/Reports/       → the actual generation logic, engine-agnostic
    PdfDrivers/                 → DompdfDriver, SnappyDriver, PuppeteerDriver
  app/Exports/                → maatwebsite/excel export class
  resources/views/reports/    → Blade templates used by all 3 PDF engines
  routes/api.php              → every /api/reports/... endpoint
  config/reports.php          → default PDF engine, storage paths, binary paths
  storage/app/reports/        → generated files land here
  storage/app/templates/      → put your fillable PDF templates here
  app/Models/Report.php       → one row per generated file, in the `reports` table
  database/migrations/        → creates the `reports` table
  database/seeders/           → optional sample rows (`php artisan db:seed`)

frontend/
  src/layouts/DashboardLayout.tsx → the shell: sidebar + top bar + page content
  src/components/Sidebar.tsx      → the "Reports" dropdown with PDF/Excel/CSV/DOCX links
  src/context/ThemeContext.tsx    → light/dark mode logic
  src/pages/Dashboard.tsx         → landing page with report counts
  src/pages/reports/*.tsx         → one page per report type
  src/lib/api.ts                  → shared HTTP client for calling the backend
```

---

## 6. Adding the fillable-PDF feature

Fillable PDF filling uses **pdftk** to fill in form fields that already
exist on a PDF (e.g. an official form exported from Adobe Acrobat or
LibreOffice with named fields) — it does not create new form fields.

1. Put your template PDF in `backend/storage/app/templates/your-form.pdf`.
2. Call `GET /api/reports/fillable-pdf/fields?template=your-form.pdf` to
   see the exact field names available on that PDF.
3. Call `POST /api/reports/fillable-pdf` with:
   ```json
   {
     "template": "your-form.pdf",
     "values": { "field_name": "value", "another_field": "value" },
     "flatten": true
   }
   ```
   `flatten: true` makes the filled-in text permanent (no longer editable);
   set it to `false` if you want the recipient to still be able to edit it.

There isn't a dedicated frontend page for this yet — it's available on the
backend and easy to wire up to a page the same way the other four are, once
you have a specific template to build a form around.

---

## 7. The `reports` database table

Every controller writes one row here right after it generates a file
(via the `RecordsReport` trait). Two read-only endpoints expose it:

- `GET /api/reports` — paginated list of every report ever generated,
  newest first. Add `?type=pdf` (or `excel`, `csv`, `docx`, `fillable_pdf`)
  to filter to one type.
- `GET /api/reports/types` — a count per type, e.g.
  `{ "pdf": 12, "excel": 4, "csv": 7, "docx": 2, "fillable_pdf": 0 }`.
  This is what the dashboard cards are built from.

If you add a new report type later, add it to `Report::TYPES` in
`app/Models/Report.php` and it will automatically show up (with a count of
`0`) in both endpoints.

## 8. Troubleshooting

- **Dashboard shows "Could not reach the backend"** — make sure
  `php artisan serve` is running and `frontend/vite.config.ts`'s proxy
  target matches its port (default `8000`).
- **Snappy PDF fails** — wkhtmltopdf isn't installed, or
  `WKHTMLTOPDF_BINARY` in `.env` points to the wrong path.
- **Puppeteer PDF fails / hangs** — run `npm install puppeteer` inside
  `backend/` first (step 2.4), and check `NODE_BINARY_PATH` in `.env`.
- **CORS errors in the browser console** — check `FRONTEND_URL` in
  `backend/.env` matches the URL you're opening the frontend from.

---

## What's new in v2.0

- **Redesigned UI**: dark/light tech theme, animated background and logo, responsive sidebar drawer, live engine status pill.
- **Sample gallery** (`/samples`): 14 ready-made reports across 6 generators (DomPDF, Snappy, Puppeteer, Excel, CSV, Word). Preview data, generate, download.
- **Excel / CSV / Word generators** are now implemented (`app/Services/Reports/Writers`).
- **History page** with download and delete, **About page** with live system status.
- New API endpoints:
  - `GET /api/samples`, `GET /api/samples/{id}/preview`, `POST /api/samples/{id}/generate`
  - `GET /api/system/about`, `GET /api/system/health`
  - `DELETE /api/reports/{id}`
- To add your own sample, add an entry to `app/Services/Reports/Samples/SampleCatalog.php` (and a Blade view for PDFs).
- Run the backend tests with `php artisan test`.
