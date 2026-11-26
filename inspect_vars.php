<?php
$z = new ZipArchive();
$z->open('storage/app/templates/format_surat_izin_cuti.docx');
$xml = $z->getFromName('word/document.xml');
$z->close();
preg_match_all('/\$\{[^}]+\}/', $xml, $m);
echo 'Variables found: ' . count($m[0]) . PHP_EOL;
foreach(array_unique($m[0]) as $v) {
    echo $v . PHP_EOL;
}
