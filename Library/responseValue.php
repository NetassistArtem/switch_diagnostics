<?php
$cable_test = array(

    'ELTEX' => array(
        1 => '4_pair_cable',
        2 => '2_pair_cable',
        3 => 'no_cable',
        4 => 'open_cable',
        5 => 'short_cable',
        6 => 'bad_cable',
        7 => 'impedance_mismatch',
    ),
    'Huawei Technologies'=> array(
        1 => 'normal',//'normal',
        2 => 'abnormalOpen',//'обрыв',
        3 => 'abnormalShort',//'замыкание',
        4 => 'abnormalOpenShort',//'замыкание или обрыв',
        5 => 'abnormalCrossTalk',//'перепутаны пары',
        6 => 'unknown',//'неизвестно',
        7 => 'notSupport',//'не поддерживается',
    ),
);

$duplex = array(
    1 => 'unknown',
    2 => 'halfDuplex',
    3 => 'fullDuplex'
);