<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Registers the classification tag used by the “做中学” content series.
 *
 * Source context:
 * https://hudong.moe.gov.cn/jyb_xwfb/gzdt_gzdt/s5987/202608/t20260804_1446039.html
 */
class m261012_000001_add_learning_by_doing_classify_tag extends Migration
{
    private const TAG_NAME = '做中学';
    // “learning-by-doing” means learning through hands-on practice.
    private const TAG_KEY = 'learning-by-doing';

    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%tags}}', true);
        if ($table === null || !isset($table->columns['name'], $table->columns['key'])) {
            throw new \RuntimeException("The tags table must have name and key columns.");
        }

        $existing = (new Query())
            ->from('{{%tags}}')
            ->where([
                'or',
                ['name' => self::TAG_NAME],
                ['key' => self::TAG_KEY],
            ])
            ->all($this->db);
        if ($existing !== []) {
            if (count($existing) !== 1
                || $existing[0]['name'] !== self::TAG_NAME
                || $existing[0]['key'] !== self::TAG_KEY) {
                throw new \RuntimeException('An existing tag conflicts with the learning-by-doing classification.');
            }
            $this->printVerifiedTag($existing[0]);
            return;
        }

        $this->insert('{{%tags}}', [
            'name' => self::TAG_NAME,
            'key' => self::TAG_KEY,
        ]);

        $tag = (new Query())
            ->select(['id', 'name', 'key'])
            ->from('{{%tags}}')
            ->where(['name' => self::TAG_NAME, 'key' => self::TAG_KEY])
            ->one($this->db);
        if ($tag === false) {
            throw new \RuntimeException('The learning-by-doing classification could not be verified after insertion.');
        }
        $this->printVerifiedTag($tag);
    }

    private function printVerifiedTag(array $tag): void
    {
        echo sprintf(
            "Verified tag: id=%s, name=%s, key=%s\n",
            $tag['id'] ?? '(unknown)',
            $tag['name'],
            $tag['key']
        );
    }

    public function safeDown()
    {
        echo "The 做中学 tag is a shared catalog entry and cannot be removed automatically.\n";
        return false;
    }
}
