/* ─────────────────────────────────────────────────────────────
   state.js — URL-State (Permalink), Snapshots (localStorage),
              gemeinsamer Notenschlüssel zwischen Tools.

   Konvention für Snapshot-/State-Schlüssel: pro Tool eindeutig,
   z.B. "notenschluessel", "punkte-note", "blocknoten".
   ───────────────────────────────────────────────────────────── */

(function (root) {
  "use strict";

  if (!root.NbCore) {
    console.error("state.js: NbCore fehlt.");
    return;
  }
  const { escapeHtml } = root.NbCore;

  const SNAP_PREFIX = "nb_snap_";   // gefolgt von toolKey
  const SHARED_KEY  = "nb_grading_key_v1";

  // ── Validierung beim Laden aus localStorage ─────────────────
  function safeJSONParse(raw) {
    try { return JSON.parse(raw); } catch (e) { return null; }
  }

  function isPlainObject(v) {
    return v !== null && typeof v === "object" && !Array.isArray(v);
  }

  // Eingaben aus URL/Storage werden nur als String akzeptiert.
  function sanitizeStringMap(obj) {
    if (!isPlainObject(obj)) return {};
    const out = {};
    for (const k of Object.keys(obj)) {
      const v = obj[k];
      if (v === null || v === undefined) continue;
      if (typeof v === "string" || typeof v === "number" || typeof v === "boolean") {
        const s = String(v);
        if (s.length <= 200) out[k] = s; // Hard-Limit gegen DoS
      }
    }
    return out;
  }

  // ── Form ↔ State Helper ─────────────────────────────────────
  function readField(id) {
    const el = document.getElementById(id);
    if (!el) return null;
    if (el.type === "checkbox") return el.checked ? "1" : "0";
    if (el.tagName === "SELECT" || el.tagName === "TEXTAREA" || el.tagName === "INPUT") {
      return el.value;
    }
    return null;
  }

  function writeField(id, val) {
    const el = document.getElementById(id);
    if (!el) return false;
    if (el.type === "checkbox") {
      el.checked = (val === "1" || val === true || val === 1);
    } else {
      el.value = String(val);
    }
    return true;
  }

  function readRadio(name) {
    const r = document.querySelector('input[name="' + name + '"]:checked');
    return r ? r.value : "";
  }

  function writeRadio(name, val) {
    const r = document.querySelector('input[name="' + name + '"][value="' + CSS.escape(String(val)) + '"]');
    if (r) r.checked = true;
  }

  // ── State ↔ Form ────────────────────────────────────────────
  // schema: { fields: ["id1","id2",...], radios: ["nameA","nameB"], defaults: {id1:"...",nameA:"..."} }
  function captureDefaults(schema) {
    const d = {};
    for (const id of (schema.fields || []))   d[id]   = readField(id);
    for (const name of (schema.radios || [])) d[name] = readRadio(name);
    return d;
  }

  function serialize(schema, defaults, opts) {
    const onlyDiff = !(opts && opts.full);
    const params = new URLSearchParams();
    for (const id of (schema.fields || [])) {
      const v = readField(id);
      if (v === null || v === "") continue;
      if (onlyDiff && defaults && defaults[id] === v) continue;
      params.set(id, v);
    }
    for (const name of (schema.radios || [])) {
      const v = readRadio(name);
      if (!v) continue;
      if (onlyDiff && defaults && defaults[name] === v) continue;
      params.set(name, v);
    }
    return params.toString();
  }

  function applyFromParams(schema, params) {
    const radios = new Set(schema.radios || []);
    const fields = new Set(schema.fields || []);
    for (const [k, v] of params) {
      if (typeof v !== "string" || v.length > 200) continue;
      if (radios.has(k)) writeRadio(k, v);
      else if (fields.has(k)) writeField(k, v);
    }
  }

  function applyFromQueryString() {
    return new URLSearchParams(window.location.search);
  }

  // ── URL-State (debounced replaceState) ──────────────────────
  function makeUrlUpdater(schema, defaults) {
    let t = 0;
    return function () {
      if (t) return;
      t = setTimeout(() => {
        t = 0;
        const params = serialize(schema, defaults);
        const target = params ? "?" + params : window.location.pathname;
        try { history.replaceState(null, "", target); } catch (e) { /* ignore */ }
      }, 400);
    };
  }

  // ── Permalink kopieren ──────────────────────────────────────
  function copyPermalink(schema, defaults, flashEl) {
    const params = serialize(schema, defaults);
    const url = window.location.origin + window.location.pathname + (params ? "?" + params : "");
    const flash = (msg) => {
      if (!flashEl) return;
      flashEl.textContent = msg;
      flashEl.style.opacity = "1";
      clearTimeout(flashEl._t);
      flashEl._t = setTimeout(() => { flashEl.style.opacity = "0"; }, 1800);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(() => flash("Permalink kopiert"))
        .catch(() => prompt("Permalink:", url));
    } else {
      prompt("Permalink:", url);
    }
  }

  // ── Snapshots (localStorage pro Tool) ───────────────────────
  function snapshots(toolKey) {
    const storeKey = SNAP_PREFIX + toolKey;

    function load() {
      const raw = localStorage.getItem(storeKey);
      const arr = safeJSONParse(raw);
      if (!Array.isArray(arr)) return [];
      // Whitelist: nur Objekte mit name (string) und data (string)
      return arr
        .filter(s => isPlainObject(s) && typeof s.name === "string" && typeof s.data === "string")
        .slice(0, 50); // Hard-Limit
    }
    function save(list) {
      try { localStorage.setItem(storeKey, JSON.stringify(list)); }
      catch (e) { /* quota */ }
    }

    return {
      list: load,
      add: function (name, schema, defaults) {
        const list = load();
        const data = serialize(schema, defaults, { full: true });
        const cleanName = String(name || "Konfiguration " + (list.length + 1)).slice(0, 80);
        list.push({ name: cleanName, data: data, ts: Date.now() });
        save(list);
        return list;
      },
      apply: function (idx, schema) {
        const list = load();
        const s = list[idx];
        if (!s) return false;
        const params = new URLSearchParams(s.data);
        applyFromParams(schema, params);
        return true;
      },
      remove: function (idx) {
        const list = load();
        list.splice(idx, 1);
        save(list);
        return list;
      },
      clear: function () {
        save([]);
        return [];
      }
    };
  }

  function renderSnapshotList(listEl, snaps, schema, onChange) {
    listEl.innerHTML = "";
    const items = snaps.list();
    items.forEach((s, i) => {
      const li = document.createElement("li");
      const span = document.createElement("span");
      span.textContent = s.name;
      const btnApply = document.createElement("button");
      btnApply.type = "button"; btnApply.className = "btn-tiny";
      btnApply.textContent = "laden";
      btnApply.addEventListener("click", () => { snaps.apply(i, schema); if (onChange) onChange("apply"); });
      const btnDel = document.createElement("button");
      btnDel.type = "button"; btnDel.className = "btn-tiny";
      btnDel.setAttribute("aria-label", 'Schnappschuss "' + s.name + '" entfernen');
      btnDel.textContent = "×";
      btnDel.addEventListener("click", () => { snaps.remove(i); renderSnapshotList(listEl, snaps, schema, onChange); if (onChange) onChange("remove"); });
      li.appendChild(span); li.appendChild(btnApply); li.appendChild(btnDel);
      listEl.appendChild(li);
    });
  }

  // ── Geteilter Notenschlüssel ────────────────────────────────
  // Felder: bestG, worstG, maxPts, bestFrom, worstFrom, halfPts ("yes"/"no"), gradeStep
  function getSharedKey() {
    const raw = localStorage.getItem(SHARED_KEY);
    const obj = safeJSONParse(raw);
    if (!isPlainObject(obj)) return null;
    const out = {};
    const fields = ["bestG", "worstG", "maxPts", "bestFrom", "worstFrom", "halfPts", "gradeStep"];
    for (const f of fields) {
      if (typeof obj[f] === "string" && obj[f].length <= 50) out[f] = obj[f];
    }
    // Mindestmenge an Feldern: alle numerischen + halfPts
    if (out.bestG === undefined || out.worstG === undefined || out.maxPts === undefined) return null;
    return out;
  }

  function setSharedKey(obj) {
    if (!isPlainObject(obj)) return false;
    const cleaned = {};
    const fields = ["bestG", "worstG", "maxPts", "bestFrom", "worstFrom", "halfPts", "gradeStep"];
    for (const f of fields) {
      const v = obj[f];
      if (v === undefined || v === null) continue;
      cleaned[f] = String(v).slice(0, 50);
    }
    try { localStorage.setItem(SHARED_KEY, JSON.stringify(cleaned)); return true; }
    catch (e) { return false; }
  }

  function clearSharedKey() {
    try { localStorage.removeItem(SHARED_KEY); } catch (e) {}
  }

  // ── Public API ──────────────────────────────────────────────
  root.NbState = {
    captureDefaults, serialize, applyFromParams, applyFromQueryString,
    makeUrlUpdater, copyPermalink,
    snapshots, renderSnapshotList,
    getSharedKey, setSharedKey, clearSharedKey,
    readField, writeField, readRadio, writeRadio,
    escapeHtml // bequeme Re-Exportierung
  };
})(window);
