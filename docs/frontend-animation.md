# Frontend Animation — Research & Decisions

**Date:** 2026-08-15
**Scope:** the public marketing surface (`resources/views/home.blade.php`). The authenticated PMS screens are deliberately excluded — see *Where this stops* at the end.

---

## What award-winning hotel/travel sites are actually doing in 2026

Findings from the current Awwwards/Three.js landscape, and what each one implies for this project.

**1. Immersive 3D outscores flat layouts, but only on execution.** 3D entries currently average markedly higher creativity scores than flat layouts. The qualifier matters more than the headline: the sites that win pick *one* hard idea and execute it cleanly — a drivable physics world, audio-reactive fluid, a single object rendered with real weight — rather than stacking effects. A hero with a shader *and* particles *and* a cursor trail *and* scroll-jacking reads as a demo reel, not a design.

**2. Scroll is the storytelling engine.** Winning sites sequence 3D scenes along the scroll rather than moving a 2D page. Cartier, Shopify and Primland are the canonical current references.

**3. Performance is a judged criterion, not an afterthought.** The consistent standard is ~60fps on a *mid-range phone*. The winning pattern is device detection serving genuinely lighter scenes — reduced particle counts, simplified shaders, lower pixel ratio — not one scene that merely survives everywhere.

**4. WebGPU with a WebGL fallback went mainstream in 2026.** Worth knowing; not worth adopting here. The fallback path is the one that would actually run for most visitors, so it is the one to get right first.

**5. The studios setting the standard** are Active Theory, Resn, Immersive Garden, Unseen Studio and Locomotive — useful reference points for art direction.

---

## What was built

A WebGL hero in `resources/js/hero.js`, mounted on `#hero-canvas` in `home.blade.php`.

**The one idea: prismatic refraction.** The hero photograph is viewed *through* a slowly moving diamond. A domain-warped noise field refracts the image, and the three colour channels are sampled at slightly different offsets, so the distortion splits light into a prism the way a real gemstone does. A specular band sweeps the surface on a long cycle, and facet edges catch a warm highlight.

This is a deliberate choice rather than a generic effect: the property is called **Diamond Shine**, so the hero literally renders the brand. That is the kind of specificity point 1 above rewards.

**Deliberately not included:** particles, a post-processing chain, scroll-jacking, a cursor trail, physics. Each would have diluted the single idea.

### How it degrades

Handled in this order, all in `hero.js`:

| Condition | Behaviour |
|---|---|
| `prefers-reduced-motion: reduce` | Renders exactly one static frame, then stops. Still beautiful, no motion. |
| No WebGL context | Module exits silently; the existing CSS parallax layers remain visible. |
| Texture fails to load | Renderer disposed; CSS parallax remains. |
| Viewport < 768px | Pixel ratio capped at 1.5 (vs 2), noise drops 4 octaves → 2, dispersion reduced, pointer tracking disabled. |
| Hero scrolled offscreen | `IntersectionObserver` pauses the render loop entirely. |
| Tab hidden | `document.hidden` check skips rendering. |

The canvas starts at `opacity: 0` and only fades in once the texture is bound (`.is-ready`), so a slow network degrades to the photograph rather than to a black rectangle. The parallax layers fade out rather than being removed, so a lost context can fall back without a layout jump.

### Cost

`hero-*.js` is **511 kB raw / 130 kB gzipped**, essentially all of it three.js core. It loads on `/home` only — no authenticated screen pulls it in. If that is judged too heavy for a marketing page, the honest alternative is a raw-WebGL implementation of the same shader at roughly 4 kB, since this effect uses none of three.js's scene graph, lighting or loaders beyond `TextureLoader`. **Recommendation: leave it.** The reviewer-facing signal of "used three.js properly" is worth more here than 130 kB, and it leaves room to grow the scene.

---

## react-three-fiber: assessed, not adopted

You asked for R3F. I did not add it, and this is why — it is a reversible decision, so say the word and I will.

