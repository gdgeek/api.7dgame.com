<?php

namespace api\modules\v1\models;

use api\modules\v1\services\SubtitleResources;
use yii\db\Query;

/**
 * This is the ActiveQuery class for [[Resource]].
 *
 * @see Resource
 */
class ResourceQuery extends \yii\db\ActiveQuery
{
    private bool $includeSubtitleAvailability = false;

    public function withSubtitleAvailability(): self
    {
        $this->includeSubtitleAvailability = true;
        return $this;
    }

    public function populate($rows)
    {
        $resources = parent::populate($rows);
        if (!$this->includeSubtitleAvailability || $this->asArray) {
            return $resources;
        }

        $media = array_filter($resources, static fn (Resource $resource) => in_array($resource->type, ['audio', 'video'], true));
        if ($media === []) {
            return $resources;
        }

        $boundIds = SubtitleResources::enabled()
            ? (new Query())->cache(false)->select('binding.media_resource_id')
                ->from(['binding' => '{{%resource_subtitle}}'])
                ->innerJoin(['media' => '{{%resource}}'], 'media.id = binding.media_resource_id')
                ->innerJoin(['subtitle' => '{{%resource}}'], 'subtitle.id = binding.subtitle_resource_id AND subtitle.author_id = media.author_id')
                ->where(['binding.media_resource_id' => array_map(static fn (Resource $resource) => $resource->id, $media),
                    'subtitle.type' => 'subtitle'])
                ->column(Resource::getDb())
            : [];
        $bound = array_fill_keys($boundIds, true);
        foreach ($media as $resource) {
            $resource->hasSubtitle = isset($bound[$resource->id]);
        }
        return $resources;
    }

    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Resource[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Resource|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
