<?php
$pageTitleFull   = 'Notenberechner für Schulnoten – Notenschlüssel & Punkte';
$pageDescription = 'Kostenloser Notenberechner: Notenschlüssel erstellen, Punkte in Noten umrechnen, Klausuren auswerten, Zeugnisnoten gewichten — direkt im Browser.';
$rootPath = '';
$schemaType = 'WebSite';
$schemaName = 'Notenberechner für Schulnoten';
$jsFiles = ['hub.js'];

// Zusätzliche strukturierte Daten: ItemList aller Tools + FAQPage für Rich Results.
$siteUrl = 'https://noten.brosemedien.de/';
$extraSchemas = [
  [
    '@context' => 'https://schema.org',
    '@type'    => 'ItemList',
    'name'     => 'Werkzeuge im Notenberechner',
    'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Notenschlüssel erstellen',     'url' => $siteUrl . 'notenschluessel'],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Punkte in Note umrechnen',      'url' => $siteUrl . 'punkte-note'],
      ['@type' => 'ListItem', 'position' => 3, 'name' => 'Note in Punkte umrechnen',      'url' => $siteUrl . 'note-punkte'],
      ['@type' => 'ListItem', 'position' => 4, 'name' => 'Klausurergebnisse auswerten',   'url' => $siteUrl . 'statistik'],
      ['@type' => 'ListItem', 'position' => 5, 'name' => 'Blocknoten gewichtet berechnen','url' => $siteUrl . 'blocknoten'],
      ['@type' => 'ListItem', 'position' => 6, 'name' => 'Schüler-Beurteilung schreiben', 'url' => $siteUrl . 'beurteilung'],
      ['@type' => 'ListItem', 'position' => 7, 'name' => 'Note würfeln',                  'url' => $siteUrl . 'wuerfeln'],
    ],
  ],
  [
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => [
      [
        '@type' => 'Question',
        'name'  => 'Was ist ein Notenschlüssel?',
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text'  => 'Ein Notenschlüssel ordnet jeder erreichten Punktzahl eine Schulnote zu. Der Notenberechner erstellt lineare Notenschlüssel in beliebiger Skala (1–6, 0–15, Punkte- oder Notensystem) — mit halben Punkten, frei wählbarem Notenschritt und individuell festlegbaren Grenzen für die beste und schlechteste Note.',
        ],
      ],
      [
        '@type' => 'Question',
        'name'  => 'Wie rechne ich Punkte in eine Note um?',
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text'  => 'Trage die erreichte Punktzahl ins Werkzeug „Punkte → Note" ein. Wenn du zuvor einen Notenschlüssel gespeichert hast, übernimmt der Rechner ihn automatisch. Andernfalls gibst du Max-Punkte, beste und schlechteste Note an — die Note wird live berechnet und kann als PDF exportiert werden.',
        ],
      ],
      [
        '@type' => 'Question',
        'name'  => 'Wie berechne ich eine Zeugnisnote aus mehreren Einzelnoten?',
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text'  => 'Das Werkzeug „Blocknoten" rechnet beliebig viele Bewertungsblöcke gewichtet zusammen — etwa Klassenarbeiten, Tests, mündliche Mitarbeit oder Referate. Gewichtung wahlweise prozentual (Summe 100 %) oder anteilig. Ergebnis als gerundete und ungerundete Note, mit CSV- und PDF-Export.',
        ],
      ],
      [
        '@type' => 'Question',
        'name'  => 'Werden meine Eingaben gespeichert oder übertragen?',
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text'  => 'Nein. Alle Berechnungen laufen direkt im Browser, es werden keine Daten an einen Server gesendet. Gespeicherte Konfigurationen liegen ausschließlich im lokalen Speicher deines Browsers (localStorage) und können jederzeit gelöscht werden.',
        ],
      ],
      [
        '@type' => 'Question',
        'name'  => 'Kann ich halbe Punkte und unterschiedliche Notenschritte verwenden?',
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text'  => 'Ja. Halbe Punkte lassen sich pro Werkzeug aktivieren. Bei der Note kannst du zwischen ganzen, halben, Drittel-, Viertel- und Zehntelnoten wählen — passend zu Sekundarstufe I/II, Oberstufe oder Hochschule.',
        ],
      ],
      [
        '@type' => 'Question',
        'name'  => 'Funktioniert der Notenberechner auch auf dem Handy?',
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text'  => 'Ja. Die Oberfläche ist responsiv und für Smartphone, Tablet und Desktop optimiert. Die Tool-Navigation wird auf kleinen Bildschirmen horizontal scrollbar.',
        ],
      ],
      [
        '@type' => 'Question',
        'name'  => 'Kann ich Notenschlüssel und Klausurstatistik als PDF exportieren?',
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text'  => 'Ja. Alle relevanten Werkzeuge bieten einen PDF-Export für die Klausurmappe sowie einen CSV-Export für die Weiterverarbeitung in Excel oder anderen Tabellenkalkulationen.',
        ],
      ],
      [
        '@type' => 'Question',
        'name'  => 'Ist der Notenberechner kostenlos?',
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text'  => 'Ja, die Nutzung ist vollständig kostenlos und ohne Anmeldung möglich. Es gibt keine Pro-Version und keine versteckten Kosten.',
        ],
      ],
    ],
  ],
];

