<?php

namespace App\Controller;

use App\Entity\Enum\MouvementType;
use App\Repository\MouvementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    public function __construct(
        private MouvementRepository $mouvementRepository
    ) {}

    #[Route('/', name: 'app_dashboard')]
    public function index(Request $request): Response
    {
        // Récupération du mois depuis l'URL ou utilisation du mois actuel
        $moisReference = $request->query->get('mois', date('Y-m'));
        
        // Validation du format du mois
        if (!preg_match('/^\d{4}-\d{2}$/', $moisReference)) {
            $moisReference = date('Y-m');
        }

        // Récupération des mouvements groupés par type
        $mouvementsGrouped = $this->mouvementRepository->findByMonthGroupedByType($moisReference);
        
        // Récupération des mois disponibles pour le sélecteur
        $moisDisponibles = $this->mouvementRepository->findAvailableMonths();
        
        // Ajout du mois actuel s'il n'existe pas encore
        $moisActuel = date('Y-m');
        if (!in_array($moisActuel, $moisDisponibles)) {
            array_unshift($moisDisponibles, $moisActuel);
        }
        
        // Statistiques pour le mois sélectionné
        $statistiques = $this->mouvementRepository->countByTypeForMonth($moisReference);
        
        // Calcul du total
        $totalMouvements = array_sum($statistiques);
        
        return $this->render('dashboard/index.html.twig', [
            'mois_reference' => $moisReference,
            'mois_disponibles' => $moisDisponibles,
            'mouvements_grouped' => $mouvementsGrouped,
            'statistiques' => $statistiques,
            'total_mouvements' => $totalMouvements,
            'types_mouvement' => MouvementType::cases(),
        ]);
    }

    #[Route('/statistiques', name: 'app_dashboard_stats')]
    public function statistiques(): Response
    {
        $statistiquesGenerales = $this->mouvementRepository->getStatistics();
        $statistiquesParMois = $this->mouvementRepository->countByMonth();
        $mouvementsRecents = $this->mouvementRepository->findRecent(5);
        
        return $this->render('dashboard/statistiques.html.twig', [
            'statistiques_generales' => $statistiquesGenerales,
            'statistiques_par_mois' => $statistiquesParMois,
            'mouvements_recents' => $mouvementsRecents,
        ]);
    }
}