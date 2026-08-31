<?php

namespace Krabo\SnelstartBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Isotope\Model\ProductCollection\Order;
use Krabo\SnelstartBundle\Contao\Backend;
use Krabo\SnelstartBundle\Helper\IsotopeOrderSync;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route(defaults={"_scope" = "backend", "_token_check" = true})
 */
class IsotopeOrderSynController extends AbstractController{

    /**
     * @var IsotopeOrderSync
     */
    private $sync;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * CheckConnectionController constructor.
     *
     * @param IsotopeOrderSync $orderSync
     */
    public function __construct(IsotopeOrderSync $orderSync, RouterInterface $router) {
        $this->sync = $orderSync;
        $this->router = $router;
    }

    /**
     * @Route(
     *     "/contao/snelstart/isotope_ordersync/{order_id<\d+>}",
     *     name="snelstart_isotope_ordersync",
     *     methods={"GET"}
     * )
     */
    public function sync($order_id): Response
    {
        $order = Order::findOneBy('id', $order_id);
        //if (!$order->snelstart_id) {
            $verkoopBoeking = $this->sync->syncOrder($order, TRUE);
        //}
        $url = $this->router->generate('snelstart_order_status', ['order_id' => $order_id]);
        return new RedirectResponse($url);

    }
}
