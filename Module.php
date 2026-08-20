<?php

namespace humhub\modules\gtag;

use humhub\modules\gtag\models\Configuration;
use Yii;
use yii\helpers\Url;

/**
 * @property-read Configuration $configuration
 */
class Module extends \humhub\components\Module
{
    /**
     * @var string defines the icon shown on the modules admin page
     */
    public $icon = 'line-chart';

    private ?Configuration $_configuration = null;

    public function getConfiguration(): Configuration
    {
        if ($this->_configuration === null) {
            $this->_configuration = new Configuration(['settingsManager' => $this->settings]);
            $this->_configuration->loadBySettings();
        }

        return $this->_configuration;
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to(['/gtag/config']);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('GtagModule.base', 'Google Analytics');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('GtagModule.base', 'Add Google Analytics (gtag.js) tracking to all pages, with HumHub pjax navigation support.');
    }
}
