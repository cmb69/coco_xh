<form action="" method="POST">
    <?=$this->esc($csrfTokenInput)?>
    <textarea id="<?=$this->esc($id)?>" name="coco_text_<?=$this->esc($name)?>" style="<?=$this->esc($style)?>"><?=$this->esc($content)?></textarea>
<?php if ($editor):?>
    <script type="text/javascript"><?=$this->esc($editor)?></script>
<?php else:?>
    <input type="submit" class="submit" value="<?=$this->esc($saveLabel)?>">
<?php endif?>
</form>
