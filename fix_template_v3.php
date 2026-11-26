<?php
/**
 * Fix XML fragmentation in Word template - v3
 * Writes to a NEW file to avoid lock issues, then renames.
 */

$backupDir = 'storage/app/templates/';
// Use the most recent backup since main file may be locked/corrupted
$backups = glob($backupDir . 'format_surat_izin_cuti_backup_*.docx');
rsort($backups); // Most recent first

// Try to find a valid source
$sources = array_merge([$backupDir . 'format_surat_izin_cuti.docx'], $backups);
$sourceFile = null;
$sourceXml = null;

foreach ($sources as $src) {
    if (!file_exists($src)) continue;
    $z = new ZipArchive();
    if ($z->open($src, ZipArchive::RDONLY) === true) {
        $xml = $z->getFromName('word/document.xml');
        $z->close();
        if ($xml !== false) {
            $sourceFile = $src;
            $sourceXml = $xml;
            echo "Using source: $src\n";
            break;
        }
    }
}

if (!$sourceXml) die("No valid template found\n");

// Check how many fragmented vars we have initially
preg_match_all('/\$\{[^}]*\}/', $sourceXml, $initMatches);
$initFragmented = array_filter($initMatches[0], fn($v) => preg_match('/</', $v));
echo "Initial: " . count($initMatches[0]) . " vars, " . count($initFragmented) . " fragmented\n";

$fixedXml = $sourceXml;

// Variables to fix - sorted longest first  
$variables = [
    'c_diluar_tanggungan', 'c_alasan_penting', 'tanggal_selesai',
    'jabatan_atasan', 'atasan_tangguh', 'catatan_atasan', 'tanggal_mulai',
    'tanggal_surat', 'ketua_tangguh', 'catatan_ketua', 'atasan_setuju',
    'c_melahirkan', 'nama_pegawai', 'nomor_surat', 'sisa_cuti_n2',
    'sisa_cuti_n1', 'alasan_cuti', 'nama_atasan', 'atasan_ubah',
    'atasan_tolak', 'ketua_setuju', 'satuan_cuti', 'sisa_cuti_n',
    'nama_ketua', 'ketua_ubah', 'ketua_tolak', 'no_telepon',
    'alamat_cuti', 'masa_kerja', 'lama_cuti', 'c_tahunan', 'tahun_n2',
    'tahun_n1', 'c_besar', 'c_sakit', 'jabatan', 'tahun_n', 'nip',
];

foreach ($variables as $var) {
    $chars = str_split($var);
    // Allow arbitrary XML tags (and whitespace) between characters
    $xmlNoise = '(?:<[^>]*>|\s)*';
    
    $pattern = '/\$\{' . $xmlNoise;
    for ($i = 0; $i < count($chars); $i++) {
        $pattern .= preg_quote($chars[$i], '/');
        if ($i < count($chars) - 1) {
            $pattern .= $xmlNoise;
        }
    }
    $pattern .= $xmlNoise . '\}/s';
    
    $replacement = '${' . $var . '}';
    
    // Count matches before replacing
    $count = preg_match_all($pattern, $fixedXml);
    if ($count > 0) {
        $fixedXml = preg_replace($pattern, $replacement, $fixedXml);
    }
}

// Now handle the special jabatan_atasan + nama_atasan merge issue
// The XML shows: ${jabatan_atasan}, [some XML for newline/paragraph], ${nama_atasan}
// but it's being parsed as one giant ${...} - need to check the raw XML

// Let's also look for the specific pattern where jabatan_atasan and nama_atasan 
// are stuck together in one "variable"
$fixedXml = preg_replace(
    '/\$\{jabatan_atasan[^}]*\$\{nama_atasan\}/',
    '${jabatan_atasan},</w:t></w:r></w:p><w:p><w:r><w:t>${nama_atasan}',
    $fixedXml
);

// Actually, let me look at what's happening more carefully
// Extract the problematic area
if (preg_match('/\$\{jabatan_atasan[^}]{0,500}/', $fixedXml, $problemMatch)) {
    echo "\nProblematic area found (first 200 chars):\n";
    echo substr($problemMatch[0], 0, 200) . "\n";
}

// Verify final state
preg_match_all('/\$\{([^}]*)\}/', $fixedXml, $finalMatches);
echo "\nFinal variables (" . count($finalMatches[0]) . "):\n";
foreach ($finalMatches[0] as $v) {
    $hasXml = preg_match('/</', $v);
    echo ($hasXml ? "  [FRAG] " : "  [OK]   ") . strip_tags($v) . "\n";
}

// Write to new file
$outputPath = $backupDir . 'format_surat_izin_cuti_fixed.docx';
copy($sourceFile, $outputPath);

$zipOut = new ZipArchive();
if ($zipOut->open($outputPath) === true) {
    $zipOut->deleteName('word/document.xml');
    $zipOut->addFromString('word/document.xml', $fixedXml);
    if ($zipOut->close()) {
        echo "\nFixed template saved to: $outputPath\n";
        // Now rename: move original out, move fixed in
        $origBackup = $backupDir . 'format_surat_izin_cuti_original.docx';
        @rename($backupDir . 'format_surat_izin_cuti.docx', $origBackup);
        if (rename($outputPath, $backupDir . 'format_surat_izin_cuti.docx')) {
            echo "Renamed fixed template to main template path.\n";
        } else {
            echo "WARN: Could not rename. Fixed file at: $outputPath\n";
        }
    } else {
        echo "\nFailed to close zip!\n";
    }
} else {
    echo "\nFailed to open output zip!\n";
}
