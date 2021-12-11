<?php

use Plib\HtmlString;
use Plib\HtmlView as View;
use Plib\Url;

/**
 * @var View $this
 * @var HtmlString $csrfTokenInput
 * @var Url $url
 * @var stdClass[] $cocos
 */

?>

<h1>Coco – <?=$this->text('menu_main')?></h1>
<div id="coco_admin_cocos">
    <ul>
<?php foreach ($cocos as $coco):?>
        <li>
            <form action="<?=$this->esc($url->relative())?>" method="POST" onsubmit="return confirm('<?=$this->esc($coco->message)?>')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="coco_name" value="<?=$this->esc($coco->name)?>">
                <button><?=$this->text("label_delete")?></button>
                <?=$this->esc($csrfTokenInput)?>
            </form>
            <?=$this->esc($coco->name)?>
        </li>
<?php endforeach?>
    </ul>
</div>
