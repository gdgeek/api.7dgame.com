<?php

declare(strict_types=1);

namespace EasyWeChat\Pay;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Support\PrivateKey;
use EasyWeChat\Kernel\Support\PublicKey;
use EasyWeChat\Pay\Contracts\Merchant as MerchantInterface;

class Merchant implements MerchantInterface
{
    /**
     * @var array<string, PublicKey>
     */
    protected array $platformCerts = [];

    /**
     * @param  array<int|string, string|PublicKey>  $platformCerts
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function __construct(
        protected int | string $mchId,
        protected PrivateKey $privateKey,
        protected PublicKey $certificate,
        protected string $secretKey,
        protected ?string $v2SecretKey = null,
        array $platformCerts = [],
    ) {
        $this->platformCerts = $this->normalizePlatformCerts($platformCerts);
    }

    public function getMerchantId(): int
    {
        return \intval($this->mchId);
    }

    public function getPrivateKey(): PrivateKey
    {
        return $this->privateKey;
    }

    public function getCertificate(): PublicKey
    {
        return $this->certificate;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getV2SecretKey(): ?string
    {
        return $this->v2SecretKey;
    }

    public function getPlatformCert(string $serial): ?PublicKey
    {
        return $this->platformCerts[$serial] ?? null;
    }
    function array_is_list(array $array): bool {
        if (empty($array)) {
            return true;
        }

        $current_key = 0;
        foreach ($array as $key => $noop) {
            if ($key !== $current_key) {
                return false;
            }
            ++$current_key;
        }

        return true;
    }

    /**
     * @param  array<array-key, string|PublicKey>  $platformCerts
     *
     * @return array<string, PublicKey>
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    protected function normalizePlatformCerts(array $platformCerts): array
    {
        $certs = [];
        $isList = $this->array_is_list($platformCerts);
        foreach ($platformCerts as $index => $publicKey) {
            if (\is_string($publicKey)) {
                $publicKey = new PublicKey($publicKey);
            }

            if (!$publicKey instanceof PublicKey) {
                throw new InvalidArgumentException('Invalid platform certficate.');
            }

            $certs[$isList ? $publicKey->getSerialNo() : $index] = $publicKey;
        }

        return $certs;
    }
}
