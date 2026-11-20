<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Rector;

use Justpilot\Billomat\Api\ContactsApi;
use Justpilot\Billomat\Api\SearchApi;
use Justpilot\Billomat\Api\TaxesApi;
use Justpilot\Billomat\Api\TemplatesApi;
use Justpilot\Billomat\Model\DocumentPdf;
use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Type\TypeCombinator;
use Rector\Rector\AbstractRector;

/**
 * Benennt Properties, Wither und Parameter um, die in 4.0 einheitlich camelCase heißen.
 *
 * - `$comment->actionkey`, `->actionkeyRaw`, `->customfield`, `->base64file` auf SDK-Objekten werden
 *   `actionKey`, `actionKeyRaw`, `customField`, `base64File`; ebenso die benannten Argumente in
 *   `new …Options(…)` und die Wither `withActionkey()`/`withBase64file()`.
 * - `$pdf->invoiceId`, `->offerId`, … auf den PDF-Models (jetzt {@see DocumentPdf}) werden `documentId`.
 * - Benannte Argumente der Listen-Methoden: `contacts->list(clientId: …, query: …)` wird
 *   `list(parentId: …, filters: …)`, `search->query(extraFilters: …)` wird `filters:`,
 *   `taxes->list(query: …)` und `templates->list(query: …)` werden `filters:`.
 */
final class RenamedMemberRector extends AbstractRector
{
    /** Namespaces der Models und Options; andere Klassen (auch eigene unter `Justpilot\Billomat`) bleiben unberührt. */
    private const array SDK = ['Justpilot\Billomat\Api\\', 'Justpilot\Billomat\Model\\'];

    /** Properties und benannte Argumente aller SDK-Klassen. */
    private const array CAMEL_CASE = [
        'actionkey' => 'actionKey',
        'actionkeyRaw' => 'actionKeyRaw',
        'customfield' => 'customField',
        'base64file' => 'base64File',
    ];

    private const array WITHERS = [
        'withActionkey' => 'withActionKey',
        'withBase64file' => 'withBase64File',
        'withCustomfield' => 'withCustomField',
    ];

    /** Die sieben 3.x-PDF-Models und ihr Dokument-ID-Feld; in 4.0 alle {@see DocumentPdf} mit `documentId`. */
    private const array PDF_DOCUMENT_IDS = [
        'Justpilot\Billomat\Model\InvoicePdf' => 'invoiceId',
        'Justpilot\Billomat\Model\OfferPdf' => 'offerId',
        'Justpilot\Billomat\Model\ConfirmationPdf' => 'confirmationId',
        'Justpilot\Billomat\Model\CreditNotePdf' => 'creditNoteId',
        'Justpilot\Billomat\Model\DeliveryNotePdf' => 'deliveryNoteId',
        'Justpilot\Billomat\Model\LetterPdf' => 'letterId',
        'Justpilot\Billomat\Model\ReminderPdf' => 'reminderId',
    ];

    /**
     * Klasse => [Methoden, [alter Parametername => neuer]]; `listByClient` heißt in 4.0 `list`.
     *
     * @var array<class-string, array{list<string>, array<string, string>}>
     */
    private const array PARAMETERS = [
        ContactsApi::class => [['list', 'listPage', 'iterateAll', 'listByClient'], ['clientId' => 'parentId', 'query' => 'filters']],
        SearchApi::class => [['query'], ['extraFilters' => 'filters']],
        TaxesApi::class => [['list', 'listPage', 'iterateAll'], ['query' => 'filters']],
        TemplatesApi::class => [['list', 'listPage', 'iterateAll'], ['query' => 'filters']],
    ];

    #[Override]
    public function getNodeTypes(): array
    {
        return [PropertyFetch::class, NullsafePropertyFetch::class, MethodCall::class, NullsafeMethodCall::class, New_::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof PropertyFetch || $node instanceof NullsafePropertyFetch) {
            return $this->renameProperty($node);
        }

        if ($node instanceof MethodCall || $node instanceof NullsafeMethodCall) {
            return $this->renameMethodCall($node);
        }

        return $node instanceof New_ ? $this->renameConstructorArguments($node) : null;
    }

