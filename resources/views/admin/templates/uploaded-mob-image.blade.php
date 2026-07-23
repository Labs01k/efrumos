
<div class="parent-files-block">
    <div class="card card-files radius-10">
        <div class="card-body{{ !$item || !$item->img_mobile ? ' d-none' : '' }}">
            <ul class="list-unstyled d-flex flex-wrap upload-files-list new_image">
                @if($item && $item->img_mobile)
                    <li class="image-element-li"
                        data-id="{{ $item->id ?? '' }}"
                        data-path="{{ $upload_path ?? '' }}">
                        @if($item->img_mobile && file_exists("upfiles/" . $upload_path .'/'. $item->img_mobile))
                            <img src="{{ asset('upfiles/'.$upload_path) }}/{{ $item->img_mobile ?? '' }}"
                                 class="rounded p-1 border img-fit-cover" width="100"
                                 height="100">
                        @else
                            <img src="{{asset('admin-assets/images/no-image.png')}}"
                                 alt="no-image" width="100" height="100"
                                 title="No image">
                        @endif
                        <span class="badge delete-image-button delete-uploaded-image"
                              data-file-name="{{ $item->img_mobile }}"
                              data-destroy-url="{{ url($lang, ['back', 'destroyFile']) }}">x</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
