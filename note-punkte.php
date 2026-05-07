<?php
$pageTitle = 'Note zu Punkte';
$pageDescription = 'Rückwärtsrechner: Wie viele Punkte sind für eine Zielnote nötig? Mit gespeichertem Notenschlüssel oder eigenen Werten — live, mit PDF-Export und teilbarem Permalink.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Note zu Punkte — Notenberechner';
$jsFiles = ['note-punkte.js'];
$needsPdf = true;
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Note → Punkte</h1>
  <p class="lead">Rückwärtsrechner: Was muss erreicht werden, um eine Zielnote zu bekommen?</p>

  <form id="np_form" class="card" aria-labelledby="np_h2" novalidate>
    <h2 id="np_h2">Eingaben</h2>
    <div class="grid">
      <div data-span="3" class="field">
        <label for="npGrade">Ziel-Note</label>
        <input id="npGrade" name="npGrade" type="number" step="0.01" min="0" value="2" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npHalfPts">Halbe Punkte?</label>
        <select id="npHalfPts" name="npHalfPts">
          <option value="yes" selected>ja</option>
          <option value="no">nein</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="npUseKey">Notenschlüssel</label>
        <select id="npUseKey" name="npUseKey">
          <option value="yes" selected>aus Notenschlüssel übernehmen</option>
          <option value="no">eigene Werte</option>
        </select>
        <p class="hint" id="np_key_hint" aria-live="polite"></p>
      </div>
      <div data-span="3" class="field">
        <label for="npGradeStep">Noten-Schritt</label>
        <select id="npGradeStep" name="npGradeStep">
          <option value="0.1">Zehntel</option>
          <option value="0.25">Viertel</option>
          <option value="0.3333333333333333">Drittel</option>
          <option value="0.5">Halbe</option>
          <option value="1" selected>Ganze</option>
        </select>
      </div>

      <div data-span="3" class="field">
        <label for="npBestG">Beste Note</label>
        <input id="npBestG" name="npBestG" type="number" step="0.01" value="1" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npWorstG">Schlechteste Note</label>
        <input id="npWorstG" name="npWorstG" type="number" step="0.01" value="6" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npMaxPts">Max. Punkte</label>
        <input id="npMaxPts" name="npMaxPts" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npBestFrom">Beste ab Punkte</label>
        <input id="npBestFrom" name="npBestFrom" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npWorstFrom">Schlechteste ab Punkte</label>
        <input id="npWorstFrom" name="npWorstFrom" type="number" step="0.5" min="0" value="0" inputmode="decimal">
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="np_calc">Berechnen</button>
      <button type="button" class="btn ghost" id="np_reset" data-action="reset">Reset</button>
    </div>

    <div id="np_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="np_out_h2" aria-live="polite">
    <h2 id="np_out_h2">Ergebnis</h2>
    <p class="result-meta" id="np_meta">Noch keine Berechnung.</p>
    <output for="npGrade npMaxPts" id="np_pts" class="result-big">–</output>
    <p class="hint mt-sm" id="np_hint"></p>
  </section>

  <section class="card" aria-labelledby="np_post_h2">
    <h2 id="np_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="np_pdf" disabled aria-disabled="true">PDF herunterladen</button>
      <button type="button" class="btn" id="np_permalink">Permalink in Zwischenablage</button>
      <button type="button" class="btn" id="np_snap_add">Aktuelle Konfiguration im Browser speichern</button>
    </div>
    <p class="hint flash-hint" id="np_flash"></p>
    <ol class="snap-list" id="np_snap_list" aria-live="polite"></ol>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
