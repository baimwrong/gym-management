<?php

namespace App\Services;

class QrCodeService
{
    /**
     * Generate an offline pure SVG QR-like visual matrix / Aztec code pattern for passes.
     * Guaranteed to work 100% offline without external internet or third-party binaries.
     */
    public static function generateSvg(string $data, int $size = 180): string
    {
        // Produce deterministic pseudo-random 2D matrix based on data hash for offline visual pass
        $hash = hash('sha256', $data);
        $gridSize = 21; // Standard Version 1 QR dimension (21x21 modules)
        $cellSize = $size / $gridSize;

        $matrix = array_fill(0, $gridSize, array_fill(0, $gridSize, false));

        // Draw 3 standard Finder Patterns (top-left, top-right, bottom-left)
        self::drawFinderPattern($matrix, 0, 0);
        self::drawFinderPattern($matrix, 0, $gridSize - 7);
        self::drawFinderPattern($matrix, $gridSize - 7, 0);

        // Draw timing patterns
        for ($i = 8; $i < $gridSize - 8; $i++) {
            $matrix[6][$i] = ($i % 2 === 0);
            $matrix[$i][6] = ($i % 2 === 0);
        }

        // Fill remaining data bits from hash
        $hashBytes = hex2bin($hash);
        $byteIndex = 0;
        $bitIndex = 0;

        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                // Skip finder patterns & timing patterns
                if (self::isReserved($r, $c, $gridSize)) {
                    continue;
                }

                $byte = ord($hashBytes[$byteIndex % strlen($hashBytes)]);
                $bit = ($byte >> ($bitIndex % 8)) & 1;
                $matrix[$r][$c] = ($bit === 1);

                $bitIndex++;
                if ($bitIndex % 8 === 0) {
                    $byteIndex++;
                }
            }
        }

        // Render SVG
        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 {$size} {$size}\" shape-rendering=\"crispEdges\">\n";
        $svg .= "  <rect width=\"{$size}\" height=\"{$size}\" fill=\"#ffffff\" rx=\"8\" />\n";

        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                if ($matrix[$r][$c]) {
                    $x = round($c * $cellSize, 2);
                    $y = round($r * $cellSize, 2);
                    $w = round($cellSize, 2);
                    $h = round($cellSize, 2);
                    $svg .= "  <rect x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$h}\" fill=\"#0f172a\" />\n";
                }
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    private static function drawFinderPattern(array &$matrix, int $startR, int $startC): void
    {
        for ($r = 0; $r < 7; $r++) {
            for ($c = 0; $c < 7; $c++) {
                if ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4)) {
                    $matrix[$startR + $r][$startC + $c] = true;
                } else {
                    $matrix[$startR + $r][$startC + $c] = false;
                }
            }
        }
    }

    private static function isReserved(int $r, int $c, int $size): bool
    {
        // Top-left finder
        if ($r <= 8 && $c <= 8) return true;
        // Top-right finder
        if ($r <= 8 && $c >= $size - 9) return true;
        // Bottom-left finder
        if ($r >= $size - 9 && $c <= 8) return true;
        // Timing patterns
        if ($r === 6 || $c === 6) return true;

        return false;
    }

    /**
     * Generate an offline 1D Barcode SVG (Code 128 style).
     */
    public static function generateBarcodeSvg(string $code, int $width = 240, int $height = 60): string
    {
        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$width}\" height=\"{$height}\" viewBox=\"0 0 {$width} {$height}\">\n";
        $svg .= "  <rect width=\"{$width}\" height=\"{$height}\" fill=\"#ffffff\" rx=\"4\" />\n";

        $hash = md5($code);
        $bars = 40;
        $barWidth = ($width - 30) / $bars;

        $x = 15;
        for ($i = 0; $i < $bars; $i++) {
            $char = $hash[$i % strlen($hash)];
            $val = hexdec($char);
            $thickness = ($val % 3 === 0) ? $barWidth * 0.8 : ($val % 2 === 0 ? $barWidth * 0.5 : $barWidth * 0.3);
            
            if ($val % 5 !== 0) { // Some spaces
                $svg .= "  <rect x=\"{$x}\" y=\"10\" width=\"{$thickness}\" height=\"".($height - 25)."\" fill=\"#1e293b\" />\n";
            }
            $x += $barWidth;
        }

        $svg .= "  <text x=\"".($width / 2)."\" y=\"".($height - 4)."\" text-anchor=\"middle\" font-family=\"monospace\" font-size=\"10\" fill=\"#334155\">{$code}</text>\n";
        $svg .= "</svg>";

        return $svg;
    }
}
