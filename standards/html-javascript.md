# HTML and JavaScript

## 1. HTML

- Generated HTML (forms, tables, pagination, templates) works without JavaScript and is valid HTML5.
- Accessible by default ([WCAG 2.2](https://www.w3.org/TR/WCAG22/) level AA as goal): every form field has a `<label>`,
  images have `alt`, semantic elements (`<button>`, `<nav>`, `<main>`, `<table>` with `<th>`), `aria-*` only where
  semantic HTML is not enough, error messages linked to their field (`aria-describedby`).
- Forms: correct `type` and `autocomplete` attributes, `method="post"` for state changes, CSRF token included (see
  [security.md](security.md), section 5).
- No inline styles and no inline event handlers (`onclick`, …); they also conflict with the Content Security Policy.
- CSS classes and `data-*` attributes in kebab-case (see [naming.md](naming.md)).
- In public libraries, changes to generated HTML (markup, CSS classes, attributes) are breaking changes (see
  [versioning.md](versioning.md)).

## 2. JavaScript

- Vanilla JavaScript in `src/js/`, one file per purpose, each wrapped in a block (`{ … }`) so it declares no global
  variables. The files are concatenated and minified into `public/js/scripts.min.js` (see section 4), so they use no
  `import`. No framework or external library without a reason agreed with the team.
- Attached to elements via `data-*` attributes.
- Progressive enhancement only: the page works without JavaScript, JavaScript improves it.
- No inline `<script>` without a CSP nonce. No `eval()`, `new Function()` or `innerHTML` with untrusted data; use
  `textContent` and DOM methods.
- `const` by default, `let` where reassignment is needed, never `var`. Strict equality (`===`) only.
- Data from the server is passed via `data-*` attributes or JSON (`<script type="application/json">`), not by
  generating JavaScript code.

## 3. CSS

- Plain CSS in `src/css/` with custom properties (`--clr-primary`) for colours, spacing and other shared values. No
  preprocessor language (Sass, Less).
- One file per block or component, imported by the entry file `src/css/styles.css`; class names in kebab-case (see
  [naming.md](naming.md)).
- Modern CSS is allowed: the build adds fallbacks and prefixes for the browsers in `"browserslist": ["defaults"]`.
- No inline styles (see section 1); state is shown with classes or attributes (`aria-current`, `[hidden]`), not set by
  JavaScript as style.

## 4. Frontend build

The same npm workflow in every project with own CSS or JavaScript: one request per asset type, modern CSS with
fallbacks. Copy `package.json`, `postcss.config.js`, `stylelint.config.js` and `prettier.config.js` from the
[templates](../templates).

- CSS: PostCSS with `postcss-import` (one file), `postcss-preset-env` (browser support) and `cssnano` (minify) →
  `public/css/styles.min.css`.
- JavaScript: `uglify-js` → `public/js/scripts.min.js`.
- npm scripts: `build`, `css`, `js`, `watch` (`chokidar` and `concurrently`), `lint` and `format` (`stylelint`,
  `prettier`).
- Built files are committed, so servers need no Node.js. Run `npm run build` before committing; never edit them by
  hand.
- Cache busting with a version query that changes with every build of the file (`/css/styles.min.css?v=20260922`).
