<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\ValueObject\PaginatedResult;

/**
 * @extends ServiceEntityRepository<Question>
 */
#[AsRepository(entityClass: Question::class)]
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function find($id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?Question
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * @return array<int, Question>
     */
    public function findByCategory(Category $category): array
    {
        /** @var array<int, Question> */
        return $this->createQueryBuilder('q')
            ->innerJoin('q.categories', 'c')
            ->andWhere('c = :category')
            ->setParameter('category', $category)
            ->orderBy('q.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array<int, string> $tagIds
     * @return array<int, Question>
     */
    public function findByTags(array $tagIds): array
    {
        if (0 === count($tagIds)) {
            return [];
        }

        /** @var array<int, Question> */
        return $this->createQueryBuilder('q')
            ->innerJoin('q.tags', 't')
            ->andWhere('t.id IN (:tagIds)')
            ->setParameter('tagIds', $tagIds)
            ->groupBy('q.id')
            ->having('COUNT(DISTINCT t.id) = :tagCount')
            ->setParameter('tagCount', count($tagIds))
            ->orderBy('q.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return PaginatedResult<Question>
     */
    public function search(SearchCriteria $criteria): PaginatedResult
    {
        $qb = $this->createSearchQueryBuilder($criteria);

        $paginator = new Paginator($qb->getQuery());
        $paginator->setUseOutputWalkers(false);

        $items = iterator_to_array($paginator);
        /** @var array<int, Question> $items */

        return new PaginatedResult(
            items: $items,
            total: count($paginator),
            page: $criteria->getPage(),
            limit: $criteria->getLimit()
        );
    }

    /**
     * @return array<string, int>
     */
    public function countByType(): array
    {
        $result = $this->createQueryBuilder('q')
            ->select('q.type AS type', 'COUNT(q.id) AS count')
            ->groupBy('q.type')
            ->getQuery()
            ->getArrayResult()
        ;

        /** @var list<array{type: \Tourze\QuestionBankBundle\Enum\QuestionType, count: string}> $result */

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['type']->value] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * @param array<int, string> $ids
     * @return array<int, Question>
     */
    public function findByIds(array $ids): array
    {
        if (0 === count($ids)) {
            return [];
        }

        /** @var array<int, Question> */
        return $this->createQueryBuilder('q')
            ->andWhere('q.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<int, Question>
     */
    public function findRandom(int $limit, ?SearchCriteria $criteria = null): array
    {
        $qb = null !== $criteria ? $this->createSearchQueryBuilder($criteria) : $this->createQueryBuilder('q');

        // 先获取所有符合条件的记录，然后在PHP中随机化
        $allResults = $qb->getQuery()->getResult();
        /** @var array<int, Question> $allResults */

        // 如果结果数量少于或等于限制，直接返回
        if (count($allResults) <= $limit) {
            return $allResults;
        }

        // 随机打乱并返回指定数量
        shuffle($allResults);

        return array_slice($allResults, 0, $limit);
    }

    private function createSearchQueryBuilder(SearchCriteria $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('q');

        $this->applyKeywordFilter($qb, $criteria);
        $this->applyTypeFilter($qb, $criteria);
        $this->applyStatusFilter($qb, $criteria);
        $this->applyDifficultyFilter($qb, $criteria);
        $this->applyCategoryFilter($qb, $criteria);
        $this->applyTagFilter($qb, $criteria);
        $this->applyOrderBy($qb, $criteria);
        $this->applyPagination($qb, $criteria);

        return $qb;
    }

    private function applyKeywordFilter(QueryBuilder $qb, SearchCriteria $criteria): void
    {
        if (null === $criteria->getKeyword() || '' === $criteria->getKeyword()) {
            return;
        }

        $qb->andWhere('(q.title LIKE :keyword OR q.content LIKE :keyword)')
            ->setParameter('keyword', '%' . $criteria->getKeyword() . '%')
        ;
    }

    private function applyTypeFilter(QueryBuilder $qb, SearchCriteria $criteria): void
    {
        if (0 === count($criteria->getTypes())) {
            return;
        }

        $qb->andWhere('q.type IN (:types)')
            ->setParameter('types', $criteria->getTypes())
        ;
    }

    private function applyStatusFilter(QueryBuilder $qb, SearchCriteria $criteria): void
    {
        if (count($criteria->getStatuses()) > 0) {
            $qb->andWhere('q.status IN (:statuses)')
                ->setParameter('statuses', $criteria->getStatuses())
            ;

            return;
        }

        if (!$criteria->includeArchived()) {
            $qb->andWhere('q.status != :archived')
                ->setParameter('archived', QuestionStatus::ARCHIVED)
            ;
        }
    }

    private function applyDifficultyFilter(QueryBuilder $qb, SearchCriteria $criteria): void
    {
        if (null !== $criteria->getMinDifficulty()) {
            $qb->andWhere('q.difficulty >= :minDifficulty')
                ->setParameter('minDifficulty', $criteria->getMinDifficulty())
            ;
        }

        if (null !== $criteria->getMaxDifficulty()) {
            $qb->andWhere('q.difficulty <= :maxDifficulty')
                ->setParameter('maxDifficulty', $criteria->getMaxDifficulty())
            ;
        }
    }

    private function applyCategoryFilter(QueryBuilder $qb, SearchCriteria $criteria): void
    {
        if (0 === count($criteria->getCategoryIds())) {
            return;
        }

        $qb->innerJoin('q.categories', 'c')
            ->andWhere('c.id IN (:categoryIds)')
            ->setParameter('categoryIds', $criteria->getCategoryIds())
        ;
    }

    private function applyTagFilter(QueryBuilder $qb, SearchCriteria $criteria): void
    {
        if (0 === count($criteria->getTagIds())) {
            return;
        }

        $qb->innerJoin('q.tags', 't')
            ->andWhere('t.id IN (:tagIds)')
            ->setParameter('tagIds', $criteria->getTagIds())
        ;

        if ($criteria->requireAllTags()) {
            $qb->groupBy('q.id')
                ->having('COUNT(DISTINCT t.id) = :tagCount')
                ->setParameter('tagCount', count($criteria->getTagIds()))
            ;
        }
    }

    private function applyOrderBy(QueryBuilder $qb, SearchCriteria $criteria): void
    {
        foreach ($criteria->getOrderBy() as $field => $direction) {
            $qb->addOrderBy('q.' . $field, $direction);
        }
    }

    private function applyPagination(QueryBuilder $qb, SearchCriteria $criteria): void
    {
        if ($criteria->getLimit() <= 0) {
            return;
        }

        $qb->setMaxResults($criteria->getLimit())
            ->setFirstResult(($criteria->getPage() - 1) * $criteria->getLimit())
        ;
    }

    public function save(Question $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Question $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
