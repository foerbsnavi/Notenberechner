<?php
$pageTitle = 'Note würfeln — Zufallsnote ziehen';
$breadcrumbName = 'Note würfeln';
$pageDescription = 'Zufällige Note würfeln: mit Tendenz nach oben oder unten, in Zehntel-, Viertel-, Drittel-, halben oder ganzen Noten und frei wählbarem Bereich — kostenlos.';
$rootPath = '';
$schemaType = 'WebApplication';
$schemaName = 'Note würfeln — Notenberechner';
$jsFiles = ['wuerfeln.js'];
$needsPdf = true;
$extraSchemas = [
  ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [
    ['@type' => 'Question', 'name' => 'Wofür braucht man eine Zufallsnote?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Vor allem für Vertretungsstunden, Beispiel-Datensätze in Notenkonferenzen, didaktische Demonstrationen oder zum Testen der anderen Tools — die Klausur-Statistik lässt sich damit prima mit Beispieldaten füttern.']],
    ['@type' => 'Question', 'name' => 'Was bewirken die Tendenz-Optionen?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => '„Eher gut" verschiebt die Würfelergebnisse statistisch in Richtung der besseren Noten, „eher nicht so gut" in Richtung der schlechteren. „Zufall" bedeutet gleichmäßige Verteilung im gewählten Bereich.']],
    ['@type' => 'Question', 'name' => 'Welche Notenformate unterstützt der Würfel?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ganze, halbe, Drittel-, Viertel- und Zehntelnoten. So passt das Ergebnis zu jedem Notensystem zwischen Grundschule und Hochschule.']],
    ['@type' => 'Question', 'name' => 'Kann ich den Bereich einschränken?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. Min-Note und Max-Note grenzen den möglichen Wertebereich ein — etwa, wenn nur Noten zwischen 2 und 4 interessant sind.']],
    ['@type' => 'Question', 'name' => 'Kann ich das Würfelergebnis weiterverwenden?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. Das Ergebnis lässt sich als druckbares PDF speichern — praktisch für Beispieldatensätze in Notenkonferenzen oder Demonstrations-Material. Mehrere Würfe lassen sich auch direkt in die Klausur-Statistik kopieren.']],
  ]],
];
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Note würfeln</h1>
  <p class="lead">Zufällige Note ziehen — mit oder ohne Tendenz, in verschiedenen Notenschritten.</p>

  <form id="dc_form" class="card" aria-labelledby="dc_h2" novalidate>
    <h2 id="dc_h2">Eingaben</h2>
    <div class="grid">
      <div data-span="6" class="field">
        <label for="dcFormat">Notenformat</label>
        <select id="dcFormat" name="dcFormat">
          <option value="1">Ganze Note (1, 2, 3 …)</option>
          <option value="0.5">Halbe Note (1,0 / 1,5 …)</option>
          <option value="0.25">Viertelnoten (1,25 / 1,5 …)</option>
          <option value="0.3333333333333333">Drittelnoten (1,000 / 1,333 …)</option>
          <option value="0.1" selected>Zehntelnoten (1,1 / 1,2 …)</option>
        </select>
      </div>
      <div data-span="6" class="field">
        <label for="dcBias">Tendenz</label>
        <select id="dcBias" name="dcBias">
          <option value="random" selected>Zufall</option>
          <option value="good">Eher gut</option>
          <option value="bad">Eher nicht so gut</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="dcMin">Min. Note</label>
        <input id="dcMin" name="dcMin" type="number" step="0.1" value="1" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="dcMax">Max. Note</label>
        <input id="dcMax" name="dcMax" type="number" step="0.1" value="6" inputmode="decimal">
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="dc_roll">
        <span aria-hidden="true">🎲</span> Würfeln
      </button>
      <button type="button" class="btn ghost" id="dc_reset" data-action="reset">Reset</button>
    </div>

    <div id="dc_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="dc_out_h2" aria-live="polite">
    <h2 id="dc_out_h2">Ergebnis</h2>
    <p class="result-meta" id="dc_meta">Noch nicht gewürfelt.</p>
    <output for="dcMin dcMax" id="dc_out" class="result-big">–</output>
  </section>

  <section class="card" aria-labelledby="dc_post_h2">
    <h2 id="dc_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="dc_pdf" disabled aria-disabled="true">PDF herunterladen</button>
    </div>
  </section>

  <section class="info-block" aria-labelledby="dc_info_h2">
    <h2 id="dc_info_h2">Note würfeln — wofür ist das gut?</h2>
    <div class="info-grid">
      <article aria-labelledby="dc_info_a_h">
        <h3 id="dc_info_a_h">Wozu eine Zufallsnote?</h3>
        <p>
          Das Würfeln ist <strong>kein</strong> Werkzeug zur Bewertung
          echter Leistungen — sondern eine Spielerei für Vertretungsstunden,
          für <strong>Beispiel-Datensätze</strong> in der Notenkonferenz,
          für didaktische Demonstrationen oder schlicht zur Auflockerung.
        </p>
        <p>
          Praktisch ist es auch zum <strong>Testen der anderen Tools</strong>:
          Ein paar gewürfelte Noten in die <a href="statistik">Klausur-Statistik</a>
          kopieren, und schon hat man Beispieldaten, um Verteilung,
          Bestehensquote und <a href="notenschluessel">Notenschlüssel</a>
          auszuprobieren.
        </p>
      </article>
      <article aria-labelledby="dc_info_b_h">
        <h3 id="dc_info_b_h">Tendenz und Bereich einstellen</h3>
        <p>
          Bei reiner Zufallsverteilung sind alle Noten gleich
          wahrscheinlich. Mit der Tendenz <strong>„eher gut"</strong>
          verteilt sich das Würfelergebnis stärker in Richtung der besseren
          Noten, mit <strong>„eher nicht so gut"</strong> umgekehrt.
        </p>
        <p>
          Über die Felder „Min-Note" und „Max-Note" lässt sich der
          Wertebereich eingrenzen — etwa wenn nur Noten zwischen 2 und 4
          interessant sind. Das Notenformat (ganz, halb, viertel, drittel,
          zehntel) bestimmt, wie fein der Würfelwert dargestellt wird.
        </p>
      </article>
    </div>
  </section>

  <section class="faq-block" aria-labelledby="dc_faq_h2">
    <h2 id="dc_faq_h2">Häufige Fragen zum Noten-Würfeln</h2>

    <details class="faq-item">
      <summary>Wofür braucht man eine Zufallsnote?</summary>
      <div class="faq-answer">
        <p>Vor allem für Vertretungsstunden, Beispiel-Datensätze in
        Notenkonferenzen, didaktische Demonstrationen oder zum Testen der
        anderen Tools — die <a href="statistik">Klausur-Statistik</a>
        lässt sich damit prima mit Beispieldaten füttern.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Was bewirken die Tendenz-Optionen?</summary>
      <div class="faq-answer">
        <p><strong>„Eher gut"</strong> verschiebt die Würfelergebnisse
        statistisch in Richtung der besseren Noten,
        <strong>„eher nicht so gut"</strong> in Richtung der schlechteren.
        <strong>„Zufall"</strong> bedeutet gleichmäßige Verteilung im
        gewählten Bereich.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Welche Notenformate unterstützt der Würfel?</summary>
      <div class="faq-answer">
        <p>Ganze, halbe, Drittel-, Viertel- und Zehntelnoten. So passt das
        Ergebnis zu jedem Notensystem zwischen Grundschule und Hochschule.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich den Bereich einschränken?</summary>
      <div class="faq-answer">
        <p>Ja. Min-Note und Max-Note grenzen den möglichen Wertebereich ein
        — etwa, wenn nur Noten zwischen 2 und 4 interessant sind.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich das Würfelergebnis weiterverwenden?</summary>
      <div class="faq-answer">
        <p>Ja. Das Ergebnis lässt sich als druckbares PDF speichern —
        praktisch für Beispieldatensätze in Notenkonferenzen oder
        Demonstrations-Material. Mehrere Würfe lassen sich auch direkt in
        die <a href="statistik">Klausur-Statistik</a> kopieren.</p>
      </div>
    </details>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
