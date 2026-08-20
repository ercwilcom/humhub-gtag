<?php

use humhub\components\View;
use humhub\modules\gtag\Events;
use humhub\modules\gtag\Module;

return [
    'id' => 'gtag',
    'class' => Module::class,
    'namespace' => 'humhub\\modules\\gtag',
    'events' => [
        ['class' => View::class, 'event' => View::EVENT_BEGIN_PAGE, 'callback' => [Events::class, 'onBeginPage']],
    ],
];
