/* ============================================================
   Sadaqa — Quran Radio player (full-surah streams)
   One global station: /api/stream/now says which surah is on
   air and the offset; the client seeks into the surah file,
   preloads the next surah in a second audio element, and swaps
   on the station clock. A surah can be heard on demand; a
   "return to live" button rejoins the station.
   ============================================================ */
(() => {
  const CFG = window.__QURAN__;
  if (!CFG) return;

  const $ = (s) => document.querySelector(s);
  const AR = "٠١٢٣٤٥٦٧٨٩";
  const toAr = (n) => String(n).replace(/\d/g, (d) => AR[+d]);
  const chapter = (id) => CFG.chapters[id - 1];
  const reciterInfo = (id) => CFG.reciters.find((r) => r.id === id) || CFG.reciters[0];
  // The link decides which reciters rotate (null = all of them).
  const ROTATION = (CFG.rotation && CFG.rotation.length ? CFG.rotation : CFG.reciters.map((r) => r.id));

  function surahUrl(surah, reciterId) {
    const rec = reciterInfo(reciterId);
    return rec.surah_sources[0].base + String(surah).padStart(3, "0") + ".mp3";
  }

  // ---------- state ----------

  const state = {
    mode: "live",          // live | ondemand
    surah: 1,
    reciterId: CFG.reciters[0].id,
    pass: 0,
    started: false,
    sessionAyahs: 0,       // ayahs credited from completed surahs
    pending: 0,
    lastFlush: Date.now(),
    lastResync: 0,
  };

  // What plays next per the station: {surah, reciterId, duration, pass, ayahCount}
  const schedule = { timer: 0, deadline: 0, next: null };

  // ---------- audio: two swap-able elements ----------

  const A = new Audio();
  const B = new Audio();
  let active = A;
  let lastSwapAt = 0;

  function setup(el) {
    el.preload = "auto";
    el.volume = 0.95;
    el.addEventListener("ended", onEnded);
    el.addEventListener("timeupdate", onTime);
    el.addEventListener("error", onError);
    el.addEventListener("play", () => { if (el === active) setPlayIcon(true); });
    el.addEventListener("pause", () => { if (el === active) setPlayIcon(false); });
  }
  setup(A);
  setup(B);

  const other = () => (active === A ? B : A);

  function seekAndPlay(el, offset) {
    const seek = () => {
      try { el.currentTime = Math.max(0, offset || 0); } catch { /* tiny file */ }
      el.removeEventListener("loadedmetadata", seek);
    };
    if (el.readyState >= 1) seek();
    else el.addEventListener("loadedmetadata", seek);
    el.play().catch(() => {});
  }

  function localNext(surah, pass) {
    const wrapped = surah >= 114;
    const n = wrapped ? 1 : surah + 1;
    const nPass = wrapped ? (pass ?? 0) + 1 : (pass ?? 0);
    return {
      surah: n,
      pass: nPass,
      reciterId: ROTATION[nPass % ROTATION.length],
      duration: 0,
      ayahCount: CFG.counts[n - 1],
    };
  }

  /** Play a surah now; arm the next boundary on the station's clock. */
  function playSurah(surah, reciterId, offset, duration) {
    state.surah = surah;
    state.reciterId = reciterId;

    const want = surahUrl(surah, reciterId);
    if (!active.src || !active.src.startsWith(want)) {
      active.src = want;
    }
    seekAndPlay(active, offset);

    const nxt = (schedule.next && schedule.next.surah !== surah)
      ? schedule.next
      : localNext(surah, state.pass);
    const nUrl = surahUrl(nxt.surah, nxt.reciterId);
    const idle = other();
    if (!idle.src || !idle.src.startsWith(nUrl)) {
      idle.src = nUrl;
      idle.load();
    }

    armBoundary(duration, offset);
    updateNowPlaying();
    updateLiveChip();
  }

  /** The station clock decides when the surah ends — not the ended event. */
  function armBoundary(duration, offset) {
    clearTimeout(schedule.timer);
    if (state.mode !== "live" || !duration) return;
    const ms = Math.max(200, ((duration - (offset || 0)) * 1000) - 150);
    schedule.deadline = performance.now() + ms;
    schedule.timer = setTimeout(swapToNext, ms);
  }

  /** Gapless boundary: swap to the preloaded next surah, confirm with station. */
  function swapToNext() {
    if (!state.started || state.mode !== "live") return;
    const now = performance.now();
    if (now - lastSwapAt < 500) return;
    lastSwapAt = now;
    clearTimeout(schedule.timer);
    const nxt = schedule.next || localNext(state.surah, state.pass);
    state.pass = nxt.pass ?? state.pass;
    schedule.next = null;
    state.sessionAyahs += CFG.counts[state.surah - 1];
    trackProgress();
    active = other();
    playSurah(nxt.surah, nxt.reciterId, 0, nxt.duration);
    resync(false);
  }

  /** Station position, guarded so playback never stalls waiting for it. */
  async function fetchNow() {
    const ctrl = new AbortController();
    const timer = setTimeout(() => ctrl.abort(), 4000);
    try {
      const rq = ROTATION.length < CFG.reciters.length ? "?r=" + ROTATION.join(",") : "";
      const res = await fetch("/api/stream/now" + rq, { signal: ctrl.signal, cache: "no-store" });
      return await res.json();
    } catch {
      return null;
    } finally {
      clearTimeout(timer);
    }
  }

  async function joinLive() {
    const pos = await fetchNow();
    if (!pos || !pos.surah) {
      toast("تعذّر الاتصال بالإذاعة، حاول مجددًا");
      return false;
    }
    state.mode = "live";
    state.pass = pos.pass ?? 0;
    schedule.next = pos.next
      ? { surah: pos.next.surah, reciterId: pos.next.reciter, duration: pos.next.duration, pass: pos.next.pass, ayahCount: pos.next.ayah_count }
      : localNext(pos.surah, state.pass);
    playSurah(pos.surah, pos.reciter, pos.offset || 0, pos.duration);
    state.lastResync = Date.now();
    return true;
  }

  /** Quiet background correction — nudges the clock, never interrupts audio. */
  async function resync(force) {
    if (!state.started || state.mode !== "live") return;
    if (!force && Date.now() - state.lastResync < 5000) return;
    state.lastResync = Date.now();
    const pos = await fetchNow();
    if (!pos) return;
    state.pass = pos.pass ?? state.pass;
    if (pos.next) {
      schedule.next = {
        surah: pos.next.surah,
        reciterId: pos.next.reciter,
        duration: pos.next.duration,
        pass: pos.next.pass,
        ayahCount: pos.next.ayah_count,
      };
    }

    if (pos.surah === state.surah) {
      state.mismatches = 0;
      if (active.src && Math.abs(active.currentTime - pos.offset) > 6) {
        try { active.currentTime = pos.offset; } catch { /* ignore */ }
      }
    } else {
      // Whole-surah drift: take the gapless swap if the station is exactly on
      // our preloaded next; otherwise rejoin after it persists.
      const onPreloaded = schedule.next && pos.surah === schedule.next.surah;
      if (onPreloaded) {
        swapToNext();
      } else {
        state.mismatches = (state.mismatches ?? 0) + 1;
        if (force || state.mismatches >= 2) {
          state.mismatches = 0;
          schedule.next = pos.next
            ? { surah: pos.next.surah, reciterId: pos.next.reciter, duration: pos.next.duration, pass: pos.next.pass, ayahCount: pos.next.ayah_count }
            : localNext(pos.surah, pos.pass ?? state.pass);
          playSurah(pos.surah, pos.reciter, pos.offset || 0, pos.duration);
        }
      }
    }
  }

  /** In live mode, the file finishing early must not advance the stream —
      hold until the station clock fires. Ended is a lost-clock safety net. */
  function onEnded(e) {
    if (e.target !== active) return;
    if (state.mode === "live") {
      if (performance.now() < (schedule.deadline ?? 0) + 3000) return;
      swapToNext();
      return;
    }
    // on-demand: plain local advance
    const nxt = localNext(state.surah, 0);
    state.sessionAyahs += CFG.counts[state.surah - 1];
    trackProgress();
    active = other();
    playSurah(nxt.surah, state.reciterId, 0, 0);
  }

  function onTime() {
    if (active.duration) {
      $("#ayahProgress").style.transform = `scaleX(${active.currentTime / active.duration})`;
    }
  }

  function onError() {
    if (!active.src || active.src === location.href) return;
    toast("تعذّر تحميل السورة، جارٍ إعادة المحاولة");
    const src = active.src;
    setTimeout(() => {
      if (active.src === src) {
        active.load();
        seekAndPlay(active, 0);
      }
    }, 1500);
  }

  // Quiet drift checks twice a minute, and when the tab becomes visible again.
  setInterval(() => resync(false), 30000);
  document.addEventListener("visibilitychange", () => {
    if (!document.hidden) resync(false);
  });

  // ---------- progress reporting (per-link stats) ----------

  function trackProgress() {
    if (!CFG.link) return;
    state.pending++;
    if (state.pending >= 2 || Date.now() - state.lastFlush > 30000) flushProgress();
  }

  async function flushProgress(useBeacon) {
    if (!CFG.link || state.pending <= 0) return;
    const n = state.pending;
    state.pending = 0;
    state.lastFlush = Date.now();
    const url = `/api/links/${CFG.link.token}/played`;
    try {
      if (useBeacon && navigator.sendBeacon) {
        navigator.sendBeacon(url, new Blob([JSON.stringify({ ayahs: n })], { type: "application/json" }));
        return;
      }
      const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ayahs: n }),
      });
      const data = await res.json();
      if (data.ok && $("#statAyahs")) {
        $("#statAyahs").textContent = toAr(data.ayahs_played);
        $("#statKhatmas").textContent = toAr(Math.floor(data.ayahs_played / 6236));
      }
    } catch { /* best-effort */ }
  }

  window.addEventListener("pagehide", () => flushProgress(true));

  // ---------- UI ----------

  function updateNowPlaying() {
    const c = chapter(state.surah);
    const rec = reciterInfo(state.reciterId);
    $("#npSurahAr").textContent = `سورة ${c.na}`;
    const bar = $("#npSurahBar");
    if (bar) bar.textContent = `سورة ${c.na}`;
    const status = $("#npStatus");
    if (status) status.textContent = state.mode === "live" ? "بث مباشر — استمع مع الجميع الآن" : "استماع منفرد لهذه السورة";
    $("#npReciter").innerHTML = `<b>${rec.ar}</b>`;
    $("#npKhatma").textContent = `السورة ${toAr(state.surah)} من ${toAr(114)} · ختمة ${toAr((state.pass % ROTATION.length) + 1)} من ${toAr(ROTATION.length)}`;
    document.title = `${c.na} · صدقة`;
    const sel = $("#surahSel");
    if (sel && sel.value !== String(state.surah)) sel.value = String(state.surah);
  }

  function updateLiveChip() {
    const chip = $("#liveChip");
    const back = $("#liveReturn");
    if (!chip) return;
    const live = state.mode === "live";
    chip.classList.toggle("off", !live);
    chip.querySelector("span").textContent = live ? "مباشر" : "استماع منفرد";
    back.style.display = live ? "none" : "inline-flex";
  }

  let toastTimer;
  function toast(msg) {
    const el = $("#toast");
    el.textContent = msg;
    el.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove("show"), 3200);
  }

  // ---------- controls ----------

  const playBtn = $("#playBtn");

  function setPlayIcon(playing) {
    playBtn.classList.toggle("is-playing", playing);
  }

  function togglePlay() {
    if (!state.started) return begin();
    if (active.paused) active.play(); else active.pause();
  }

  async function begin() {
    state.started = true;
    playBtn.disabled = true;
    $("#npSurahAr").textContent = "جارٍ الاتصال بالإذاعة…";
    const ok = await joinLive();
    if (ok) $("#beginOverlay")?.classList.add("hide");
    else $("#npSurahAr").textContent = "—";
    playBtn.disabled = false;
  }

  playBtn.addEventListener("click", togglePlay);
  $("#beginBtn")?.addEventListener("click", begin);
  $("#beginOverlay")?.addEventListener("click", (e) => {
    if (e.target.closest("a, button")) return;
    if (!state.started) begin();
  });

  // Resuming after a pause = rejoin the live moment (radio behavior).
  let wasPaused = false;
  A.addEventListener("pause", () => { wasPaused = true; });
  B.addEventListener("pause", () => { wasPaused = true; });
  A.addEventListener("play", () => { if (wasPaused) { wasPaused = false; resync(true); } });
  B.addEventListener("play", () => { if (wasPaused) { wasPaused = false; resync(true); } });

  document.addEventListener("keydown", (e) => {
    if (e.code === "Space" && !["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) {
      e.preventDefault();
      togglePlay();
    }
  });

  // Surah tab: personal on-demand listening from the surah's start.
  $("#surahSel").addEventListener("change", () => {
    const surah = +$("#surahSel").value;
    state.mode = "ondemand";
    playSurah(surah, state.reciterId, 0, 0);
  });

  $("#liveReturn")?.addEventListener("click", async () => {
    if (await joinLive()) toast("عدت إلى البث المباشر");
  });

  // Theme
  const themeBtn = $("#themeBtn");
  function applyTheme(t) {
    if (t === "parchment") t = "light";
    document.documentElement.dataset.theme = t;
    localStorage.setItem("theme", t);
  }
  themeBtn.addEventListener("click", () =>
    applyTheme(document.documentElement.dataset.theme === "night" ? "light" : "night"));
  applyTheme(localStorage.getItem("theme") || "light");

  // Share
  const shareUrl = location.origin + location.pathname;
  async function copyLink() {
    try {
      await navigator.clipboard.writeText(shareUrl);
      toast("تم نسخ الرابط — شاركه مع من تحب");
    } catch {
      prompt("انسخ هذا الرابط:", shareUrl);
    }
  }
  document.querySelectorAll("#shareCopy").forEach((b) => b.addEventListener("click", copyLink));
  document.querySelectorAll("#shareWa").forEach((b) => b.addEventListener("click", (e) => {
    e.preventDefault();
    const txt = CFG.link
      ? (CFG.link.recipient
          ? `تلاوة قرآنية عن ${CFG.link.recipient}. استمع: ${shareUrl}`
          : `تلاوة قرآنية عامة. استمع: ${shareUrl}`)
      : `استمع للقرآن الكريم، إذاعة مباشرة: ${shareUrl}`;
    window.open("https://wa.me/?text=" + encodeURIComponent(txt), "_blank");
  }));

  updateLiveChip();
})();
