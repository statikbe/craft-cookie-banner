<?php
/**
 * Image Optimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace statikbe\cookiebanner\assetbundles\cookiebanner;

use craft\web\AssetBundle;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class CookieBannerAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@statikbe/cookiebanner/assetbundles/cookiebanner/dist';
        $this->js = [
            'js/cookie.js'
        ];

        $this->css = [
            'css/inert.css'
        ];

        $this->depends = [];

        parent::init();
    }
}
