<?php

namespace App\Repository;

use App\Entity\FirmwareVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FirmwareVersion>
 */
class FirmwareVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FirmwareVersion::class);
    }

    /**
     * @return list<FirmwareVersion>
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.name', 'ASC')
            ->addOrderBy('f.systemVersionAlt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<FirmwareVersion>
     */
    public function findByNormalizedSystemVersion(string $version): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('LOWER(f.systemVersionAlt) = :version')
            ->setParameter('version', mb_strtolower(ltrim(trim($version), 'vV')))
            ->getQuery()
            ->getResult();
    }

    public function findLatestForName(string $name): ?FirmwareVersion
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.name = :name')
            ->andWhere('f.latest = true')
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function clearLatestForName(string $name, ?int $excludeId = null): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->update(FirmwareVersion::class, 'f')
            ->set('f.latest', ':latest')
            ->where('f.name = :name')
            ->setParameter('latest', false)
            ->setParameter('name', $name);

        if ($excludeId !== null) {
            $qb->andWhere('f.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        $qb->getQuery()->execute();
    }
}
