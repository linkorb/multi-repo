<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Helper;

trait IndentionFormatAwareTrait
{
    private function fixIndent(string $content, int $formattedIndent, ?string $indentStyle, int $indentSize): string
    {
        $getIndentStyle = function (?string $configValue): string {
            switch ($configValue ?? 'space') {
                case 'tab':
                    return "\t";
                case 'space':
                default:
                    return ' ';
            }
        };

        return preg_replace(
            sprintf('/(^|\G) {%d}/m', $formattedIndent),
            str_repeat($getIndentStyle($indentStyle), $indentSize),
            $content
        );
    }

    private function fixLineBreaks(string $content, ?string $lineBreakOption): string
    {
        $getLineBreaks = function (?string $configValue): string {
            switch ($configValue ?? 'LF') {
                case 'CR':
                    return "\r";
                case 'CRLF':
                    return "\r\n";
                case 'LF':
                default:
                    return "\n";
            }
        };

        return preg_replace(
            sprintf('/%s/m', PHP_EOL),
            $getLineBreaks($lineBreakOption),
            $content
        );
    }
}
