<?php

use Coco\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.0 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var list<string> $cocos
 */
?>
<!-- coco administration -->
<h1>Coco – <?=$this->text('menu_main')?></h1>
<form id="coco_admin_cocos" method="get">
  <input type="hidden" name="selected" value="coco"/>
  <input type="hidden" name="admin" value="plugin_main"/>
  <table>
    <tr>
      <th>Coco</th>
    </tr>
<?foreach ($cocos as $coco):?>
    <tr>
      <td>
        <label>
          <input type="checkbox" name="coco_name[]" value="<?=$coco?>">
          <span><?=$coco?></span>
        </label>
      </td>
    </tr>
<?endforeach?>
  </table>
  <p><button name="action" value="delete"><?=$this->text('label_delete')?></button></p>
</form>
