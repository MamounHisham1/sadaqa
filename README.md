<div align="center">

# صدقة · Sadaqa Quran Radio

**A Quran radio that never stops. Dedicated to someone you love.**

One global recitation, streamed live like a real radio station. Anyone can create a
dedication link in a beloved one's name — everyone who opens it joins the same recitation
at the same moment, and the rewards keep counting.

`Laravel` · `SQLite` · `Vanilla JS` · no API keys · no queues · no paid services

</div>

---

## What it does

- **Live synchronized radio** — open any link and you hear exactly what every other
  listener hears *right now*: same surah, same second. Leaving and coming back rejoins
  the live moment.
- **Sadaqa links** — pick a dedication (صدقة or هدية), optionally a name, a short message,
  and which reciters to rotate through. You get a simple shareable URL like
  `/streaming/1835482` with a live counter of ayahs recited and khatmas completed.
- **Khatma reciter rotation** — one reciter recites the entire mushaf, then hands over to
  the next. A link that checked only 3 reciters rotates between those 3, one full khatma
  each, forever.
- **Full-surah audio** — the stream plays one complete surah file at a time (not per-ayah
  fragments), so there is almost nothing to get out of sync.

No Quran text is rendered on the stream page — the radio is just recitation, a now-playing
card, and a play button.

## How the synchronization works

There is no streaming media server. The station position is **pure math**:

```
position(now) = walk( now − epoch, surah durations of the chosen reciters )
```

- A single DB row stores only the broadcast **epoch** — a fixed point in time.
- `GET /api/stream/now` walks the timeline from the epoch (a few thousand arithmetic
  steps, cached for 1s) and returns `{surah, reciter, offset, duration, next}`.
- The browser seeks into the current surah MP3 at `offset` (plain HTTP range requests on
  the CDN), **preloads the next surah** in a second `<audio>` element, and swaps on a
  timer armed from the server's exact duration — boundaries are gapless.
- A quiet background correction nudges the clock if drift exceeds a few seconds; pausing
  and resuming jumps back to live.

Because position is a pure function, links with different reciter selections get their own
consistent schedules from the same epoch — and the server stays **O(1)** in listeners and
links: no per-listener state, no RAM per link, audio bytes never touch your server.

## Recitation source

Full-surah MP3s come from [mp3quran.net](https://mp3quran.net) (128 kbps, 12 reciters,
all verified). Durations are measured once via `HEAD` content-length and cached forever
in the `surah_durations` table — the schedule is exact, never estimated.

## Quick start

```bash
git clone https://github.com/MamounHisham1/sadaqa.git
cd sadaqa

composer install
cp .env.example .env
php artisan key:generate

# one-time: fetch mushaf metadata (surah names, ayah counts, revelation places)
php artisan quran:fetch

# one-time: measure all surah durations for every reciter (~1400 HEAD requests)
php artisan quran:warm-durations all

php artisan migrate
php artisan serve      # → http://127.0.0.1:8000
```

Requires PHP 8.2+ with `pdo_sqlite`, `mbstring` and `curl`. SQLite works out of the box;
MySQL/Postgres and Redis are drop-in for larger audiences.

## Routes

| Route | Purpose |
|---|---|
| `/` | Landing page + create-a-link form (everything optional) |
| `/radio` | The bare live station |
| `/streaming/{code}` | A dedication link's live page |
| `GET /api/stream/now` | Station position — `?r=id1,id2` for a custom reciter rotation |
| `GET /api/quran/page/{n}` | Mushaf page JSON (kept for future text features) |
| `POST /api/links/{token}/played` | Best-effort listener counter (throttled) |

## Reciters

Mishary Alafasy · Abdul Basit · Sudais · Maher Al Muaiqly · Minshawi · Husary ·
Ash-Shatri · Shuraim · Hudhaify · Ajamy · Hani Ar-Rifai · Muhammad Ayyub

Add or swap reciters in [`config/quran.php`](config/quran.php) — one entry with a name
and a full-surah MP3 base URL is all it takes.

## Admin & feedback

- Footer on every page links to a feedback form (bug / feature request,
  optional contact). Set `ADMIN_PASSWORD` in `.env`, then visit `/admin`
  to read reports, mark them handled, and browse recent links.
- Links created with a **password** can be edited later by anyone who
  knows it: an edit button appears on the link page and the password
  gates the changes (hashed at rest, rate-limited).

## Deployment notes

- Point nginx/Apache at `public/`, run `php artisan migrate` and the two `quran:*`
  commands once, and you're live.
- The epoch lives in `stream_state.started_at`; the station has effectively been
  broadcasting since the moment it was first seeded.
- SQLite (WAL) comfortably serves thousands of listeners at one poll per surah; switch
  `DB_CONNECTION` and `CACHE_STORE` when you outgrow it.

## License

[MIT](LICENSE) — recitation audio is served by mp3quran.net and remains the property of
its reciters and publishers.

<div align="center">

﴿ وَمَا تُقَدِّمُوا لِأَنفُسِكُم مِّنْ خَيْرٍ تَجِدُوهُ عِندَ اللَّهِ ﴾

</div>
