<?php

/**
 * GDPR consent partial for registration form.
 *
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var cgsmith\user\models\RegistrationForm $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

?>

<?php if ($module->enableGdprConsent && $module->requireGdprConsentBeforeRegistration): ?>
<div class="gdpr-consent-fields">
    <?php if ($module->gdprConsentUrl): ?>
    <p class="text-muted">
        <?= Yii::t('user', 'By registering, you agree to our {privacy_policy}.', [
            'privacy_policy' => Html::a(
                Yii::t('user', 'Privacy Policy'),
                $module->gdprConsentUrl,
                ['target' => '_blank', 'rel' => 'noopener']
            ),
        ]) ?>
    </p>
    <?php endif; ?>

    <?= $form->field($model, 'gdprConsent')->checkbox([
        'label' => Yii::t('user', 'I have read and accept the privacy policy'),
    ]) ?>

    <?= $form->field($model, 'gdprMarketingConsent')->checkbox([
        'label' => Yii::t('user', 'I agree to receive marketing communications (optional)'),
    ]) ?>
</div>
<?php endif; ?>
