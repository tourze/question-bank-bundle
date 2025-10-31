<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\QuestionBankBundle\Entity\Tag;

/**
 * @extends ServiceEntityRepository<Tag>
 */
#[AsRepository(entityClass: Tag::class)]
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return array<Tag>
     */
    public function findPopularTags(int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.usageCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array<string> $names
     *
     * @return array<Tag>
     */
    public function findByNames(array $names): array
    {
        if (0 === count($names)) {
            return [];
        }

        return $this->createQueryBuilder('t')
            ->andWhere('t.name IN (:names)')
            ->setParameter('names', $names)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 搜索标签
     *
     * @return array<Tag>
     */
    public function search(string $keyword, int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.name LIKE :keyword OR t.slug LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('t.usageCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Tag $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tag $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
