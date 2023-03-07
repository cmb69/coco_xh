<?php

use Coco\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.0 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $id
 * @var string $name
 * @var string $style
 * @var string $content
 * @var string|false $editor
 * @var string $csrf_token
 */
?>
<!-- coco edit -->
<form action="" method="POST">
  <input type="hidden" name="xh_csrf_token" value="<?=$csrf_token?>">
  <textarea id="<?=$id?>" name="coco_text_<?=$name?>" style="<?=$style?>"><?=$content?></textarea>
<?if ($editor):?>
  <script type="text/javascript"><?=$editor?></script>
<?else:?>
  <input type="submit" class="submit" value="<?=$this->text("label_save")?>">
<?endif?>
</form>
