<?php

namespace statikbe\cookiebanner\variables;


use craft\web\View;
use statikbe\cookiebanner\assetbundles\cookiebanner\CookieBannerAsset;

class CookieBannerVariable
{
    public $supportIE = false;

    public function render($settings = [])
    {
        if ($this->isBot()) {
            return false;
        }

        if (isset($settings['supportIE']) && !empty($settings['supportIE'])) {
            $this->supportIE = $settings['supportIE'];
        }

        $modal = 'cookie-banner/_modal';
        $banner = 'cookie-banner/_banner';


        try {

            if (isset($settings['modal']) && !empty($settings['modal'])) {
                $modal = $settings['modal'];
                echo \Craft::$app->getView()->renderTemplate($modal, [], View::TEMPLATE_MODE_SITE);
            } else {
                echo \Craft::$app->getView()->renderTemplate($modal, [], View::TEMPLATE_MODE_CP);

            }

            if (isset($settings['banner']) && !empty($settings['banner'])) {
                $banner = $settings['banner'];
                echo \Craft::$app->getView()->renderTemplate($banner, [], View::TEMPLATE_MODE_SITE);
            } else {
                echo \Craft::$app->getView()->renderTemplate($banner, [], View::TEMPLATE_MODE_CP);
            }

            if (isset($settings['overlay']) && !empty($settings['overlay'])) {
                echo \Craft::$app->getView()->renderString($settings['overlay'], [], View::TEMPLATE_MODE_SITE);
            }

            \Craft::$app->getView()->registerAssetBundle(CookieBannerAsset::class, View::POS_END);

        } catch (\Exception $e) {
            \Craft::error($e->getMessage(), 'cookie-banner');
            return false;
        }

    }

    private function isBot($userAgent = '/bot|crawl|facebook|google|slurp|spider|mediapartners/i')
    {
        try {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                return $_SERVER['HTTP_USER_AGENT'] && preg_match($userAgent, $_SERVER['HTTP_USER_AGENT']);
            }
            return false;
        } catch (\Exception $e) {
            \Craft::error($e->getMessage(), 'cookie-banner');
            return false;
        }
    }
}