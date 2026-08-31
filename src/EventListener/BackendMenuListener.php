<?php

namespace Krabo\SnelstartBundle\EventListener;

use Krabo\SnelstartBundle\Controller\CheckConnectionController;
use Contao\CoreBundle\Event\MenuEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @ServiceTag("kernel.event_listener", event="contao.backend_menu_build", priority=-255)
 */
class BackendMenuListener
{
    protected $router;
    protected $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function __invoke(MenuEvent $event): void
    {
        $factory = $event->getFactory();
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        $contentNode = $tree->getChild('system');

        $node = $factory
            ->createItem('snelstart')
            ->setUri($this->router->generate('snelstart_check'))
            ->setLabel('Snelstart')
            ->setLinkAttribute('title', 'Snelstart')
            ->setLinkAttribute('class', 'snelstart')
            ->setCurrent($this->requestStack->getCurrentRequest()->get('_controller') === CheckConnectionController::class)
        ;

        $contentNode->addChild($node);
    }
}
