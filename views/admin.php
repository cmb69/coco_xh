<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.0 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var list<string> $old_cocos
 * @var list<string> $cocos
 */
?>
<!-- coco administration -->
<h1>Coco – <?=$this->text('menu_main')?></h1>
<?if ($old_cocos):?>
<form id="coco_admin_cocos" method="get">
  <input type="hidden" name="selected" value="coco"/>
  <input type="hidden" name="admin" value="plugin_main"/>
  <table>
    <tr>
      <th><?=$this->text('label_old_coco')?></th>
    </tr>
<?  foreach ($old_cocos as $coco):?>
    <tr>
      <td>
        <label>
          <input type="checkbox" name="coco_name[]" value="<?=$this->esc($coco)?>">
          <span><?=$this->esc($coco)?></span>
        </label>
      </td>
    </tr>
<?  endforeach?>
  </table>
  <p><button name="action" value="migrate"><?=$this->text('label_migrate')?></button></p>
</form>
<?endif?>
<form id="coco_admin_cocos" method="get">
  <input type="hidden" name="selected" value="coco"/>
  <input type="hidden" name="admin" value="plugin_main"/>
  <table>
    <tr>
      <th><?=$this->text('label_coco')?></th>
    </tr>
<?foreach ($cocos as $coco):?>
    <tr>
      <td>
        <label>
          <input type="checkbox" name="coco_name[]" value="<?=$this->esc($coco)?>">
          <span><?=$this->esc($coco)?></span>
        </label>
      </td>
    </tr>
<?endforeach?>
  </table>
  <p><button name="action" value="delete"><?=$this->text('label_delete')?></button></p>
</form>
