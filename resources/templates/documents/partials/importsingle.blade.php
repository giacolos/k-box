
<div class="item import-{{$item->status_message}}" data-id="{{$item->id}}" data-file-id="{{$item->file->id}}">

	<div class="icon">
		<span class="status-icon icon-action-black status-{{$item->status_message}}"></span>

		@if($item->is_remote)

			<span class="origin-icon icon-action-black icon-action-black-ic_language_black_24dp"></span>

		@else

			<span class="origin-icon icon-action-black"></span>

		@endif
	</div>

	<h2 class="title">

		{{ $item->file->name }}

		<span class="comment origin">{{$item->file->original_uri}}</span>
		
	</h2>

	<div class="meta">

		<span class="meta-info creation-date">
			<span class="meta-label">{{trans('documents.descriptor.added_on')}}&nbsp;</span>{{$item->created_at}}
		</span>

		<span class="meta-info status">
			{{$item->status_message}}
		</span>

		<span class="meta-info document-type">
			{{$item->mime_type}}
		</span>

		<span class="meta-info progress">

			@if($item->bytes_expected > 0)

				{{round($item->bytes_received/$item->bytes_expected*100)}}%

			@else 

				0%

			@endif

			
		</span>
		
	</div>

</div>