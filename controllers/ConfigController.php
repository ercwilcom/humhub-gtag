<?php

namespace humhub\modules\gtag\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\gtag\models\Configuration;
use humhub\modules\gtag\Module;
use Yii;

class ConfigController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        /** @var Module $module */
        $module = $this->module;

        $model = new Configuration(['settingsManager' => $module->settings]);
        $model->loadBySettings();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
            return $this->refresh();
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
