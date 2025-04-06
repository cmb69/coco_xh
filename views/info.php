<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.0 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $version
 * @var list<object{class:string,message:string}> $checks
 */
?>
<!-- coco info -->
<h1>Coco <?=$this->esc($version)?></h1>
<div class="coco_syscheck">
  <h2><?=$this->text('syscheck_title')?></h2>
<?foreach ($checks as $check):?>
  <p class="<?=$this->esc($check->class)?>"><?=$this->esc($check->message)?></p>
<?endforeach?>
</div>
