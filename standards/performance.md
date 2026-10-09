# Performance

Best possible performance is the first priority of every design and implementation decision. Only security and
correctness come before it: never trade them for speed.

## 1. Requests

- Respond as early as possible: cheap checks first (invalid input, missing permission, cache hit, `304 Not Modified`),
  before data is loaded or anything is rendered. Early returns in code as well (see [php.md](php.md), section 1).
- Do only the work the response needs: load data and create services when they are used, not up front.
- Work the response does not need (sending emails, image processing, calls to external services) runs after the
  response (`fastcgi_finish_request()`), in a queue or in a cron job.
- External HTTP calls in a request are avoided where possible, otherwise cached and always with a short timeout.

## 2. Database

- No queries in loops (N+1): load related data with a `JOIN` or one `IN (…)` query.
- Select only the needed columns, no `SELECT *`. Filter, sort, aggregate and paginate in SQL, not in PHP.
- Index the columns used in `WHERE`, `JOIN` and `ORDER BY`; check new queries on large tables with `EXPLAIN`.
- Write many rows in one statement or one transaction.

## 3. Caching

- Cache wherever it saves noticeable time: results of expensive queries and computations, responses of external
  services, compiled templates, rendered fragments, configuration.
- Every cache has a key containing everything the result depends on (parameters, language, role), a lifetime, and is
  invalidated when its source data changes.
- Repeated lookups within one request are kept in memory (memoization).
- HTTP: `Cache-Control` and `ETag` or `Last-Modified` for static and rarely changing responses, long lifetime with
  cache busting for assets (see [html-javascript.md](html-javascript.md), section 4).
- Never cache personal data or responses with CSRF tokens in shared caches; such pages send
  `Cache-Control: private, no-store`.
- Production runs with OPcache and an optimized Composer autoloader (`composer install --no-dev --optimize-autoloader`).

## 4. Code

- Keyed arrays for lookups (`array_key_exists()`) instead of searching lists in loops (`in_array()`).
- Invariant work (counts, queries, object creation) moves out of loops.
- Large data is streamed (generators, row by row, file streams) instead of loaded into memory at once.
- Slow pages and queries are found by measuring (profiler, slow query log), and the fix is checked by measuring again.

## 5. Frontend

- One minified CSS and one minified JavaScript file (see [html-javascript.md](html-javascript.md), section 4),
  scripts with `defer`.
- Images in the displayed size and a modern format (WebP, AVIF), with `width` and `height`; `loading="lazy"` below
  the fold.
- Fonts self-hosted as WOFF2 with `font-display: swap`; preload only what the first screen needs.
- The server compresses text responses (Brotli or gzip).
