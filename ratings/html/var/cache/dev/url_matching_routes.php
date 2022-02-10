<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/_health' => [[['_route' => 'instana_robotshop_ratings_health__invoke', '_controller' => 'Instana\\RobotShop\\Ratings\\Controller\\HealthController'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/api/(?'
                    .'|rate/([^/]++)/([^/]++)(*:37)'
                    .'|fetch/([^/]++)(*:58)'
                .')'
            .')/?$}sD',
    ],
    [ // $dynamicRoutes
        37 => [[['_route' => 'instana_robotshop_ratings_ratingsapi_put', '_controller' => 'Instana\\RobotShop\\Ratings\\Controller\\RatingsApiController::put'], ['sku', 'score'], ['PUT' => 0], null, false, true, null]],
        58 => [
            [['_route' => 'instana_robotshop_ratings_ratingsapi_get', '_controller' => 'Instana\\RobotShop\\Ratings\\Controller\\RatingsApiController::get'], ['sku'], ['GET' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
