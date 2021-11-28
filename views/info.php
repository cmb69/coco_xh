<?php

use Plib\HtmlView as View;

/**
 * @var View $this
 * @var string $logo
 * @var string $version
 * @var stdClass[] $checks
 */

?>

<h1>Coco <?=$this->esc($version)?></h1>
<div class="coco_syscheck">
    <h2><?=$this->text('syscheck_title')?></h2>
<?php foreach ($checks as $check):?>
    <p class="xh_<?=$this->esc($check->state)?>"><?=$this->text('syscheck_message', $check->label, $check->stateLabel)?></p>
<?php endforeach?>
</div>
