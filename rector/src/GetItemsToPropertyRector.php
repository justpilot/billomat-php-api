<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Rector;

use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\RecurringCreateOptions;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;

/**
 * `$options->getItems()` auf den Dokument-`*CreateOptions` wird `$options->items`.
 */
final class GetItemsToPropertyRector extends AbstractRector
{
    private const array OPTIONS = [
        InvoiceCreateOptions::class,
        OfferCreateOptions::class,
        ConfirmationCreateOptions::class,
        CreditNoteCreateOptions::class,
        DeliveryNoteCreateOptions::class,
        RecurringCreateOptions::class,
    ];

    #[Override]
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof MethodCall || !$this->isName($node->name, 'getItems') || [] !== $node->args) {
            return null;
        }

        foreach (self::OPTIONS as $class) {
            if ($this->isObjectType($node->var, new ObjectType($class))) {
                return new PropertyFetch($node->var, 'items');
            }
        }

        return null;
    }
}
