<?php

namespace statikbe\cookiebanner\variables;


use craft\web\View;
use statikbe\cookiebanner\assetbundles\cookiebanner\CookieBannerAsset;
use statikbe\cookiebanner\assetbundles\cookiebanner\CookieBannerIEAsset;

class CookieBannerVariable
{
    public $supportIE = false;
    public $modal = [
        'template' => 'cookie-banner/_modal',
        'mode' => View::TEMPLATE_MODE_CP
    ];
    public $banner = [
        'template' => 'cookie-banner/_banner',
        'mode' => View::TEMPLATE_MODE_CP
    ];
    public $overlay = 'cookie-banner/_overlay';

    public $assetBundle = CookieBannerAsset::class;

    public function render($settings = [])
    {
        if ($this->isBot()) {
            return false;
        }

        if (isset($settings['supportIE']) && !empty($settings['supportIE'])) {
            $this->supportIE = $settings['supportIE'];
        }

        try {
            
            if (isset($settings['modal']) && !empty($settings['modal'])) {
                $this->modal = ['template' => $settings['modal'], 'mode' => View::TEMPLATE_MODE_SITE];
            }
            echo \Craft::$app->getView()->renderTemplate($this->modal['template'], [], $this->modal['mode']);
            
            
            if(!isset($settings['showCookieBanner']) || $settings['showCookieBanner']){
                if (isset($settings['banner']) && !empty($settings['banner'])) {
                    $this->banner = ['template' => $settings['banner'], 'mode' => View::TEMPLATE_MODE_SITE];
                }
                echo \Craft::$app->getView()->renderTemplate($this->banner['template'], [], $this->banner['mode']);
            }
            
            
            
            if (isset($settings['overlay']) && !empty($settings['overlay'])) {
                echo \Craft::$app->getView()->renderString($settings['overlay'], [], View::TEMPLATE_MODE_SITE);
            } else {
                echo \Craft::$app->getView()->renderTemplate($this->overlay, [], View::TEMPLATE_MODE_CP);
            }
            
            
            if ($this->supportIE && $this->isBot("/Trident/i")) {
                $this->assetBundle = CookieBannerIEAsset::class;
            }
            
            \Craft::$app->getView()->registerAssetBundle($this->assetBundle, View::POS_END);
                
            
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