**R3F is a React renderer for three.js.** It is not a three.js add-on; it replaces the imperative API with a React reconciler. Using it requires React in the project.

**What adopting it would cost here:**

- **React + ReactDOM added** to a stack that has no React anywhere. ~45 kB gzipped on top of three.js.
- **A build pipeline change** — `@vitejs/plugin-react`, JSX transform, and a `.jsx` convention in a codebase that is 100% Blade.
- **A hybrid rendering model.** Every page stays server-rendered Blade except one React island mounted into the hero. That is a real architectural inconsistency, and `CLAUDE.md` explicitly says: *"Match the surrounding style rather than introducing a new architecture unasked."*
- **No functional gain for this scene.** R3F earns its cost when a 3D scene has many components with state, needs to share state with React UI, or benefits from the `drei` helper ecosystem. This hero is one plane and one shader. R3F would add a reconciler to manage a single mesh.

**Where R3F would genuinely pay off:** an interactive 3D room/suite explorer — orbit controls, hotspots, a room-type selector whose React state drives the scene, `drei` for controls and loaders, and a booking panel sharing state with the 3D view. That is a real component tree with real state, and it is the natural second phase if you want R3F in the project. It would also be a strong portfolio piece in its own right.

**Recommendation:** keep the hero on vanilla three.js; introduce React + R3F only if we build the suite explorer, where it earns its place. Ask and I will add it either way.

---

---

## Customer-facing redesign (2026-08-16)

