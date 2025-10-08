<?php

namespace App\Controller;

use App\Entity\Mouvement;
use App\Form\MouvementType;
use App\Repository\MouvementRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mouvement')]
#[IsGranted('ROLE_USER')]
class MouvementController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MouvementRepository $mouvementRepository,
        private NotificationService $notificationService
    ) {}

    #[Route('/', name: 'app_mouvement_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $moisReference = $request->query->get('mois', date('Y-m'));
        
        if ($search) {
            $mouvements = $this->mouvementRepository->search($search, $moisReference);
        } else {
            $mouvements = $this->mouvementRepository->findByMonth($moisReference);
        }
        
        return $this->render('mouvement/index.html.twig', [
            'mouvements' => $mouvements,
            'search' => $search,
            'mois_reference' => $moisReference,
        ]);
    }

    #[Route('/nouveau', name: 'app_mouvement_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_RH')]
    public function new(Request $request): Response
    {
        $mouvement = new Mouvement();
        $form = $this->createForm(MouvementType::class, $mouvement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir le mois de référence basé sur la date d'effet
            $dateEffet = $mouvement->getDateEffet();
            $moisReference = $dateEffet->format('Y-m');
            $mouvement->setMoisReference($moisReference);
            
            // Définir l'utilisateur créateur
            $mouvement->setCreatedBy($this->getUser());
            
            $this->entityManager->persist($mouvement);
            $this->entityManager->flush();
            
            // Envoi de la notification email
            try {
                $this->notificationService->sendMovementNotification(
                    $mouvement, 
                    'creation',
                    [$mouvement->getId()] // IDs des mouvements modifiés/ajoutés
                );
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Mouvement créé mais erreur lors de l\'envoi de la notification : ' . $e->getMessage());
            }
            
            $this->addFlash('success', 'Mouvement créé avec succès.');
            
            return $this->redirectToRoute('app_dashboard', ['mois' => $moisReference]);
        }

        return $this->render('mouvement/new.html.twig', [
            'mouvement' => $mouvement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mouvement_show', methods: ['GET'])]
    public function show(Mouvement $mouvement): Response
    {
        return $this->render('mouvement/show.html.twig', [
            'mouvement' => $mouvement,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_mouvement_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_RH')]
    public function edit(Request $request, Mouvement $mouvement): Response
    {
        $form = $this->createForm(MouvementType::class, $mouvement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour le mois de référence si la date d'effet a changé
            $dateEffet = $mouvement->getDateEffet();
            $moisReference = $dateEffet->format('Y-m');
            $mouvement->setMoisReference($moisReference);
            
            // Définir l'utilisateur modificateur
            $mouvement->setUpdatedBy($this->getUser());
            
            $this->entityManager->flush();
            
            // Envoi de la notification email
            try {
                $this->notificationService->sendMovementNotification(
                    $mouvement, 
                    'modification',
                    [$mouvement->getId()] // IDs des mouvements modifiés
                );
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Mouvement modifié mais erreur lors de l\'envoi de la notification : ' . $e->getMessage());
            }
            
            $this->addFlash('success', 'Mouvement modifié avec succès.');
            
            return $this->redirectToRoute('app_dashboard', ['mois' => $moisReference]);
        }

        return $this->render('mouvement/edit.html.twig', [
            'mouvement' => $mouvement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_mouvement_delete', methods: ['POST'])]
    #[IsGranted('ROLE_RH')]
    public function delete(Request $request, Mouvement $mouvement): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mouvement->getId(), $request->request->get('_token'))) {
            $moisReference = $mouvement->getMoisReference();
            
            // Envoi de la notification avant suppression
            try {
                $this->notificationService->sendMovementNotification(
                    $mouvement, 
                    'suppression'
                );
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Erreur lors de l\'envoi de la notification : ' . $e->getMessage());
            }
            
            $this->entityManager->remove($mouvement);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Mouvement supprimé avec succès.');
            
            return $this->redirectToRoute('app_dashboard', ['mois' => $moisReference]);
        }
        
        $this->addFlash('error', 'Erreur lors de la suppression du mouvement.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/{id}/toggle-prise-en-compte', name: 'app_mouvement_toggle_prise_en_compte', methods: ['POST'])]
    #[IsGranted('ROLE_INFO')]
    public function togglePriseEnCompte(Request $request, Mouvement $mouvement): JsonResponse
    {
        if (!$this->isCsrfTokenValid('toggle-prise-en-compte'.$mouvement->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 400);
        }
        
        $currentState = $mouvement->isPriseEnCompteInfo();
        $mouvement->setPriseEnCompteInfo(!$currentState);
        
        if (!$currentState) {
            // Marqué comme pris en compte
            $mouvement->setPriseEnCompteAt(new \DateTimeImmutable());
            $mouvement->setPriseEnCompteBy($this->getUser());
            $message = 'Mouvement marqué comme pris en compte';
        } else {
            // Marqué comme non pris en compte
            $mouvement->setPriseEnCompteAt(null);
            $mouvement->setPriseEnCompteBy(null);
            $message = 'Prise en compte annulée';
        }
        
        $this->entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'new_state' => $mouvement->isPriseEnCompteInfo(),
            'prise_en_compte_at' => $mouvement->getPriseEnCompteAt()?->format('d/m/Y H:i'),
            'prise_en_compte_by' => $mouvement->getPriseEnCompteBy()?->getDisplayName()
        ]);
    }
}