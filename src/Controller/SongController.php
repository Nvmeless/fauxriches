<?php

namespace App\Controller;

use App\Entity\Song;
use App\Repository\LinkRepository;
use App\Repository\SongRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
class SongController extends AbstractController
{
    #[Route('/song', name: 'app_song')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SongController.php',
        ]);
    }

    #[Route('/api/songs', name: 'song.getAll', methods: ["GET"])]
    public function getAllSongs(SongRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $songs = $repository->findAll();
        $jsonSongs =  $serializer->serialize($songs, 'json',["groups" => "default"] );
        return new JsonResponse($jsonSongs, Response::HTTP_OK, [],  true); 
    }

    // #[Route('/api/songs/{idSong}', name: 'song.get')]
    // public function getSong(int $idSong ,SongRepository $repository, SerializerInterface $serializer): JsonResponse
    // {
    //     $songs = $repository->find($idSong);
    //     $jsonSongs =  $serializer->serialize($songs, 'json' );
    //     return new JsonResponse($jsonSongs, JsonResponse::HTTP_OK, [],  true); 
    // }

        #[Route('/api/songs/{song}', name: 'song.get' , methods: ["GET"])]
    public function getSong(Song $song, SerializerInterface $serializer): JsonResponse
    {
        $jsonSongs =  $serializer->serialize($song, 'json', ["groups" => "default"] );
        return new JsonResponse($jsonSongs, Response::HTTP_OK, [],  true); 
    }

    #[Route("/api/songs", name:'song.create', methods: ["POST"])]
    public function createSong(Request $request, LinkRepository $linkRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): JsonResponse {
        // dd($request);

        $song = $serializer->deserialize($request->getContent(),Song::class, 'json');
        $song->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setStatus("on");

  
            $idLinks = $request->toArray()["idLink"];
            $links = [];
            foreach($idLinks as $idLink){
                $links[] = $linkRepository->find($idLink);
            }
            $song->setLinks($links);

        $entityManager->persist($song);
        $entityManager->flush();

        $jsonSong = $serializer->serialize($song, "json" , ["groups" => "default"]);
        $location = $urlGenerator->generate("song.get", ["song" => $song->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonSong, Response::HTTP_CREATED, ["Location" => $location],  true);
    }

    #[Route('/api/songs/{song}', name:"song.delete", methods:["DELETE"])]
    public function deleteSong(Song $song, EntityManagerInterface $entityManager ): JsonResponse {

        $entityManager->remove($song);
        $entityManager->flush( );
 
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }



    #[Route("/api/songs/{song}", name:"song.update", methods:["PUT", "PATCH"])]
    public function updateSong(Song $song, Request $request,LinkRepository $linkRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager, ):JsonResponse {
        $updatedSong = $serializer->deserialize($request->getContent(), Song::class, "json",[AbstractNormalizer::OBJECT_TO_POPULATE => $song]);
        $idLinks = $request->toArray()["idLink"];
        $links = [];
        foreach($idLinks as $idLink){
            $links[] = $linkRepository->find($idLink);
        }
        $song->setLink($links);
        $updatedSong->setUpdatedAt(new DateTime());
        
        $entityManager->persist($updatedSong);
        $entityManager->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
