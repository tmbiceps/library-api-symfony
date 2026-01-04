<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return Book[]
     */
    public function findByAuthorId(int $authorId): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.author', 'a')
            ->andWhere('a.id = :aid')
            ->setParameter('aid', $authorId)
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
