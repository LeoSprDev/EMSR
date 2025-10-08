<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\MouvementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private MouvementRepository $mouvementRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/', name: 'app_admin_index')]
    public function index(): Response
    {
        $statistiquesGenerales = $this->mouvementRepository->getStatistics();
        $countByRole = $this->userRepository->countByRole();
        $recentMovements = $this->mouvementRepository->findRecent(10);
        $notTakenIntoAccount = $this->mouvementRepository->findNotTakenIntoAccount();
        
        return $this->render('admin/index.html.twig', [
            'statistiques_generales' => $statistiquesGenerales,
            'count_by_role' => $countByRole,
            'recent_movements' => $recentMovements,
            'not_taken_into_account' => $notTakenIntoAccount,
        ]);
    }

    #[Route('/utilisateurs', name: 'app_admin_users')]
    public function users(Request $request): Response
    {
        $search = $request->query->get('search', '');
        
        if ($search) {
            $users = $this->userRepository->search($search);
        } else {
            $users = $this->userRepository->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']);
        }
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    #[Route('/utilisateur/nouveau', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function newUser(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur créé avec succès.');
            
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user_new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/utilisateur/{id}/modifier', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le nouveau mot de passe s'il est fourni
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            
            $user->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user_edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/utilisateur/{id}/supprimer', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            // Vérifier que l'utilisateur ne supprime pas son propre compte
            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
                return $this->redirectToRoute('app_admin_users');
            }
            
            // Vérifier s'il y a des mouvements liés
            $hasMovements = !$user->getMouvementsCreated()->isEmpty() || !$user->getMouvementsUpdated()->isEmpty();
            
            if ($hasMovements) {
                $this->addFlash('error', 'Impossible de supprimer cet utilisateur car il a créé ou modifié des mouvements.');
                return $this->redirectToRoute('app_admin_users');
            }
            
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Erreur lors de la suppression de l\'utilisateur.');
        }
        
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/configuration', name: 'app_admin_config')]
    public function configuration(): Response
    {
        // Page de configuration système
        return $this->render('admin/configuration.html.twig', [
            'info_email' => $this->getParameter('app.info_email'),
            'base_url' => $this->getParameter('app.base_url'),
        ]);
    }
}