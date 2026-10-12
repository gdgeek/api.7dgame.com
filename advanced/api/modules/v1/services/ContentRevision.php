<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Meta;
use api\modules\v1\models\Verse;
use yii\db\ActiveRecord;

final class ContentRevision
{
    public static function canonical(mixed $value): string
    {
        return json_encode(self::ordered($value), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }

    public static function hash(mixed $value): string
    {
        return 'sha256:' . hash('sha256', self::canonical($value));
    }

    private static function ordered(mixed $value): mixed
    {
        if (is_object($value)) {
            $properties = get_object_vars($value);
            ksort($properties, SORT_STRING);
            return (object) array_map([self::class, 'ordered'], $properties);
        }
        if (!is_array($value)) {
            return $value;
        }
        if (!array_is_list($value)) {
            ksort($value, SORT_STRING);
        }
        return array_map([self::class, 'ordered'], $value);
    }

    public static function of(ActiveRecord $model): string
    {
        $attributes = $model->getAttributes();
        // DB JSON columns and older TEXT columns must describe the same content.
        foreach (['data', 'events', 'info'] as $name) {
            if (isset($attributes[$name]) && is_string($attributes[$name])) {
                try {
                    $attributes[$name] = json_decode($attributes[$name], false, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    // Plain text info remains plain text.
                }
            }
        }
        // Use the same cached relation as expanded API responses, never a later refresh.
        $code = $model instanceof Meta ? $model->metaCode : ($model instanceof Verse ? $model->verseCode : null);
        return self::hash(['attributes' => $attributes, 'code' => $code?->getAttributes()]);
    }
}
