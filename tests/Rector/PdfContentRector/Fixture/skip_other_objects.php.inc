<?php

namespace Justpilot\Billomat\Tests\Rector\PdfContentRector\Fixture;

use Justpilot\Billomat\Api\TemplatesApi;

final class SkipOtherObjects
{
    public function run(TemplatesApi $templates, \stdClass $other, $untyped): void
    {
        $other->pdf(1, null, true);
        $untyped->pdf(1, rawPdf: true);
        $templates->thumb(1);
    }
}
?>
