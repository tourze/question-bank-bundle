<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\QuestionBankBundle\Entity\Option;
use Tourze\QuestionBankBundle\Entity\Question;

/**
 * @extends ServiceEntityRepository<Option>
 */
#[AsRepository(entityClass: Option::class)]
class OptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Option::class);
    }

    public function find($id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?Option
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * @return Option[]
     */
    public function findByQuestion(Question $question): array
    {
        /** @var array<Option> */
        return $this->createQueryBuilder('o')
            ->andWhere('o.question = :question')
            ->setParameter('question', $question)
            ->orderBy('o.sortOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Option[]
     */
    public function findCorrectOptionsByQuestion(Question $question): array
    {
        /** @var array<Option> */
        return $this->createQueryBuilder('o')
            ->andWhere('o.question = :question')
            ->andWhere('o.isCorrect = :isCorrect')
            ->setParameter('question', $question)
            ->setParameter('isCorrect', true)
            ->orderBy('o.sortOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByQuestion(Question $question): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.question = :question')
            ->setParameter('question', $question)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param array<string, int> $reorderData
     */
    public function reorderOptions(array $reorderData): void
    {
        foreach ($reorderData as $optionId => $newOrder) {
            // 使用实体方式更新以确保正确处理 UUID
            $option = $this->find($optionId);
            if (null !== $option) {
                $option->setSortOrder($newOrder);
                $this->getEntityManager()->persist($option);
            }
        }
        $this->getEntityManager()->flush();
    }

    public function save(Option $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Option $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