include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Notenberechner für Schulnoten</h1>
  <p class="lead">
    Notenschlüssel erstellen, Punkte in Noten umrechnen, Klausuren auswerten,
    Blocknoten gewichten, Beurteilungen schreiben — sieben kostenlose Werkzeuge
    für Lehrkräfte, direkt im Browser. Ohne Anmeldung, ohne Datenspeicherung.
  </p>

  <div class="hub-grid">
    <a class="hub-card" href="notenschluessel">
      <h2>1 · Notenschlüssel</h2>
      <p>Linearen Punkte-zu-Noten-Schlüssel generieren — frei skalierbar, mit CSV- und PDF-Export.</p>
    </a>
    <a class="hub-card" href="punkte-note">
      <h2>2 · Punkte → Note</h2>
      <p>Aus erreichten Punkten direkt die Note ablesen, optional mit dem gespeicherten Schlüssel.</p>
    </a>
    <a class="hub-card" href="note-punkte">
      <h2>3 · Note → Punkte</h2>
      <p>Rückwärts: Wie viele Punkte sind für eine Zielnote nötig?</p>
    </a>
    <a class="hub-card" href="statistik">
      <h2>4 · Klausur-Statistik</h2>
      <p>Werte einfügen, Kennzahlen und Verteilung erhalten — mit CSV- und PDF-Export.</p>
    </a>
    <a class="hub-card" href="blocknoten">
      <h2>5 · Blocknoten</h2>
      <p>Mehrere Bewertungsblöcke gewichtet zusammenrechnen, prozentual oder anteilig.</p>
    </a>
    <a class="hub-card" href="beurteilung">
      <h2>6 · Beurteilung</h2>
      <p>Textbausteine für Schüler-Beurteilungen — Note, Fach, Länge wählen, fertig.</p>
    </a>
    <a class="hub-card" href="wuerfeln">
      <h2>7 · Note würfeln</h2>
      <p>Zufällige Note ziehen, optional mit Tendenz nach oben oder unten.</p>
    </a>
  </div>

  <section class="card mt-lg" aria-labelledby="hub_snap_h2">
    <h2 id="hub_snap_h2">Gespeicherte Berechnungen</h2>
    <p class="hint">Konfigurationen, die du in den einzelnen Werkzeugen über
       „Aktuelle Konfiguration im Browser speichern" abgelegt hast. Klick auf
       „laden" öffnet das passende Werkzeug mit den hinterlegten Werten.</p>
    <ol class="snap-list" id="hub_snap_list" aria-live="polite"></ol>
    <p class="hint" id="hub_snap_empty" hidden>Noch keine Konfigurationen gespeichert.</p>
  </section>

  <div class="hub-disclaimer">
    Alle Berechnungen erfolgen ohne Gewähr und ohne Datenspeicherung auf einem Server.
    Konfigurationen liegen ausschließlich im lokalen Speicher deines Browsers.
  </div>

  <section class="info-block" aria-labelledby="info_h2">
    <h2 id="info_h2">Was kann der Notenberechner?</h2>
    <div class="info-grid">

    <article aria-labelledby="info_about_h">
      <h3 id="info_about_h">Sieben Werkzeuge unter einem Dach</h3>
      <p>
        Der Notenberechner bündelt die Aufgaben, die im Lehrkraft-Alltag
        regelmäßig anfallen: einen <strong>Notenschlüssel erstellen</strong>,
        <strong>Punkte in Noten umrechnen</strong>, die Ergebnisse einer
        Klausur <strong>statistisch auswerten</strong>, mehrere Einzelnoten
        zu einer <strong>Zeugnis- oder Halbjahresnote</strong> zusammenführen
        und am Ende eine <strong>schriftliche Beurteilung</strong>
        formulieren.
      </p>
      <p>
        Alle Werkzeuge greifen sauber ineinander: Der einmal angelegte
        Notenschlüssel steht in den anderen Tools automatisch zur Verfügung.
        Konfigurationen lassen sich per Permalink teilen oder im Browser
        merken. Nichts muss installiert oder eingerichtet werden.
      </p>
    </article>

    <article aria-labelledby="info_workflow_h">
      <h3 id="info_workflow_h">Notenschlüssel erstellen — so funktioniert es</h3>
      <p>
        Du legst die <strong>Skala</strong> fest (etwa 1 bis 6), die
        <strong>maximale Punktzahl</strong> und die Grenzen, ab denen die
        beste und schlechteste Note vergeben wird. Optional halbe Punkte,
        Notenausgabe in Zehntel-, Viertel-, Drittel-, halben oder ganzen
        Schritten.
      </p>
      <p>
        Den fertigen <a href="notenschluessel">Notenschlüssel</a> kannst du
        als Standard im Browser merken. „Punkte → Note", „Note → Punkte",
        „Klausur-Statistik" und „Blocknoten" greifen automatisch darauf zurück
        — keine doppelten Eingaben, keine Excel-Tabelle nebenher.
      </p>
    </article>

    <article aria-labelledby="info_klausur_h">
      <h3 id="info_klausur_h">Klausur korrigieren und auswerten</h3>
      <p>
        Beim Korrigieren nutzt du <a href="punkte-note">„Punkte → Note"</a>:
        Punkte eintragen, Note ablesen. Live-Berechnung, sauber gerundet im
        gewählten Notenschritt. Wer rückwärts denkt — etwa für die Information
        vor der Klausur — nimmt <a href="note-punkte">„Note → Punkte"</a>
        und sieht, wie viele Punkte für eine Zielnote nötig sind.
      </p>
      <p>
        Ist die Klausur durch, hilft die
        <a href="statistik">Klausur-Statistik</a>: Werte als Komma-,
        Leerzeichen- oder Zeilenliste einfügen — Durchschnitt, Median, Modus,
        Standardabweichung, Spannweite und die Bestehensquote werden direkt
        ausgegeben.
      </p>
    </article>

    <article aria-labelledby="info_zeugnis_h">
      <h3 id="info_zeugnis_h">Zeugnis- und Halbjahresnoten gewichten</h3>
      <p>
        Zum Schuljahresende oder Halbjahr fließen meist mehrere Bestandteile
        zur Gesamtnote zusammen: Klassenarbeiten, kürzere Tests, mündliche
        Mitarbeit, Referate, Projekte. <a href="blocknoten">„Blocknoten"</a>
        bildet diese Struktur ab — beliebig viele Blöcke mit je bis zu fünf
        Einzelnoten, frei benennbar und einzeln gewichtbar.
      </p>
      <p>
        Du entscheidest zwischen prozentualer Gewichtung (Summe 100 %) und
        anteiliger Gewichtung mit beliebigen positiven Werten. Das Ergebnis
        erscheint mit zwei Nachkommastellen sowie gerundet auf den
        eingestellten Notenschritt.
      </p>
    </article>

    <article aria-labelledby="info_audience_h">
      <h3 id="info_audience_h">Für wen ist der Notenberechner gedacht?</h3>
      <p>
        Konzipiert für <strong>Lehrkräfte aller Schulformen</strong> —
        Grundschule, Sekundarstufe I und II, berufliche Schulen. Genauso
        brauchbar für <strong>Referendarinnen und Referendare</strong> im
        Vorbereitungsdienst, <strong>Dozierende an Hochschulen</strong> mit
        Punkte- oder Notensystemen und für die <strong>private
        Nachhilfe</strong>.
      </p>
      <p>
        Auch Schülerinnen und Schüler, die für eine Arbeit ausrechnen wollen,
        wie viele Punkte sie für ihre Wunschnote brauchen, sind hier richtig.
      </p>
    </article>

    <article aria-labelledby="info_privacy_h">
      <h3 id="info_privacy_h">Datenschutz: alles bleibt im Browser</h3>
      <p>
        Kein Login, kein Tracking, keine Server-Speicherung. Sämtliche
        Berechnungen laufen <strong>clientseitig in deinem Browser</strong>.
        Was du speicherst, landet ausschließlich im lokalen Speicher
        (localStorage) deines Geräts.
      </p>
      <p>
        Schülernamen im Beurteilungsgenerator werden weder geteilt noch
        persistiert. Das macht den Notenberechner auch dann unbedenklich
        einsetzbar, wenn die Schule strenge Vorgaben an digitale Werkzeuge
        stellt.
      </p>
    </article>
    </div>
  </section>

  <section class="faq-block" aria-labelledby="hub_faq_h2">
    <h2 id="hub_faq_h2">Häufige Fragen</h2>

    <details class="faq-item">
      <summary>Was ist ein Notenschlüssel?</summary>
      <div class="faq-answer">
        <p>Ein Notenschlüssel ordnet jeder erreichten Punktzahl eine Schulnote
        zu. Der Notenberechner erstellt lineare Notenschlüssel in beliebiger
        Skala (1–6, 0–15, Punkte- oder Notensystem) — mit halben Punkten,
        frei wählbarem Notenschritt und individuell festlegbaren Grenzen für
        die beste und schlechteste Note.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Wie rechne ich Punkte in eine Note um?</summary>
      <div class="faq-answer">
        <p>Trage die erreichte Punktzahl im Werkzeug
        <a href="punkte-note">„Punkte → Note"</a> ein. Wenn du zuvor einen
        Notenschlüssel gespeichert hast, übernimmt der Rechner ihn automatisch.
        Andernfalls gibst du Max-Punkte, beste und schlechteste Note an — die
        Note wird live berechnet und kann als PDF exportiert werden.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Wie berechne ich eine Zeugnisnote aus mehreren Einzelnoten?</summary>
      <div class="faq-answer">
        <p>Das Werkzeug <a href="blocknoten">„Blocknoten"</a> rechnet beliebig
        viele Bewertungsblöcke gewichtet zusammen — etwa Klassenarbeiten,
        Tests, mündliche Mitarbeit oder Referate. Gewichtung wahlweise
        prozentual (Summe 100 %) oder anteilig. Ergebnis als gerundete und
        ungerundete Note, mit CSV- und PDF-Export.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Werden meine Eingaben gespeichert oder übertragen?</summary>
      <div class="faq-answer">
        <p>Nein. Alle Berechnungen laufen direkt im Browser, es werden keine
        Daten an einen Server gesendet. Gespeicherte Konfigurationen liegen
        ausschließlich im lokalen Speicher deines Browsers (localStorage) und
        können jederzeit gelöscht werden.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich halbe Punkte und unterschiedliche Notenschritte verwenden?</summary>
      <div class="faq-answer">
        <p>Ja. Halbe Punkte lassen sich pro Werkzeug aktivieren. Bei der Note
        kannst du zwischen ganzen, halben, Drittel-, Viertel- und Zehntelnoten
        wählen — passend zu Sekundarstufe I/II, Oberstufe oder Hochschule.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Funktioniert der Notenberechner auch auf dem Handy?</summary>
      <div class="faq-answer">
        <p>Ja. Die Oberfläche ist responsiv und für Smartphone, Tablet und
        Desktop optimiert. Die Tool-Navigation wird auf kleinen Bildschirmen
        horizontal scrollbar.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich Notenschlüssel und Klausurstatistik als PDF exportieren?</summary>
      <div class="faq-answer">
        <p>Ja. Alle relevanten Werkzeuge bieten einen PDF-Export für die
        Klausurmappe sowie einen CSV-Export für die Weiterverarbeitung in
        Excel oder anderen Tabellenkalkulationen.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Ist der Notenberechner kostenlos?</summary>
      <div class="faq-answer">
        <p>Ja, die Nutzung ist vollständig kostenlos und ohne Anmeldung
        möglich. Es gibt keine Pro-Version und keine versteckten Kosten.</p>
      </div>
    </details>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
