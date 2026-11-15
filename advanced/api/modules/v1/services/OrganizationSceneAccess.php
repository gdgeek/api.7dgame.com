<?php

namespace api\modules\v1\services;

use api\modules\v1\models\Organization;
use api\modules\v1\models\UserOrganization;
use api\modules\v1\models\Verse;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\web\ForbiddenHttpException;

/** Manager/admin organization membership grants reading only; editing stays with Verse::getEditable(). */
final class OrganizationSceneAccess
{
    public static function requireReader(): int
    {
        $userId = (int) Yii::$app->user->id;
        if ($userId <= 0 || !self::hasReaderRole($userId) || !self::memberships($userId)->exists()) {
            throw new ForbiddenHttpException('Organization scenes require a manager or admin with organization membership');
        }
        return $userId;
    }

    public static function applyScope(ActiveQuery $query, int $userId): void
    {
        $query->andWhere(['exists', self::sharedMembership($userId)->andWhere('scene_member.user_id = verse.author_id')]);
    }

    public static function canView(Verse $verse): bool
    {
        $userId = (int) Yii::$app->user->id;
        return $userId > 0 && self::hasReaderRole($userId)
            && self::sharedMembership($userId)->andWhere(['scene_member.user_id' => (int) $verse->author_id])->exists();
    }

    private static function hasReaderRole(int $userId): bool
    {
        $roles = Yii::$app->authManager->getRolesByUser($userId);
        return isset($roles['manager']) || isset($roles['admin']);
    }

    private static function memberships(int $userId): Query
    {
        return (new Query())->select('scene_viewer.organization_id')
            ->from(['scene_viewer' => UserOrganization::tableName()])
            ->innerJoin(['scene_organization' => Organization::tableName()], 'scene_organization.id = scene_viewer.organization_id')
            ->where(['scene_viewer.user_id' => $userId]);
    }

    private static function sharedMembership(int $userId): Query
    {
        return (new Query())->select('scene_member.user_id')
            ->from(['scene_member' => UserOrganization::tableName()])
            ->andWhere(['scene_member.organization_id' => self::memberships($userId)]);
    }
}
