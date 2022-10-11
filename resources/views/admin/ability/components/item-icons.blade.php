@includeWhen(isset($item->pivot), 'admin.ability.components.item-pivot-icons', ['pivot' => $item->pivot])
@if($item instanceof \App\Models\Ability)
@if($item->only_owned)
<span class="fas fa-user-edit text-info ml-1" title="{{ __('admin/abilities.details.only_owned') }}" data-toggle="tooltip"></span>
@endif
@endif