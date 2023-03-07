<?php

use Coco\Infra\View;

/**
 * @var View $this
 * @var string $logo
 * @var string $version
 * @var array<array{state:string,key:string,arg:string,state_key:string}> $checks
 */
?>
<!-- coco info -->
<h1>Coco <?=$version?></h1>
<div class="coco_syscheck">
  <h2><?=$this->text('syscheck_title')?></h2>
<?foreach ($checks as $check):?>
  <p class="xh_<?=$check["state"]?>"><?=$this->text($check['key'], $check['arg'])?> <?=$this->text($check['state_key'])?></p>
<?endforeach?>
</div>
