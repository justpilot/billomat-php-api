<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Articles;

use Justpilot\Billomat\Api\ArticleCreateOptions;
use Justpilot\Billomat\Api\ArticleTagCreateOptions;
use Justpilot\Billomat\Api\ArticleUpdateOptions;
use Justpilot\Billomat\Model\Article;
use Justpilot\Billomat\Model\ArticleTag;
use Justpilot\Billomat\Model\ArticleTagCloudEntry;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ArticlesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canListArticlesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $articles = $billomat->articles->list(['per_page' => 5]);

        self::assertIsArray($articles);
        self::assertContainsOnlyInstancesOf(Article::class, $articles);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateUpdateDeleteArticleInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        $opts = new ArticleCreateOptions(title: 'Integrationstest-Artikel '.date('His'));
        $opts->description = 'Auto-Test';
        $opts->salesPrice = $faker->randomFloat(2, 5, 250);
        $opts->currencyCode = 'EUR';
        $opts->unit = 'Stück';

        $article = $billomat->articles->create($opts);

        self::assertInstanceOf(Article::class, $article);
        self::assertNotNull($article->id);
        self::assertNotNull($article->title);

        // Update
        $update = new ArticleUpdateOptions();
        $update->title = $article->title.' (geändert)';
        $updated = $billomat->articles->update($article->id, $update);

        self::assertSame($article->id, $updated->id);
        self::assertNotNull($updated->title);
        self::assertStringEndsWith('(geändert)', $updated->title);

        // Get
        $fetched = $billomat->articles->get($article->id);
        self::assertInstanceOf(Article::class, $fetched);
        self::assertSame($article->id, $fetched->id);

        // Cleanup
        self::assertTrue($billomat->articles->delete($article->id));
        self::assertNull($billomat->articles->get($article->id));
    }

    #[Group('integration')]
    #[Test]
    public function canManageArticleTagsInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        // Artikel anlegen
        $articleOpts = new ArticleCreateOptions(title: 'Tag-Test-Artikel '.date('His'));
        $articleOpts->salesPrice = 9.99;
        $articleOpts->unit = 'Stück';
        $article = $billomat->articles->create($articleOpts);

        try {
            // Tag erstellen
            $tagOpts = new ArticleTagCreateOptions(articleId: $article->id, name: 'IT-Tag-'.date('His'));
            $tag = $billomat->articleTags->create($tagOpts);

            self::assertInstanceOf(ArticleTag::class, $tag);
            self::assertNotNull($tag->id);

            // List by article
            $tags = $billomat->articleTags->listByArticle($article->id);
            self::assertContainsOnlyInstancesOf(ArticleTag::class, $tags);
            self::assertGreaterThanOrEqual(1, \count($tags));

            // Cloud
            $cloud = $billomat->articleTags->cloud();
            self::assertContainsOnlyInstancesOf(ArticleTagCloudEntry::class, $cloud);

            // Cleanup tag
            self::assertTrue($billomat->articleTags->delete($tag->id));
        } finally {
            $billomat->articles->delete($article->id);
        }
    }
}
