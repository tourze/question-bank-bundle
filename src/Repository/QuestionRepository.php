<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\QuestionBankBundle\DTO\SearchCriteria;
use Tourze\QuestionBankBundle\Entity\Category;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\ValueObject\PaginatedResult;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository implements QuestionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function save(Question $question, bool $flush = true): void
    {
        $this->getEntityManager()->persist($question);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Question $question): void
    {
        $this->getEntityManager()->remove($question);
        $this->getEntityManager()->flush();
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?Question
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('q')
            ->innerJoin('q.categories', 'c')
            ->andWhere('c = :category')
            ->setParameter('category', $category)
            ->orderBy('q.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTags(array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }

        return $this->createQueryBuilder('q')
            ->innerJoin('q.tags', 't')
            ->andWhere('t.id IN (:tagIds)')
            ->setParameter('tagIds', $tagIds)
            ->groupBy('q.id')
            ->having('COUNT(DISTINCT t.id) = :tagCount')
            ->setParameter('tagCount', count($tagIds))
            ->orderBy('q.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function search(SearchCriteria $criteria): PaginatedResult
    {
        $qb = $this->createSearchQueryBuilder($criteria);
        
        $paginator = new Paginator($qb->getQuery());
        $paginator->setUseOutputWalkers(false);
        
        return new PaginatedResult(
            items: iterator_to_array($paginator),
            total: count($paginator),
            page: $criteria->getPage(),
            limit: $criteria->getLimit()
        );
    }

    public function countByType(): array
    {
        $result = $this->createQueryBuilder('q')
            ->select('q.type AS type', 'COUNT(q.id) AS count')
            ->groupBy('q.type')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['type']->value] = (int) $row['count'];
        }

        return $counts;
    }

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        // 将 UUID 对象转换为字符串
        $stringIds = array_map(fn($id) => (string) $id, $ids);
        
        return $this->createQueryBuilder('q')
            ->andWhere('q.id IN (:ids)')
            ->setParameter('ids', $stringIds)
            ->getQuery()
            ->getResult();
    }

    public function findRandom(int $limit, ?SearchCriteria $criteria = null): array
    {
        $qb = $criteria ? $this->createSearchQueryBuilder($criteria) : $this->createQueryBuilder('q');
        
        // 先获取所有符合条件的记录，然后在PHP中随机化
        $allResults = $qb->getQuery()->getResult();
        
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

        // 关键词搜索
        if ($criteria->getKeyword()) {
            $qb->andWhere('(q.title LIKE :keyword OR q.content LIKE :keyword)')
                ->setParameter('keyword', '%' . $criteria->getKeyword() . '%');
        }

        // 题型过滤
        if (!empty($criteria->getTypes())) {
            $qb->andWhere('q.type IN (:types)')
                ->setParameter('types', $criteria->getTypes());
        }

        // 状态过滤
        if (!empty($criteria->getStatuses())) {
            $qb->andWhere('q.status IN (:statuses)')
                ->setParameter('statuses', $criteria->getStatuses());
        } elseif (!$criteria->includeArchived()) {
            $qb->andWhere('q.status != :archived')
                ->setParameter('archived', QuestionStatus::ARCHIVED);
        }

        // 难度范围过滤
        if ($criteria->getMinDifficulty() !== null) {
            $qb->andWhere('q.difficulty >= :minDifficulty')
                ->setParameter('minDifficulty', $criteria->getMinDifficulty());
        }
        if ($criteria->getMaxDifficulty() !== null) {
            $qb->andWhere('q.difficulty <= :maxDifficulty')
                ->setParameter('maxDifficulty', $criteria->getMaxDifficulty());
        }

        // 分类过滤
        if (!empty($criteria->getCategoryIds())) {
            $qb->innerJoin('q.categories', 'c')
                ->andWhere('c.id IN (:categoryIds)')
                ->setParameter('categoryIds', $criteria->getCategoryIds());
        }

        // 标签过滤
        if (!empty($criteria->getTagIds())) {
            $qb->innerJoin('q.tags', 't')
                ->andWhere('t.id IN (:tagIds)')
                ->setParameter('tagIds', $criteria->getTagIds());
            
            if ($criteria->requireAllTags()) {
                $qb->groupBy('q.id')
                    ->having('COUNT(DISTINCT t.id) = :tagCount')
                    ->setParameter('tagCount', count($criteria->getTagIds()));
            }
        }

        // 排序
        foreach ($criteria->getOrderBy() as $field => $direction) {
            $qb->addOrderBy('q.' . $field, $direction);
        }

        // 分页
        if ($criteria->getLimit() > 0) {
            $qb->setMaxResults($criteria->getLimit())
                ->setFirstResult(($criteria->getPage() - 1) * $criteria->getLimit());
        }

        return $qb;
    }
}