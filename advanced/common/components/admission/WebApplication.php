<?php

declare(strict_types=1);

namespace common\components\admission;

/** Enabled adapter only: preserve Yii's end events/response, unwind real finally blocks. */
final class WebApplication extends \yii\web\Application
{
    public function __construct($config = [])
    {
        $errorHandler = $config['components']['errorHandler'] ?? [];
        if (!is_array($errorHandler) || ($errorHandler['class'] ?? \yii\web\ErrorHandler::class) !== \yii\web\ErrorHandler::class) {
            HttpAdmission::unknownResult(); throw new \RuntimeException('Unreviewed Yii error handler component.');
        }
        $session = $config['components']['session'] ?? [];
        if (!is_array($session) || !in_array($session['class'] ?? \yii\web\Session::class, [\yii\web\Session::class, Session::class], true)) {
            HttpAdmission::unknownResult(); throw new \RuntimeException('Unreviewed Yii session component.');
        }
        require_once __DIR__ . '/Session.php';
        $session['class'] = Session::class; $config['components']['session'] = $session;
        parent::__construct($config);
    }

    public function end($status = 0, $response = null)
    {
        HttpAdmission::endRequested();
        if ($this->state === self::STATE_BEFORE_REQUEST || $this->state === self::STATE_HANDLING_REQUEST) {
            $this->state = self::STATE_AFTER_REQUEST; $this->trigger(self::EVENT_AFTER_REQUEST);
        }
        if ($this->state !== self::STATE_SENDING_RESPONSE && $this->state !== self::STATE_END) {
            $this->state = self::STATE_END; ($response ?: $this->getResponse())->send();
        }
        throw new AdmissionEnd('Reviewed Yii request termination.');
    }
}
