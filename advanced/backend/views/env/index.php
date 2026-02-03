<?php
use yii\helpers\Html;

$this->title = '环境变量检测';
$this->params['breadcrumbs'][] = $this->title;

$totalMissing = $missingCount + $emptyCount;
?>

<div class="env-check">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-<?= $totalMissing > 0 ? 'warning' : 'success' ?>">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-<?= $totalMissing > 0 ? 'exclamation-triangle' : 'check-circle' ?>"></i>
                        环境变量检测结果
                    </h3>
                    <div class="box-tools pull-right">
                        <?php if ($totalMissing > 0): ?>
                            <span class="label label-warning"><?= $missingCount ?> 未设置</span>
                            <span class="label label-danger"><?= $emptyCount ?> 必填为空</span>
                        <?php else: ?>
                            <span class="label label-success">全部正常</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="box-body">
                    <?php if ($emptyCount > 0): ?>
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-circle"></i>
                            <strong>警告：</strong>有 <?= $emptyCount ?> 个必填环境变量未设置，系统可能无法正常运行！
                        </div>
                    <?php elseif ($missingCount > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i>
                            <strong>提示：</strong>有 <?= $missingCount ?> 个可选环境变量未设置，将使用默认值
                        </div>
                    <?php endif; ?>

                    <?php foreach ($results as $group => $vars): ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><?= Html::encode($group) ?></h4>
                            </div>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 25%">变量名</th>
                                        <th style="width: 30%">当前值</th>
                                        <th style="width: 30%">说明</th>
                                        <th style="width: 15%">状态</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vars as $var => $info): ?>
                                        <tr class="<?= $info['status'] === 'empty' ? 'danger' : ($info['status'] === 'missing' && $info['required'] ? 'danger' : ($info['status'] === 'missing' ? 'warning' : '')) ?>">
                                            <td>
                                                <code><?= Html::encode($var) ?></code>
                                                <?php if ($info['required']): ?>
                                                    <span class="text-danger">*</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($info['status'] === 'missing'): ?>
                                                    <span class="text-muted">未设置</span>
                                                    <?php if (isset($info['default'])): ?>
                                                        <br><small class="text-info">默认值: <?= Html::encode($info['default']) ?></small>
                                                    <?php endif; ?>
                                                <?php elseif ($info['value'] === ''): ?>
                                                    <span class="text-muted">(空)</span>
                                                <?php else: ?>
                                                    <code><?= Html::encode($info['value']) ?></code>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= Html::encode($info['description']) ?></td>
                                            <td>
                                                <?php if ($info['status'] === 'ok'): ?>
                                                    <span class="label label-success"><i class="fa fa-check"></i> 正常</span>
                                                <?php elseif ($info['status'] === 'missing' && $info['required']): ?>
                                                    <span class="label label-danger"><i class="fa fa-exclamation-circle"></i> 必填未设置</span>
                                                <?php elseif ($info['status'] === 'missing'): ?>
                                                    <span class="label label-warning"><i class="fa fa-question"></i> 未设置</span>
                                                <?php else: ?>
                                                    <span class="label label-danger"><i class="fa fa-times"></i> 必填为空</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($totalMissing > 0): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-clipboard"></i> 缺少的环境变量</h3>
                    </div>
                    <div class="box-body">
                        <p>复制以下内容到 <code>.env</code> 文件：</p>
                        <pre><?php
                            $missing = [];
                            foreach ($results as $group => $vars) {
                                $groupMissing = [];
                                foreach ($vars as $var => $info) {
                                    if ($info['status'] !== 'ok') {
                                        $default = $info['default'] ?? '';
                                        $groupMissing[] = "{$var}={$default}";
                                    }
                                }
                                if (!empty($groupMissing)) {
                                    $missing[] = "# {$group}";
                                    $missing = array_merge($missing, $groupMissing);
                                    $missing[] = "";
                                }
                            }
                            echo Html::encode(implode("\n", $missing));
                        ?></pre>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
