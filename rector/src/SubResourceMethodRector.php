<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Rector;

use Justpilot\Billomat\BillomatClient;
use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;

/**
 * Passt Aufrufe auf 3.x-Sub-Ressourcen an die Signaturen der 4.0-Klassen an.
 *
 * - `listByInvoice($id)`, `listByOffer($id)`, … werden `list($id)`.
 * - Benannte Parent-ID-Argumente (`invoiceId: $id`) heißen bei Kommentaren, Tags und Positionen `parentId:`.
 * - `list($filters)`, `listPage($filters)` und `iterateAll($filters, $pageSize)` auf Zahlungen und
 *   Property-Werten bekommen benannte Argumente, weil die Parent-ID jetzt der erste Parameter ist.
 *
 * Erkannt werden Aufrufe auf einer flachen `BillomatClient`-Property (`$billomat->invoicePayments->list()`)
 * und auf Ausdrücken, deren Typ eine 3.x-Klasse ist (`InvoicePaymentsApi $payments`).
 */
final class SubResourceMethodRector extends AbstractRector
{
    private const array OPTIONAL_PARENT_METHODS = ['list', 'listPage', 'iterateAll'];

    /**
     * Namen der bisherigen positionalen Parameter von `list()`, `listPage()` und `iterateAll()`.
     */
    private const array OPTIONAL_PARENT_PARAMETERS = ['filters', 'pageSize'];

    #[Override]
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof MethodCall || $node->isFirstClassCallable()) {
            return null;
        }

        $method = $this->getName($node->name);
        $newClass = $this->resolveNewClass($node);
        if (null === $method || null === $newClass) {
            return null;
        }

        // Neue Knoten statt Änderungen am Original: so bleibt der Aufruf unverändert, wenn die Regel null liefert.
        $newMethod = SubResourceMap::hasListByParent($newClass) && str_starts_with($method, 'listBy') ? 'list' : $method;
        $args = array_values($node->getArgs());

        if (SubResourceMap::hasGenericParentId($newClass)) {
            $args = array_map($this->renameParentIdArgument(...), $args);
        }

        if (SubResourceMap::hasOptionalParent($newClass) && \in_array($method, self::OPTIONAL_PARENT_METHODS, true)) {
            $args = $this->nameFilterArguments($args);
        }

        if ($newMethod === $method && $args === $node->getArgs()) {
            return null;
        }

        return new MethodCall($node->var, new Identifier($newMethod), $args);
    }

    private function resolveNewClass(MethodCall $node): ?string
    {
        $var = $node->var;
        if ($var instanceof PropertyFetch && $this->isObjectType($var->var, new ObjectType(BillomatClient::class))) {
            $class = SubResourceMap::newClassFor($this->getName($var->name), null);
            if (null !== $class) {
                return $class;
            }
        }

        foreach ($this->getType($var)->getObjectClassNames() as $className) {
            $class = SubResourceMap::newClassFor(null, $className);
            if (null !== $class) {
                return $class;
            }
        }

        return null;
    }

    private function renameParentIdArgument(Arg $arg): Arg
    {
        $name = $arg->name?->toString();
        if (null === $name || 'parentId' === $name || !str_ends_with($name, 'Id')) {
            return $arg;
        }

        return new Arg($arg->value, $arg->byRef, $arg->unpack, [], new Identifier('parentId'));
    }

    /**
     * Benennt die positionalen Argumente nach den 3.x-Parametern; Aufrufe mit `...$args` bleiben unverändert.
     *
     * @param list<Arg> $args
     *
     * @return list<Arg>
     */
    private function nameFilterArguments(array $args): array
    {
        if (array_any($args, static fn (Arg $arg): bool => $arg->unpack)) {
            return $args;
        }

        $named = [];
        foreach ($args as $position => $arg) {
            $parameter = self::OPTIONAL_PARENT_PARAMETERS[$position] ?? null;
            $named[] = null === $arg->name && null !== $parameter
                ? new Arg($arg->value, $arg->byRef, false, [], new Identifier($parameter))
                : $arg;
        }

        return $named;
    }
}
