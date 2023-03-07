<?php

use Coco\Infra\View;

/**
 * @var View $this
 * @var list<array{key:string,arg:string}> $errors
 * @var string $csrf_token
 * @var list<string> $cocos
 */
?>
<!-- coco confirmation -->
<?foreach ($errors as $error):?>
<p class="xh_fail"><?=$this->text($error['key'], $error['arg'])?></p>
<?endforeach?>
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
