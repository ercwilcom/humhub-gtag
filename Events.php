<?php

namespace humhub\modules\gtag;

use humhub\modules\gtag\models\Configuration;
use Yii;
use yii\helpers\Json;
use yii\web\View;

class Events
{
    /**
     * Injects the Google Analytics (gtag.js) snippet into the page <head>.
     *
     * The base snippet is rendered once on full page loads. HumHub navigates
     * between pages via pjax (Ajax), which does not re-run the layout head, so a
     * single delegated `pjax:end` listener sends a page_view for each in-app
     * navigation.
     *
     * When the consent banner is enabled, gtag.js is NOT loaded until the
     * visitor explicitly accepts; the choice is remembered in a cookie.
     */
    public static function onBeginPage($event)
    {
        // Only inject on real web requests (skip console / mail rendering).
        if (!(Yii::$app instanceof \yii\web\Application)) {
            return;
        }

        /** @var Module|null $module */
        $module = Yii::$app->getModule('gtag');
        if ($module === null) {
            return;
        }

        $config = $module->getConfiguration();

        if (!$config->enabled || $config->measurementId === null || trim($config->measurementId) === '') {
            return;
        }

        // Optionally don't track logged-in admins, to keep team activity out of the stats.
        if ($config->excludeAdmins
            && !Yii::$app->user->isGuest
            && Yii::$app->user->getIdentity() !== null
            && Yii::$app->user->isAdmin()
        ) {
            return;
        }

        $id = trim($config->measurementId);

        /** @var View $view */
        $view = $event->sender;

        if ($config->requireConsent) {
            $view->registerJs(self::buildConsentJs($id, $config), View::POS_HEAD, 'gtag-init');
            return;
        }

        // No consent required: load gtag.js immediately.
        $view->registerJsFile(
            'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode($id),
            ['async' => true, 'position' => View::POS_HEAD],
            'gtag-lib',
        );
        $view->registerJs(self::buildDirectJs($id), View::POS_HEAD, 'gtag-init');
    }

    /**
     * Snippet used when no consent is required: gtag.js is loaded by a
     * registerJsFile() tag, this only configures it and wires up pjax tracking.
     */
    private static function buildDirectJs(string $id): string
    {
        $idJs = Json::encode($id);

        return <<<JS
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', {$idJs}, { anonymize_ip: true });
(function bindPjax(){
    if (window.jQuery) {
        jQuery(function (\$) {
            \$(document).off('pjax:end.gtag').on('pjax:end.gtag', function () {
                gtag('event', 'page_view', {
                    page_path: location.pathname + location.search,
                    page_location: location.href,
                    page_title: document.title
                });
            });
        });
    } else {
        setTimeout(bindPjax, 50);
    }
})();
JS;
    }

    /**
     * Self-contained consent-gated snippet. Shows a banner and only loads
     * gtag.js after the visitor clicks "accept"; the decision is stored in a
     * cookie so the banner is not shown again.
     */
    private static function buildConsentJs(string $id, Configuration $config): string
    {
        $idJs = Json::encode($id);
        $textJs = Json::encode((string)$config->consentText);
        $acceptJs = Json::encode((string)$config->consentAcceptLabel);
        $declineJs = Json::encode((string)$config->consentDeclineLabel);
        $privacyUrlJs = Json::encode($config->privacyUrl !== null ? trim($config->privacyUrl) : '');
        $privacyLabelJs = Json::encode((string)$config->privacyLinkLabel);

        return <<<JS
(function () {
    var GA_ID = {$idJs};
    var COOKIE = 'gtag_consent';

    function readCookie() {
        var m = document.cookie.match(/(?:^|;\\s*)gtag_consent=([^;]+)/);
        return m ? m[1] : null;
    }
    function writeCookie(value, days) {
        var d = new Date();
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = COOKIE + '=' + value + '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax';
    }

    function bindPjax() {
        if (window.jQuery) {
            jQuery(function (\$) {
                \$(document).off('pjax:end.gtag').on('pjax:end.gtag', function () {
                    if (window.gtag) {
                        window.gtag('event', 'page_view', {
                            page_path: location.pathname + location.search,
                            page_location: location.href,
                            page_title: document.title
                        });
                    }
                });
            });
        } else {
            setTimeout(bindPjax, 50);
        }
    }

    function loadGA() {
        if (window.__gtagLoaded) { return; }
        window.__gtagLoaded = true;

        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(GA_ID);
        document.head.appendChild(s);

        window.dataLayer = window.dataLayer || [];
        function gtag(){ window.dataLayer.push(arguments); }
        window.gtag = gtag;
        gtag('js', new Date());
        gtag('config', GA_ID, { anonymize_ip: true });
        bindPjax();
    }

    function removeBanner() {
        var el = document.getElementById('gtag-consent');
        if (el && el.parentNode) { el.parentNode.removeChild(el); }
    }

    function showBanner() {
        if (document.getElementById('gtag-consent')) { return; }

        var bar = document.createElement('div');
        bar.id = 'gtag-consent';
        bar.setAttribute('role', 'dialog');
        bar.setAttribute('aria-live', 'polite');
        bar.style.cssText = 'position:fixed;left:0;right:0;bottom:0;z-index:1050;'
            + 'background:#2f3133;color:#fff;padding:14px 18px;'
            + 'box-shadow:0 -2px 8px rgba(0,0,0,.2);font-size:14px;line-height:1.45;'
            + 'display:flex;flex-wrap:wrap;align-items:center;gap:12px;justify-content:center;';

        var msg = document.createElement('span');
        msg.style.cssText = 'flex:1 1 320px;max-width:760px;';
        msg.textContent = {$textJs};

        var privacyUrl = {$privacyUrlJs};
        if (privacyUrl) {
            msg.appendChild(document.createTextNode(' '));
            var link = document.createElement('a');
            link.href = privacyUrl;
            link.textContent = {$privacyLabelJs};
            link.style.color = '#fff';
            link.style.textDecoration = 'underline';
            link.target = '_blank';
            link.rel = 'noopener';
            msg.appendChild(link);
        }

        var btnWrap = document.createElement('span');
        btnWrap.style.cssText = 'display:flex;gap:8px;flex:0 0 auto;';

        var decline = document.createElement('button');
        decline.type = 'button';
        decline.className = 'btn btn-sm';
        decline.style.cssText = 'background:transparent;border:1px solid rgba(255,255,255,.55);color:#fff;';
        decline.textContent = {$declineJs};
        decline.onclick = function () { writeCookie('no', 180); removeBanner(); };

        var accept = document.createElement('button');
        accept.type = 'button';
        accept.className = 'btn btn-primary btn-sm';
        accept.textContent = {$acceptJs};
        accept.onclick = function () { writeCookie('yes', 365); removeBanner(); loadGA(); };

        btnWrap.appendChild(decline);
        btnWrap.appendChild(accept);
        bar.appendChild(msg);
        bar.appendChild(btnWrap);

        if (document.body) {
            document.body.appendChild(bar);
        }
    }

    var consent = readCookie();
    if (consent === 'yes') {
        loadGA();
    } else if (consent !== 'no') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showBanner);
        } else {
            showBanner();
        }
    }
})();
JS;
    }
}
