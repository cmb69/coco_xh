<?php

use Plib\HtmlString;
use Plib\HtmlView as View;

/**
 * @var View $this
 * @var string $id
 * @var string $name
 * @var string $style
 * @var string $content
 * @var HtmlString|false $editor
 * @var string $csrf_token
 */

?>

<form action="" method="POST">
    <input type="hidden" name="xh_csrf_token" value="<?=$this->esc($csrf_token)?>">
    <textarea id="<?=$this->esc($id)?>" name="coco_text_<?=$this->esc($name)?>" style="<?=$this->esc($style)?>"><?=$this->esc($content)?></textarea>
<?php if ($editor):?>
    <script type="text/javascript"><?=$this->esc($editor)?></script>
<?php else:?>
    <input type="submit" class="submit" value="<?=$this->text("label_save")?>">
<?php endif?>
</form>
