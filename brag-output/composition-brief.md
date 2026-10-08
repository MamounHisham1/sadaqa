# Hyperframes Composition Brief: صدقة — Sadaqa Quran Radio

## Objective
Create a 20-second Arabic (RTL), no-music, no-voice, pure-SFX tutorial-style launch
video showing how to create a Sadaqa dedication link and what that link does.

## Output
- Composition directory: `brag-output/composition/`
- Rendered video: `brag-output/brag.mp4`
- Format: landscape — 1920x1080
- Duration: 20 seconds (user-specified; scenes sum to exactly 20s)

## Source Material
- Project root: `/home/mamoun/ai/sadaqa`
- Primary files read: `README.md`, `resources/views/layout.blade.php`, `resources/views/home.blade.php`, `resources/views/stream.blade.php`, `app/Http/Controllers/HomeController.php`, `public/css/app.css`
- Product name: صدقة (Sadaqa) — Quran Radio
- Tagline / strongest claim: إذاعة قرآنية لا تتوقف — a live Quran radio that never stops; the link stays forever and the rewards keep counting
- Key UI or visual moment to recreate: the create-a-link form (صدقة/هدية radio pills, لمن الصدقة field, reciter pills, green أنشئ الرابط button) and the stream page (dedication card, مباشر live chip, play button, ختمة counter)
- Copy that must appear verbatim:
  - بِسْمِ ٱللَّهِ ٱلرَّحْمَـٰنِ ٱلرَّحِيمِ
  - إذاعة قرآنية لا تتوقف
  - أنشئ رابطًا باسم من تحب
  - صدقة / هدية (radio pills)
  - لمن الصدقة (field label) — typed value: أمي
  - القرّاء (reciter pills label); reciter names e.g. مشاري العفاسي، عبد الباسط، سعد الغامدي
  - أنشئ الرابط (submit button)
  - الرابط جاهز — شاركه (success banner)
  - sadaqa.app/streaming/1835482 (URL reveal) and نسخ (copy button)
  - صدقة عن أمي (dedication card), رحمةً ونورًا (message), من أحمد بمحبة (sender)
  - مباشر (live chip), ختمة كاملة (stats line)
  - رابطٌ واحد · تلاوةٌ لا تتوقف · أجرٌ لا ينقطع (outro line)

## Creative Direction
- Tone preset: polished
- Creative direction: "a serene how-to that feels like a sadaqa invitation — quiet confidence, no noise"
- Interpretation: generous negative space, soft exact motion, the product's own parchment/green identity; restraint matches the reverence of the subject. Tutorial clarity first: the viewer must finish knowing (1) how a link is created and (2) what opening it does.
- Angle: a calm 20-second Arabic tutorial — form → button → link → live radio — ending on صدقة جارية (rewards that never stop).
- Hook: crescent mark + bismillah line settling in, then the headline إذاعة قرآنية لا تتوقف lands fast and holds.
- Outro / punchline: brand lockup صدقة with "رابطٌ واحد · تلاوةٌ لا تتوقف · أجرٌ لا ينقطع" and the bare route /streaming/1835482 as the last readable beat.
- Avoid:
  - Generic SaaS language
  - Abstract filler visuals
  - Unrelated visual redesign
  - Any music bed or voice narration (user disabled both)
  - LTR-styled Arabic: the whole composition is RTL (`dir="rtl"`), text right-aligned where natural

## Visual Identity
- Background: #f6f5f1 parchment (light scenes); #0f1512 night (hook + outro); panels #ffffff / #161e19; lines #e3e0d5 / #2b3931
- Text: #1d231f ink (light), #e9e7e0 (night); muted #5d6a62 / #9baaa1
- Accent: #167a5e deep green (light), #3ba685 (night)
- Display font: Amiri (Arabic display; Amiri Quran for the bismillah line)
- Body font: IBM Plex Sans Arabic
- Fonts must be locally bundled in the composition (download WOFF2 into `assets/fonts/`) — no runtime Google Fonts dependency in the render.
- Visual references from the project: crescent moon SVG brand mark (single-path moon from the topbar), pill-shaped radio/checkbox controls, the pulsing مباشر chip (green dot + label), the play button circle, the success banner with monospace-ish URL, khatma counter line.

## Storyboard
Use the storyboard in `brag-output/brag-plan.md` as the creative contract.

