<?php

namespace App\Service;

use App\Entity\Enum\MouvementType;
use App\Entity\Mouvement;
use App\Repository\MouvementRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class NotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private MouvementRepository $mouvementRepository,
        private string $fromEmail,
        private string $fromName,
        private string $infoEmail,
        private string $baseUrl
    ) {}

    /**
     * Envoie une notification email au service informatique
     */
    public function sendMovementNotification(
        Mouvement $mouvement, 
        string $action, 
        array $highlightedIds = []
    ): void {
        $moisReference = $mouvement->getMoisReference();
        
        // Récupérer tous les mouvements du mois groupés par type
        $mouvementsGrouped = $this->mouvementRepository->findByMonthGroupedByType($moisReference);
        
        // Statistiques du mois
        $statistiques = $this->mouvementRepository->countByTypeForMonth($moisReference);
        $totalMouvements = array_sum($statistiques);
        
        // Préparation des données pour l'email
        $emailData = [
            'action' => $action,
            'mouvement' => $mouvement,
            'mois_reference' => $moisReference,
            'mois_reference_formatted' => $this->formatMonth($moisReference),
            'mouvements_grouped' => $mouvementsGrouped,
            'statistiques' => $statistiques,
            'total_mouvements' => $totalMouvements,
            'highlighted_ids' => $highlightedIds,
            'dashboard_url' => $this->baseUrl . '/?mois=' . $moisReference,
            'types_mouvement' => MouvementType::cases(),
        ];
        
        // Création du sujet de l'email
        $subject = $this->createEmailSubject($action, $mouvement, $moisReference);
        
        // Création de l'email
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($this->infoEmail)
            ->subject($subject)
            ->htmlTemplate('emails/mouvement_notification.html.twig')
            ->context($emailData);
        
        // Envoi de l'email
        $this->mailer->send($email);
    }

    /**
     * Crée le sujet de l'email selon l'action
     */
    private function createEmailSubject(string $action, Mouvement $mouvement, string $moisReference): string
    {
        $moisFormatted = $this->formatMonth($moisReference);
        $typeLabel = $mouvement->getType()->getLabel();
        $nomComplet = $mouvement->getDisplayName();
        
        return match($action) {
            'creation' => "[EMSR] Nouveau mouvement - {$typeLabel} - {$nomComplet} ({$moisFormatted})",
            'modification' => "[EMSR] Mouvement modifié - {$typeLabel} - {$nomComplet} ({$moisFormatted})",
            'suppression' => "[EMSR] Mouvement supprimé - {$typeLabel} - {$nomComplet} ({$moisFormatted})",
            default => "[EMSR] Mouvement {$action} - {$nomComplet} ({$moisFormatted})",
        };
    }

    /**
     * Formate un mois YYYY-MM en français
     */
    private function formatMonth(string $moisReference): string
    {
        $mois = [
            '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
            '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
            '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
        ];
        
        [$annee, $moisNum] = explode('-', $moisReference);
        
        return ($mois[$moisNum] ?? 'Mois inconnu') . ' ' . $annee;
    }

    /**
     * Envoie un email de test
     */
    public function sendTestEmail(): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($this->infoEmail)
            ->subject('[EMSR] Test de configuration email')
            ->htmlTemplate('emails/test_email.html.twig')
            ->context([
                'test_date' => new \DateTime(),
                'base_url' => $this->baseUrl
            ]);
        
        $this->mailer->send($email);
    }
}