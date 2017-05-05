<h1>Coco – <?=$this->text('menu_main')?></h1>
<div id="coco_admin_cocos">
    <ul>
<?php foreach ($this->cocos as $coco):?>
        <li>
            <form action="<?=$this->url()?>" method="POST" onsubmit="return confirm('<?=$this->escape($coco->message)?>')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="coco_name" value="<?=$this->escape($coco->name)?>">
                <input type="image" src="<?=$this->deleteIcon()?>" alt="<?=$this->alt()?>" title="<?=$this->alt()?>">
                <?=$this->csrfTokenInput()?>
            </form>
            <?=$this->escape($coco->name)?>
        </li>
<?php endforeach?>
    </ul>
</div>
