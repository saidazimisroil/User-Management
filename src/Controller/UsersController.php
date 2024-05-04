<?php

namespace App\Controller;

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
        $users = $repository->findAll();
        return $this->render('users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/delete/{id<\d+>}', name: 'app_users_delete_one')]
    public function deleteOne(int $id, UserRepository $repository, EntityManagerInterface $em): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirect('/users');
        }
    
        try {
            $em->remove($user);
            $em->flush();
        } catch (\Exception $e) {
            // Log the exception or handle it accordingly
            $this->addFlash('error', 'Could not delete user.');
            return $this->redirect('/users');
        }
    
        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirect('/users');
    }
}
