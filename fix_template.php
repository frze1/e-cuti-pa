<?php
/**
 * Fix XML fragmentation in Word template.
 * PHPWord's TemplateProcessor cannot find ${variable} if Word splits it
 * across multiple XML runs. This script stitches them back together.
 */

$templatePath = 'storage/app/templates/format_surat_izin_cuti.docx';
$backupPath = 'storage/app/templates/format_surat_izin_cuti_backup_' . date('Ymd_His') . '.docx';

// Backup first
copy($templatePath, $backupPath);
echo "Backup created: $backupPath\n";

$zip = new ZipArchive();
if ($zip->open($templatePath) !== true) {
    die("Cannot open $templatePath\n");
}

$xml = $zip->getFromName('word/document.xml');

// Regex to find fragmented ${...} placeholders and stitch them
// Match ${ followed by any XML tags and text nodes until we find }
$pattern = '/\$\{((?:(?!\$\{)[^}])*)\}/s';

// First, strip XML tags between ${ and } to find the variable name
// We need a more careful approach: find ${ potentially followed by XML noise, then the var name, then }
$fixedXml = preg_replace_callback(
    '/\$\{((?:(?!\$\{).)*?)\}/s',
    function ($match) {
        $inner = $match[1];
        // Strip all XML tags to get the pure variable name
        $varName = strip_tags($inner);
        // Also strip any remaining XML-like content
        $varName = preg_replace('/<[^>]+>/', '', $varName);
        $varName = trim($varName);
        
        if (empty($varName)) {
            return $match[0]; // Return unchanged if no variable name found
        }
        
        return '${' . $varName . '}';
    },
    $xml
);

// Now we need to handle cases where ${ and } are in different runs entirely
// More aggressive approach: find w:t elements containing ${ without matching }
// and stitch forward until we find the }

// Step 1: Extract all w:t content, track positions
// Step 2: Find broken ${...} patterns across w:t boundaries
// Step 3: Reconstruct

// Actually, let's use a simpler approach:
// Find all <w:r ...>...</w:r> blocks and concatenate their text to find variables

// Most robust: work at the text level
// Extract text from XML, find variable positions, then reconstruct

// Alternative simple approach: just replace known fragmented patterns
// The output showed specific fragmentations. Let's handle them.

// Generic fix: for each known variable, find the fragmented version and replace
$variables = [
    'nomor_surat', 'nama_pegawai', 'nip', 'jabatan', 'masa_kerja',
    'c_tahunan', 'c_besar', 'c_sakit', 'c_melahirkan', 'c_alasan_penting',
    'c_diluar_tanggungan', 'alasan_cuti', 'lama_cuti', 'satuan_cuti',
    'tanggal_mulai', 'tanggal_selesai', 'tanggal_surat',
    'tahun_n2', 'tahun_n1', 'tahun_n',
    'sisa_cuti_n2', 'sisa_cuti_n1', 'sisa_cuti_n',
    'alamat_cuti', 'no_telepon',
    'atasan_setuju', 'atasan_ubah', 'atasan_tangguh', 'atasan_tolak',
    'catatan_atasan', 'jabatan_atasan', 'nama_atasan',
    'ketua_setuju', 'ketua_ubah', 'ketua_tangguh', 'ketua_tolak',
    'catatan_ketua', 'nama_ketua',
];

$fixCount = 0;

foreach ($variables as $var) {
    // Build a regex that matches ${var} with possible XML tags interspersed
    // between any characters of the variable name
    $chars = str_split($var);
    $regexParts = [preg_quote('${', '/')];
    
    // Allow XML tags between ${ and variable name, and between characters
    $xmlNoise = '(?:<[^>]*>)*';
    
    $regexParts[] = $xmlNoise;
    
    for ($i = 0; $i < count($chars); $i++) {
        $regexParts[] = preg_quote($chars[$i], '/');
        if ($i < count($chars) - 1) {
            $regexParts[] = $xmlNoise;
        }
    }
    
    $regexParts[] = $xmlNoise;
    $regexParts[] = preg_quote('}', '/');
    
    $regex = '/' . implode('', $regexParts) . '/s';
    
    $clean = '${' . $var . '}';
    
    // Only replace if the fragmented version exists (not the clean one)
    $newXml = preg_replace($regex, $clean, $fixedXml, -1, $count);
    if ($count > 0 && $newXml !== null) {
        // Check if we're not just matching the already-clean version
        $fixedXml = $newXml;
        $fixCount += $count;
    }
}

// Verify results
preg_match_all('/\$\{[^}]+\}/', $fixedXml, $afterMatches);
$cleanVars = array_filter($afterMatches[0], function($v) {
    return !preg_match('/</', $v); // No XML tags inside
});
$fragmentedVars = array_filter($afterMatches[0], function($v) {
    return preg_match('/</', $v); // Still has XML tags
});

echo "Total variables after fix: " . count($afterMatches[0]) . "\n";
echo "Clean variables: " . count($cleanVars) . "\n";
echo "Still fragmented: " . count($fragmentedVars) . "\n";

if (count($fragmentedVars) > 0) {
    echo "\nStill fragmented variables:\n";
    foreach ($fragmentedVars as $fv) {
        // Extract var name
        $name = strip_tags($fv);
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
        echo "  - $name\n";
    }
}

echo "\nClean variables found:\n";
foreach ($cleanVars as $cv) {
    echo "  $cv\n";
}

// Save the fixed XML back
$zip->deleteName('word/document.xml');
$zip->addFromString('word/document.xml', $fixedXml);
$zip->close();

echo "\nTemplate fixed and saved!\n";
