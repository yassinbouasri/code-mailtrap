<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    /**
     * @return Booking[]
     */
    public function findUpcomingFor(Customer $customer): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.customer = :customer')
            ->andWhere('b.date >= :now')
            ->setParameter('customer', $customer)
            ->setParameter('now', new \DateTimeImmutable('now'))
            ->orderBy('b.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Booking[]
     */
    public function findPreviousFor(Customer $customer): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.customer = :customer')
            ->andWhere('b.date < :now')
            ->setParameter('customer', $customer)
            ->setParameter('now', new \DateTimeImmutable('now'))
            ->orderBy('b.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
