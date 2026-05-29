<?php
$pageTitle = 'Notenschlüssel online erstellen';
$breadcrumbName = 'Notenschlüssel';
$pageDescription = 'Notenschlüssel kostenlos online erstellen: Punkte-zu-Noten-Schlüssel in beliebiger Skala, mit halben Punkten, frei wählbarem Notenschritt, CSV & PDF.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Notenschlüssel — Notenberechner';
$jsFiles = ['notenschluessel.js'];
$needsPdf = true;
$extraSchemas = [
  ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [
    ['@type' => 'Question', 'name' => 'Was ist ein Notenschlüssel?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ein Notenschlüssel ordnet jeder erreichbaren Punktzahl einer Klausur eine Note zu. Der Notenberechner erzeugt ihn linear in beliebiger Skala (1–6, 0–15, Punkte- oder Notensystem), mit halben Punkten und frei wählbarem Notenschritt.']],
    ['@type' => 'Question', 'name' => 'Kann ich Bonuspunkte oder einen Punkte-Puffer einbauen?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. „Beste Note vergeben ab Punkten" muss nicht der Maximalpunktzahl entsprechen — wer einen Puffer einrechnen möchte, setzt diesen Wert niedriger. So gibt es die Bestnote auch unterhalb der vollen Punktzahl.']],
    ['@type' => 'Question', 'name' => 'Welche Notenschritte unterstützt der Rechner?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Zehntel-, Viertel-, Drittel-, halbe und ganze Noten — abgestimmt auf Sekundarstufe I/II, Oberstufe und Hochschule.']],
    ['@type' => 'Question', 'name' => 'Übernimmt der Notenschlüssel sich automatisch in andere Werkzeuge?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Wer den aktuellen Schlüssel als Standard im Browser merkt, kann ihn in „Punkte → Note", „Note → Punkte", „Klausur-Statistik" und „Blocknoten" per Toggle übernehmen. Die Daten bleiben dabei lokal im Browser.']],
    ['@type' => 'Question', 'name' => 'Kann ich den Notenschlüssel als PDF oder CSV exportieren?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. Der Schlüssel lässt sich als druckbare PDF und als CSV für Excel oder andere Tabellenkalkulationen herunterladen.']],
  ]],
];
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Notenschlüssel</h1>
  <p class="lead">Linearer Punkte-zu-Noten-Schlüssel mit beliebiger Skala. Wird automatisch von „Punkte → Note", „Note → Punkte" und „Klausur-Statistik" übernommen.</p>

  <form id="ns_form" class="card" aria-labelledby="ns_h2" novalidate>
    <h2 id="ns_h2">Eingaben</h2>

    <div class="grid">
      <div data-span="3" class="field">
        <label for="bestGrade">Beste Note</label>
        <input id="bestGrade" name="bestGrade" type="number" step="0.01" value="1" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="worstGrade">Schlechteste Note</label>
        <input id="worstGrade" name="worstGrade" type="number" step="0.01" value="6" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="maxPts">Maximale Punktzahl</label>
        <input id="maxPts" name="maxPts" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="halfPts">Halbe Punkte zählen?</label>
        <select id="halfPts" name="halfPts">
          <option value="yes" selected>ja</option>
          <option value="no">nein</option>
        </select>
      </div>

      <div data-span="6" class="field">
        <label for="bestFromPts">Beste Note vergeben ab Punkten</label>
        <input id="bestFromPts" name="bestFromPts" type="number" step="0.5" value="60" inputmode="decimal">
        <p class="hint">Standard: maximale Punktzahl.</p>
      </div>
      <div data-span="6" class="field">
        <label for="worstFromPts">Schlechteste Note vergeben ab Punkten</label>
        <input id="worstFromPts" name="worstFromPts" type="number" step="0.5" value="0" inputmode="decimal">
        <p class="hint">Standard: 0.</p>
      </div>

      <div data-span="6" class="field">
        <label for="gradeStep">Noten vergeben als</label>
        <select id="gradeStep" name="gradeStep">
          <option value="0.1">Zehntelnoten (1,1 / 1,2 …)</option>
          <option value="0.25">Viertelnoten (1,25 / 1,5 …)</option>
          <option value="0.3333333333333333">Drittelnoten (1,000 / 1,333 / 1,667 …)</option>
          <option value="0.5">halbe Noten (1,0 / 1,5 …)</option>
          <option value="1" selected>ganze Noten (1, 2, 3 …)</option>
        </select>
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="ns_calc">Berechnen</button>
      <button type="button" class="btn ghost" id="ns_reset" data-action="reset">Reset</button>
    </div>

    <div id="ns_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="ns_out_h2" id="ns_out_section" aria-live="polite">
    <h2 id="ns_out_h2">Ergebnis</h2>
    <p class="result-meta" id="ns_meta">Noch keine Berechnung.</p>
    <div class="table-wrap">
      <table class="table table--compact" id="ns_table" hidden>
        <thead>
          <tr>
            <th scope="col">Punkte</th>
            <th scope="col">Note</th>
            <th class="col-filler" aria-hidden="true"></th>
          </tr>
        </thead>
        <tbody id="ns_tbody"></tbody>
      </table>
    </div>
    <p class="hint mt-sm" id="ns_default_hint"></p>
  </section>

  <section class="card" aria-labelledby="ns_post_h2">
    <h2 id="ns_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="ns_csv" disabled aria-disabled="true">CSV herunterladen</button>
      <button type="button" class="btn" id="ns_pdf" disabled aria-disabled="true">PDF herunterladen</button>
      <button type="button" class="btn" id="ns_permalink">Permalink in Zwischenablage</button>
      <button type="button" class="btn" id="ns_snap_add">Aktuelle Konfiguration im Browser speichern</button>
    </div>
    <p class="hint flash-hint" id="ns_flash"></p>
    <ol class="snap-list" id="ns_snap_list" aria-live="polite"></ol>
  </section>

  <section class="info-block" aria-labelledby="ns_info_h2">
    <h2 id="ns_info_h2">Notenschlüssel — was steckt dahinter?</h2>
    <div class="info-grid">
      <article aria-labelledby="ns_info_a_h">
        <h3 id="ns_info_a_h">Wofür ein Notenschlüssel?</h3>
        <p>
          Ein <strong>Notenschlüssel</strong> ordnet jeder erreichbaren
          Punktzahl einer Klausur die passende Note zu. Sobald die Skala
          steht, lässt sich jede einzelne Arbeit ohne Nachdenken bewerten —
          und die Verteilung bleibt über die Klasse hinweg konsistent.
        </p>
        <p>
          Der hier erzeugte Schlüssel ist <strong>linear</strong>: zwischen
          „beste Note ab X Punkten" und „schlechteste Note ab Y Punkten"
          werden die Noten gleichmäßig verteilt. Das deckt den klassischen
          Schulfall ab und ist transparent gegenüber Schülerinnen, Schülern
          und Eltern.
        </p>
      </article>
      <article aria-labelledby="ns_info_b_h">
        <h3 id="ns_info_b_h">Tipps für die passende Skala</h3>
        <p>
          Für die <strong>Sekundarstufe I</strong> reichen meist ganze oder
          halbe Noten. Die <strong>Oberstufe</strong> arbeitet üblicherweise
          mit Zehntel- oder Drittelnoten, das <strong>Punktesystem</strong>
          (0–15) lässt sich über „beste Note 15, schlechteste Note 0"
          abbilden.
        </p>
        <p>
          „Beste Note vergeben ab Punkten" muss nicht zwangsläufig der
          Maximalpunktzahl entsprechen — wer Bonuspunkte oder einen
          „kostenlosen Puffer" einrechnen will, setzt diesen Wert niedriger
          und vergibt die Bestnote dann etwas großzügiger.
        </p>
      </article>
    </div>
  </section>

  <section class="faq-block" aria-labelledby="ns_faq_h2">
    <h2 id="ns_faq_h2">Häufige Fragen zum Notenschlüssel</h2>

    <details class="faq-item">
      <summary>Was ist ein Notenschlüssel?</summary>
      <div class="faq-answer">
        <p>Ein Notenschlüssel ordnet jeder erreichbaren Punktzahl einer
        Klausur eine Note zu. Der Notenberechner erzeugt ihn
        <strong>linear in beliebiger Skala</strong> (1–6, 0–15,
        Punkte- oder Notensystem), mit halben Punkten und frei wählbarem
        Notenschritt.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich Bonuspunkte oder einen Punkte-Puffer einbauen?</summary>
      <div class="faq-answer">
        <p>Ja. „Beste Note vergeben ab Punkten" muss nicht der
        Maximalpunktzahl entsprechen — wer einen Puffer einrechnen möchte,
        setzt diesen Wert niedriger. So gibt es die Bestnote auch
        unterhalb der vollen Punktzahl.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Welche Notenschritte unterstützt der Rechner?</summary>
      <div class="faq-answer">
        <p>Zehntel-, Viertel-, Drittel-, halbe und ganze Noten —
        abgestimmt auf Sekundarstufe I/II, Oberstufe und Hochschule.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Übernimmt der Notenschlüssel sich automatisch in andere Werkzeuge?</summary>
      <div class="faq-answer">
        <p>Wer den aktuellen Schlüssel als Standard im Browser merkt, kann
        ihn in <a href="punkte-note">„Punkte → Note"</a>,
        <a href="note-punkte">„Note → Punkte"</a>,
        <a href="statistik">„Klausur-Statistik"</a> und
        <a href="blocknoten">„Blocknoten"</a> per Toggle übernehmen. Die
        Daten bleiben dabei lokal im Browser.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich den Notenschlüssel als PDF oder CSV exportieren?</summary>
      <div class="faq-answer">
        <p>Ja. Der Schlüssel lässt sich als druckbares PDF und als CSV für
        Excel oder andere Tabellenkalkulationen herunterladen.</p>
      </div>
    </details>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
