<?php

namespace humhub\modules\gtag\models;

use humhub\components\SettingsManager;
use Yii;
use yii\base\Model;

class Configuration extends Model
{
    public SettingsManager $settingsManager;

    public bool $enabled = false;
    public ?string $measurementId = '';
    public bool $excludeAdmins = true;

    public bool $requireConsent = true;
    public ?string $consentText = '';
    public ?string $consentAcceptLabel = '';
    public ?string $consentDeclineLabel = '';
    public ?string $privacyUrl = '';
    public ?string $privacyLinkLabel = '';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enabled', 'excludeAdmins', 'requireConsent'], 'boolean'],
            [['measurementId', 'consentText', 'consentAcceptLabel', 'consentDeclineLabel', 'privacyUrl', 'privacyLinkLabel'], 'trim'],
            [['consentText', 'consentAcceptLabel', 'consentDeclineLabel', 'privacyLinkLabel'], 'string', 'max' => 500],
            ['measurementId', 'string', 'max' => 32],
            [
                'measurementId',
                'match',
                'pattern' => '/^(G|UA|AW|GT|GTM)-[A-Z0-9\-]+$/i',
                'skipOnEmpty' => true,
                'message' => Yii::t('GtagModule.config', 'Enter a valid measurement ID, e.g. G-XXXXXXXXXX.'),
            ],
            [
                'measurementId',
                'required',
                'when' => fn ($model) => $model->enabled,
                'whenClient' => false,
                'message' => Yii::t('GtagModule.config', 'A measurement ID is required when tracking is enabled.'),
            ],
            ['privacyUrl', 'url', 'defaultScheme' => 'https', 'skipOnEmpty' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'enabled' => Yii::t('GtagModule.config', 'Enable tracking'),
            'measurementId' => Yii::t('GtagModule.config', 'Measurement ID'),
            'excludeAdmins' => Yii::t('GtagModule.config', 'Do not track logged-in administrators'),
            'requireConsent' => Yii::t('GtagModule.config', 'Show a cookie-consent banner (load Analytics only after the visitor accepts)'),
            'consentText' => Yii::t('GtagModule.config', 'Banner text'),
            'consentAcceptLabel' => Yii::t('GtagModule.config', 'Accept button label'),
            'consentDeclineLabel' => Yii::t('GtagModule.config', 'Decline button label'),
            'privacyUrl' => Yii::t('GtagModule.config', 'Privacy policy URL (optional)'),
            'privacyLinkLabel' => Yii::t('GtagModule.config', 'Privacy policy link label'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'measurementId' => Yii::t('GtagModule.config', 'Your Google Analytics 4 measurement ID, e.g. G-XXXXXXXXXX.'),
            'excludeAdmins' => Yii::t('GtagModule.config', 'Keeps your own team’s activity out of the statistics.'),
            'requireConsent' => Yii::t('GtagModule.config', 'Recommended for GDPR / Québec Law 25 compliance. When off, Analytics loads on every page immediately.'),
            'privacyUrl' => Yii::t('GtagModule.config', 'If set, a link to this page is shown in the banner.'),
        ];
    }

    public function loadBySettings(): void
    {
        $this->enabled = (bool)$this->settingsManager->get('enabled', $this->enabled);
        $this->excludeAdmins = (bool)$this->settingsManager->get('excludeAdmins', $this->excludeAdmins);
        $this->measurementId = $this->settingsManager->get('measurementId', $this->measurementId);

        $this->requireConsent = (bool)$this->settingsManager->get('requireConsent', $this->requireConsent);
        $this->consentText = $this->settingsManager->get('consentText', $this->defaultConsentText());
        $this->consentAcceptLabel = $this->settingsManager->get('consentAcceptLabel', $this->defaultAcceptLabel());
        $this->consentDeclineLabel = $this->settingsManager->get('consentDeclineLabel', $this->defaultDeclineLabel());
        $this->privacyUrl = $this->settingsManager->get('privacyUrl', $this->privacyUrl);
        $this->privacyLinkLabel = $this->settingsManager->get('privacyLinkLabel', $this->defaultPrivacyLinkLabel());
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settingsManager->set('enabled', $this->enabled);
        $this->settingsManager->set('excludeAdmins', $this->excludeAdmins);
        $this->settingsManager->set('measurementId', trim((string)$this->measurementId));

        $this->settingsManager->set('requireConsent', $this->requireConsent);
        $this->settingsManager->set('consentText', trim((string)$this->consentText) !== '' ? trim((string)$this->consentText) : $this->defaultConsentText());
        $this->settingsManager->set('consentAcceptLabel', trim((string)$this->consentAcceptLabel) !== '' ? trim((string)$this->consentAcceptLabel) : $this->defaultAcceptLabel());
        $this->settingsManager->set('consentDeclineLabel', trim((string)$this->consentDeclineLabel) !== '' ? trim((string)$this->consentDeclineLabel) : $this->defaultDeclineLabel());
        $this->settingsManager->set('privacyUrl', trim((string)$this->privacyUrl));
        $this->settingsManager->set('privacyLinkLabel', trim((string)$this->privacyLinkLabel) !== '' ? trim((string)$this->privacyLinkLabel) : $this->defaultPrivacyLinkLabel());

        return true;
    }

    public function defaultConsentText(): string
    {
        return Yii::t('GtagModule.config', 'We use Google Analytics to understand how this site is used. Analytics cookies are only set if you accept.');
    }

    public function defaultAcceptLabel(): string
    {
        return Yii::t('GtagModule.config', 'Accept');
    }

    public function defaultDeclineLabel(): string
    {
        return Yii::t('GtagModule.config', 'Decline');
    }

    public function defaultPrivacyLinkLabel(): string
    {
        return Yii::t('GtagModule.config', 'Learn more');
    }
}
