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
    el.play().catch((err) => {
      if (err && err.name === "NotAllowedError") handleBlockedAutoplay(el);
    });
  }

  // ---------- autoplay policy ----------

  let unlockArmed = false;
  /** First user gesture anywhere: unmute / start, so a blocked page
      comes alive with a single tap instead of hunting for the button. */
  function armUnlock() {
    if (unlockArmed) return;
    unlockArmed = true;
    const unlock = () => {
      document.removeEventListener("pointerdown", unlock, true);
      document.removeEventListener("touchstart", unlock, true);
      document.removeEventListener("keydown", unlock, true);
      unlockArmed = false;
      if (!state.started) { begin(); return; }
      unmute();
      if (active.paused) active.play().catch(() => {});
    };
    document.addEventListener("pointerdown", unlock, true);
    document.addEventListener("touchstart", unlock, true);
    document.addEventListener("keydown", unlock, true);
  }

  function unmute() {
    active.muted = false;
    state.mutedAutoplay = false;
    playBtn.classList.remove("attn");
  }

  /** Unmuted autoplay was refused. Browsers still allow muted playback:
      start silent (stream stays live and buffered), then the first tap
      anywhere restores the sound. */
  async function handleBlockedAutoplay(el) {
    if (el !== active || state.mutedAutoplay) return;
    el.muted = true;
    try {
      await el.play();
      state.mutedAutoplay = true;
      playBtn.classList.add("attn");
      toast("اضغط في أي مكان لتشغيل الصوت");
    } catch { /* fully blocked (e.g. iOS low power) */ }
    armUnlock();
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
    if (active.paused) return; // waiting for a gesture (autoplay blocked) or paused
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
      if (data.ok && $("#statKhatmas")) {
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
    $("#npReciter").innerHTML = `<b>${rec.ar}</b>`;
    $("#npKhatma").textContent = `السورة ${toAr(state.surah)} من ${toAr(114)} · ختمة ${toAr((state.pass % ROTATION.length) + 1)} من ${toAr(ROTATION.length)}`;
    document.title = `${c.na} · صدقة`;
  }

  function updateLiveChip() {
    const chip = $("#liveChip");
    if (!chip) return;
    const live = state.mode === "live";
    chip.classList.toggle("off", !live);
    chip.querySelector("span").textContent = live ? "مباشر" : "تشغيل خاص";
    setTab(state.mode !== "live");
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
    unmute();
    if (!state.started) return begin();
    if (active.paused) active.play(); else active.pause();
  }

  let joinAttempt = false;
  /** Join the station immediately when the page opens. If the browser blocks
      autoplay, the play button stays as the normal fallback control. */
  async function begin() {
    if (joinAttempt || state.started) return;
    joinAttempt = true;
    state.started = true;
    playBtn.disabled = true;
    $("#npSurahAr").textContent = "جارٍ الاتصال بالإذاعة…";
    const ok = await joinLive();
    joinAttempt = false;
    playBtn.disabled = false;
    if (!ok) {
      $("#npSurahAr").textContent = "تعذّر الاتصال — اضغط زر التشغيل للمحاولة";
    }
  }

  playBtn.addEventListener("click", togglePlay);

  // Open the link → the stream starts on its own. Fired from document load
  // with a short delay so the page settles before claiming audio focus.
  const kick = () => setTimeout(begin, 200);
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", kick);
  } else {
    kick();
  }

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

  // ---------- tabs: live station vs manual listening ----------

  function setTab(manual) {
    const live = $("#tabLive");
    const man = $("#tabManual");
    if (!live || !man) return;
    live.classList.toggle("on", !manual);
    man.classList.toggle("on", manual);
    live.setAttribute("aria-selected", String(!manual));
    man.setAttribute("aria-selected", String(manual));
    const panel = $("#manualControls");
    if (panel) panel.hidden = !manual;
  }

  $("#tabLive").addEventListener("click", async () => {
    setTab(false);
    if (state.mode !== "live") {
      if (await joinLive()) toast("عدت إلى البث المباشر");
    } else if (active.paused) {
      unmute();
      active.play().catch(() => {});
    }
  });

  $("#tabManual").addEventListener("click", () => {
    setTab(true);
    if (state.mode !== "ondemand") {
      // Detach from the station clock; the current surah keeps playing and
      // advances locally until the user picks something.
      state.mode = "ondemand";
      clearTimeout(schedule.timer);
      updateLiveChip();
      updateNowPlaying();
    }
  });

  // ---------- searchable pickers (manual mode) ----------

  function makePicker(root, items, initialValue, onPick) {
    const btn = root.querySelector(".picker-btn");
    const value = root.querySelector(".picker-value");
    const menu = root.querySelector(".picker-menu");
    const search = root.querySelector(".picker-search");
    const list = root.querySelector(".picker-list");
    let current = initialValue;

    const labelOf = (v) => {
      const item = items.find((i) => String(i.v) === String(v));
      return item ? item.label : "—";
    };

    const renderList = (query) => {
      const q = (query || "").trim().toLowerCase();
      const d_q = q.replace(/[٠-٩]/g, (d) => "٠١٢٣٤٥٦٧٨٩".indexOf(d));
      list.innerHTML = "";
      const hits = items.filter((i) => !q || String(i.label).toLowerCase().includes(q) || String(i.v) === d_q);
      if (!hits.length) {
        const empty = document.createElement("div");
        empty.className = "picker-empty";
        empty.textContent = "لا توجد نتائج";
        list.appendChild(empty);
        return;
      }
      for (const item of hits) {
        const opt = document.createElement("button");
        opt.type = "button";
        opt.className = "picker-opt" + (String(item.v) === String(current) ? " on" : "");
        opt.textContent = item.label;
        opt.addEventListener("click", () => {
          current = item.v;
          value.textContent = labelOf(current);
          menu.hidden = true;
          onPick(item.v);
        });
        list.appendChild(opt);
      }
    };

    const close = () => { menu.hidden = true; };

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const opening = menu.hidden;
      document.querySelectorAll(".picker-menu").forEach((m) => { if (m !== menu) m.hidden = true; });
      menu.hidden = !opening;
      if (opening) {
        renderList(search.value);
        search.value = "";
        search.focus();
      }
    });
    search.addEventListener("input", () => renderList(search.value));
    menu.addEventListener("click", (e) => e.stopPropagation());
    document.addEventListener("click", close);
    document.addEventListener("keydown", (e) => { if (e.key === "Escape") close(); });

    value.textContent = labelOf(current);
  }

  makePicker(
    $("#surahPicker"),
    CFG.chapters.map((c) => ({ v: c.id, label: `${toAr(c.id)} · ${c.na}`, sub: c.n || "", search: `${c.id} ${c.na} ${c.n || ""}` })),
    state.surah,
    (surah) => {
      state.mode = "ondemand";
      playSurah(surah, state.reciterId, 0, 0);
      updateLiveChip();
    },
  );

  makePicker(
    $("#reciterPicker"),
    CFG.reciters.map((r) => ({ v: r.id, label: r.ar, sub: r.name || "", search: `${r.ar} ${r.name || ""}` })),
    state.reciterId,
    (reciterId) => {
      if (state.mode !== "ondemand") {
        state.mode = "ondemand";
        clearTimeout(schedule.timer);
      }
      playSurah(state.surah, reciterId, 0, 0);
      updateLiveChip();
    },
  );

  // Theme toggling lives in the layout so it works on every page.

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
