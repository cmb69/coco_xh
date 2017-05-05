<form action="" method="POST">
    <?=$this->csrfTokenInput()?>
    <textarea id="<?=$this->id()?>" name="coco_text_<?=$this->name()?>" style="<?=$this->style()?>"><?=$this->content()?></textarea>
<?php if ($this->editor):?>
    <script type="text/javascript"><?=$this->editor()?></script>
<?php else:?>
    <input type="submit" class="submit" value="<?=$this->saveLabel()?>">
<?php endif?>
</form>
