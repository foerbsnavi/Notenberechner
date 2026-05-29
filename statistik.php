<?php
$pageTitle = 'Klausurergebnisse auswerten';
$breadcrumbName = 'Klausur-Statistik';
$pageDescription = 'Klausurergebnisse auswerten: Durchschnitt, Median, Modus, Standardabweichung, Spannweite, Bestehensquote, Notenverteilung — mit CSV- und PDF-Export.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Klausurergebnisse auswerten — Notenberechner';
$jsFiles = ['statistik.js'];
$needsPdf = true;
$extraSchemas = [
  ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [
    ['@type' => 'Question', 'name' => 'Welche Trennzeichen kann ich für die Eingabe verwenden?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Komma, Leerzeichen und Zeilenumbruch sind erlaubt — gemischt geht auch. Als Dezimaltrennzeichen funktionieren sowohl Komma als auch Punkt.']],
    ['@type' => 'Question', 'name' => 'Kann ich auch Noten statt Punkte eingeben?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. Über den Modus-Schalter wählst du, ob die eingegebenen Werte als Noten oder als Punkte interpretiert werden. Bei Punkten kannst du die Maximalpunktzahl angeben oder den gespeicherten Notenschlüssel übernehmen.']],
    ['@type' => 'Question', 'name' => 'Was bedeutet die Standardabweichung?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Die Standardabweichung misst, wie stark die einzelnen Ergebnisse vom Mittelwert abweichen. Ein kleiner Wert deutet auf eine homogene Klasse hin, ein großer Wert auf breit gestreute Leistungen.']],
    ['@type' => 'Question', 'name' => 'Was ist die Bestehensquote?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Der Anteil aller Arbeiten mit Note 4 oder besser — also bestanden. Sie wird automatisch berechnet, wenn die Eingabe in Punkten erfolgt und ein Notenschlüssel anliegt.']],
    ['@type' => 'Question', 'name' => 'Wie wird die Notenverteilung gerundet?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Die Verteilung wird auf den eingestellten Notenschritt (ganz, halb, viertel, drittel, zehntel) gerundet. So entspricht sie dem Format, das im Zeugnis tatsächlich vergeben wird.']],
  ]],
];
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

  <section class="info-block" aria-labelledby="ks_info_h2">
    <h2 id="ks_info_h2">Klausurergebnisse auswerten</h2>
    <div class="info-grid">
      <article aria-labelledby="ks_info_a_h">
        <h3 id="ks_info_a_h">Was die Kennzahlen aussagen</h3>
        <p>
          <strong>Durchschnitt</strong> und <strong>Median</strong> zeigen
          das mittlere Niveau einer Klausur. Liegen sie weit auseinander,
          gibt es einzelne starke Ausreißer; der Median ist dann der
          robustere Wert. Der <strong>Modus</strong> verrät, welche Note am
          häufigsten vorkommt.
        </p>
        <p>
          Die <strong>Standardabweichung</strong> misst die Streuung: ein
          kleiner Wert deutet auf eine homogene Klasse, ein großer Wert auf
          breit verteilte Leistungen. Die <strong>Spannweite</strong> nennt
          schlicht die Differenz zwischen bester und schwächster Note.
        </p>
      </article>
      <article aria-labelledby="ks_info_b_h">
        <h3 id="ks_info_b_h">Bestehensquote und Notenverteilung</h3>
        <p>
          Bei Eingabe in Punkten lässt sich automatisch die
          <strong>Bestehensquote</strong> berechnen (Anteil mit Note ≤ 4).
          Sie ist ein schneller Indikator dafür, ob eine Klausur
          angemessen schwer war oder ob der
          <a href="notenschluessel">Notenschlüssel</a> nachjustiert werden
          sollte.
        </p>
        <p>
          Die <strong>Verteilungstabelle</strong> zeigt, wie viele Arbeiten
          auf welche Note entfallen — gerundet auf den eingestellten
          Notenschritt. Praktisch für Notenkonferenzen, Elternabende oder
          die eigene Reflexion der Aufgabenstellung.
        </p>
      </article>
    </div>
  </section>

  <section class="faq-block" aria-labelledby="ks_faq_h2">
    <h2 id="ks_faq_h2">Häufige Fragen zur Klausur-Statistik</h2>

    <details class="faq-item">
      <summary>Welche Trennzeichen kann ich für die Eingabe verwenden?</summary>
      <div class="faq-answer">
        <p>Komma, Leerzeichen und Zeilenumbruch sind erlaubt — gemischt
        geht auch. Als Dezimaltrennzeichen funktionieren sowohl Komma als
        auch Punkt.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich auch Noten statt Punkte eingeben?</summary>
      <div class="faq-answer">
        <p>Ja. Über den Modus-Schalter wählst du, ob die eingegebenen Werte
        als Noten oder als Punkte interpretiert werden. Bei Punkten kannst
        du die Maximalpunktzahl angeben oder den gespeicherten
        <a href="notenschluessel">Notenschlüssel</a> übernehmen.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Was bedeutet die Standardabweichung?</summary>
      <div class="faq-answer">
        <p>Die Standardabweichung misst, wie stark die einzelnen Ergebnisse
        vom Mittelwert abweichen. Ein <strong>kleiner Wert</strong> deutet
        auf eine homogene Klasse hin, ein <strong>großer Wert</strong> auf
        breit gestreute Leistungen.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Was ist die Bestehensquote?</summary>
      <div class="faq-answer">
        <p>Der Anteil aller Arbeiten mit Note 4 oder besser — also
        bestanden. Sie wird automatisch berechnet, wenn die Eingabe in
        Punkten erfolgt und ein Notenschlüssel anliegt.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Wie wird die Notenverteilung gerundet?</summary>
      <div class="faq-answer">
        <p>Die Verteilung wird auf den eingestellten Notenschritt (ganz,
        halb, viertel, drittel, zehntel) gerundet. So entspricht sie dem
        Format, das im Zeugnis tatsächlich vergeben wird.</p>
      </div>
    </details>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
