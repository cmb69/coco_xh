<?php

use Coco\Infra\View;

/**
 * @var View $this
 * @var string $csrf_token
 * @var string $url
 * @var list<array{name:string,message:string}> $cocos
 */
?>
<!-- coco administration -->
<h1>Coco – <?=$this->text('menu_main')?></h1>
<div id="coco_admin_cocos">
  <ul>
<?foreach ($cocos as $coco):?>
    <li>
      <form action="<?=$url?>" method="POST" onsubmit="return confirm('<?=$coco['message']?>')">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="coco_name" value="<?=$coco['name']?>">
        <button><?=$this->text("label_delete")?></button>
        <input type="hidden" name="xh_csrf_token" value="<?=$csrf_token?>">
      </form>
      <?=$coco['name']?>
    </li>
<?endforeach?>
  </ul>
</div>
