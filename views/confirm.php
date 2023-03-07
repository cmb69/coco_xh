<?php

use Coco\Infra\View;

/**
 * @var View $this
 * @var string $action
 * @var string $csrf_token
 * @var list<string> $cocos
 */
?>
<!-- coco confirmation -->
<form action="<?=$action?>" method="post">
  <input type="hidden" name="xh_csrf_token" value="<?=$csrf_token?>">
  <p><?=$this->text('confirm_delete')?></p>
  <ul>
<?foreach ($cocos as $coco):?>
    <li>
      <span><?=$coco?></span>
      <input type="hidden" name="coco_name[]" value="<?=$coco?>">
    </li>
<?endforeach?>
  </ul>
  <p><button name="action" value="delete"><?=$this->text('label_delete')?></button></p>
</form>
