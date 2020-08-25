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
    <tr>
        <th scope="row">商品名</th>
        <td><?= h($biditem->name) ?></td>
    </tr>
    <tr>
        <th scope="row">商品説明</th>
        <td><?= h($biditem->description) ?></td>
    </tr>
    <tr>
        <th scope="row">終了時間</th>
        <td><?= h($biditem->endtime) ?></td>
    </tr>
</table>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>
