<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Rector;

use Justpilot\Billomat\BillomatClient;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;

/**
 * Hängt die flachen 3.x-Sub-Ressourcen an ihren Parent.
 *
 * - `$billomat->invoiceComments` wird `$billomat->invoices->comments`,
 *   `$billomat->dunningLevels` wird `$billomat->reminderTexts`. Nur für Ausdrücke, die PHPStan
 *   als `BillomatClient` erkennt.
 * - `new InvoiceCommentsApi($http)` wird `new InvoicesApi($http)->comments`, weil die generischen
 *   4.0-Klassen einen anderen Konstruktor haben.
 */
final class NestedSubResourceRector extends AbstractRector
{
    #[Override]
    public function getNodeTypes(): array
    {
        return [PropertyFetch::class, New_::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        return $node instanceof New_ ? $this->refactorNew($node) : $this->refactorPropertyFetch($node);
    }

    private function refactorPropertyFetch(Node $node): ?PropertyFetch
    {
        if (!$node instanceof PropertyFetch) {
            return null;
        }

        $name = $this->getName($node->name);
        $target = null === $name ? null : SubResourceMap::target($name);
        if (null === $target || !$this->isObjectType($node->var, new ObjectType(BillomatClient::class))) {
            return null;
        }

        [$parent, $child] = $target;
        $parentFetch = new PropertyFetch($node->var, $parent);

        return null === $child ? $parentFetch : new PropertyFetch($parentFetch, $child);
    }

    private function refactorNew(New_ $node): ?PropertyFetch
    {
        // `new $class()` und anonyme Klassen haben keinen Namen und bleiben unverändert.
        $construction = SubResourceMap::construction($this->getName($node->class) ?? '');
        if (null === $construction) {
            return null;
        }

        [$parentClass, $child] = $construction;

        return new PropertyFetch(new New_(new FullyQualified($parentClass), $node->args), $child);
    }
}