    private function renameProperty(PropertyFetch|NullsafePropertyFetch $node): ?Node
    {
        $new = $this->newPropertyName((string) $this->getName($node->name), $this->sdkClasses($node->var));
        if (null === $new) {
            return null;
        }

        return $node instanceof NullsafePropertyFetch
            ? new NullsafePropertyFetch($node->var, new Identifier($new))
            : new PropertyFetch($node->var, new Identifier($new));
    }

    private function renameMethodCall(MethodCall|NullsafeMethodCall $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }

        $method = (string) $this->getName($node->name);
        $classes = $this->sdkClasses($node->var);
        $newMethod = [] === $classes ? $method : (self::WITHERS[$method] ?? $method);
        // Die Wither nehmen die camelCase-Namen, die Listen-Methoden die umbenannten Parameter.
        $args = $this->renamedArguments($node->getArgs(), $newMethod !== $method ? self::CAMEL_CASE : $this->parameterRenames($classes, $method));

        if ($newMethod === $method && null === $args) {
            return null;
        }

        return $node instanceof NullsafeMethodCall
            ? new NullsafeMethodCall($node->var, new Identifier($newMethod), $args ?? $node->getArgs())
            : new MethodCall($node->var, new Identifier($newMethod), $args ?? $node->getArgs());
    }

    private function renameConstructorArguments(New_ $node): ?Node
    {
        $class = $this->getName($node->class) ?? '';
        if (!$this->isSdkClass($class)) {
            return null;
        }

        $documentId = self::PDF_DOCUMENT_IDS[$class] ?? null;
        $renames = null === $documentId ? self::CAMEL_CASE : [...self::CAMEL_CASE, $documentId => 'documentId'];
        $args = $this->renamedArguments($node->getArgs(), $renames);

        return null === $args ? null : new New_($node->class, $args);
    }

    /**
     * @param array<string> $classes
     *
     * @return array<string, string>
     */
    private function parameterRenames(array $classes, string $method): array
    {
        foreach (self::PARAMETERS as $class => [$methods, $renames]) {
            if (\in_array($class, $classes, true) && \in_array($method, $methods, true)) {
                return $renames;
            }
        }

        return [];
    }

    /**
     * @param array<string> $classes
     */
    private function newPropertyName(string $name, array $classes): ?string
    {
        if ([] === $classes) {
            return null;
        }

        foreach ($classes as $class) {
            $documentId = self::PDF_DOCUMENT_IDS[$class] ?? null;
            // In 4.0 liefert pdf() ein DocumentPdf; ein `$pdf->invoiceId` darauf stammt aus 3.x.
            if ($documentId === $name || (DocumentPdf::class === $class && \in_array($name, self::PDF_DOCUMENT_IDS, true))) {
                return 'documentId';
            }
        }

        return self::CAMEL_CASE[$name] ?? null;
    }

    /**
     * Neue Argumente mit umbenannten Namen; `null`, wenn kein Name passt.
     *
     * @param array<Arg>            $args
     * @param array<string, string> $renames
     *
     * @return array<Arg>|null
     */
    private function renamedArguments(array $args, array $renames): ?array
    {
        $renamed = array_map(
            static fn (Arg $arg): Arg => isset($renames[(string) $arg->name?->toString()])
                ? new Arg($arg->value, $arg->byRef, $arg->unpack, [], new Identifier($renames[(string) $arg->name?->toString()]))
                : $arg,
            $args,
        );

        return $renamed === $args ? null : $renamed;
    }

    /**
     * SDK-Klassen im Typ des Ausdrucks; auch 3.x-Klassen, die es in 4.0 nicht mehr gibt.
     *
     * @return array<string>
     */
    private function sdkClasses(Expr $expr): array
    {
        return array_filter(
            TypeCombinator::removeNull($this->getType($expr))->getObjectClassNames(),
            $this->isSdkClass(...),
        );
    }

    private function isSdkClass(string $class): bool
    {
        return array_any(self::SDK, static fn (string $namespace): bool => str_starts_with($class, $namespace));
    }
}
