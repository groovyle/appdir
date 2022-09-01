@if(trim($slot))
<a href="#" class="d-inline-block ml-1" data-toggle="popover" title="@lang('admin/app_verifications.verification_comment')" data-content='<span class="text-pre-wrap">{{ e($slot) }}</span>' data-trigger="hover focus" data-html="true"><span class="fas fa-comment"></span></a>
@endif