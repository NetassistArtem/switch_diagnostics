<?php
$cable_test = array(

    'Eltex' => array(
        'full' => array(
            0 => 'unknown',
            1 => '4_pair_cable',
            2 => '2_pair_cable',
            3 => 'no_cable',
            4 => 'open_cable',
            5 => 'short_cable',
            6 => 'bad_cable',
            7 => 'impedance_mismatch',
        ),
        'pairs' => array(
            0 => 'test-failed',
            1 => 'ok',
            2 => 'open',
            3 => 'short',
            4 => 'impedance-mismatch',
            5 => 'short-with-pair-1',
            6 => 'short-with-pair-2',
            7 => 'short-with-pair-3',
            8 => 'short-with-pair-4',
        ),
    ),
    'Huawei' => array(
        'full' => array(
            1 => 'normal',//'normal',
            2 => 'abnormalOpen',//'обрыв',
            3 => 'abnormalShort',//'замыкание',
            4 => 'abnormalOpenShort',//'замыкание или обрыв',
            5 => 'abnormalCrossTalk',//'перепутаны пары',
            6 => 'unknown',//'неизвестно',
            7 => 'notSupport',//'не поддерживается',
        ),
        'pairs' => array(
            1 => 'normal',//'normal',
            2 => 'abnormalOpen',//'обрыв',
            3 => 'abnormalShort',//'замыкание',
            4 => 'abnormalOpenShort',//'замыкание или обрыв',
            5 => 'abnormalCrossTalk',//'перепутаны пары',
            6 => 'unknown',//'неизвестно',
            7 => 'notSupport',//'не поддерживается',
        )

    ),
    'D-Link' => array(
        'full' => array(
            0 => 'ok',
            1 => 'open',
            2 => 'short',
            3 => 'open-short',
            4 => 'crosstalk',
            5 => 'unknown',
            6 => 'count',
            7 => 'no-cable',
            8 => 'other',
        ),
        'pairs' => array(
            0 => 'ok',
            1 => 'open',
            2 => 'short',
            3 => 'open-short',
            4 => 'crosstalk',
            5 => 'unknown',
            6 => 'count',
            7 => 'no-cable',
            8 => 'other',
        )

    ),
    'Edge-Core' => array(
        'full' => array(
            0 => 'ok',
            1 => 'open',
            2 => 'short',
            3 => 'open-short',
            4 => 'crosstalk',
            5 => 'unknown',
            6 => 'count',
            7 => 'no-cable',
            8 => 'other',
        ),
        'pairs' => array(
            0 => 'ok',
            1 => 'open',
            2 => 'short',
            3 => 'open-short',
            4 => 'crosstalk',
            5 => 'unknown',
            6 => 'count',
            7 => 'no-cable',
            8 => 'other',
        )

    )
);

$duplex = array(
    1 => 'unknown',
    2 => 'halfDuplex',
    3 => 'fullDuplex'
);
$tp_link = array(
    '8 Giga + 1 SFP ports managed switch w/WebView' => 'TP-Link TL-SG3109',
    '24-Port Managed 10/100 Switch w/WebView' => 'TP-Link TL-SL3428'
);