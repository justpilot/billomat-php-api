<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Rector;

use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;

/**
 * Zieht die Eltern-ID aus den Create-Options von Zahlungen, Kommentaren, Schlagworten, Attributwerten und
 * E-Mail-Empfängern in das erste Argument von `create()`.
 *
 * `create(new InvoicePaymentCreateOptions(invoiceId: $id, amount: …))` wird
 * `create($id, new InvoicePaymentCreateOptions(amount: …))`. In 3.x war die Eltern-ID der erste
 * Konstruktor-Parameter; erkannt wird sie benannt (`invoiceId:`) oder als erstes positionales Argument.
 * Options, die vorher in einer Variablen gebaut werden, bleiben unverändert.
 */
final class ParentIdToCreateArgumentRector extends AbstractRector
{
    private const string OPTIONS_PATTERN = '/^Justpilot\\\\Billomat\\\\Api\\\\(\w+?)(?:Payment|Comment|Tag|PropertyValue)CreateOptions$/';

    #[Override]
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof MethodCall || $node->isFirstClassCallable() || !$this->isName($node->name, 'create')) {
            return null;
        }

        $args = $node->getArgs();
        if (1 !== \count($args) || !\in_array($args[0]->name?->toString(), [null, 'options'], true)) {
            return null;
        }

        $new = $args[0]->value;
        if (!$new instanceof New_ || !$new->class instanceof Name || $new->isFirstClassCallable()) {
            return null;
        }

        $class = $this->getName($new->class);
        $parameter = null === $class ? null : $this->parentParameter($class);
        if (null === $parameter) {
            return null;
        }

        $optionArgs = array_values($new->getArgs());
        if (array_any($optionArgs, static fn (Arg $arg): bool => $arg->unpack)) {
            return null;
        }

        foreach ($optionArgs as $position => $arg) {
            if (null === $arg->name ? 0 === $position : $parameter === $arg->name->toString()) {
                unset($optionArgs[$position]);

                return $this->createCall($node, $arg, new New_($new->class, array_values($optionArgs), $new->getAttributes()), $args[0], $class);
            }
        }

        return null;
    }

    /**
     * Name des 3.x-Konstruktor-Parameters mit der Eltern-ID, z. B. `invoiceId` oder `creditNoteId`.
     */
    private function parentParameter(string $class): ?string
    {
        if (RecurringEmailReceiverCreateOptions::class === $class) {
            return 'recurringId';
        }

        return 1 === preg_match(self::OPTIONS_PATTERN, $class, $match) ? lcfirst($match[1]).'Id' : null;
    }

    private function createCall(MethodCall $node, Arg $parent, New_ $options, Arg $original, string $class): MethodCall
    {
        // Benannte Aufrufe bleiben benannt; der Parameter heißt in 4.0 `parentId` bzw. bei E-Mail-Empfängern `recurringId`.
        $parentName = $original->name instanceof Identifier ? new Identifier(RecurringEmailReceiverCreateOptions::class === $class ? 'recurringId' : 'parentId') : null;

        return new MethodCall($node->var, $node->name, [
            new Arg($parent->value, false, false, [], $parentName),
            new Arg($options, false, false, [], $original->name),
        ]);
    }
}
