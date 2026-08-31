<?php

namespace Krabo\SnelstartBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use GuzzleHttp\Psr7\Request;
use Krabo\SnelstartBundle\Contao\Backend;
use SnelstartPHP\Connector\V2\LandConnector;
use SnelstartPHP\Request\V2\LandRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Exception\GuzzleException;
use Krabo\SnelstartBundle\Factory;
use Twig\Environment as TwigEnvironment;

/**
 * @Route(defaults={"_scope" = "backend", "_token_check" = true})
 */
class CheckConnectionController extends AbstractController{

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var TwigEnvironment
     */
    private $twig;

    /**
     * CheckConnectionController constructor.
     *
     * @param Factory $factory
     * @param TwigEnvironment $twigEnvironment
     */
    public function __construct(Factory  $factory, TwigEnvironment $twig) {
        $this->factory = $factory;
        $this->twig = $twig;
    }

    /**
     * @Route("/contao/snelstart/check", name="snelstart_check")
     */
    public function check(): Response
    {
        $templateData = [
            'title' => 'Snelstart',
            'error' => false,
            'success' => false,
        ];

        try {
            $connection = $this->factory->getConnection();
            $landRequest = new LandConnector($connection);
            $landen = $landRequest->findAll();
            $templateData['success'] = 'Connected to Snelstart.';

        } catch (GuzzleException $ex) {
            $templateData['error'] = 'Could not connect: ' . $ex->getMessage();
        }
        return new Response($this->twig->render('@Snelstart/check_connection.html.twig', $templateData));

    }
}
