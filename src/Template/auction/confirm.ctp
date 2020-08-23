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
    <?= $this->Form->hidden('user_id', ['value' => $authuser['id']]) ?>
    <tr>
        <th scope="row">商品名</th>
        <?= $this->Form->hidden('name') ?>
        <td><?= h($biditem->name) ?></td>
    </tr>
    <tr>
        <th scope="row">商品説明</th>
        <?= $this->Form->hidden('description') ?>
        <td><?= h($biditem->description) ?></td>
    </tr>
    <?= $this->Form->hidden('finished', ['value' => 0]) ?>
    <tr>
        <th scope="row">終了時間</th>
        <?= $this->Form->hidden('endtime') ?>
        <td><?= h($biditem->endtime) ?></td>
    </tr>
</table>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>
