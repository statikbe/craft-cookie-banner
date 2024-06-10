<?php

namespace statikbe\cookiebanner;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\events\TemplateEvent;
use craft\web\assets\admintable\AdminTableAsset;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use statikbe\cookiebanner\variables\CookieBannerVariable;
use statikbe\translate\elements\Translate;
use statikbe\translate\events\RegisterPluginTranslationEvent;
use yii\base\Event;

class CookieBanner extends Plugin
{
    public bool $hasCpSection = true;

    public function init(): void
    {
        parent::init();

        if (Craft::$app->getPlugins()->isPluginEnabled('translate')) {
            Event::on(
            /** @phpstan-ignore-next-line */
                Translate::class,
                /** @phpstan-ignore-next-line */
                Translate::EVENT_REGISTER_PLUGIN_TRANSLATION,
                /** @phpstan-ignore-next-line */
                function(RegisterPluginTranslationEvent $event) {
                    $event->plugins['cookie-banner'] = $this;
                }
            );
        }

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('cookieBanner', CookieBannerVariable::class);
            }
        );

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(View::class, View::EVENT_BEFORE_RENDER_TEMPLATE, function(TemplateEvent $event) {
                Craft::$app->getView()->registerAssetBundle(AdminTableAsset::class);
            });
        }

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['cookie-tracking/add-choice-to-database'] = 'cookie-banner/cookie-tracking/add-choice-to-database';
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['cookie-banner/statistics'] = 'cookie-banner/statistics/render-index';
            $event->rules['cookie-banner/statistics/table-view-site/<siteId:\d+>'] = 'cookie-banner/statistics/table-view-site';
            $event->rules['cookie-banner/statistics/table-view-group/<groupId:\d+>'] = 'cookie-banner/statistics/table-view-group';
        });
    }

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        $item['url'] = 'cookie-banner/statistics';

        return $item;
    }
}
