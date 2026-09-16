{{--
    Проверка выбранных фото до отправки. За пределами лимитов PHP не отвечает
    ошибкой, а молча выкидывает лишние файлы (max_file_uploads) или весь POST
    (post_max_size) — поэтому считаем здесь и не даём отправить. Сервер
    проверяет каждый файл ещё раз сам (ShadePaletteController::photoProblem).
--}}
@php
    $upload_limits = \App\Http\Controllers\Admin\ShadePaletteController::uploadLimits();
@endphp
<script>
    (function () {
        var limits = @json($upload_limits);
        var messages = {
            size: @json(__('variables.shades_error_size', ['max' => $upload_limits['file_mb']])),
            type: @json(__('variables.shades_error_type')),
            tooMany: @json(__('variables.shades_error_too_many', ['max' => $upload_limits['max_files'], 'count' => '__COUNT__'])),
            batch: @json(__('variables.shades_error_batch_size', ['max' => $upload_limits['post_mb']]))
        };
        var types = ['image/jpeg', 'image/png', 'image/webp'];

        function problems(input) {
            var files = Array.prototype.slice.call(input.files || []);
            var list = [];
            var total = 0;

            if (limits.max_files && files.length > limits.max_files) {
                list.push(messages.tooMany.replace('__COUNT__', files.length));
            }

            files.forEach(function (file) {
                total += file.size;
                if (file.size > limits.file_bytes) list.push(file.name + ': ' + messages.size);
                else if (file.type && types.indexOf(file.type) === -1) list.push(file.name + ': ' + messages.type);
            });

            // запас на поля формы и заголовки multipart
            if (limits.post_bytes && total > limits.post_bytes - 65536) list.push(messages.batch);

            return list;
        }

        function show(form, list) {
            var box = form.previousElementSibling && form.previousElementSibling.hasAttribute('data-shade-upload-error')
                ? form.previousElementSibling : null;

            if (!list.length) {
                if (box) box.remove();
                return;
            }

            if (!box) {
                box = document.createElement('div');
                box.className = 'alert alert-danger';
                box.setAttribute('data-shade-upload-error', '');
                form.parentNode.insertBefore(box, form);
            }

            box.textContent = '';
            list.forEach(function (text) {
                var line = document.createElement('div');
                line.textContent = text;
                box.appendChild(line);
            });
        }

        Array.prototype.forEach.call(document.querySelectorAll('input[data-shade-upload]'), function (input) {
            var form = input.form;

            input.addEventListener('change', function () { show(form, problems(input)); });
            form.addEventListener('submit', function (event) {
                var list = problems(input);
                show(form, list);
                if (list.length) event.preventDefault();
            });
        });
    })();
</script>
