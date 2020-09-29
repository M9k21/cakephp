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
            <?= h($biditem->endtime) ?>
            <?= $this->Form->hidden('endtime.year', ['value' => $biditem->endtime->year]) ?>
            <?= $this->Form->hidden('endtime.month', ['value' =>  $biditem->endtime->month]) ?>
            <?= $this->Form->hidden('endtime.day', ['value' =>  $biditem->endtime->day]) ?>
            <?= $this->Form->hidden('endtime.hour', ['value' =>   $biditem->endtime->hour]) ?>
            <?= $this->Form->hidden('endtime.minute', ['value' => $biditem->endtime->minute]) ?>
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
