<?php

use humhub\modules\gtag\models\Configuration;
use humhub\modules\gtag\Module;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use yii\web\View;

/**
 * @var View $this
 * @var Configuration $model
 */

/** @var Module $module */
$module = Yii::$app->getModule('gtag');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= $module->getName() ?></strong>
        <div class="text-body-secondary">
            <?= $module->getDescription() ?>
        </div>
    </div>

    <div class="panel-body">
        <div class="alert alert-info" role="alert">
            <?= Yii::t('GtagModule.config', 'Privacy note: IP anonymization is enabled and HumHub’s own analytics are not affected. Depending on your jurisdiction (e.g. GDPR / Québec Law 25), sending data to Google may require a cookie-consent banner.') ?>
        </div>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'enabled')->checkbox() ?>
        <?= $form->field($model, 'measurementId')->textInput(['maxlength' => true, 'placeholder' => 'G-XXXXXXXXXX']) ?>
        <?= $form->field($model, 'excludeAdmins')->checkbox() ?>

        <hr>
        <h4><?= Yii::t('GtagModule.config', 'Cookie-consent banner') ?></h4>

        <?= $form->field($model, 'requireConsent')->checkbox() ?>
        <?= $form->field($model, 'consentText')->textarea(['rows' => 2]) ?>
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'consentAcceptLabel')->textInput(['maxlength' => true]) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'consentDeclineLabel')->textInput(['maxlength' => true]) ?></div>
        </div>
        <div class="row">
            <div class="col-md-8"><?= $form->field($model, 'privacyUrl')->textInput(['maxlength' => true, 'placeholder' => 'https://…']) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'privacyLinkLabel')->textInput(['maxlength' => true]) ?></div>
        </div>

        <?= Button::save()->submit() ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
