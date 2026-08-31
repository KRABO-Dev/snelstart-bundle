<?php

namespace Krabo\SnelstartBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Isotope\Model\ProductCollection\Order;
use Krabo\SnelstartBundle\Contao\Backend;
use Krabo\SnelstartBundle\Snelstart\Grootboek;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Krabo\SnelstartBundle\Factory;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as TwigEnvironment;

/**
 * @Route(defaults={"_scope" = "backend", "_token_check" = true})
 */
class OrderStatusController extends AbstractController{

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var TwigEnvironment
     */
    private $twig;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * CheckConnectionController constructor.
     *
     * @param Factory $factory
     * @param TwigEnvironment $twigEnvironment
     */
    public function __construct(Factory  $factory, TwigEnvironment $twig, RouterInterface $router) {
        $this->factory = $factory;
        $this->twig = $twig;
        $this->router = $router;
    }

    /**
     * @Route(
     *     "/contao/snelstart/order_status/{order_id<\d+>}",
     *     name="snelstart_order_status",
     *     methods={"GET"}
     * )
     */
    public function status($order_id): Response
    {
        $templateData = [
            'title' => 'Snelstart',
            'error' => false,
            'snelstart_debug' => false,
            'snelstart_date' => false,
            'snelstart_id' => false,
        ];

        $order = Order::findOneBy('id', $order_id);
        $templateData['snelstart_id'] = $order->snelstart_id;
        $templateData['snelstart_date'] = date('d-m-Y H:i:s', $order->snelstart_sync_date);
        $templateData['snelstart_error'] = $order->snelstart_error_message;
        $templateData['snelstart_debug'] = $order->snelstart_debug;
        $templateData['force_sync_url'] = $this->router->generate('snelstart_isotope_ordersync', ['order_id' => $order_id]);

        return new Response($this->twig->render('@Snelstart/order_status.html.twig', $templateData));

    }
}
