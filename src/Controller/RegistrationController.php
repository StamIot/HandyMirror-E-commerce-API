<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $tokenGenerator;
    private $mailer;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
        $this->validator = $validator;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'email' => [
                new Assert\Email(),
            ],
            'password' => [
                new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/',
                    'message' => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial.',
                ]),
            ],
            'prenom' => [
                new Assert\NotBlank(),
            ],
            'nom' => [
                new Assert\NotBlank(),
            ],
        ]);

        $violations = $this->validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
        
        $token = $this->tokenGenerator->generateToken();

        // Créer un nouvel utilisateur avec les données validées
        $user = new User();
        $user->setEmail($data['email']);
        $encodedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($encodedPassword);
        $user->setPrenom($data['prenom']);
        $user->setNom($data['nom']);

        // Enregistrer l'utilisateur dans la base de données, etc.
        $this->entityManager->persist($user);
        $user->setConfirmationToken($token);
        $this->entityManager->flush();
        
        $this->sendConfirmationEmail($user);

        return $this->json(['message' => 'Inscription réussie'], Response::HTTP_CREATED);
    }
    
    private function sendConfirmationEmail(User $user): void
    {
        $confirmationUrl = $this->generateUrl('confirm_account', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('handymirror@gmail.com')
            ->to($user->getEmail())
            ->subject('Confirmation de compte')
            ->text('Merci de confirmez votre compte en cliquant sur ce lien: '.$confirmationUrl);

        $this->mailer->send($email);
    }
}
