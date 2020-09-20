<h2>商品を出品する</h2>
<h3>※出品内容を確認してください</h3>
<?= $this->Form->create($biditem, [
    'type' => 'post',
    'url' => [
        'controller' => 'Auction',
        'action' => 'confirm'
    ]
]) ?>
<table class="vertical-table">
    <?= $this->Form->hidden('user_id', ['value' => $biditem->user_id]) ?>
    <tr>
        <th scope="row">商品名</th>
        <td>
            <?= h($biditem->name) ?>
            <?= $this->Form->hidden('name', ['value' => $biditem->name]) ?>
        </td>
    </tr>
    <tr>
        <th scope="row">商品説明</th>
        <td>
            <?= h($biditem->description) ?>
            <?= $this->Form->hidden('description', ['value' => $biditem->description]) ?>
        </td>
    </tr>
    <?= $this->Form->hidden('finished', ['value' => $biditem->finished]) ?>
    <tr>
        <th scope="row">終了時間</th>
        <td>
            <?= h($biditem->endtime['year']) . '/' . h($biditem->endtime['month']) . '/' . h($biditem->endtime['day']) . ' ' . h($biditem->endtime['hour']) . ':' . h($biditem->endtime['minute']); ?>
            <?= $this->Form->hidden('endtime.year', $biditem->endtime) ?>
            <?= $this->Form->hidden('endtime.month', $biditem->endtime) ?>
            <?= $this->Form->hidden('endtime.day', $biditem->endtime) ?>
            <?= $this->Form->hidden('endtime.hour', $biditem->endtime) ?>
            <?= $this->Form->hidden('endtime.minute', $biditem->endtime) ?>
        </td>
    </tr>
    <tr>
        <th scope="row">出品画像</th>
        <td>
            <?= $this->Html->image('uploaded/' . $biditem->image, ['width' => 300, 'height' => 200]) ?>
            <?= $this->Form->hidden('image', ['value' => $biditem->image]) ?>
        </td>
    </tr>
</table>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>
<h6><?= $this->Html->link(__('<< 修正する'), ['action' => 'add', '?' => ['additem' => 'rewrite']]) ?></h6>
