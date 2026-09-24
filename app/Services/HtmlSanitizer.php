<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'richtext' => ['p', 'br', 'strong', 'em', 'b', 'i', 'a', 'ul', 'ol', 'li'],
        'text' => ['strong', 'em', 'b', 'i'],
    ];

    public static function clean(string $html, string $type = 'richtext'): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $allowedTags = self::ALLOWED_TAGS[$type] ?? self::ALLOWED_TAGS['richtext'];

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"?><div id="tandlab-root">' . $html . '</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('tandlab-root');
        if ($root === null) {
            return '';
        }

        self::cleanChildren($root, $allowedTags);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private static function cleanChildren(DOMNode $node, array $allowedTags): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            if (!$child instanceof DOMElement) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);

            if (!in_array($tag, $allowedTags, true)) {
                self::cleanChildren($child, $allowedTags);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                if ($tag === 'a' && $attr->name === 'href') {
                    continue;
                }
                $child->removeAttribute($attr->name);
            }

            if ($tag === 'a') {
                $href = trim($child->getAttribute('href'));
                if ($href === '' || !preg_match('#^(https?://|mailto:|tel:)#i', $href)) {
                    $child->removeAttribute('href');
                    $href = '';
                }
                if ($href !== '') {
                    $child->setAttribute('rel', 'noopener');
                    $child->setAttribute('target', '_blank');
                }
            }

            self::cleanChildren($child, $allowedTags);
        }
    }
}
