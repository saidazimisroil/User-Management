<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserStatusEnum;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'app_users')]
    public function index(UserRepository $repository): Response
    {
        $userAuth = $this->getUser();
        $user = $repository->findBy(['email' => $userAuth->getUserIdentifier()]);
        $status = $user[0]->getStatus();
        if ($status == 'blocked') {
            return $this->redirectToRoute('app_users_blocked');
        }

        $users = $repository->findAll();
        return $this->render('users/index.html.twig', [
            'users' => $users,
            'status' => $status,
        ]);
    }

    #[Route('/delete/{id<\d+>}', name: 'app_users_delete_one')]
    public function deleteOne(int $id, UserRepository $repository, EntityManagerInterface $em): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('app_users');
        }
    
        try {
            $em->remove($user);
            $em->flush();
        } catch (\Exception $e) {
            // Log the exception or handle it accordingly
            $this->addFlash('error', 'Could not delete user.');
            return $this->redirectToRoute('app_users');
        }
    
        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('app_users');
    }

    #[Route('/block/{id<\d+>}', name: 'app_users_block_one')]
    public function blockOne(int $id, UserRepository $repository, EntityManagerInterface $em): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_users');
        }
    
        try {
            $user->setStatus(UserStatusEnum::BLOCKED);
            $em->flush();
        } catch (\Exception $e) {
            // Log the exception or handle it accordingly
            $this->addFlash('error', 'Could not block user.');
            return $this->redirectToRoute('app_users');
        }
    
        $this->addFlash('success', 'User blocked successfully.');
        return $this->redirectToRoute('app_users');
    }

    #[Route('/unblock/{id<\d+>}', name: 'app_users_unblock_one')]
    public function unblockOne(int $id, UserRepository $repository, EntityManagerInterface $em): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_users');
        }
    
        try {
            $user->setStatus(UserStatusEnum::ACTIVE);
            $em->flush();
        } catch (\Exception $e) {
            // Log the exception or handle it accordingly
            $this->addFlash('error', 'Could not unblock user.');
            return $this->redirectToRoute('app_users');
        }
    
        $this->addFlash('success', 'User unblocked successfully.');
        return $this->redirectToRoute('app_users');
    }
    #[Route('/delete-all', name: 'app_users_delete_all')]
    public function deleteAll(EntityManagerInterface $entityManager): Response
    {
        // Get the repository of the User entity
        $userRepository = $entityManager->getRepository(User::class);

        // Create a Query to delete all users
        $query = $entityManager->createQuery('DELETE FROM App\Entity\User');
        $numDeleted = $query->execute(); // Execute the deletion

        // Flash a message to the session to confirm deletion
        $this->addFlash('success', $numDeleted . ' users have been deleted.');

        // Redirect to another page, such as the users list
        return $this->redirectToRoute('app_users');
    }
    #[Route('/you-are-blocked', name: 'app_users_blocked')]
    public function blocked(): Response
    {
        return $this->render('users/blocked.html.twig');
    }
}
