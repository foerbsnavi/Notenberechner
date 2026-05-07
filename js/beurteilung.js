/* ─────────────────────────────────────────────────────────────
   beurteilung.js — Tool 6: Beurteilungsgenerator.
   Pools werden lazy via fetch() aus /js/data/beurteilung-pools.json
   geladen — nur auf dieser Tool-Seite.
   ───────────────────────────────────────────────────────────── */

(function () {
  "use strict";
  if (!window.NbCore || !window.NbExport) return;

  const C = window.NbCore;
  const E = window.NbExport;
  const $ = (id) => document.getElementById(id);

  let DATA = null;
  let lastUsed = { starter: null, lines: new Set() };
  let lastText = "";

  // ── Daten lazy laden ────────────────────────────────────────
  async function loadData() {
    if (DATA) return DATA;
    const url = "js/data/beurteilung-pools.json";
    const res = await fetch(url, { cache: "force-cache" });
    if (!res.ok) throw new Error("Pool-Daten konnten nicht geladen werden.");
    const json = await res.json();
    if (!json || !Array.isArray(json.starters) || !json.pools) {
      throw new Error("Pool-Daten ungültig.");
    }
    DATA = json;
    return DATA;
  }

  // ── Helfer ──────────────────────────────────────────────────
  function render(tpl, ctx) {
    return String(tpl)
      .replaceAll("{{NAME}}", ctx.name)
      .replaceAll("{{FACH}}", ctx.subject)
      .replaceAll("{{FACH_IN}}", ctx.subjectIn);
  }

  function pickNoRepeat(arr, last) {
    if (arr.length <= 1) return arr[0];
    let s, tries = 0;
    do {
      s = arr[Math.floor(Math.random() * arr.length)];
      tries++;
    } while (s === last && tries < 20);
    return s;
  }

  function pickManyUniqueNoRepeat(arr, count, usedSet) {
    const a = arr.filter(x => !usedSet || !usedSet.has(x));
    const pool = a.length ? a : arr.slice();
    for (let i = pool.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [pool[i], pool[j]] = [pool[j], pool[i]];
    }
    return pool.slice(0, Math.min(count, pool.length));
  }

  function randInt(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; }

  function sentenceCount(len, grade) {
    const g = Number(grade);
    if (len === "short") return g >= 5 ? randInt(3, 4) : randInt(2, 3);
    if (len === "long")  return g >= 5 ? randInt(6, 8) : randInt(5, 7);
    return g >= 5 ? randInt(5, 6) : randInt(4, 5);
  }

  function showError(msg) { $("bg_err").textContent = msg || ""; }

  // ── Generierung ─────────────────────────────────────────────
  async function build() {
    showError("");
    $("bg_meta").textContent = "Generiere…";
    $("bg_copy").disabled = true; $("bg_copy").setAttribute("aria-disabled", "true");
    $("bg_pdf").disabled = true; $("bg_pdf").setAttribute("aria-disabled", "true");

    let data;
    try { data = await loadData(); }
    catch (e) { showError(e.message); $("bg_meta").textContent = "Fehler."; return; }

    const grade = Number($("bgGrade").value);
    if (!(grade >= 1 && grade <= 6)) {
      showError("Bitte Note 1 bis 6 wählen."); return;
    }

    const subject = ($("bgSubject").value || "").trim();
    const ctx = {
      name:       ($("bgName").value || "").trim() || "Du",
      subject:    subject || "dem Unterricht",
      subjectIn:  subject ? ("in " + subject) : "im Unterricht"
    };

    const n = sentenceCount($("bgLen").value, grade);

    const pool = data.pools[String(grade)] || [];
    if (!pool.length) { showError("Keine Textbausteine für diese Note."); return; }

    const parts = [];
    const starterTpl = pickNoRepeat(data.starters, lastUsed.starter);
    lastUsed.starter = starterTpl;
    parts.push(render(starterTpl, ctx));

    const chosen = pickManyUniqueNoRepeat(pool, Math.max(1, n - 1), lastUsed.lines);
    chosen.forEach(s => lastUsed.lines.add(s));
    if (lastUsed.lines.size > 200) lastUsed.lines = new Set([...lastUsed.lines].slice(-100));
    for (const s of chosen) parts.push(render(s, ctx));

    const txt = parts.join(" ");
    $("bg_text").value = txt;
    lastText = txt;
    $("bg_meta").textContent = "Note " + grade + " · " + ctx.subject + " · ~" + n + " Sätze";
    $("bg_copy").disabled = false; $("bg_copy").setAttribute("aria-disabled", "false");
    $("bg_pdf").disabled  = false; $("bg_pdf").setAttribute("aria-disabled", "false");
  }

  // ── Kopieren ────────────────────────────────────────────────
  async function copy() {
    const v = $("bg_text").value.trim();
    if (!v) return;
    const btn = $("bg_copy");
    try {
      await navigator.clipboard.writeText(v);
      btn.textContent = "Kopiert!";
      setTimeout(() => btn.textContent = "Kopieren", 1200);
    } catch (e) {
      $("bg_text").focus(); $("bg_text").select();
    }
  }

  // ── PDF ─────────────────────────────────────────────────────
  function exportPDF() {
    const txt = $("bg_text").value.trim();
    if (!txt) return;
    const grade = Number($("bgGrade").value);
    const subject = ($("bgSubject").value || "").trim() || "Unterricht";
    const name = ($("bgName").value || "").trim() || "Schüler/in";
    E.exportPDF("beurteilung", (w) => {
      w.line("Beurteilung", 16, true);
      w.line(name + " · " + subject + " · Note " + grade, 11);
      w.blank(8);
      w.line(txt, 11, false);
    });
  }

  // ── Reset ───────────────────────────────────────────────────
  function reset() {
    $("bgName").value = "";
    $("bgSubject").value = "Mathe";
    $("bgGrade").value = "2";
    $("bgLen").value = "medium";
    $("bg_text").value = "";
    showError("");
    $("bg_meta").textContent = "Noch keine Beurteilung.";
    $("bg_copy").disabled = true; $("bg_copy").setAttribute("aria-disabled", "true");
    $("bg_pdf").disabled  = true; $("bg_pdf").setAttribute("aria-disabled", "true");
    lastUsed = { starter: null, lines: new Set() };
    lastText = "";
  }

  // ── Init ────────────────────────────────────────────────────
  function init() {
    $("bg_gen").addEventListener("click", build);
    $("bg_copy").addEventListener("click", copy);
    $("bg_pdf").addEventListener("click", exportPDF);
    $("bg_reset").addEventListener("click", reset);

    const form = $("bg_form");
    form.addEventListener("submit", (e) => { e.preventDefault(); build(); });
    form.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && e.target.tagName !== "TEXTAREA") {
        e.preventDefault(); build();
      }
    });

    // Bei manueller Texteingabe Copy/PDF aktivieren
    $("bg_text").addEventListener("input", () => {
      const has = $("bg_text").value.trim().length > 0;
      $("bg_copy").disabled = !has; $("bg_copy").setAttribute("aria-disabled", String(!has));
      $("bg_pdf").disabled  = !has; $("bg_pdf").setAttribute("aria-disabled", String(!has));
    });
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
