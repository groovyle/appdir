<?php
$display_col = $display_col ?? 'title';
$with_icons = $with_icons ?? true;
?>
{{ $item->$display_col }}
@includeWhen($with_icons, 'admin.ability.components.item-icons')