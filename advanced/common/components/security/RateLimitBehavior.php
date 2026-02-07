<?php

namespace common\components\security;

use Yii;
use yii\base\ActionFilter;
use yii\web\Response;
use yii\web\TooManyRequestsHttpException;

/**
 * RateLimitBehavior - Yii2 ActionFilter for API rate limiting.
 *
 * Integrates the RateLimiter component into the controller action lifecycle.
 * Before each action, it checks the rate limit and either allows the request
 * (recording it and setting informational headers) or blocks it with HTTP 429.
 *
 * Configuration example:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'rateLimiter' => [
 *             'class' => \common\components\security\RateLimitBehavior::class,
 *             'rateLimiter' => 'rateLimiter', // component ID or instance
 *             'defaultStrategy' => 'ip',
 *             'actionStrategies' => [
 *                 'login' => 'login',
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * Response headers set on every request:
 *   - X-RateLimit-Limit: Maximum number of requests allowed in the window
 *   - X-RateLimit-Remaining: Number of remaining requests in the current window
 *   - X-RateLimit-Reset: Unix timestamp when the rate limit window resets
 *
 * When rate limit is exceeded:
 *   - HTTP 429 Too Many Requests status
 *   - Retry-After header with seconds until reset
 *   - JSON error response body
 *
 * Implements Requirements 4.1, 4.2, 4.3 from backend-security-hardening spec.
 *
 * @author Kiro AI
 * @since 1.0
 */
class RateLimitBehavior extends ActionFilter
{
    /**
     * @var RateLimiter|string The RateLimiter component instance or application component ID.
     * If a string is provided, it will be resolved via Yii::$app->get().
     */
    public $rateLimiter = 'rateLimiter';

    /**
     * @var string Default rate limit strategy name.
     * Used when no specific strategy is mapped for the current action.
     */
    public $defaultStrategy = 'ip';

    /**
     * @var array Mapping of action IDs to strategy names.
     * Example: ['login' => 'login', 'register' => 'login']
     */
    public $actionStrategies = [];

    /**
     * Resolve the RateLimiter instance.
     *
     * If $rateLimiter is a string, resolves it as a Yii application component ID.
     * If it is already a RateLimiter instance, returns it directly.
     *
     * @return RateLimiter
     * @throws \yii\base\InvalidConfigException if the component cannot be resolved
     */
    protected function resolveRateLimiter(): RateLimiter
    {
        if ($this->rateLimiter instanceof RateLimiter) {
            return $this->rateLimiter;
        }

        if (is_string($this->rateLimiter)) {
            $component = Yii::$app->get($this->rateLimiter);
            if (!$component instanceof RateLimiter) {
                throw new \yii\base\InvalidConfigException(
                    "The '{$this->rateLimiter}' component must be an instance of " . RateLimiter::class
                );
            }
            return $component;
        }

        throw new \yii\base\InvalidConfigException(
            'The rateLimiter property must be a RateLimiter instance or a valid component ID string.'
        );
    }

    /**
     * Determine the rate limit strategy for the given action.
     *
     * Checks the actionStrategies mapping first; falls back to defaultStrategy.
     *
     * @param \yii\base\Action $action The action being executed
     * @return string The strategy name
     */
    protected function getStrategyForAction($action): string
    {
        $actionId = $action->id;

        if (isset($this->actionStrategies[$actionId])) {
            return $this->actionStrategies[$actionId];
        }

        return $this->defaultStrategy;
    }

    /**
     * Determine the identifier for rate limiting.
     *
     * Uses the authenticated user's ID if available, otherwise falls back
     * to the client's IP address.
     *
     * @return string The rate limit identifier
     */
    protected function getIdentifier(): string
    {
        if (!Yii::$app->user->isGuest) {
            return 'user_' . Yii::$app->user->id;
        }

        return Yii::$app->request->getUserIP() ?: '127.0.0.1';
    }

    /**
     * {@inheritdoc}
     *
     * Checks the rate limit before the action executes.
     * If the limit is not exceeded, records the request and sets informational headers.
     * If the limit is exceeded, sends an HTTP 429 response with Retry-After header.
     *
     * @param \yii\base\Action $action The action to be executed
     * @return bool Whether the action should continue to run
     */
    public function beforeAction($action)
    {
        $limiter = $this->resolveRateLimiter();
        $strategy = $this->getStrategyForAction($action);
        $identifier = $this->getIdentifier();

        $strategyConfig = $limiter->getStrategy($strategy);
        $limit = $strategyConfig['limit'];

        $allowed = $limiter->checkLimit($identifier, $strategy);

        if (!$allowed) {
            $remaining = 0;
            $resetTime = (int) $limiter->getResetTime($identifier, $strategy);
            $now = time();
            $retryAfter = max(1, $resetTime - $now);

            $this->setRateLimitHeaders($limit, $remaining, $resetTime);

            return $this->sendTooManyRequestsResponse($retryAfter);
        }

        // Record the request
        $limiter->recordRequest($identifier, $strategy);

        // Set informational headers
        $remaining = $limiter->getRemainingRequests($identifier, $strategy);
        $resetTime = (int) $limiter->getResetTime($identifier, $strategy);

        $this->setRateLimitHeaders($limit, $remaining, $resetTime);

        return parent::beforeAction($action);
    }

    /**
     * Set rate limit informational headers on the response.
     *
     * @param int $limit     The maximum number of requests allowed
     * @param int $remaining The number of remaining requests
     * @param int $resetTime Unix timestamp when the window resets
     */
    protected function setRateLimitHeaders(int $limit, int $remaining, int $resetTime): void
    {
        $response = Yii::$app->response;
        $response->headers->set('X-RateLimit-Limit', (string) $limit);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
        $response->headers->set('X-RateLimit-Reset', (string) $resetTime);
    }

    /**
     * Send an HTTP 429 Too Many Requests response.
     *
     * Sets the Retry-After header and sends a JSON error body.
     * Returns false to prevent the action from executing.
     *
     * @param int $retryAfter Seconds until the client should retry
     * @return bool Always returns false
     */
    protected function sendTooManyRequestsResponse(int $retryAfter): bool
    {
        $response = Yii::$app->response;
        $response->setStatusCode(429, 'Too Many Requests');
        $response->headers->set('Retry-After', (string) $retryAfter);
        $response->format = Response::FORMAT_JSON;
        $response->data = [
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ],
        ];

        return false;
    }
}
