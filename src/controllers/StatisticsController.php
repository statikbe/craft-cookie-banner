<?php

namespace statikbe\cookiebanner\controllers;

use craft\web\Controller;
use statikbe\cookiebanner\records\CookieTrackingRecord;
use yii\web\Response;
use function array_pop;

class StatisticsController extends Controller
{
    protected int|bool|array $allowAnonymous = [];

    public function actionRenderIndex(int $siteId = 0): Response
    {
        // INFO: we invent empty site to represent all sites
        $site = ['name' => 'All sites', 'id' => 0, 'handle' => 'all'];

        if ($siteId) {
            $site = \Craft::$app->sites->getSiteById($siteId);
        }

        $records = CookieTrackingRecord::find()->orderBy('sectionDate DESC')->all();

        $acceptedCookies = 0;
        $deniedCookies = 0;
        $settingsCookies = 0;
        /** @var CookieTrackingRecord $record */
        foreach($records as $record) {
            if ($siteId && $siteId !== $record->siteId) {
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
            $firstRecordDate= \DateTimeImmutable::createFromFormat('Y-m', array_pop($records)->sectionDate)->format('F Y');
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

        return $this->renderTemplate('cookie-banner/_cp/_statistics', $context);
    }

    public function actionTableView(int $siteId, int $page = 1): Response
    {
        $rows = [];
        $records = CookieTrackingRecord::find()->orderBy('sectionDate DESC')->all();

        /** @var CookieTrackingRecord $record */
        foreach ($records as $record) {
            if ($siteId != 0 && $siteId !== $record->siteId) {
                continue;
            }

            $title = \DateTimeImmutable::createFromFormat('Y-m', $record->sectionDate)->format('F Y');
            if($siteId === 0) {
                $siteName = \Craft::$app->sites->getSiteById($record->siteId)->name;
                $title = $title . ' - ' . $siteName;
            }
            $total = $record->accept + $record->deny + $record->settings;

            $row = [
                'title' => $title,
                'accepted' => $record->accept . " ({$this->getPercentage($record->accept, $total)}%)",
                'denied' => $record->deny . " ({$this->getPercentage($record->deny, $total)}%)",
                'settings' => $record->settings . " ({$this->getPercentage($record->settings, $total)}%)",
            ];
            $rows[] = $row;
        }

        return $this->returnAdminTableResult($rows, sprintf('cookie-banner/statistics/table-view/%s', $siteId), $page);
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
            'data' => $rows
        ]);
    }

    private function getPercentage($a, $b)
    {
        return round((($a / $b) * 100));
    }
}
