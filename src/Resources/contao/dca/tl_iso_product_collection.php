<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_iso_product_collection']['config']['onload_callback'][] = array('Krabo\SnelstartBundle\EventListener\ProductCollectionListener', 'loadSnelstartFilter');
$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['panelLayout'] = 'snelstart_filter,'.$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['panelLayout'];
$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['panel_callback']['snelstart_filter'] = array('Krabo\SnelstartBundle\EventListener\ProductCollectionListener', 'snelstartFilter');

$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['operations']['view_snelstart_status'] = array
(
    'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['view_snelstart_status'],
    'href'              => 'key=view_snelstart_status',
    'icon'              => 'system/modules/isotope/assets/images/paper-clip.png',
    'button_callback'   => array('Krabo\SnelstartBundle\EventListener\ProductCollectionListener', 'viewSnelstartStatusButton'),
);

$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['snelstart_id'] = array(
    'label'         => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['snelstart_id'],
    'exclude'       => true,
    'search'        => true,
    'readonly'        => true,
    'inputType'     => 'text',
    'eval'          => array('readonly' => TRUE, 'mandatory'=>false, 'tl_class'=>'w50 clr' ),
    'sql'           => "varchar(255) NOT NULL default ''",
);
$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['snelstart_sync_date'] = array(
    'label'         => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['snelstart_sync_date'],
    'exclude'       => true,
    'inputType'     => 'text',
    'filter'        => false,
    'flag'          => 8,
    'readonly'      => true,
    'eval'          => array('readonly' => TRUE, 'rgxp'=>'datim', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
    'sql'           => 'int(10) NULL',
);
$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['snelstart_error_message'] = array(
    'label'         => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['snelstart_error_message'],
    'exclude'       => true,
    'search'        => true,
    'readonly'        => true,
    'inputType'     => 'textarea',
    'eval'          => array('readonly' => TRUE, 'mandatory'=>false, 'tl_class'=>'w50 clr' ),
    'sql'           => "TEXT NOT NULL default ''",
);
$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['snelstart_debug'] = array(
    'label'         => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['snelstart_debug'],
    'exclude'       => true,
    'search'        => true,
    'readonly'        => true,
    'inputType'     => 'textarea',
    'eval'          => array('readonly' => TRUE, 'mandatory'=>false, 'tl_class'=>'w50 clr' ),
    'sql'           => "TEXT NOT NULL default ''",
);