<?php

namespace Krabo\SnelstartBundle\EventListener;

use Contao\Database;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\Routing\RouterInterface;

class ProductCollectionListener {

    protected $router;
    protected $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * Return the shipping button if a shipping method is available
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function viewSnelstartStatusButton($row, $href, $label, $title, $icon, $attributes)
    {
        $url = $this->router->generate('snelstart_order_status', ['order_id' => $row['id']]);
        if ($row['snelstart_sync_date'] > 0) {
            $icon = 'ok.gif';
            if (empty($row['snelstart_id'])) {
                $icon = 'error.gif';
            }
            return  '<a href="' . $url . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' ;
        }
        $icon = 'help.gif';
        return  '<a href="' . $url . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' ;
    }

    /**
     * @param \Contao\DataContainer $dc
     */
    public function loadSnelstartFilter(\Contao\DataContainer $dc) {
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');
        $session = $objSessionBag->all();
        $field = 'snelstart_sync_date';
        $filter = ($GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['mode'] == 4) ? $dc->table . '_' . CURRENT_ID : $dc->table;
        $snelstart_sync_id = Database::quoteIdentifier('snelstart_id');
        $snelstart_sync_date = Database::quoteIdentifier('snelstart_sync_date');
        if (isset($session['filter'][$filter][$field]) && $session['filter'][$filter][$field] === "0")
        {
            $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['filter'][] = "(".$snelstart_sync_id."= '')";
        } elseif (isset($session['filter'][$filter][$field]) && $session['filter'][$filter][$field] === "1")
        {
            $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['filter'][] = "(".$snelstart_sync_id."!= '')";
        } elseif (isset($session['filter'][$filter][$field]) && $session['filter'][$filter][$field] === "2")
        {
            $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['filter'][] = "(".$snelstart_sync_id."= '' AND ".$snelstart_sync_date." IS NOT NULL)";
        }
    }

    /**
     * @param \Contao\DataContainer $dc
     */
    public function snelstartFilter(\Contao\DataContainer $dc) {
        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');
        $session = $objSessionBag->all();
        $field = 'snelstart_sync_date';
        $filter = ($GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['mode'] == 4) ? $dc->table . '_' . CURRENT_ID : $dc->table;
        if (Input::post('FORM_SUBMIT') == 'tl_filters' && Input::post($field, true) != 'tl_' . $field) {
            $session['filter'][$filter][$field] = Input::post($field, true);
            $objSessionBag->replace($session);
        } elseif (Input::post('FORM_SUBMIT') == 'tl_filters' && isset($session['filter'][$filter][$field])) {
            unset($session['filter'][$filter][$field]);
            $objSessionBag->replace($session);
        }
        $selectedValue = '';
        if (isset($session['filter'][$filter][$field])) {
            $selectedValue = $session['filter'][$filter][$field];
        }

        return '
            <div class="tl_filter tl_subpanel">
            <strong>' . $GLOBALS['TL_LANG'][$dc->table][$field.'_filter_header'] . ':</strong>
            <select name="' . $field . '" id="' . $field . '" class="tl_select' . (isset($session['filter'][$filter][$field]) ? ' active' : '') . '">
                <option value="tl_' . $field . '">'.$GLOBALS['TL_LANG'][$dc->table][$field.'_filter_label']['empty'].'</option>
                <option value="tl_' . $field . '">---</option>
                <option value="0" '.($selectedValue == "0" ? ' selected="selected"' : '').'>'.$GLOBALS['TL_LANG'][$dc->table][$field.'_filter_label'][0].'</option>
                <option value="1" '.($selectedValue == "1" ? ' selected="selected"' : '').'>'.$GLOBALS['TL_LANG'][$dc->table][$field.'_filter_label'][1].'</option>
                <option value="2" '.($selectedValue == "2" ? ' selected="selected"' : '').'>'.$GLOBALS['TL_LANG'][$dc->table][$field.'_filter_label'][2].'</option>
            </select>
            </div>';
    }

}
