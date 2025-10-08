<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve un utilisateur par son login
     */
    public function findByLogin(string $login): ?User
    {
        return $this->findOneBy(['login' => $login]);
    }

    /**
     * Trouve tous les utilisateurs avec un rôle spécifique
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', json_encode($role))
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les utilisateurs RH
     */
    public function findRhUsers(): array
    {
        return $this->findByRole('ROLE_RH');
    }

    /**
     * Trouve tous les utilisateurs INFO
     */
    public function findInfoUsers(): array
    {
        return $this->findByRole('ROLE_INFO');
    }

    /**
     * Trouve tous les administrateurs
     */
    public function findAdminUsers(): array
    {
        return $this->findByRole('ROLE_ADMIN');
    }

    /**
     * Recherche d'utilisateurs par nom, prénom ou login
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.login LIKE :query')
            ->orWhere('u.nom LIKE :query')
            ->orWhere('u.prenom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'utilisateurs par rôle
     */
    public function countByRole(): array
    {
        $result = $this->createQueryBuilder('u')
            ->select('u.roles')
            ->getQuery()
            ->getResult();

        $counts = [
            'ROLE_USER' => 0,
            'ROLE_RH' => 0,
            'ROLE_INFO' => 0,
            'ROLE_ADMIN' => 0
        ];

        foreach ($result as $user) {
            $roles = $user['roles'];
            foreach ($roles as $role) {
                if (isset($counts[$role])) {
                    $counts[$role]++;
                }
            }
        }

        return $counts;
    }
}