Scene summary:
1. Hook / brand — 3s — night backdrop, crescent draws in, bismillah + headline + subtitle
2. Fill the form — 5s — real form UI, cursor taps صدقة pill, types أمي, reciter pills confirm, presses أنشئ الرابط
3. The link is ready — 4s — success banner, URL reveals, نسخ clicked, link chip flies off
4. What the link does — 5s — stream page: dedication card, مباشر chip pulses, play pressed, progress + ختمة counter tick, 3 device cards light up one by one
5. Outro / brand lockup — 3s — night, crescent + صدقة, closing line, route as final beat

## Audio
- Audio role: **pure SFX only — music AND voice are explicitly disabled by the user.** No `<audio>` music bed, no TTS, no narration. Silence between cues is intentional.
- Audio arc: one warm soft hit on the hook → tactile glass taps through the form → satisfying copy-chirp + whoosh at the link → play-pop + progress/counter ticks + device blips on the stream page → one soft chime at the lockup, then silence.
- Music: none (disabled by user)
- Music treatment: n/a
- Music cue guidance: n/a — no music track exists; SFX are timed to visual events, not beats. No beat-locks or beat-grids required.
- Audio-reactive treatment: none
- Audio-coupled moments:
  - Scene 1 headline land — one soft deep hit + faint shimmer
  - Scene 2 صدقة pill tap — click; typing أمي — randomized keyboard keypresses (RTL text: fire ticks on letter-group reveals); reciter pills — soft ticks; أنشئ الرابط press — click + confirm
  - Scene 3 URL reveal — soft tick per chunk; نسخ click — chirp; link chip fly-off — whoosh
  - Scene 4 مباشر pulse — soft blip; play press — pop; progress bar — subtle ticks; ختمة counter — increment ticks; 3 device cards — one soft blip each, in order
  - Scene 5 lockup settle — single soft chime
- SFX selection guidance: polished tone = minimal but present, low high-frequency-risk files, soft volumes (~0.5–0.7). Prefer `interface/drop_*`, `interface/click_*`, `ui/click*`, `ui/mouseclick1`, `keyboard/keypress-*` (randomized per typed group), `impact/impactSoft_medium_*` for the hook, `impact/impactGlass_light_*` or a soft bell for the outro chime. Read `assets/sfx/sfx-analysis.md` before final picks; avoid aggressive/high-HF-risk files.
- SFX analysis guidance: `~/.agents/skills/brag/assets/sfx/sfx-analysis.md` (+ `.json`)
- Exact SFX choice: Hyperframes chooses filenames, timestamps, density, and volume based on the implemented animation. SFX start at the same timestamp as the visual they accompany.
- Audio files: copy chosen SFX into `brag-output/composition/assets/sfx/…` (no music directory needed).

## Hyperframes Instructions
> **v2 update — real browser captures.** Per user direction ("take the content from the
> actual site — like recording a video from inside the browser"), scenes 2–4 no longer
> rebuild the UI. They composite authentic captures of the running Laravel app
> (booted at 127.0.0.1:8099; a real dedication link `4848998` was created through the
> real `POST /links` endpoint) inside a minimal browser-frame (URL bar + traffic dots).
> Captures live in `composition/assets/captures/` via `capture-states*.cjs` (puppeteer-core,
> 1280×800 @2x, phone 390×780 @3x; local domain masked to `sadaqa.app` in-page).
> Camera punches/scroll-jump cuts + overlay cursor simulate the live recording; audio
> remains pure SFX (no page audio captured). URL token everywhere is the real
> `/streaming/4848998`.
Load the composition-building Hyperframes domain skills — `hyperframes-core`, `hyperframes-animation`, `hyperframes-creative`, `hyperframes-keyframes`, `hyperframes-cli`. /brag is its own workflow: do not enter the `hyperframes` entry-point intent interview and do not route into its generic promo / launch-video workflow.

Requirements:
- Show at least one real UI, copy, or visual element from the source project (the form and stream page recreations satisfy this).
- Keep all text readable in the final render — Arabic lines hold per the reading-time floors (headline ≥1.6s settled; sentence lines ~0.3s/word).
- Total duration exactly 20 seconds.
- Include the planned SFX layer; include NO music and NO voice track.
- No beat sync (no music). Natural timing only.
- Use local assets for fonts and SFX.
- Run `hyperframes check` before render — it is brag's single gate.
