<?php

class msGiftCardsPdf
{
    public static function build(array $certificate)
    {
        $title = self::sanitizeText('GIFT CERTIFICATE');
        $codeLabel = self::sanitizeText('Code');
        $expiryLabel = self::sanitizeText('Valid until');
        $nominalLabel = self::sanitizeText('Nominal');

        $code = self::sanitizeText(isset($certificate['code']) ? $certificate['code'] : '');
        $expiresOn = self::sanitizeText(isset($certificate['expireson_formatted']) ? $certificate['expireson_formatted'] : '');
        if ($expiresOn === '') {
            $expiresOn = self::sanitizeText('No expiration');
        }
        $nominal = self::sanitizeText(isset($certificate['nominal_formatted']) ? $certificate['nominal_formatted'] : '');
        if ($nominal === '') {
            $nominal = self::sanitizeText('0');
        }

        $content = implode("\n", [
            'q',
            '0.97 0.95 0.90 rg',
            '24 24 547 794 re f',
            '0.79 0.64 0.25 RG',
            '4 w',
            '36 36 523 770 re S',
            '0.26 0.20 0.10 rg',
            'BT /F1 24 Tf 72 760 Td (' . self::escapeText($title) . ') Tj ET',
            '0.40 0.31 0.14 rg',
            'BT /F1 11 Tf 72 700 Td (' . self::escapeText($codeLabel) . ') Tj ET',
            'BT /F1 22 Tf 72 672 Td (' . self::escapeText($code) . ') Tj ET',
            'BT /F1 11 Tf 72 620 Td (' . self::escapeText($expiryLabel) . ') Tj ET',
            'BT /F1 18 Tf 72 594 Td (' . self::escapeText($expiresOn) . ') Tj ET',
            'BT /F1 11 Tf 72 540 Td (' . self::escapeText($nominalLabel) . ') Tj ET',
            'BT /F1 28 Tf 72 506 Td (' . self::escapeText($nominal) . ') Tj ET',
            'Q',
        ]);

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>';
        $objects[] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        return self::buildDocument($objects);
    }

    protected static function buildDocument(array $objects)
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= 'xref' . "\n";
        $pdf .= '0 ' . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\n";
        $pdf .= 'startxref' . "\n";
        $pdf .= $xrefOffset . "\n";
        $pdf .= '%%EOF';

        return $pdf;
    }

    protected static function escapeText($text)
    {
        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', '', ' '],
            (string)$text
        );
    }

    protected static function sanitizeText($text)
    {
        $text = trim((string)$text);
        if ($text === '') {
            return '';
        }

        $replacements = [
            '₽' => 'RUB',
            '€' => 'EUR',
            '£' => 'GBP',
            '№' => 'No.',
        ];
        $text = strtr($text, $replacements);
        $text = preg_replace('/[^\x20-\x7E]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim((string)$text);
    }
}
