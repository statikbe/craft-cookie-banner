<?php

namespace statikbe\cookiebanner\controllers;

use Craft;
use craft\fields\Date;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use craft\models\Site;
use craft\web\Controller;
use statikbe\cookiebanner\records\CookieTrackingRecord;
use yii\web\Response;
use function array_pop;

class  StatisticsController extends Controller
{
    protected int|bool|array $allowAnonymous = [];

    public function actionRenderIndex(int $groupId = null, $site = null): Response
    {
        $siteParam = $this->request->getQueryParam('site');
        // INFO: we invent empty site to represent all sites

        $siteIds = null;
        if ($site) {
            $site = \Craft::$app->sites->getSiteByHandle($site);
        } else {
            $site = ['name' => 'All sites', 'id' => 0, 'handle' => 'all'];
        }


        // TODO check persmissions before getting al groups?
        $groups = Craft::$app->getSites()->getAllGroups();
        $allSites = \Craft::$app->getSites()->getAllSites();
        $editableSites = $this->getEditableSites($allSites);


        if ($groupId && ($site != '*' && $site != null)) {
            $sites = [$site];
        } elseif ($groupId) {
            $allSites = \Craft::$app->getSites()->getSitesByGroupId($groupId);
            $sites = $this->getEditableSites($allSites);
        } else {
            $sites = \Craft::$app->getSites()->getEditableSites();
        }

        $siteIds = collect($sites)->pluck('id')->all();
        $records = CookieTrackingRecord::find()->orderBy('sectionDate DESC')->all();
        $acceptedCookies = 0;
        $deniedCookies = 0;
        $settingsCookies = 0;
        /** @var CookieTrackingRecord $record */
        foreach ($records as $record) {
            if ($siteIds && !in_array($record->siteId, $siteIds)) {
                continue;
            }

            $acceptedCookies += $record->accept;
            $deniedCookies += $record->deny;
            $settingsCookies += $record->settings;
        }

        if (empty($records)) {
            $cookiesPresent = false;
            $firstRecordDate = '';
        } else {
            $cookiesPresent = true;
            $firstRecordDate = \DateTimeImmutable::createFromFormat('Y-m', array_pop($records)->sectionDate)->format('F Y');
        }

        $total = $acceptedCookies + $deniedCookies + $settingsCookies;

        $context = [
            'cookiesPresent' => $cookiesPresent,
            'acceptedCookies' => $acceptedCookies . " ({$this->getPercentage($acceptedCookies, $total)}%)",
            'deniedCookies' => $deniedCookies . " ({$this->getPercentage($deniedCookies, $total)}%)",
            'settingCookies' => $settingsCookies . " ({$this->getPercentage($settingsCookies, $total)}%)",
            'selectedSite' => $site,
            'firstRecordDate' => $firstRecordDate,
        ];

        $crumbs = [[
            'label' => Craft::t('app', 'Cookie acceptance statistics'),
            'url' => UrlHelper::cpUrl('cookie-banner/statistics', ['site' => '*']),
        ]];


        if (count($groups) > 1) {
            $crumbs[] = [
                "label" => ($this->request->getQueryParam('groupId') ? Craft::$app->getSites()->getGroupById($this->request->getQueryParam('groupId'))->name : 'All sites'),
                "menu" => [
                    "label" => "Select a site group",
                    "items" => array_merge([[
                        'label' => "All sites",
                        'url' => UrlHelper::cpUrl('cookie-banner/statistics', ['site' => '*']),
                        'selected' => (!$this->request->getQueryParam('groupId')),
                        // TODO check groups for sites to which the user has access
                    ]], array_map(function($group) {
                        return [
                            'label' => $group->name,
                            'selected' => ($this->request->getQueryParam('groupId') == $group->id),
                            'url' => UrlHelper::cpUrl('cookie-banner/statistics', ['groupId' => $group->id, 'site' => '*']),
                        ];
                    }, $groups)),
                ],
            ];
        } else {
            $group = $groups[0];
            $crumbs[] = [
                "label" => $group->name,
                'url' => UrlHelper::cpUrl('cookie-banner/statistics', ['site' => '*', 'groupId' => $group->id]),
            ];
        }


        if ($groupId || count($groups) === 1) {
            $group = Craft::$app->getSites()->getGroupById($groupId ?? $groups[0]->id);
            $crumbs[] = [
                'label' => $site ? $site->name : 'All sites',
                'menu' => [
                    "label" => "Select a site",
                    "items" => array_merge([[
                        "label" => "All sites",
                        "url" => UrlHelper::cpUrl('cookie-banner/statistics', ['groupId' => $group->id, 'site' => '*']),
                    ]], Cp::siteMenuItems($sites, is_object($site) ? $site : null, ['showSiteGroupHeadings' => false])),
                ],
            ];
        }


        return $this->asCpScreen()
            ->title("Cookie acceptance statistics")
            ->crumbs($crumbs)
            ->contentTemplate('cookie-banner/_cp/_stats/_content', $context);
    }