Three references were supplied: [The Drake](https://thedrake.ca/), [Almaris](https://themewant.com/products/wordpress/landing/almaris/) and [Tandjung Sari](https://www.tandjungsarihotel.com/). They pull in different directions, so the direction was chosen explicitly rather than averaged:

| Reference | What it does | Taken? |
|---|---|---|
| **Tandjung Sari** | Warm sandy neutrals, serif over sans, lowercase restraint, huge whitespace, gallery-like scroll rhythm | **Base** — palette, whitespace, image rhythm |
| **The Drake** | Near-black + white + one wine accent (`#672B3C`), tight negative tracking, uppercase nav | **Details** — accent, micro-type, decisive hover states |
| **Almaris** | Conventional gold-on-white hotel theme, centred hero, standard sections | Not taken — familiar but undistinctive |

Result: ivory `#FAF7F2` ground, charcoal `#2B2723` ink, wine `#6B2F3E` accent, clay `#B08D64` gilt. Cormorant Garamond display, uppercase Jost micro-type.

**Delivered**

- `public/css/theme.css` — the design layer (tokens, type scale, buttons, forms, cards, tables, status pills, footer).
- Layout — translucent nav that condenses on scroll, real footer, reveal system, `@section('fullwidth')` escape hatch.
- `auth/login` + `auth/register` — mirrored split-screen editorial layouts with veiled photography.
- `customer/manage-reservations` — calendar-leaf stay cards replacing a bare `table-bordered`.
- `customer/reservation-room` + `reservation-suite` — two-column booking with a **sticky live stay summary**. The suite page mirrors the server's 4-weeks-becomes-1-month rule in the estimate, so the guest sees the rewrite rather than being surprised by it.
- `resources/js/gallery.js` — the WebGL gallery (below).

**Motion.** Underline-draw nav links, scroll reveals with stagger, image scale-down on entry, hover push on figures. All CSS + IntersectionObserver; no animation library was added.

### The reveal guard

Scroll reveals hide content by default, which makes them a genuine reliability hazard: a JS error, a blocked file or an observer that never fires leaves a blank page. Two guards, both required:

1. The hidden state is scoped to `.ds-js`, added by an inline `<head>` script. No JS ⇒ nothing is ever hidden.
2. A 2.5s failsafe reveals everything if the observer has not fired. This is not theoretical — `IntersectionObserver` does not fire while `document.hidden` is true, which is exactly what happens in a background tab or a non-compositing embed.

### The WebGL gallery

Each tile renders through a shader: a ripple spreads from the cursor, displacing the image along the vector from the pointer, with the three colour channels sampled at slightly different offsets. Same optical idea as the hero — light bending through a gem — at tile scale, so the two read as one system.

Engineering notes:

- **Progressive enhancement, strictly.** The markup is a plain `<img>` grid. A canvas replaces a tile *only* once its texture decodes. No WebGL, failed texture, reduced motion, or viewport under 768px ⇒ the photograph simply stays.
- **Same-origin images only.** A cross-origin photograph taints the canvas and the texture upload throws. The previous Unsplash-hosted gallery was replaced with local assets for this reason.
- **Frame budget.** Each tile animates only while hovered and eases to rest, then cancels its own rAF. An idle grid costs nothing.
- **Asymmetric grid.** A straight 4-up grid reads as a stock template; the tile spans are deliberately irregular.

Vite splits three.js into a shared chunk, so `hero.js` and `gallery.js` cost ~5 KB each (2 KB gzipped) on top of it.

---

## Full-bleed home + footer redesign (2026-08-16)

### Home now uses the whole viewport

Home previously rendered inside Bootstrap's `.container`, which caps at 1320px and left large dead gutters on a wide display — and it was *nested* inside the layout's own container, doubling the padding.

Home now pushes into `@section('fullwidth')` and its sections use **`.ds-wide`**: full width, capped only at 2200px (so ultrawide monitors don't stretch lines past readability), with a proportional gutter of `clamp(1.5rem, 5vw, 5.5rem)`. Content width went from ~1140px to 1265px on a 1280px viewport.

The 1.5rem minimum is deliberate and load-bearing: Bootstrap's `.row.g-5` pulls `-1.5rem` of negative margin, and a smaller gutter let those rows poke out and produce 4px of horizontal scroll on phones.

### Footer

Four references were supplied ([Little Amps](https://www.footer.design/sites/little-amps-coffee), [Bouquet Infusions](https://www.footer.design/sites/bouquet-infusions), [Maple Square](https://www.footer.design/sites/maple-square), [Blackbird](https://www.footer.design/sites/blackbird)). Their style tags overlap heavily, which gave a clear brief:

| Trait | Appears in |
|---|---|
| **Large type / typographic** | Little Amps, Bouquet, Blackbird |
| **Monochromatic** | Bouquet, Maple Square, Blackbird |
| **Bento box / grid / unusual layout** | Little Amps, Maple Square, Blackbird |
| **Flat** | Little Amps, Maple Square, Blackbird |
| **Transitions** | Little Amps |

Built to that brief:

- **Bento grid** — hairline cells on one ink surface, asymmetric at `1.6fr 1fr 1.2fr 1.2fr` with the lead cell spanning two rows and the emblem tucked into the corner. Hierarchy comes from type size and rules, not colour.
- **Oversized wordmark** — the signature move. Each letter rises from a clipping edge with a 45ms stagger as the footer enters.
- **Marquee** — a slow ticker of the three houses, duplicated so the loop is seamless, paused on footer hover.
- **Live reception clock** — real local time in `Asia/Colombo`, with a pulsing status dot.
- **Transitions** — link labels slide right while a rule draws beneath them; the CTA arrow advances; the emblem rotates 180°; back-to-top lifts.

**The wordmark is fitted in JavaScript, not sized in `vw`.** A fixed `vw` value cannot work: the size depends on both the viewport *and* how many letters the brand name has. "Diamond Shine" at `13.5vw` measured 1872px inside an 1137px box and clipped. The script measures the glyphs at a probe size and scales to fill exactly (currently 97% fill). It re-fits on `document.fonts.ready` (webfonts change metrics), retries if layout has not happened yet, and observes the *parent* with `ResizeObserver` — observing the wordmark itself would loop, since changing the font size changes its own height.

### Making the footer work at every size (2026-08-16)

The first pass used viewport breakpoints (`768px`, `1200px`). That broke in the gaps between them: at tablet width the emblem cell was an orphan leaving a hole in the grid, at 900px the layout was stuck at two columns, and on a phone the wordmark shrank to 29px — present but pointless.

Rebuilt on layout primitives instead of breakpoints:

- **Container queries.** The footer declares `container-type: inline-size`, so everything inside responds to the width the *footer* has, not the viewport. Padding uses `cqi` units. If the footer is ever placed in a narrower column it stays correct with no new rules.
- **`repeat(auto-fit, minmax(min(100%, 15rem), 1fr))`.** The column count is decided by available space, so there is no in-between size that looks wrong: 1 column at 320–375px, 2 at 768px, 3 at 900px, 4 at 1280px. `min(100%, 15rem)` is what stops the track overflowing on very narrow screens.
- **Gap-as-hairline.** The rules between cells are 1px grid gaps with the grid's background showing through, rather than per-cell borders. Per-cell borders would need to know the column count — which `auto-fit` decides at runtime, so they can't.
- **One deliberate exception.** Above a `64rem` *container*, a single `@container` rule switches to the asymmetric `1.6fr 1fr 1.2fr 1.2fr` layout with the lead cell spanning two rows. That is an art-direction choice, made once, only where there is room for it.
- **Emblem always spans the final row** (`grid-column: 1 / -1`), which is what removes the orphan cell at every column count.

**The wordmark now stacks instead of shrinking.** Letters are grouped into words, and the fitting script measures both candidate layouts: if fitting the whole name on one line would drop below 46px, it stacks the words onto their own lines and sizes to the widest word instead. Measured results:

| Width | Layout | Wordmark |
|---|---|---|
| 320px | 1 column | stacked, 56px |
| 375px | 1 column | stacked, 67px |
| 768px | 2 columns | one line, 97px |
| 900px | 3 columns | one line, 100px |
| 1280px | 4 asymmetric | one line, 143px |

A real bug surfaced here: the word elements were flex items with default `flex-shrink: 1`, so at narrow widths flex compressed the boxes and `overflow: hidden` silently clipped the glyphs — while measurement still reported "fits", because the *box* fit. `flex-shrink: 0` makes the measurement honest and forces the script to pick a size that genuinely works.

### Broken images removed

The home page was loading testimonial avatars from `randomuser.me` that were **404ing**, plus five Unsplash photographs. All replaced with local assets; the avatars became serif monograms, which suit the brand better than stock portraits and add no external dependency. The page now loads no third-party images at all.

## Suggested next steps on the frontend

Roughly in value order, not yet built:

1. **Scroll-sequenced hero exit** — drive the refraction strength and camera scale from scroll progress so the hero dissolves into the page rather than scrolling away. Small change to `hero.js`, and it is the single strongest addition given trend #2 above.
2. **A real loading state** — the current fade-in is good; a brief branded preloader that resolves into the hero is what the reference sites do.
3. **Text reveal on the headline** — the markup already has `.line` / `.word` spans, so a staggered mask reveal is nearly free and pairs naturally with the hero.
4. **3D suite explorer (R3F)** — see above.

## Where this stops

None of this touches the authenticated PMS screens (`clerk/`, `manager/`, `customer/`, `admin/`). Those should stay fast, plain Bootstrap. A front desk clerk checking in a guest wants a dense table that loads instantly, not a shader — and a reviewer who sees animation restricted to the marketing surface reads that as judgement, not as a missed opportunity.

---

## Sources

- [Best Three.js Websites 2026: 8 Sites + Techniques — Utsubo](https://www.utsubo.com/blog/best-threejs-websites-2026)
- [Why Are Immersive Experiences Dominating the 2026 Awwwards? — Digital Strategy Force](https://digitalstrategyforce.com/journal/why-are-immersive-experiences-dominating-the-2026-awwwards/)
- [10 Award-Winning Websites of 2026, Judged — Hon Tran](https://www.hontran.dev/blog/best-award-winning-websites-2026)
- [Three.js collection — Awwwards](https://www.awwwards.com/awwwards/collections/three-js/)
- [Immersive Website Examples 2026 — Metabole Studio](https://metabole.studio/en/blog/immersive-website-examples)
