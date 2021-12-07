<?php

namespace statikbe\cookiebanner;

use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use statikbe\cookiebanner\variables\CookieBannerVariable;
use statikbe\translate\elements\Translate;
use statikbe\translate\events\RegisterPluginTranslationEvent;
use yii\base\Event;

class CookieBanner extends Plugin
{
    public function init()
    {
        parent::init();


        if (\Craft::$app->getPlugins()->isPluginEnabled('translate')) {
            Event::on(
                Translate::class,
                Translate::EVENT_REGISTER_PLUGIN_TRANSLATION,
                function (RegisterPluginTranslationEvent $event) {
                    $event->plugins['cookie-banner'] = $this;
                }
            );
        }

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
