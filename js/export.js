/* ─────────────────────────────────────────────────────────────
   export.js — PDF (jsPDF) und CSV-Helfer.
   Wird nur auf Tool-Seiten geladen, die exportieren können.
   ───────────────────────────────────────────────────────────── */

(function (root) {
  "use strict";

  if (!root.NbCore) {
    console.error("export.js: NbCore fehlt.");
    return;
  }
  const { ensureJsPDF, pdfHeaderLines, downloadBlob, makeFilename, setLoading } = root.NbCore;

  // ── CSV ─────────────────────────────────────────────────────
  function csvEscape(v) {
    let s = (v === null || v === undefined) ? "" : String(v);
    s = s.replace(/\r?\n/g, " ");
    if (s.indexOf(";") !== -1 || s.indexOf('"') !== -1) {
      s = '"' + s.replace(/"/g, '""') + '"';
    }
    return s;
  }

  function buildCSV(rows) {
    // rows: Array von Arrays
    const lines = rows.map(r => r.map(csvEscape).join(";"));
    return "﻿" + lines.join("\n");
  }

  function exportCSV(rows, filenamePrefix) {
    const csv = buildCSV(rows);
    downloadBlob(new Blob([csv], { type: "text/csv;charset=utf-8" }),
                 makeFilename(filenamePrefix, "csv"));
  }

  // ── PDF: einfacher Stream-Schreiber ─────────────────────────
  function makePdfWriter(jsPDFCtor, opts) {
    const pdf = new jsPDFCtor({ unit: "pt", format: "a4" });
    const margin = 48;
    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = pdf.internal.pageSize.getHeight();
    let y = margin;

    function ensure(linesNeeded, lineHeight) {
      if (y + linesNeeded * lineHeight > pageH - margin) {
        pdf.addPage();
        y = margin;
      }
    }

    function line(text, size, bold) {
      size = size || 11;
      pdf.setFont("helvetica", bold ? "bold" : "normal");
      pdf.setFontSize(size);
      const arr = pdf.splitTextToSize(String(text == null ? "" : text), pageW - 2 * margin);
      for (const t of arr) {
        ensure(1, size + 5);
        pdf.text(t, margin, y);
        y += size + 5;
      }
    }

    function blank(px) { y += (px || 6); }

    function rule() {
      ensure(1, 8);
      pdf.setLineWidth(0.5);
      pdf.line(margin, y, pageW - margin, y);
      y += 8;
    }

    // Kürzt Text mit "…" so, dass er in maxWidth (pt) passt, mit etwas Abstand.
    function clipCell(text, maxWidth) {
      const safeMax = Math.max(0, maxWidth - 6); // 6pt Sicherheitsabstand
      let s = String(text == null ? "" : text);
      if (pdf.getTextWidth(s) <= safeMax) return s;
      while (s.length > 1 && pdf.getTextWidth(s + "…") > safeMax) {
        s = s.slice(0, -1);
      }
      return s + "…";
    }

    function table(headers, rows, colWidthsRel) {
      // colWidthsRel: Array gleicher Länge wie headers; Summe darf < 1 sein,
      // dann bleibt rechts Leerraum. Werte sind Anteile der nutzbaren Breite.
      const usable = pageW - 2 * margin;
      const widths = (colWidthsRel && colWidthsRel.length === headers.length)
        ? colWidthsRel.map(w => w * usable)
        : headers.map(() => usable / headers.length);

      const drawHeader = () => {
        pdf.setFont("helvetica", "bold");
        pdf.setFontSize(11);
        let x = margin;
        for (let i = 0; i < headers.length; i++) {
          pdf.text(clipCell(headers[i], widths[i]), x, y);
          x += widths[i];
        }
        y += 6;
        pdf.setLineWidth(0.5);
        pdf.line(margin, y, pageW - margin, y);
        y += 14;
        pdf.setFont("helvetica", "normal");
        pdf.setFontSize(11);
      };

      drawHeader();

      for (const row of rows) {
        if (y + 16 > pageH - margin) {
          pdf.addPage();
          y = margin;
          drawHeader();
        }
        let x = margin;
        for (let i = 0; i < headers.length; i++) {
          pdf.text(clipCell(row[i], widths[i]), x, y);
          x += widths[i];
        }
        y += 16;
      }
    }

    function save(filename) {
      pdf.save(filename);
    }

    function setY(newY) { y = newY; }

    return { pdf, margin, pageW, pageH,
             get y() { return y; },
             setY,
             line, blank, rule, table, save };
  }

  // ── QR-Code für Permalink ────────────────────────────────────
  async function generateQRDataURL(text, sizePx) {
    if (!root.QRCode || typeof root.QRCode.toDataURL !== "function") return null;
    try {
      return await root.QRCode.toDataURL(String(text), {
        width: sizePx || 256,
        margin: 1,
        errorCorrectionLevel: "M",
        color: { dark: "#000000", light: "#ffffff" }
      });
    } catch (e) {
      return null;
    }
  }

  // QR oben rechts platzieren, ggf. mehrere Header-Zeilen LINKS daneben.
  // QR-Größe: ca. 80pt x 80pt = ~2.8cm.
  async function drawHeaderWithQR(w) {
    const url = (typeof window !== "undefined" && window.location)
      ? window.location.href
      : "https://noten.brosemedien.de/";
    const dataUrl = await generateQRDataURL(url, 256);
    const qrSize = 80;
    if (dataUrl) {
      try {
        // x: rechtsbündig, y: oben (vor erstem Header-Text)
        w.pdf.addImage(dataUrl, "PNG", w.pageW - w.margin - qrSize, w.margin - 8, qrSize, qrSize);
      } catch (e) { /* ignore — Standard-Header reicht */ }
    }
    // Standard-Header-Text linksbündig
    for (const l of pdfHeaderLines()) w.line(l, 10, false);
    // y ggf. nach unten schieben, falls Header-Text kürzer als QR-Code ist —
    // sonst überlappt der erste Inhalt mit dem QR.
    if (dataUrl) {
      const minY = w.margin + qrSize + 8;
      if (w.y < minY) w.setY(minY);
    }
  }

  // ── Wrapper: PDF mit Loading + Fehler-Handling ──────────────
  function exportPDF(filenamePrefix, builder) {
    setLoading(true, "Erzeuge PDF…");
    setTimeout(async () => {
      try {
        const jsPDF = await ensureJsPDF();
        const w = makePdfWriter(jsPDF);
        await drawHeaderWithQR(w);
        builder(w);
        w.save(makeFilename(filenamePrefix, "pdf"));
      } catch (e) {
        alert("PDF-Erzeugung fehlgeschlagen: " + (e && e.message ? e.message : e));
      } finally {
        setLoading(false);
      }
    }, 30);
  }

  // ── Public API ──────────────────────────────────────────────
  root.NbExport = {
    csvEscape, buildCSV, exportCSV, exportPDF, makePdfWriter
  };
})(window);
