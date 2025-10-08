<?php

namespace App\Repository;

use App\Entity\Enum\MouvementType;
use App\Entity\Mouvement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mouvement>
 */
class MouvementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mouvement::class);
    }

    /**
     * Trouve tous les mouvements d'un mois donné
     */
    public function findByMonth(string $moisReference): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.moisReference = :mois')
            ->setParameter('mois', $moisReference)
            ->orderBy('m.type', 'ASC')
            ->addOrderBy('m.nom', 'ASC')
            ->addOrderBy('m.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les mouvements d'un mois groupés par type
     */
    public function findByMonthGroupedByType(string $moisReference): array
    {
        $mouvements = $this->findByMonth($moisReference);
        
        $grouped = [
            MouvementType::ENTREE->value => [],
            MouvementType::SORTIE->value => [],
            MouvementType::MOBILITE->value => [],
            MouvementType::RENOUVELLEMENT_CDD->value => []
        ];

        foreach ($mouvements as $mouvement) {
            $grouped[$mouvement->getType()->value][] = $mouvement;
        }

        return $grouped;
    }

    /**
     * Trouve tous les mouvements d'un type spécifique pour un mois
     */
    public function findByMonthAndType(string $moisReference, MouvementType $type): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.moisReference = :mois')
            ->andWhere('m.type = :type')
            ->setParameter('mois', $moisReference)
            ->setParameter('type', $type)
            ->orderBy('m.nom', 'ASC')
            ->addOrderBy('m.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les mois disponibles (pour le filtrage)
     */
    public function findAvailableMonths(): array
    {
        $result = $this->createQueryBuilder('m')
            ->select('DISTINCT m.moisReference')
            ->orderBy('m.moisReference', 'DESC')
            ->getQuery()
            ->getResult();

        return array_column($result, 'moisReference');
    }

    /**
     * Compte les mouvements par mois
     */
    public function countByMonth(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.moisReference, COUNT(m.id) as count')
            ->groupBy('m.moisReference')
            ->orderBy('m.moisReference', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les mouvements par type pour un mois donné
     */
    public function countByTypeForMonth(string $moisReference): array
    {
        $result = $this->createQueryBuilder('m')
            ->select('m.type, COUNT(m.id) as count')
            ->where('m.moisReference = :mois')
            ->setParameter('mois', $moisReference)
            ->groupBy('m.type')
            ->getQuery()
            ->getResult();

        $counts = [
            MouvementType::ENTREE->value => 0,
            MouvementType::SORTIE->value => 0,
            MouvementType::MOBILITE->value => 0,
            MouvementType::RENOUVELLEMENT_CDD->value => 0
        ];

        foreach ($result as $row) {
            $counts[$row['type']->value] = $row['count'];
        }

        return $counts;
    }

    /**
     * Trouve les mouvements non encore pris en compte par le service INFO
     */
    public function findNotTakenIntoAccount(string $moisReference = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.priseEnCompteInfo = false');

        if ($moisReference) {
            $qb->andWhere('m.moisReference = :mois')
               ->setParameter('mois', $moisReference);
        }

        return $qb->orderBy('m.createdAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Recherche de mouvements par nom, prénom, numéro d'agent
     */
    public function search(string $query, string $moisReference = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.nom LIKE :query')
            ->orWhere('m.prenom LIKE :query')
            ->orWhere('m.numeroAgent LIKE :query')
            ->orWhere('m.emploi LIKE :query')
            ->orWhere('m.service LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        if ($moisReference) {
            $qb->andWhere('m.moisReference = :mois')
               ->setParameter('mois', $moisReference);
        }

        return $qb->orderBy('m.updatedAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Trouve les derniers mouvements créés
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques générales
     */
    public function getStatistics(): array
    {
        $total = $this->count([]);
        $priseEnCompte = $this->count(['priseEnCompteInfo' => true]);
        $enAttente = $total - $priseEnCompte;
        
        $currentMonth = date('Y-m');
        $currentMonthCount = $this->count(['moisReference' => $currentMonth]);
        
        return [
            'total' => $total,
            'prise_en_compte' => $priseEnCompte,
            'en_attente' => $enAttente,
            'mois_actuel' => $currentMonthCount,
            'pourcentage_prise_en_compte' => $total > 0 ? round(($priseEnCompte / $total) * 100, 1) : 0
        ];
    }
}