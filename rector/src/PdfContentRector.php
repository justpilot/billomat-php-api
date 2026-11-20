<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Rector;

use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\DeliveryNotesApi;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\LettersApi;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Api\RemindersApi;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;

/**
 * `pdf($id, $type, rawPdf: true)` auf den Dokument-APIs wird `pdfContent($id, $type)`.
 *
 * Das Flag `rawPdf` (benannt oder drittes Argument) fällt weg: `true` wird `pdfContent()`, `false` bleibt
 * `pdf()`. Ein Flag, dessen Wert erst zur Laufzeit feststeht (`rawPdf: $raw`), bleibt unverändert und
 * muss von Hand aufgeteilt werden.
 */
final class PdfContentRector extends AbstractRector
{
    private const array APIS = [
        InvoicesApi::class,
        OffersApi::class,
        ConfirmationsApi::class,
        CreditNotesApi::class,
        DeliveryNotesApi::class,
        LettersApi::class,
        RemindersApi::class,
    ];

    /** Position des Flags in der 3.x-Signatur `pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false)`. */
    private const int RAW_PDF_POSITION = 2;

    #[Override]
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof MethodCall || $node->isFirstClassCallable() || !$this->isName($node->name, 'pdf') || !$this->isDocumentApi($node)) {
            return null;
        }

        // `...$args` an der Flag-Position ist keine Konstante und fällt unten heraus.
        $args = $node->getArgs();

        $flagPosition = null;
        foreach ($args as $position => $arg) {
            if (null === $arg->name ? self::RAW_PDF_POSITION === $position : 'rawPdf' === $arg->name->toString()) {
                $flagPosition = $position;
            }
        }

        if (null === $flagPosition || !$args[$flagPosition]->value instanceof ConstFetch) {
            return null;
        }

        $raw = $this->getName($args[$flagPosition]->value);
        if (null === $raw || !\in_array(strtolower($raw), ['true', 'false'], true)) {
            return null;
        }

        unset($args[$flagPosition]);

        return new MethodCall($node->var, new Identifier('true' === strtolower($raw) ? 'pdfContent' : 'pdf'), array_values($args));
    }

    private function isDocumentApi(MethodCall $node): bool
    {
        return array_any(self::APIS, fn (string $class): bool => $this->isObjectType($node->var, new ObjectType($class)));
    }
}
