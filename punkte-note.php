<?php
$pageTitle = 'Punkte in Note umrechnen';
$breadcrumbName = 'Punkte → Note';
$pageDescription = 'Punkte in Note umrechnen — kostenlos, ohne Anmeldung. Mit gespeichertem Notenschlüssel oder eigenen Werten, live berechnet, mit PDF-Export.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Punkte in Note umrechnen — Notenberechner';
$jsFiles = ['punkte-note.js'];
$needsPdf = true;
$extraSchemas = [
  ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [
    ['@type' => 'Question', 'name' => 'Wie wird die Note aus den Punkten gerundet?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Die Note wird linear aus dem Notenschlüssel ermittelt und dann auf den eingestellten Notenschritt gerundet — wahlweise ganze, halbe, viertel, drittel oder zehntel Noten. So passt die Ausgabe immer zur tatsächlichen Praxis im Zeugnis.']],
    ['@type' => 'Question', 'name' => 'Kann ich halbe Punkte eingeben?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. Halbe Punkte lassen sich pro Klausur ein- oder ausschalten. Die Berechnung berücksichtigt sie konsistent über den gesamten Schlüssel.']],
    ['@type' => 'Question', 'name' => 'Was passiert, wenn ich keinen Notenschlüssel gespeichert habe?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Du gibst die Eckdaten direkt im Tool an: maximale Punkte, beste und schlechteste Note sowie die Punktegrenzen für die Bestnote und die schlechteste Note. Der Rechner ermittelt damit alle Zwischenwerte selbst.']],
    ['@type' => 'Question', 'name' => 'Funktioniert die Berechnung live?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. Während der Eingabe wird die Note mit kurzer Verzögerung live aktualisiert, damit beim Tippen keine Zwischenergebnisse flackern.']],
    ['@type' => 'Question', 'name' => 'Werden die Eingaben oder das Ergebnis gespeichert?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Nein. Es wird nichts an einen Server gesendet. Wer die Konfiguration sichern möchte, nutzt „Aktuelle Konfiguration im Browser speichern" — dann landet sie ausschließlich im lokalen Speicher des Browsers.']],
  ]],
];
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Punkte in Note umrechnen</h1>
  <p class="lead">Aus erreichten Punkten unmittelbar die zugehörige Note ermitteln. Eingaben werden live verarbeitet.</p>

  <form id="pn_form" class="card" aria-labelledby="pn_h2" novalidate>
    <h2 id="pn_h2">Eingaben</h2>
    <div class="grid">
      <div data-span="3" class="field">
        <label for="pnPoints">Erreichte Punkte</label>
        <input id="pnPoints" name="pnPoints" type="number" step="0.5" min="0" value="0" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnHalfPts">Halbe Punkte?</label>
        <select id="pnHalfPts" name="pnHalfPts">
          <option value="yes" selected>ja</option>
          <option value="no">nein</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="pnUseKey">Notenschlüssel</label>
        <select id="pnUseKey" name="pnUseKey">
          <option value="yes" selected>aus Notenschlüssel übernehmen</option>
          <option value="no">eigene Werte</option>
        </select>
        <p class="hint" id="pn_key_hint" aria-live="polite"></p>
      </div>
      <div data-span="3" class="field">
        <label for="pnGradeStep">Noten-Schritt</label>
        <select id="pnGradeStep" name="pnGradeStep">
          <option value="0.1">Zehntel</option>
          <option value="0.25">Viertel</option>
          <option value="0.3333333333333333">Drittel</option>
          <option value="0.5">Halbe</option>
          <option value="1" selected>Ganze</option>
        </select>
      </div>

      <div data-span="3" class="field">
        <label for="pnBestG">Beste Note</label>
        <input id="pnBestG" name="pnBestG" type="number" step="0.01" value="1" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnWorstG">Schlechteste Note</label>
        <input id="pnWorstG" name="pnWorstG" type="number" step="0.01" value="6" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnMaxPts">Max. Punkte</label>
        <input id="pnMaxPts" name="pnMaxPts" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnBestFrom">Beste ab Punkte</label>
        <input id="pnBestFrom" name="pnBestFrom" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnWorstFrom">Schlechteste ab Punkte</label>
        <input id="pnWorstFrom" name="pnWorstFrom" type="number" step="0.5" min="0" value="0" inputmode="decimal">
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="pn_calc">Berechnen</button>
      <button type="button" class="btn ghost" id="pn_reset" data-action="reset">Reset</button>
    </div>

    <div id="pn_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="pn_out_h2" aria-live="polite">
    <h2 id="pn_out_h2">Ergebnis</h2>
    <p class="result-meta" id="pn_meta">Noch keine Berechnung.</p>
    <output for="pnPoints pnMaxPts" id="pn_grade" class="result-big">–</output>
  </section>

  <section class="card" aria-labelledby="pn_post_h2">
    <h2 id="pn_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="pn_pdf" disabled aria-disabled="true">PDF herunterladen</button>
      <button type="button" class="btn" id="pn_permalink">Permalink in Zwischenablage</button>
      <button type="button" class="btn" id="pn_snap_add">Aktuelle Konfiguration im Browser speichern</button>
    </div>
    <p class="hint flash-hint" id="pn_flash"></p>
    <ol class="snap-list" id="pn_snap_list" aria-live="polite"></ol>
  </section>

  <section class="info-block" aria-labelledby="pn_info_h2">
    <h2 id="pn_info_h2">Punkte in Note umrechnen</h2>
    <div class="info-grid">
      <article aria-labelledby="pn_info_a_h">
        <h3 id="pn_info_a_h">Klausur schnell korrigieren</h3>
        <p>
          Beim Korrigieren ist „Punkte → Note" das Werkzeug für die
          <strong>Einzelumrechnung</strong>: Du gibst die erreichten Punkte
          ein und siehst sofort die zugehörige Note. Die Berechnung läuft
          <strong>live mit kurzer Verzögerung</strong>, damit Tipper nicht
          jedes Zwischenergebnis anzeigen.
        </p>
        <p>
          Halbe Punkte lassen sich pro Aufgabe ein- und ausschalten.
          Gerundet wird automatisch auf den gewählten Notenschritt — ganz,
          halb, viertel, drittel oder zehntel.
        </p>
      </article>
      <article aria-labelledby="pn_info_b_h">
        <h3 id="pn_info_b_h">Mit Notenschlüssel arbeiten</h3>
        <p>
          Hast du zuvor unter <a href="notenschluessel">Notenschlüssel</a>
          einen Standardschlüssel als Browser-Default gespeichert, wird er
          hier automatisch übernommen. Die Meta-Zeile zeigt, welche
          <strong>Quelle</strong> aktuell aktiv ist.
        </p>
        <p>
          Wer pro Klausur eigene Werte angeben will, schaltet den
          Schlüssel-Toggle ab und arbeitet mit eigenen Punkt- und
          Notengrenzen. Per Permalink lässt sich die Konfiguration teilen,
          ohne dass die Daten den Browser verlassen.
        </p>
      </article>
    </div>
  </section>

  <section class="faq-block" aria-labelledby="pn_faq_h2">
    <h2 id="pn_faq_h2">Häufige Fragen zu „Punkte in Note umrechnen"</h2>

    <details class="faq-item">
      <summary>Wie wird die Note aus den Punkten gerundet?</summary>
      <div class="faq-answer">
        <p>Die Note wird linear aus dem <a href="notenschluessel">Notenschlüssel</a>
        ermittelt und dann auf den eingestellten Notenschritt gerundet —
        wahlweise <strong>ganze, halbe, viertel, drittel oder zehntel</strong>
        Noten. So passt die Ausgabe immer zur tatsächlichen Praxis im
        Zeugnis.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich halbe Punkte eingeben?</summary>
      <div class="faq-answer">
        <p>Ja. Halbe Punkte lassen sich pro Klausur ein- oder ausschalten.
        Die Berechnung berücksichtigt sie konsistent über den gesamten
        Schlüssel.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Was passiert, wenn ich keinen Notenschlüssel gespeichert habe?</summary>
      <div class="faq-answer">
        <p>Du gibst die Eckdaten direkt im Tool an: maximale Punkte, beste
        und schlechteste Note sowie die Punktegrenzen. Der Rechner
        ermittelt damit alle Zwischenwerte selbst.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Funktioniert die Berechnung live?</summary>
      <div class="faq-answer">
        <p>Ja. Während der Eingabe wird die Note mit kurzer Verzögerung
        live aktualisiert, damit beim Tippen keine Zwischenergebnisse
        flackern.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Werden die Eingaben oder das Ergebnis gespeichert?</summary>
      <div class="faq-answer">
        <p>Nein. Es wird nichts an einen Server gesendet. Wer die
        Konfiguration sichern möchte, nutzt „Aktuelle Konfiguration im
        Browser speichern" — dann landet sie ausschließlich im lokalen
        Speicher des Browsers.</p>
      </div>
    </details>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
