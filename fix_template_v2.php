<?php
/**
 * Fix XML fragmentation in Word template - v2
 * Run with BypassSandbox to avoid permission issues
 */

$templatePath = 'storage/app/templates/format_surat_izin_cuti.docx';
$backupPath = 'storage/app/templates/format_surat_izin_cuti_backup_' . date('Ymd_His') . '.docx';

// Use backup from previous run if main file is corrupted, or use main
if (!file_exists($templatePath)) {
    die("Template not found\n");
}

// Backup first
copy($templatePath, $backupPath);
echo "Backup: $backupPath\n";

$zip = new ZipArchive();
if ($zip->open($templatePath) !== true) {
    die("Cannot open $templatePath\n");
}

$xml = $zip->getFromName('word/document.xml');
$zip->close();

// Variables to fix
$variables = [
    'tanggal_surat', 'nomor_surat', 'nama_pegawai', 'nip', 'jabatan', 'masa_kerja',
    'c_tahunan', 'c_besar', 'c_sakit', 'c_melahirkan', 'c_alasan_penting',
    'c_diluar_tanggungan', 'alasan_cuti', 'lama_cuti', 'satuan_cuti',
    'tanggal_mulai', 'tanggal_selesai',
    'tahun_n2', 'tahun_n1', 'tahun_n',
    'sisa_cuti_n2', 'sisa_cuti_n1', 'sisa_cuti_n',
    'alamat_cuti', 'no_telepon',
    'atasan_setuju', 'atasan_ubah', 'atasan_tangguh', 'atasan_tolak',
    'catatan_atasan', 'jabatan_atasan', 'nama_atasan',
    'ketua_setuju', 'ketua_ubah', 'ketua_tangguh', 'ketua_tolak',
    'catatan_ketua', 'nama_ketua',
];

// Sort by length descending to avoid partial matches (e.g. 'nama_atasan' before 'nama_')
usort($variables, function($a, $b) { return strlen($b) - strlen($a); });

$fixedXml = $xml;

foreach ($variables as $var) {
    // Build regex: match ${ then each character of var name with optional XML noise between them, then }
    $chars = str_split($var);
    $xmlNoise = '(?:<[^>]*>)*';
    
    $regex = '/\$\{' . $xmlNoise;
    for ($i = 0; $i < count($chars); $i++) {
        $regex .= preg_quote($chars[$i], '/');
        $regex .= $xmlNoise;
    }
    $regex .= '\}/s';
    
    $clean = '${' . $var . '}';
    
    $fixedXml = preg_replace($regex, $clean, $fixedXml);
}

// Verify
preg_match_all('/\$\{([^}]*)\}/', $fixedXml, $afterMatches);
$allVars = $afterMatches[0];
$fragmentedVars = array_filter($allVars, function($v) {
    return preg_match('/</', $v);
});

echo "Total vars: " . count($allVars) . "\n";
echo "Clean: " . (count($allVars) - count($fragmentedVars)) . "\n";
echo "Fragmented: " . count($fragmentedVars) . "\n";

foreach ($allVars as $v) {
    $name = preg_replace('/<[^>]*>/', '', $v);
    echo "  $name\n";
}

if (count($fragmentedVars) > 0) {
    echo "\nStill fragmented:\n";
    foreach ($fragmentedVars as $fv) {
        $name = strip_tags($fv);
        echo "  - $name\n";
    }
}

// Save using a fresh ZipArchive open
$zip2 = new ZipArchive();
if ($zip2->open($templatePath, ZipArchive::CREATE) !== true) {
    die("Cannot re-open for writing\n");
}
// Delete and re-add document.xml
$zip2->deleteName('word/document.xml');
$zip2->addFromString('word/document.xml', $fixedXml);
$result = $zip2->close();
if ($result) {
    echo "\nTemplate saved successfully!\n";
} else {
    echo "\nFailed to save template. Trying alternative method...\n";
    
    // Alternative: write to temp file, then copy over
    $tmpFile = tempnam(sys_get_temp_dir(), 'docx_');
    copy($backupPath, $tmpFile);
    
    $zip3 = new ZipArchive();
    $zip3->open($tmpFile);
    $zip3->deleteName('word/document.xml');
    $zip3->addFromString('word/document.xml', $fixedXml);
    $zip3->close();
    
    copy($tmpFile, $templatePath);
    unlink($tmpFile);
    echo "Template saved via temp file!\n";
}
