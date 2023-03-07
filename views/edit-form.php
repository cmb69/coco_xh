<?php

use Coco\Infra\View;

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

<form action="" method="POST">
    <input type="hidden" name="xh_csrf_token" value="<?=$csrf_token?>">
    <textarea id="<?=$id?>" name="coco_text_<?=$name?>" style="<?=$style?>"><?=$content?></textarea>
<?php if ($editor):?>
    <script type="text/javascript"><?=$editor?></script>
<?php else:?>
    <input type="submit" class="submit" value="<?=$this->text("label_save")?>">
<?php endif?>
</form>
