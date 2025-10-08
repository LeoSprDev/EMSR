<?php

namespace App\Service;

use App\Entity\Mouvement;
use App\Entity\User;
use App\Repository\MouvementRepository;
use Doctrine\ORM\EntityManagerInterface;

class MouvementService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MouvementRepository $mouvementRepository,
        private NotificationService $notificationService,
        private string $infoEmail,
        private string $baseUrl
    ) {}

    /**
     * Crée un nouveau mouvement avec toute la logique métier
     */
    public function createMouvement(Mouvement $mouvement, User $createdBy): Mouvement
    {
        // Définir les informations de création
        $mouvement->setCreatedBy($createdBy);
        
        // Calculer le mois de référence basé sur la date d'effet
        $dateEffet = $mouvement->getDateEffet();
        $moisReference = $dateEffet->format('Y-m');
        $mouvement->setMoisReference($moisReference);
        
        // Sauvegarder en base
        $this->entityManager->persist($mouvement);
        $this->entityManager->flush();
        
        // Envoyer la notification
        $this->notificationService->sendMovementNotification(
            $mouvement, 
            'creation',
            [$mouvement->getId()]
        );
        
        return $mouvement;
    }

    /**
     * Met à jour un mouvement existant
     */
    public function updateMouvement(Mouvement $mouvement, User $updatedBy): Mouvement
    {
        // Définir les informations de modification
        $mouvement->setUpdatedBy($updatedBy);
        
        // Recalculer le mois de référence si la date d'effet a changé
        $dateEffet = $mouvement->getDateEffet();
        $moisReference = $dateEffet->format('Y-m');
        $mouvement->setMoisReference($moisReference);
        
        // Sauvegarder en base
        $this->entityManager->flush();
        
        // Envoyer la notification
        $this->notificationService->sendMovementNotification(
            $mouvement, 
            'modification',
            [$mouvement->getId()]
        );
        
        return $mouvement;
    }

    /**
     * Supprime un mouvement
     */
    public function deleteMouvement(Mouvement $mouvement): void
    {
        // Envoyer la notification avant suppression
        $this->notificationService->sendMovementNotification(
            $mouvement, 
            'suppression'
        );
        
        // Supprimer de la base
        $this->entityManager->remove($mouvement);
        $this->entityManager->flush();
    }

    /**
     * Marque ou démarque la prise en compte d'un mouvement par le service INFO
     */
    public function togglePriseEnCompte(Mouvement $mouvement, User $infoUser): array
    {
        $wasChecked = $mouvement->isPriseEnCompteInfo();
        
        $mouvement->setPriseEnCompteInfo(!$wasChecked);
        
        if (!$wasChecked) {
            // Marquer comme pris en compte
            $mouvement->setPriseEnCompteAt(new \DateTimeImmutable());
            $mouvement->setPriseEnCompteBy($infoUser);
            $action = 'pris en compte';
        } else {
            // Annuler la prise en compte
            $mouvement->setPriseEnCompteAt(null);
            $mouvement->setPriseEnCompteBy(null);
            $action = 'prise en compte annulée';
        }
        
        $this->entityManager->flush();
        
        return [
            'success' => true,
            'action' => $action,
            'new_state' => $mouvement->isPriseEnCompteInfo(),
            'prise_en_compte_at' => $mouvement->getPriseEnCompteAt()?->format('d/m/Y H:i'),
            'prise_en_compte_by' => $mouvement->getPriseEnCompteBy()?->getDisplayName()
        ];
    }

    /**
     * Récupère les statistiques des mouvements
     */
    public function getStatistics(): array
    {
        return $this->mouvementRepository->getStatistics();
    }

    /**
     * Récupère les mouvements d'un mois avec statistiques
     */
    public function getMouvementsByMonth(string $moisReference): array
    {
        return [
            'mouvements_grouped' => $this->mouvementRepository->findByMonthGroupedByType($moisReference),
            'statistiques' => $this->mouvementRepository->countByTypeForMonth($moisReference),
            'total' => array_sum($this->mouvementRepository->countByTypeForMonth($moisReference))
        ];
    }

    /**
     * Recherche de mouvements avec filtres
     */
    public function searchMouvements(string $query, ?string $moisReference = null): array
    {
        return $this->mouvementRepository->search($query, $moisReference);
    }

    /**
     * Récupère les mouvements non pris en compte
     */
    public function getMouvementsNonPrisEnCompte(?string $moisReference = null): array
    {
        return $this->mouvementRepository->findNotTakenIntoAccount($moisReference);
    }

    /**
     * Valide qu'un mois de référence est correct
     */
    public function validateMoisReference(string $moisReference): bool
    {
        return preg_match('/^\d{4}-\d{2}$/', $moisReference) === 1;
    }

    /**
     * Génère le mois de référence à partir d'une date
     */
    public function generateMoisReference(\DateTime $date): string
    {
        return $date->format('Y-m');
    }
}