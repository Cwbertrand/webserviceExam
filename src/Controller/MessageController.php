<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class MessageController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em, private SerializerInterface $serializer, )
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    #[Route(path: 'api/create/message', name: 'create_message')]
    public function SendMessage(Request $request): JsonResponse 
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['id' => $this->getUser()]);
        if($user)
        {
            $requestData = json_decode($request->getContent(), true);
            $filteredComm = htmlspecialchars($requestData['content'], ENT_QUOTES);

            if($filteredComm){
                $message = $request->getContent();
                $serialize = $this->serializer->deserialize($message, Message::class, 'json');
                $serialize->setContent($serialize->getContent());
                $serialize->setUser($user);
                $this->em->persist($serialize);
                $this->em->flush();
            }
            return new JsonResponse([
                'status' => JsonResponse::HTTP_OK,
                'format' => 'application/json',
                'method' => 'post',
                'description' => 'successfully send a message',
            ]);
        }

        return new JsonResponse(['status' => JsonResponse::HTTP_NOT_FOUND]);
    }

    #[Route(path: 'api/get/message', name: 'get_message')]
    public function GetMessage(MessageRepository $msgRepo)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['id' => $this->getUser()]);
        if($user) {
            $getMessage = $msgRepo->getMessages($user);
            return new JsonResponse([
                $getMessage,
                'status' => JsonResponse::HTTP_OK,
                'format' => 'application/json',
                'method' => 'get',
                'description' => 'getting all messages of a particular user',
            ]);
        }
    }
}