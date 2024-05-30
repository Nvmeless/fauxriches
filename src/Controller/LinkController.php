<?php

namespace App\Controller;

use App\Entity\Link;
use App\Repository\LinkRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Font\NotoSans;
 
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
    public function getAllLinks(LinkRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $links = $repository->findAll();

        $jsonLinks =  $serializer->serialize($links, 'json',["groups" => "default"] );
        return new JsonResponse($jsonLinks, Response::HTTP_OK, [],  true); 
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
}
