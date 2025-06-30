<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Tests\Integration\Service;

use Tourze\QuestionBankBundle\DTO\TagDTO;
use Tourze\QuestionBankBundle\Service\TagService;
use Tourze\QuestionBankBundle\Tests\BaseIntegrationTestCase;

class TagServiceTest extends BaseIntegrationTestCase
{
    private TagService $tagService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->tagService = $this->getContainer()->get(TagService::class);
    }
    
    public function testCreateTag(): void
    {
        $dto = TagDTO::create('Test Tag');
        
        $tag = $this->tagService->createTag($dto);
        
        $this->assertSame('Test Tag', $tag->getName());
        $this->assertTrue($tag->isValid());
        $this->assertSame(0, $tag->getUsageCount());
    }
    
    public function testFindTagById(): void
    {
        $dto = TagDTO::create('Test Tag');
        $createdTag = $this->tagService->createTag($dto);
        
        $foundTag = $this->tagService->findTag((string) $createdTag->getId());
        
        $this->assertSame($createdTag->getId(), $foundTag->getId());
        $this->assertSame('Test Tag', $foundTag->getName());
    }
    
    public function testUpdateTag(): void
    {
        $dto = TagDTO::create('Test Tag');
        $tag = $this->tagService->createTag($dto);
        
        $updateDto = TagDTO::create('Updated Tag');
        $updateDto->slug = 'updated-tag';
        $updateDto->description = 'Updated description';
        $updateDto->color = '#FF0000';
        
        $updatedTag = $this->tagService->updateTag((string) $tag->getId(), $updateDto);
        
        $this->assertSame('Updated Tag', $updatedTag->getName());
        $this->assertSame('updated-tag', $updatedTag->getSlug());
        $this->assertSame('Updated description', $updatedTag->getDescription());
        $this->assertSame('#FF0000', $updatedTag->getColor());
    }
}