<?php

use Coco\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.0 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $version
 * @var array<array{class:string,message:string}> $checks
 */
?>
<!-- coco info -->
<h1>Coco <?=$version?></h1>
<div class="coco_syscheck">
  <h2><?=$this->text('syscheck_title')?></h2>
<?foreach ($checks as $check):?>
  <p class="<?=$check['class']?>"><?=$check['message']?></p>
<?endforeach?>
</div>
