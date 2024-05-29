<?php

namespace App\Controller;

use App\Repository\LinkRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LinkController extends AbstractController
{
    #[Route('/link', name: 'app_link')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/LinkController.php',
        ]);
    }
        #[Route('/api/links', name: 'link.getAll', methods: ["GET"])]
    public function getAllSongs(LinkRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $links = $repository->findAll();
        $jsonLinks =  $serializer->serialize($links, 'json',["groups" => "default"] );
        return new JsonResponse($jsonLinks, Response::HTTP_OK, [],  true); 
    }

}
