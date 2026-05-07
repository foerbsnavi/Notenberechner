<?php
$pageTitle = 'Klausur-Statistik';
$pageDescription = 'Klausurergebnisse auswerten: Kennzahlen, Verteilung und Bestehensquote berechnen. Mit CSV- und PDF-Export.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Klausur-Statistik — Notenberechner';
$jsFiles = ['statistik.js'];
$needsPdf = true;
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Klausur-Statistik</h1>
  <p class="lead">Werte einfügen — die Auswertung erscheint sofort. Akzeptiert Komma, Punkt, Leerzeichen oder Zeilenumbruch als Trennzeichen.</p>

  <form id="ks_form" class="card" aria-labelledby="ks_h2" novalidate>
    <h2 id="ks_h2">Eingaben</h2>
    <div class="grid">
      <div data-span="12" class="field">
        <label for="ksValues">Werte (Noten oder Punkte)</label>
        <textarea id="ksValues" name="ksValues" rows="5" placeholder="z.B. 2, 3, 1, 2,5&#10;oder Punkte: 47 52 33 60 …"></textarea>
        <p class="hint">Dezimaltrennzeichen: Komma oder Punkt.</p>
      </div>

      <div data-span="3" class="field">
        <label for="ksMode">Eingabe ist</label>
        <select id="ksMode" name="ksMode">
          <option value="grades" selected>Noten</option>
          <option value="points">Punkte</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="ksGradeStep">Noten-Schritt</label>
        <select id="ksGradeStep" name="ksGradeStep">
          <option value="1" selected>ganze Noten</option>
          <option value="0.5">halbe Noten</option>
          <option value="0.25">Viertelnoten</option>
          <option value="0.3333333333333333">Drittelnoten</option>
          <option value="0.1">Zehntelnoten</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="ksUseKey">Notenschlüssel</label>
        <select id="ksUseKey" name="ksUseKey">
          <option value="yes" selected>aus Notenschlüssel übernehmen</option>
          <option value="no">eigene Werte</option>
        </select>
        <p class="hint" id="ks_key_hint" aria-live="polite"></p>
      </div>
      <div data-span="3" class="field">
        <label for="ksPassThreshold">Bestanden bis Note</label>
        <input id="ksPassThreshold" name="ksPassThreshold" type="number" step="0.5" value="4" inputmode="decimal">
      </div>

      <div data-span="3" class="field">
        <label for="ksBestG">Beste Note</label>
        <input id="ksBestG" name="ksBestG" type="number" step="0.01" value="1" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="ksWorstG">Schlechteste Note</label>
        <input id="ksWorstG" name="ksWorstG" type="number" step="0.01" value="6" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="ksMaxPts">Max. Punkte (bei Punkten)</label>
        <input id="ksMaxPts" name="ksMaxPts" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="ksBestFrom">Beste ab Punkte</label>
        <input id="ksBestFrom" name="ksBestFrom" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="ksWorstFrom">Schlechteste ab Punkte</label>
        <input id="ksWorstFrom" name="ksWorstFrom" type="number" step="0.5" min="0" value="0" inputmode="decimal">
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="ks_calc">Auswerten</button>
      <button type="button" class="btn ghost" id="ks_reset" data-action="reset">Reset</button>
    </div>

    <div id="ks_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="ks_out_h2" aria-live="polite">
    <h2 id="ks_out_h2">Ergebnis</h2>
    <p class="result-meta" id="ks_meta">Noch keine Auswertung.</p>

    <table class="table table--compact" id="ks_summary" hidden>
      <caption class="sr-only">Statistische Kennzahlen</caption>
      <thead><tr>
        <th scope="col">Kennzahl</th>
        <th scope="col">Wert</th>
        <th class="col-filler" aria-hidden="true"></th>
      </tr></thead>
      <tbody id="ks_summary_body"></tbody>
    </table>

    <table class="table table--compact mt-lg" id="ks_dist" hidden>
      <caption class="sr-only">Notenverteilung</caption>
      <thead><tr>
        <th scope="col">Note</th>
        <th scope="col">Anzahl</th>
        <th scope="col">Anteil</th>
        <th class="col-filler" aria-hidden="true"></th>
      </tr></thead>
      <tbody id="ks_dist_body"></tbody>
    </table>
  </section>

  <section class="card" aria-labelledby="ks_post_h2">
    <h2 id="ks_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="ks_csv" disabled aria-disabled="true">CSV herunterladen</button>
      <button type="button" class="btn" id="ks_pdf" disabled aria-disabled="true">PDF herunterladen</button>
      <button type="button" class="btn" id="ks_permalink">Permalink in Zwischenablage</button>
      <button type="button" class="btn" id="ks_snap_add">Aktuelle Konfiguration im Browser speichern</button>
    </div>
    <p class="hint flash-hint" id="ks_flash"></p>
    <ol class="snap-list" id="ks_snap_list" aria-live="polite"></ol>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
