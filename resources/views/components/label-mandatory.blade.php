<?php
$absolute = ($absolute ?? $flying ?? false) ? 'flying' : '';
?><span class="fas fa-asterisk mandatory-icon {{ $absolute }} text-danger" data-toggle="tooltip" title="{{ __('common.field_mandatory') }}"></span>