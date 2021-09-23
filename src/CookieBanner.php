<?php

namespace statikbe\cookiebanner;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use statikbe\cookiebanner\variables\CookieBannerVariable;
use yii\base\Event;

class CookieBanner extends Plugin
{
	 public function init()
    {
        parent::init();

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('cookieBanner', CookieBannerVariable::class);
            }
        );
    }
}
