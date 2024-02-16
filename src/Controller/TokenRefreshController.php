<?php
namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenRefreshController extends AbstractController
{
    public function refresh(Request $request, JWTTokenManagerInterface $JWTManager, UserProviderInterface $userProvider): JsonResponse
    {
        $currentToken = $request->headers->get('Authorization');

        if (!$currentToken) {
            throw new AuthenticationException('No token provided');
        }

        // Assuming the token is prefixed with 'Bearer ', strip that prefix
        $currentToken = str_replace('Bearer ', '', $currentToken);

        // Fetch the user from the token
        $username = $this->getUser()->getUserIdentifier();
        $user = $userProvider->loadUserByIdentifier($username);

        // Generate a new token
        $newToken = $JWTManager->create($user);

        return new JsonResponse(['token' => $newToken]);
    }
}
