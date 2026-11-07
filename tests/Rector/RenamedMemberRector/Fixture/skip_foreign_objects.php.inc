<?php

namespace Justpilot\Billomat\Tests\Rector\RenamedMemberRector\Fixture;

final class ForeignComment
{
    public ?string $actionkey = null;
    public ?int $invoiceId = null;

    public function withActionkey(string $key): self
    {
        return $this;
    }
}

final class SkipForeignObjects
{
    /**
     * @param class-string $class
     */
    public function run(ForeignComment $comment, \stdClass $raw, string $class): array
    {
        return [new $class(actionkey: 1), new class(actionkey: 1) {
            public function __construct(public int $actionkey)
            {
            }
        }, $comment->{'actionkey'}, $comment->actionkey, $comment->invoiceId, $comment->withActionkey('x'), $raw->base64file, new \ArrayObject(array: [])];
    }
}
?>
