<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\QuestionBankBundle\Entity\Category;

/**
 * @extends ServiceEntityRepository<Category>
 */
#[AsRepository(entityClass: Category::class)]
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return array<Category>
     */
    public function findRootCategories(): array
    {
        /** @var array<Category> */
        return $this->createQueryBuilder('c')
            ->andWhere('c.parent IS NULL')
            ->andWhere('c.valid = :valid')
            ->setParameter('valid', true)
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCode(string $code): ?Category
    {
        /** @var Category|null */
        return $this->createQueryBuilder('c')
            ->andWhere('c.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return array<Category>
     */
    public function findActiveCategories(): array
    {
        /** @var array<Category> */
        return $this->createQueryBuilder('c')
            ->andWhere('c.valid = :valid')
            ->setParameter('valid', true)
            ->orderBy('c.level', 'ASC')
            ->addOrderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取分类树（包含所有层级）
     *
     * @return array<Category>
     */
    public function getCategoryTree(): array
    {
        $rootCategories = $this->findRootCategories();

        // 预加载所有子分类以避免 N+1 查询
        $this->createQueryBuilder('c')
            ->leftJoin('c.children', 'children')
            ->addSelect('children')
            ->andWhere('c.parent IN (:parents)')
            ->setParameter('parents', $rootCategories)
            ->getQuery()
            ->getResult()
        ;

        return $rootCategories;
    }

    public function save(Category $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
