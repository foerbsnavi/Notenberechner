<?php
$pageTitle = 'Schüler-Beurteilung schreiben';
$breadcrumbName = 'Beurteilung';
$pageDescription = 'Schüler-Beurteilungen für Zeugnis oder Bericht formulieren: Name, Fach, Note, Textlänge wählen — fertigen Text editieren, kopieren oder als PDF speichern.';
$rootPath = '';
$schemaType = 'WebApplication';
$schemaName = 'Schüler-Beurteilung schreiben — Notenberechner';
$jsFiles = ['beurteilung.js'];
$needsPdf = true;
$extraSchemas = [
  ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [
    ['@type' => 'Question', 'name' => 'Werden Schülernamen gespeichert?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Nein. Der eingegebene Name bleibt nur in der aktuellen Sitzung sichtbar — er wird weder in den Browser-Speicher noch in eine URL geschrieben. Sobald die Seite geschlossen wird, ist er weg.']],
    ['@type' => 'Question', 'name' => 'Wie passt der Generator die Tonalität an die Note an?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Pro Note gibt es einen eigenen Textbaustein-Pool mit passender Formulierung — eine 1 klingt anders als eine 4. Zusätzlich sorgt ein Wiederholungs-Schutz dafür, dass Starter und Satzbausteine nicht direkt aufeinander folgen.']],
    ['@type' => 'Question', 'name' => 'Kann ich die generierten Texte bearbeiten?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. Der Text liegt nach der Generierung in einem editierbaren Textfeld — du kannst Sätze umstellen, Formulierungen schärfen oder weicher machen, eigene Beobachtungen ergänzen.']],
    ['@type' => 'Question', 'name' => 'Welche Fächer und Textlängen sind möglich?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Standardmäßig Mathe, Deutsch, Englisch, Kunst, Musik, Sport, Biologie und Geschichte. Drei Textlängen: kurz (2–4 Sätze), mittel (4–6) und lang (5–8).']],
    ['@type' => 'Question', 'name' => 'Kann ich den Text kopieren oder als PDF speichern?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Beides ist möglich. Der Kopieren-Knopf legt den Text in die Zwischenablage, der PDF-Knopf erzeugt ein druckbares Schreiben — etwa für Zeugnis-Anhang oder Lernentwicklungsbericht.']],
  ]],
];
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Schüler-Beurteilung schreiben</h1>
  <p class="lead">Textbausteine für Schüler-Beurteilungen — generieren, anpassen, kopieren oder als PDF exportieren.</p>

  <form id="bg_form" class="card" aria-labelledby="bg_h2" novalidate>
    <h2 id="bg_h2">Eingaben</h2>
    <div class="grid">
      <div data-span="3" class="field">
        <label for="bgName">Schülername</label>
        <input id="bgName" name="bgName" type="text" maxlength="40" placeholder="z.B. Alex">
      </div>
      <div data-span="3" class="field">
        <label for="bgSubject">Unterrichtsbereich</label>
        <select id="bgSubject" name="bgSubject">
          <option value="Mathe" selected>Mathe</option>
          <option value="Deutsch">Deutsch</option>
          <option value="Englisch">Englisch</option>
          <option value="Kunst">Kunst</option>
          <option value="Musik">Musik</option>
          <option value="Sport">Sport</option>
          <option value="Biologie">Biologie</option>
          <option value="Geschichte">Geschichte</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="bgGrade">Note</label>
        <select id="bgGrade" name="bgGrade">
          <option value="1">1</option>
          <option value="2" selected>2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
          <option value="6">6</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="bgLen">Textlänge</label>
        <select id="bgLen" name="bgLen">
          <option value="short">kurz</option>
          <option value="medium" selected>mittel</option>
          <option value="long">lang</option>
        </select>
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="bg_gen">Beurteilung generieren</button>
      <button type="button" class="btn ghost" id="bg_reset" data-action="reset">Reset</button>
    </div>

    <div id="bg_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="bg_out_h2" aria-live="polite">
    <h2 id="bg_out_h2">Ergebnis</h2>
    <p class="result-meta" id="bg_meta">Noch keine Beurteilung.</p>
    <label for="bg_text" class="sr-only">Generierte Beurteilung (editierbar)</label>
    <textarea id="bg_text" rows="6" placeholder="Der Text erscheint hier…"></textarea>
    <p class="hint">Du kannst den Text vor dem Kopieren oder Exportieren noch anpassen.</p>
  </section>

  <section class="card" aria-labelledby="bg_post_h2">
    <h2 id="bg_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="bg_copy" disabled aria-disabled="true">Kopieren</button>
      <button type="button" class="btn" id="bg_pdf" disabled aria-disabled="true">PDF herunterladen</button>
    </div>
  </section>

  <section class="info-block" aria-labelledby="bg_info_h2">
    <h2 id="bg_info_h2">Beurteilungstexte für Schülerinnen und Schüler</h2>
    <div class="info-grid">
      <article aria-labelledby="bg_info_a_h">
        <h3 id="bg_info_a_h">Wofür sind Beurteilungstexte gut?</h3>
        <p>
          Beurteilungen werden in <strong>Zeugnissen</strong>, in
          <strong>Lernentwicklungsberichten</strong> und in
          <strong>Übergabeprotokollen</strong> gebraucht — überall dort, wo
          eine reine Zahl nicht reicht und Verhalten oder Leistung in Worte
          gefasst werden müssen.
        </p>
        <p>
          Der Generator liefert je nach Note und Fach passende
          <strong>Textbausteine</strong> in drei Längen. Das ist kein
          Ersatz für eine individuelle Einschätzung, sondern ein
          Startpunkt: Den Text danach noch anpassen, ergänzen und auf das
          konkrete Kind zuschneiden.
        </p>
      </article>
      <article aria-labelledby="bg_info_b_h">
        <h3 id="bg_info_b_h">Bausteine anpassen und kombinieren</h3>
        <p>
          Schülername, Fach, Note und Textlänge geben den Rahmen vor — der
          Generator achtet darauf, dass die <strong>Tonalität zur Note
          passt</strong> und Satz-Anfänge nicht direkt hintereinander
          wiederholt werden. Das Ergebnis ist nicht perfekt, aber lesbar.
        </p>
        <p>
          Im Anschluss steht der Text in einem editierbaren Feld — du kannst
          Sätze umstellen, Formulierungen verschärfen oder weicher machen,
          Beobachtungen ergänzen. Per Knopfdruck in die Zwischenablage oder
          als <strong>druckbares PDF</strong>.
        </p>
      </article>
    </div>
  </section>

  <section class="faq-block" aria-labelledby="bg_faq_h2">
    <h2 id="bg_faq_h2">Häufige Fragen zur Schüler-Beurteilung</h2>

    <details class="faq-item">
      <summary>Werden Schülernamen gespeichert?</summary>
      <div class="faq-answer">
        <p>Nein. Der eingegebene Name bleibt nur in der aktuellen Sitzung
        sichtbar — er wird weder in den Browser-Speicher noch in eine URL
        geschrieben. Sobald die Seite geschlossen wird, ist er weg.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Wie passt der Generator die Tonalität an die Note an?</summary>
      <div class="faq-answer">
        <p>Pro Note gibt es einen eigenen Textbaustein-Pool mit passender
        Formulierung — eine 1 klingt anders als eine 4. Zusätzlich sorgt
        ein <strong>Wiederholungs-Schutz</strong> dafür, dass Starter und
        Satzbausteine nicht direkt aufeinander folgen.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich die generierten Texte bearbeiten?</summary>
      <div class="faq-answer">
        <p>Ja. Der Text liegt nach der Generierung in einem editierbaren
        Textfeld — du kannst Sätze umstellen, Formulierungen schärfen oder
        weicher machen, eigene Beobachtungen ergänzen.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Welche Fächer und Textlängen sind möglich?</summary>
      <div class="faq-answer">
        <p>Standardmäßig Mathe, Deutsch, Englisch, Kunst, Musik, Sport,
        Biologie und Geschichte. Drei Textlängen: kurz (2–4 Sätze),
        mittel (4–6) und lang (5–8).</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich den Text kopieren oder als PDF speichern?</summary>
      <div class="faq-answer">
        <p>Beides ist möglich. Der Kopieren-Knopf legt den Text in die
        Zwischenablage, der PDF-Knopf erzeugt ein druckbares Schreiben —
        etwa für Zeugnis-Anhang oder Lernentwicklungsbericht.</p>
      </div>
    </details>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
