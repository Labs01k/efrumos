<div class="parent-files-block">
    <div class="card card-files radius-10">
        <div class="card-body{{ $images->isEmpty() ? ' d-none' : '' }}">
            <ul class="list-unstyled d-flex flex-wrap upload-files-list sort-img{{ $images->isEmpty() ? ' new_image' : '' }}">
                @if(!empty($images) && count($images))
                    @foreach($images as $one_image)
                        <li class="image-element-li cursor-move"
                            data-id="{{ $one_image->id }}"
                            data-path="{{ $upload_path/*$modules_name->modulesId->alias*/ }}">
                            @if(getImageExtension($one_image->img) == 'pdf' && ($one_image->img && file_exists("upfiles/" . $upload_path .'/'. $one_image->img)))
                                <img src="{{ asset('admin-assets/images/icons/pdf-icon.png') }}"
                                     class="rounded p-1 border img-fit-cover" width="100"
                                     height="100">
                            @elseif($one_image->img && file_exists("upfiles/" . $upload_path .'/'. $one_image->img))
                                <img src="{{ asset('upfiles/'.$upload_path) }}/{{ $one_image->img }}"
                                     class="rounded p-1 border img-fit-cover" width="100"
                                     height="100">
                            @else
                                <img src="{{asset('admin-assets/images/no-image.png')}}"
                                     alt="no-image" width="100" height="100"
                                     title="No image">
                            @endif
                            <span class="badge delete-image-button delete-uploaded-image"
                                  data-file-name="{{ $one_image->img }}"
                                  data-destroy-url="{{ url($lang, ['back', 'destroyFiles']) }}">x</span>
                        </li>
                    @endforeach
                        <li class="new_image d-flex"></li>
                @endif
            </ul>
        </div>
    </div>
</div>
