<?php

namespace statikbe\cookiebanner\controllers;

use Craft;
use craft\web\Controller;
use statikbe\cookiebanner\records\CookieTrackingRecord;

class CookieTrackingController extends Controller
{
    protected array|int|bool $allowAnonymous = ['add-choice-to-database'];

    public function beforeAction($action): bool
    {
        if ($action->id === 'add-choice-to-database') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public static function actionAddChoiceToDatabase(): bool
    {
        $req = \Craft::$app->request;
        if (empty($req->getBodyParam('response'))) {
            return false;
        }

        $siteId = Craft::$app->sites->currentSite->id;
        $currentDateSection = (new \DateTime())->format('Y-m');

        $choice = $req->getBodyParam('response');

        $cookieTrackingRecord = CookieTrackingRecord::find()->where(['siteId' => $siteId, 'sectionDate' => $currentDateSection])->one();

        if ($cookieTrackingRecord === null) {
            $cookieTrackingRecord = new CookieTrackingRecord();
            $cookieTrackingRecord->accept = 0;
            $cookieTrackingRecord->deny = 0;
            $cookieTrackingRecord->settings = 0;
            $cookieTrackingRecord->siteId = $siteId;
            $cookieTrackingRecord->sectionDate = $currentDateSection;
        }

        switch ($choice) {
            case 'accept':
                $cookieTrackingRecord->accept = $cookieTrackingRecord->accept + 1;
                break;
            case 'deny':
                $cookieTrackingRecord->deny = $cookieTrackingRecord->deny + 1;
                break;
            case 'settings':
                $cookieTrackingRecord->settings = $cookieTrackingRecord->settings + 1;
                break;
        }

        try {
            return $cookieTrackingRecord->save();
        } catch (\Exception $exception) {
            return false;
        }
    }
}
