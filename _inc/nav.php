<?php
// Erwartet: $currentSlug (z.B. "notenschluessel" oder "" für Hub), $rootPath
$rootPath    = $rootPath    ?? '';
$currentSlug = $currentSlug ?? '';

$tools = [
    ['slug' => '',                  'label' => 'Übersicht'],
    ['slug' => 'notenschluessel',   'label' => 'Notenschlüssel'],
    ['slug' => 'punkte-note',       'label' => 'Punkte → Note'],
    ['slug' => 'note-punkte',       'label' => 'Note → Punkte'],
    ['slug' => 'statistik',         'label' => 'Klausur-Statistik'],
    ['slug' => 'blocknoten',        'label' => 'Blocknoten'],
    ['slug' => 'beurteilung',       'label' => 'Beurteilung'],
    ['slug' => 'wuerfeln',          'label' => 'Note würfeln'],
];
?>
<div class="tool-nav-wrapper">
  <button type="button" id="nav_toggle" class="nav-toggle" aria-expanded="false" aria-controls="tool_nav">
    <span class="nav-toggle-icon" aria-hidden="true"><span></span><span></span><span></span></span>
    <span class="nav-toggle-label">Werkzeuge</span>
  </button>
  <nav id="tool_nav" class="tool-nav" aria-label="Werkzeuge">
    <ul class="tool-nav-list">
<?php foreach ($tools as $t):
    // Hub: absoluter Pfad "/", sonst relativer Slug. So funktioniert der
    // Hub-Link von jeder Tool-Seite aus zuverlässig.
    if ($t['slug'] === '') {
        $href = $rootPath !== '' ? $rootPath : '/';
    } else {
        $href = $rootPath . $t['slug'];
    }
    $isActive = ($t['slug'] === $currentSlug) || ($t['slug'] === '' && $currentSlug === '');
?>
      <li>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
           <?= $isActive ? 'aria-current="page"' : '' ?>>
          <?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') ?>
        </a>
      </li>
<?php endforeach; ?>
    </ul>
  </nav>
</div>
