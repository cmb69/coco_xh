<?php

use Coco\Infra\View;

/**
 * @var View $this
 * @var string $csrf_token
 * @var list<string> $cocos
 */
?>
<!-- coco confirmation -->
<form method="post">
  <input type="hidden" name="xh_csrf_token" value="<?=$csrf_token?>">
  <p><?=$this->text('confirm_delete')?></p>
  <ul>
<?foreach ($cocos as $coco):?>
    <li><?=$coco?></li>
<?endforeach?>
  </ul>
  <p><button name="coco_do" value="delete"><?=$this->text('label_delete')?></button></p>
</form>
