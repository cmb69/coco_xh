<?php

use Plib\View;

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
<form method="post">
  <input type="hidden" name="xh_csrf_token" value="<?=$this->esc($csrf_token)?>">
  <textarea id="<?=$this->esc($id)?>" name="coco_text_<?=$this->esc($name)?>" style="<?=$this->esc($style)?>"><?=$this->esc($content)?></textarea>
<?if ($editor):?>
  <script><?=$this->raw($editor)?></script>
<?else:?>
  <input type="submit" class="submit" value="<?=$this->text("label_save")?>">
<?endif?>
</form>
