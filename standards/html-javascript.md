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

- Vanilla JavaScript as ES modules. No framework or build step without a reason agreed with the team.
- One module per purpose, attached to elements via `data-*` attributes, no global variables.
- Progressive enhancement only: the page works without JavaScript, JavaScript improves it.
- No inline `<script>` without a CSP nonce. No `eval()`, `new Function()` or `innerHTML` with untrusted data; use
  `textContent` and DOM methods.
- `const` by default, `let` where reassignment is needed, never `var`. Strict equality (`===`) only.
- Data from the server is passed via `data-*` attributes or JSON (`<script type="application/json">`), not by
  generating JavaScript code.
