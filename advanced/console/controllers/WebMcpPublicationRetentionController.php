<?php

namespace console\controllers;

use api\modules\v1\services\PublicationArchive;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\Json;

/** Read-only pre-deployment inventory. No apply/delete action or schema migration. */
final class WebMcpPublicationRetentionController extends Controller
{
    /** Preview the next publication for up to 100 scenes; paginate using nextAfter. */
    public function actionPlan(int $after = 0, int $limit = 100): int
    {
        if ($after < 0 || $limit < 1 || $limit > 100) {
            throw new \InvalidArgumentException('after must be >= 0 and limit must be 1..100');
        }
        $db = \api\modules\v1\models\Verse::getDb();
        $result = $db->useMaster(function () use ($after, $limit, $db) {
            $ids = (new Query())->cache(false)->select('scene_id')->distinct()->from(PublicationArchive::TABLE)
                ->where(['>', 'scene_id', $after])->andWhere(['>', 'byte_length', 0])
                ->orderBy(['scene_id' => SORT_ASC])->limit($limit + 1)->column($db);
            $more = count($ids) > $limit;
            $ids = array_slice($ids, 0, $limit);
            return ['dryRun' => true, 'retainedVersions' => PublicationArchive::retainedVersions(),
                'scenes' => array_map(static fn ($id) => PublicationArchive::retentionPlan((int) $id, true), $ids),
                'nextAfter' => $more ? (int) end($ids) : null];
        });
        $this->stdout(Json::encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");
        return 0;
    }
}
