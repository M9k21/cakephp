<h2>商品を出品する</h2>
<?= $this->Form->create($biditem, [
    'type' => 'file',
    'url' => [
        'controller' => 'Auction',
        'action' => 'add'
    ]
]) ?>
<fieldset>
    <legend>※出品情報を入力：</legend>
    <?php
    echo $this->Form->hidden('user_id', ['value' => $authuser['id']]);
    echo '<p><strong>USER: ' . h($authuser['username']) . '</strong></p>';
    echo $this->Form->control('name');
    echo $this->Form->input('description', ['rows' => 5, 'label' => 'Description']);
    echo $this->Form->hidden('finished', ['value' => 0]);
    echo $this->Form->control('endtime');
    echo $this->Form->control('image', ['type' => 'file']);
    ?>
</fieldset>
<?= $this->Form->button(__('Confirm')) ?>
<?= $this->Form->end() ?>