    public function actionTableViewSite(int $siteId, int $page = 1): Response
    {
        if ($siteId === 0) {
            $sites = \Craft::$app->getSites()->getAllSites();
            $sites = $this->getEditableSites($sites);
            $siteIds = collect($sites)->pluck('id')->all();
            $rows = $this->parseDataForTable($siteIds);
        } else {
            $rows = $this->parseDataForTable([$siteId]);
        }

        return $this->returnAdminTableResult($rows, sprintf('cookie-banner/statistics/table-view-site/%s', $siteId), $page);
    }

    public function actionTableViewGroup(int $groupId, int $page = 1): Response
    {
        $sites = \Craft::$app->getSites()->getSitesByGroupId($groupId);
        $sites = $this->getEditableSites($sites);
        $siteIds = collect($sites)->pluck('id')->all();
        $rows = $this->parseDataForTable($siteIds);
        return $this->returnAdminTableResult($rows, sprintf('cookie-banner/statistics/table-view-group/%s', $groupId), $page);
    }


    private function parseDataForTable(array $sites = []): array
    {
        $rows = [];
        $records = CookieTrackingRecord::find()->orderBy('sectionDate DESC')->all();
        /** @var CookieTrackingRecord $record */
        foreach ($records as $record) {
            if ($sites && !in_array($record->siteId, $sites)) {
                continue;
            }

            $date = new \DateTime($record->sectionDate);
            $date->setDate($date->format('Y'), $date->format('m'), 1);
            $title = \DateTimeImmutable::createFromFormat('Y-m', $date->format('Y-m'))->format('F Y');
            $siteName = \Craft::$app->sites->getSiteById($record->siteId)->name;
            $title = $title . ' - ' . $siteName;

            $total = $record->accept + $record->deny + $record->settings;

            $row = [
                'title' => $title,
                'accepted' => $record->accept . " ({$this->getPercentage($record->accept, $total)}%)",
                'denied' => $record->deny . " ({$this->getPercentage($record->deny, $total)}%)",
                'settings' => $record->settings . " ({$this->getPercentage($record->settings, $total)}%)",
            ];
            $rows[] = $row;
        }
        return $rows;
    }

    private function returnAdminTableResult(array $rows, string $baseUrl, int $page): Response
    {
        $total = count($rows);
        $limit = 100;
        $from = ($page - 1) * $limit + 1;
        $lastPage = (int)ceil($total / $limit);
        $to = $page === $lastPage ? $total : ($page * $limit);
        $nextPageUrl = $baseUrl . sprintf('?page=%d', ($page + 1));
        $prevPageUrl = $baseUrl . sprintf('?page=%d', ($page - 1));
        $rows = array_slice($rows, $from - 1, $limit);
        return $this->asJson([
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$limit,
                'current_page' => (int)$page,
                'last_page' => (int)$lastPage,
                'next_page_url' => $nextPageUrl,
                'prev_page_url' => $prevPageUrl,
                'from' => (int)$from,
                'to' => (int)$to,
            ],
            'data' => $rows,
        ]);
    }

    private function getPercentage($a, $b)
    {
        return round((($a / $b) * 100));
    }

    private function getEditableSites(array|Site $sites)
    {
        return array_filter($sites, function($site) {
            return self::currentUser()->can("editSite:{$site->uid}");
        });
    }
}
