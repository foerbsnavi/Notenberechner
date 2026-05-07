/* ─────────────────────────────────────────────────────────────
   core.js — Utilities, Number-Helper, Notengrundlagen.
   Wird auf jeder Tool-Seite zuerst geladen.
   ───────────────────────────────────────────────────────────── */

(function (root) {
  "use strict";

  // ── Zahlen / Formate ────────────────────────────────────────
  const num = (v) => {
    const s = String(v ?? "").trim();
    if (!s) return NaN;
    const x = Number(s.replace(",", "."));
    return Number.isFinite(x) ? x : NaN;
  };
  const isFiniteNum  = (x) => typeof x === "number" && Number.isFinite(x);
  // clamp toleriert vertauschte Grenzen — wichtig, da bei Noten "best"
  // numerisch kleiner als "worst" sein kann (best=1, worst=6).
  const clamp        = (x, a, b) => Math.min(Math.max(x, Math.min(a, b)), Math.max(a, b));
  const clampMinMax  = clamp; // historischer Alias, gleiches Verhalten
  const roundToStep  = (x, step) => (step > 0 ? Math.round(x / step) * step : x);

  const decimalsForStep = (step) => {
    if (step === 1)    return 0;
    if (step === 0.5)  return 1;
    if (step === 0.25) return 2;
    if (step > 0.2 && step < 0.4) return 3; // Drittel: 1,000 / 1,333 / 1,667
    return 1;
  };
  const fmt      = (x, step)    => Number(x).toFixed(decimalsForStep(step)).replace(".", ",");
  const fmtGrade = (x, step)    => fmt(x, step);
  const fmtPts   = (x, halfPts) => (halfPts
    ? (Number.isInteger(x) ? String(x) : Number(x).toFixed(1))
    : String(Math.round(x))).replace(".", ",");

  // ── Sicherheit / DOM ────────────────────────────────────────
  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  // ── Debounce ────────────────────────────────────────────────
  function debounce(fn, ms) {
    let t = 0;
    return function () {
      const args = arguments, ctx = this;
      clearTimeout(t);
      t = setTimeout(() => fn.apply(ctx, args), ms);
    };
  }

  // ── Note ↔ Punkte (linear) ──────────────────────────────────
  function linearGradeFromPoints(points, cfg) {
    const { bestG, worstG, bestFrom, worstFrom } = cfg;
    if (points >= bestFrom)  return bestG;
    if (points <= worstFrom) return worstG;
    const span = bestFrom - worstFrom;
    if (span <= 0) return bestG; // Selbstschutz: degenerierte Schwellen
    const t = (points - worstFrom) / span;
    const range = Math.abs(worstG - bestG);
    const higherIsBetter = bestG > worstG;
    return higherIsBetter ? (worstG + t * range) : (worstG - t * range);
  }

  function gradeMeetsTarget(g, target, bestG, worstG) {
    const higherIsBetter = bestG > worstG;
    return higherIsBetter ? (g >= target - 1e-9) : (g <= target + 1e-9);
  }

  // ── jsPDF aus /lib/ ─────────────────────────────────────────
  function ensureJsPDF() {
    if (root.jspdf && root.jspdf.jsPDF) return Promise.resolve(root.jspdf.jsPDF);
    return Promise.reject(new Error("jsPDF nicht verfügbar (lib/jspdf.umd.min.js fehlt)."));
  }

  function pdfTimestamp() {
    const d = new Date();
    const pad = (x) => String(x).padStart(2, "0");
    return "Erstellt am: " + pad(d.getDate()) + "." + pad(d.getMonth() + 1) + "." + d.getFullYear() +
           " um " + pad(d.getHours()) + ":" + pad(d.getMinutes());
  }
  const PDF_SOURCE = "Quelle: https://noten.brosemedien.de/";
  function pdfHeaderLines() { return [PDF_SOURCE, pdfTimestamp(), ""]; }

  // ── Datei-Download ──────────────────────────────────────────
  function downloadBlob(blob, filename) {
    const a = document.createElement("a");
    const url = URL.createObjectURL(blob);
    a.href = url; a.download = filename;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1500);
  }

  function makeFilename(prefix, ext) {
    const d = new Date();
    const pad = (x) => String(x).padStart(2, "0");
    const stamp = d.getFullYear() + "-" + pad(d.getMonth() + 1) + "-" + pad(d.getDate()) +
                  "_" + pad(d.getHours()) + pad(d.getMinutes());
    return prefix + "_" + stamp + "." + ext;
  }

  // ── Loading-Overlay (per Bedarf erzeugt) ────────────────────
  function setLoading(state, text) {
    let ov = document.getElementById("nb_loading_overlay");
    if (state) {
      if (!ov) {
        ov = document.createElement("div");
        ov.id = "nb_loading_overlay";
        ov.className = "loading-overlay";
        ov.setAttribute("role", "status");
        ov.setAttribute("aria-live", "polite");
        ov.innerHTML = '<div class="spinner" aria-hidden="true"></div><p id="nb_loading_text"></p>';
        document.body.appendChild(ov);
      }
      const tx = document.getElementById("nb_loading_text");
      if (tx) tx.textContent = text || "Bitte warten…";
      ov.hidden = false;
    } else if (ov) {
      ov.hidden = true;
    }
  }

  // ── Burger-Menü auf kleinen Bildschirmen ────────────────────
  document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("nav_toggle");
    const nav = document.getElementById("tool_nav");
    if (!btn || !nav) return;
    btn.addEventListener("click", () => {
      const open = nav.classList.toggle("open");
      btn.classList.toggle("open", open);
      btn.setAttribute("aria-expanded", String(open));
    });
    // Beim Klick auf einen Nav-Link schließt sich das Menü auf Mobile
    nav.addEventListener("click", (e) => {
      if (e.target.tagName === "A" && nav.classList.contains("open")) {
        nav.classList.remove("open");
        btn.classList.remove("open");
        btn.setAttribute("aria-expanded", "false");
      }
    });
  });

  // ── Tastaturkürzel (Enter, R) ───────────────────────────────
  // Enter wird vom Browser im Formular automatisch behandelt.
  // R = Reset-Button auslösen, wenn vorhanden, außerhalb von Eingaben.
  document.addEventListener("keydown", (e) => {
    if (e.ctrlKey || e.metaKey || e.altKey) return;
    const t = e.target;
    if (t && typeof t.matches === "function" &&
        t.matches("input, select, textarea, button, summary, [contenteditable]")) return;
    if (e.key && e.key.toLowerCase() === "r") {
      const resetBtn = document.querySelector('[data-action="reset"]');
      if (resetBtn) {
        e.preventDefault();
        resetBtn.click();
      }
    }
  });

  // ── Public API ──────────────────────────────────────────────
  root.NbCore = {
    // Zahlen
    num, isFiniteNum, clamp, clampMinMax, roundToStep,
    decimalsForStep, fmt, fmtGrade, fmtPts,
    // Sicherheit/DOM
    escapeHtml, debounce,
    // Noten
    linearGradeFromPoints, gradeMeetsTarget,
    // Export-Helper
    ensureJsPDF, pdfTimestamp, pdfHeaderLines, PDF_SOURCE,
    downloadBlob, makeFilename, setLoading
  };
})(window);
