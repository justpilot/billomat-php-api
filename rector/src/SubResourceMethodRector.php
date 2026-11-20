<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Rector;

use Justpilot\Billomat\Api\CommentFilter;
use Justpilot\Billomat\BillomatClient;
use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use Rector\Rector\AbstractRector;

/**
 * Passt Aufrufe auf 3.x-Sub-Ressourcen an die Signaturen der 4.0-Klassen an.
 *
 * - `listByInvoice($id)`, `listByOffer($id)`, … werden `list($id)`.
 * - Benannte Parent-ID-Argumente (`invoiceId: $id`) heißen bei Kommentaren, Tags, Positionen,
 *   Mahnungspositionen und E-Mail-Empfängern `parentId:`, `query:` heißt `filters:`.
 * - Die ActionKeys von `listByInvoice($id, $actionKeys)` auf Kommentaren wandern in einen `CommentFilter`:
 *   `list($id, new CommentFilter(actionKeys: $actionKeys))`; ein `null` fällt weg.
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
        $args = $node->getArgs();

        if (SubResourceMap::hasGenericParentId($newClass)) {
            $args = array_map($this->renameParentIdArgument(...), $args);
        }

        if (SubResourceMap::hasActionKeys($newClass) && str_starts_with($method, 'listBy')) {
            $args = $this->wrapActionKeys($args);
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
        $new = match (true) {
            null === $name, 'parentId' === $name => null,
            'query' === $name => 'filters',
            str_ends_with($name, 'Id') => 'parentId',
            default => null,
        };

        return null === $new ? $arg : new Arg($arg->value, $arg->byRef, $arg->unpack, [], new Identifier($new));
    }

    /**
     * Ersetzt das ActionKey-Argument (zweites oder `actionKeys:`) durch `new CommentFilter(actionKeys: …)`.
     *
     * @param array<Arg> $args
     *
     * @return array<Arg>
     */
    private function wrapActionKeys(array $args): array
    {
        if (array_any($args, static fn (Arg $arg): bool => $arg->unpack)) {
            return $args;
        }

        $wrapped = [];
        foreach (array_values($args) as $position => $arg) {
            $isActionKeys = null === $arg->name ? 1 === $position : 'actionKeys' === $arg->name->toString();
            if (!$isActionKeys) {
                $wrapped[] = $arg;

                continue;
            }

            if ($arg->value instanceof ConstFetch && $this->isName($arg->value, 'null')) {
                continue;
            }

            $filter = new New_(new FullyQualified(CommentFilter::class), [
                new Arg($this->nonNullActionKeys($arg->value), name: new Identifier('actionKeys')),
            ]);
            $wrapped[] = new Arg($filter, name: null === $arg->name ? null : new Identifier('filters'));
        }

        return $wrapped;
    }

    /**
     * 3.x nahm `?array`, der Filter eine Liste: ein möglicherweise null-wertiger Ausdruck bekommt `?? []`.
     */
    private function nonNullActionKeys(Expr $value): Expr
    {
        if ($value instanceof Array_ || !TypeCombinator::containsNull($this->getType($value))) {
            return $value;
        }

        return new Coalesce($value, new Array_());
    }

    /**
     * Benennt die positionalen Argumente nach den 3.x-Parametern; Aufrufe mit `...$args` bleiben unverändert.
     *
     * @param array<Arg> $args
     *
     * @return array<Arg>
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
