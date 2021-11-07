<?php

use Coco\HtmlString;
use Coco\View;

/**
 * @var View $this
 * @var string $id
 * @var string $name
 * @var string $style
 * @var string $content
 * @var HtmlString|false $editor
 * @var string $saveLabel
 * @var HtmlString $csrfTokenInput
 */

?>

<form action="" method="POST">
    <?=$this->esc($csrfTokenInput)?>
    <textarea id="<?=$this->esc($id)?>" name="coco_text_<?=$this->esc($name)?>" style="<?=$this->esc($style)?>"><?=$this->esc($content)?></textarea>
<?php if ($editor):?>
    <script type="text/javascript"><?=$this->esc($editor)?></script>
<?php else:?>
    <input type="submit" class="submit" value="<?=$this->esc($saveLabel)?>">
<?php endif?>
</form>
