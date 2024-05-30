<?php

namespace App\Controller;

use App\Entity\Link;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;
use App\Repository\LinkRepository;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\NotoSans;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 
class LinkController extends AbstractController
{
    #[Route('/api/links/{link}', name: 'link.get' , methods: ["GET"])]
    public function getLink(Link $link, SerializerInterface $serializer): JsonResponse
    {
        $jsonLinks =  $serializer->serialize($link, 'json', ["groups" => "default"] );
        return new JsonResponse($jsonLinks, Response::HTTP_OK, [],  true); 
    }
        #[Route('/api/links', name: 'link.getAll', methods: ["GET"])]
    public function getAllLinks(LinkRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $links = $repository->findAll();

        $jsonLinks =  $serializer->serialize($links, 'json',["groups" => "default"] );
        return new JsonResponse($jsonLinks, Response::HTTP_OK, [],  true); 
    }   
    #[Route("/api/links", name:'link.create', methods: ["POST"])]
    public function createLink(Request $request,ValidatorInterface  $validator, LinkRepository $linkRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): JsonResponse {

        $link = $serializer->deserialize($request->getContent(),Link::class, 'json');
        $link->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setStatus("on");
        $errors =  $validator->validate($link);
        if($errors->count()){
            return new JsonResponse($serializer->serialize($errors, "json"), JsonResponse::HTTP_BAD_REQUEST, [], true );
        }
        $entityManager->persist($link);
        $entityManager->flush();

        $jsonLink = $serializer->serialize($link, "json" , ["groups" => "default"]);
        $location = $urlGenerator->generate("link.get", ["link" => $link->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonLink, Response::HTTP_CREATED, ["Location" => $location],  true);
    }
    #[Route('/qr-codes/{link}', name: 'link.qrCode')]
    public function qrcodes(Link $link): Response
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create($link->getUrl())
            ->setEncoding(new Encoding('UTF-8'))
            // ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(120)
            ->setMargin(0)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        $logo = Logo::create('img/logo.png')
            ->setResizeToWidth(60);
        $label = Label::create('')->setFont(new NotoSans(8));
 
        $qrCodes = [];
        // $qrCodes['img'] = $writer->write($qrCode, $logo)->getDataUri();
        $qrCodes['img'] = $writer->write($qrCode)->getDataUri();
        $qrCodes['simple'] = $writer->write(
                                $qrCode,
                                null,
                                $label->setText('Simple')
                            )->getDataUri();
 
        $qrCode->setForegroundColor(new Color(255, 0, 0));
        $qrCodes['changeColor'] = $writer->write(
            $qrCode,
            null,
            $label->setText('Color Change')
        )->getDataUri();
 
        $qrCode->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 0, 0));
        $qrCodes['changeBgColor'] = $writer->write(
            $qrCode,
            null,
            $label->setText('Background Color Change')
        )->getDataUri();
 
        $qrCode->setSize(200)->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 255, 255));
        $qrCodes['withImage'] = $writer->write(
            $qrCode,
            $logo,
            $label->setText('With Image')->setFont(new NotoSans(20))
        )->getDataUri();
 
        return $this->render('qr_code_generator/index.html.twig', $qrCodes);
    }

    #[Route("/api/links/{link}", name:"link.update", methods:["PUT", "PATCH"])]
    public function updateLink(Link $link, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ):JsonResponse {
        $updatedLink = $serializer->deserialize($request->getContent(), Link::class, "json",[AbstractNormalizer::OBJECT_TO_POPULATE => $link]);

        $updatedLink->setUpdatedAt(new \DateTime());
        
        $entityManager->persist($updatedLink);
        $entityManager->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
