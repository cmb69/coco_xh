<?php

use Plib\HtmlView as View;

/**
 * @var View $this
 * @var string $logo
 * @var string $version
 * @var array<array{state:string,key:string,arg:string,state_key:string}> $checks
 */

?>

<h1>Coco <?=$this->esc($version)?></h1>
<div class="coco_syscheck">
    <h2><?=$this->text('syscheck_title')?></h2>
<?php foreach ($checks as $check):?>
    <p class="xh_<?=$this->esc($check["state"])?>"><?=$this->text($check['key'], $check['arg'])?> <?=$this->text($check['state_key'])?></p>
<?php endforeach?>
</div>
