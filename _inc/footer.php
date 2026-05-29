<footer class="site-footer">
  <div class="footer-inner">
    <p class="footer-copy">&copy; <?= date('Y') ?> Notenberechner</p>
    <nav class="footer-nav" aria-label="Rechtliches">
      <a href="<?= $rootPath ?? '' ?>impressum">Impressum</a>
      <a href="<?= $rootPath ?? '' ?>datenschutz">Datenschutz</a>
      <a href="https://fabianbrose.de/" class="footer-more" rel="author noopener" aria-label="Weitere Projekte von Fabian Brose">mehr</a>
    </nav>
  </div>
</footer>
<?php
$jsBase  = __DIR__ . '/../js/';
$libBase = __DIR__ . '/../lib/';
$rp      = $rootPath ?? '';

// Reihenfolge ist wichtig: Erst Bibliotheken (jspdf), dann Core, dann State,
// dann Export (braucht NbCore), dann Tool-spezifisches JS (braucht NbExport).
if (!empty($needsPdf)) {
    $pdfMt = @filemtime($libBase . 'jspdf.umd.min.js') ?: time();
    echo '<script src="' . htmlspecialchars($rp . 'lib/jspdf.umd.min.js?v=' . $pdfMt, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
    $qrMt = @filemtime($libBase . 'qrcode.min.js') ?: time();
    echo '<script src="' . htmlspecialchars($rp . 'lib/qrcode.min.js?v=' . $qrMt, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
}

$coreMt = @filemtime($jsBase . 'core.js') ?: time();
echo '<script src="' . htmlspecialchars($rp . 'js/core.js?v=' . $coreMt, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";

$stateMt = @filemtime($jsBase . 'state.js') ?: time();
echo '<script src="' . htmlspecialchars($rp . 'js/state.js?v=' . $stateMt, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";

if (!empty($needsPdf)) {
    $expMt = @filemtime($jsBase . 'export.js') ?: time();
    echo '<script src="' . htmlspecialchars($rp . 'js/export.js?v=' . $expMt, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
}

if (!empty($jsFiles) && is_array($jsFiles)) {
    foreach ($jsFiles as $jf) {
        $mt = @filemtime($jsBase . $jf) ?: time();
        echo '<script src="' . htmlspecialchars($rp . 'js/' . $jf . '?v=' . $mt, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
    }
}
?>
</body>
</html>
