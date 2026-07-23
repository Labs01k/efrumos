<div class="parent-files-block">
    <div class="card card-files radius-10">
        <div class="card-body{{ !$item || !$item_image_name ? ' d-none' : '' }}">
            <ul class="list-unstyled d-flex flex-wrap upload-files-list">
                @if($item && $item_image_name)
                    <li class="image-element-li"
                        data-id="{{ $item->id ?? '' }}"
                        data-path="{{ $upload_path ?? '' }}">
                        @if(getImageExtension($item_image_name) == 'pdf' && ($item_image_name && file_exists("upfiles/" . $upload_path .'/'. $item_image_name)))
                            <img src="{{ asset('admin-assets/images/icons/pdf-icon.png') }}"
                                 class="rounded p-1 border img-fit-cover" width="100"
                                 height="100">
                        @elseif($item_image_name && file_exists("upfiles/" . $upload_path .'/'.$item_image_name))
                            <img src="{{ asset('upfiles/'.$upload_path) }}/{{ $item_image_name ?? '' }}"
                                 class="rounded p-1 border img-fit-cover" width="100"
                                 height="100">
                        @else
                            <img src="{{asset('admin-assets/images/no-image.png')}}"
                                 alt="no-image" width="100" height="100"
                                 title="No image">
                        @endif
                        <span class="badge delete-image-button delete-uploaded-image"
                              data-file-name="{{ $item_image_name }}"
                              data-destroy-url="{{ url($lang, ['back', 'destroyFile']) }}">x</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>


