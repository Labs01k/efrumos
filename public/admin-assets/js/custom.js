(function ($) {

    $(document).ready(function () {
        // Global variables
        let global_lang = $('html').attr('lang');

        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });

        /*CKEditor initialize*/
        window.editors = [];
        if ($('#add-form, #edit-form').hasClass('form') && $('#add-form textarea, #edit-form textarea').hasClass('editor')) {

            const editors = document.querySelectorAll('.editor');
            editors.forEach((el, index) => {

                ClassicEditor.create(el, {
                    ckfinder: {
                        uploadUrl: '/admin-assets/js/ckeditor5/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&responseType=json',
                        options: {
                            language: global_lang
                        }
                    },
                    fontSize: {
                        options: [
                            9,
                            11,
                            13,
                            'default',
                            17,
                            19,
                            21,
                            28
                        ]
                    },
                    language: global_lang,
                    removePlugins: [
                        'MediaEmbedToolbar'
                    ],
                })
                    .then(newEditor => {
                        window.editors[index] = newEditor;
                    })
                    .catch(error => {
                        console.error(error);
                    });
            });
        }


        /* Authentication-signin */
        $("#show_hide_password a").on('click', function (event) {
            event.preventDefault();
            if ($('#show_hide_password input').attr("type") == "text") {
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass("bx-hide");
                $('#show_hide_password i').removeClass("bx-show");
            } else if ($('#show_hide_password input').attr("type") == "password") {
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass("bx-hide");
                $('#show_hide_password i').addClass("bx-show");
            }
        });

        /*Switch lang id in url*/
        $('#lang').on('change', function () {
            let link = $(this).val();
            let new_location = window.location.href.slice(0, -1) + link;
            if ($(this).parents('#edit-form').length > 0)
                window.location.href = new_location;
        });

        /* Generate alias */
        if ($('#alias').val() === '') {
            $('#name').on('keyup contextmenu input', function (e) {
                let evtobj = window.event ? event : e;

                if (evtobj.keyCode !== 67 && !evtobj.ctrlKey)
                    $('#alias').val(translit($(this).val()));
            });
        }

        /* If selected type page link, show input link */
        $("#page_type").change(function () {
            let val = $(this).val();
            if (val === "link") {
                $(".show-link").show();
                $(".show-ckeditor").hide();
            } else {
                $(".show-link").hide();
                $(".show-ckeditor").show();
            }
        });

        /*Show SEO settings*/
        $(".show-seo-settings").click(function () {
            $('.hide-seo-settings').toggle();
        });

        /*Show goods parameters*/
        $(".show-goods-parameter").click(function () {
            $('.hide-goods-parameters').toggle();
        });

        //Notiflix 3.2.5 Init
        Notiflix.Notify.init({
            width: '280px',
            position: 'right-bottom',
            distance: '10px',
            opacity: 1,
            clickToClose: true,
            useIcon: true,
            borderRadius: '5px',
            rtl: false,
            //timeout: 3000,
            success: {
                background: '#15ca20',
                notiflixIconColor: '#fff',
                textColor: '#fff',
            },
            failure: {
                background: '#fd3550',
                notiflixIconColor: '#fff',
                textColor: '#fff',
            },
            info: {
                background: '#0dcaf0',
                notiflixIconColor: '#fff',
                textColor: '#fff',
            },
            warning: {
                background: '#ffc107',
                notiflixIconColor: '#fff',
                textColor: '#fff',
            }
        });

        /* Select 2 */
        $('.single-select').select2();
        $('.multiple-select').select2();

        /*Bootstrap 4 style*/
        /*$('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
        $('.multiple-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });*/

        /* Sorting table rows */
        $('.sort-table').sortable({
            items: "tr",
            cursor: 'move',
            opacity: 0.6,
            axis: "y",
            handle: '.position',
            update: function () {
                sendOrderToServer($(this), 'sort-table');
            }
        });

        /*Sorting images*/
        $('.sort-img').sortable({
            cursor: 'move',
            opacity: 0.6,
            /*axis: "x",*/
            update: function () {
                sendOrderToServer($(this), 'sort-images');
            }
        }).disableSelection();

        /* Select all elements*/
        $('.select-all-elements').on('click', function () {
            let check_boxes = $('.destroy-element');
            check_boxes.prop('checked', !check_boxes.prop('checked'));
        });

        /* Count of items selected for deletion */
        let destroy_elements_count = 0;
        $('.destroy-element, .select-all-elements').on('click', function () {

            let check_box = $(this).find('.destroy-element');
            check_box.prop('checked', !check_box.prop('checked'));

            let destroy_all_elements = $('.destroy-all-elements');
            destroy_all_elements.prop('disabled', $('.destroy-element:checked').length === 0);

            let destroy_elements_count = $('.destroy-element').filter(':checked').length;
            $('.destroy-all-elements span').html(parseInt(destroy_elements_count));

            let selected_ids = [];
            $('.destroy-element:checked').each(function () {
                selected_ids.push($(this).val())
            });

            destroy_all_elements.attr('data-goods-id', selected_ids);
        });

        /* Select all restore elements*/
        $('.select-all-restore-elements').on('click', function () {
            let check_boxes = $('.restore-element');
            check_boxes.prop('checked', !check_boxes.prop('checked'));
        });

        /* Count of items selected for restore */
        let restore_elements_count = 0;
        $('.restore-element, .select-all-restore-elements').on('click', function () {

            let check_box = $(this).find('.restore-element');
            check_box.prop('checked', !check_box.prop('checked'));

            let restore_all_elements = $('.restore-all-elements');
            restore_all_elements.prop('disabled', $('.restore-element:checked').length === 0);

            let restore_elements_count = $('.restore-element').filter(':checked').length;
            $('.restore-all-elements span').html(parseInt(restore_elements_count));

            let selected_ids = [];
            $('.restore-element:checked').each(function () {
                selected_ids.push($(this).val())
            });

            restore_all_elements.attr('data-goods-id', selected_ids);
        });

        //Upload multiple images
        $("#upload_files").change(function (e) {
            e.stopPropagation();
            e.preventDefault();
            image_select(this);
            //readURL(this);
        });

    });

    /* Change active element */
    $(document).on("change", '.change-active', function (e) {
        e.preventDefault();

        let active = $(this).data('active');
        let type = $(this).data('type');
        let action = $(this).data('action');
        let element_id = $(this).data('element-id');
        let module_with_submenu = $(this).data('module-with-submenu');
        let url = '';

        if (active === 1) {
            $(this).prop('checked', false);
            $(this).data('active', 0);
        } else {
            $(this).prop('checked', true);
            $(this).data('active', 1);
        }

        if (action !== undefined) {
            url = $(this).data('url') + '/ajaxRequest/changeActive';
        } else if (module_with_submenu === 1) {
            url = window.location.pathname + '/changeActive';
        } else {
            url = window.location.pathname + '/ajaxRequest/changeActive';
        }

        $.ajax({
            type: "POST",
            url: url,
            data: {
                action: action,
                active: active,
                id: element_id,
                type: type
            },
            success: function (response) {
                if (response.status === true) {

                    Notiflix.Notify.info(response.messages[0], {
                            timeout: 3000
                        }
                    );
                } else {
                    Notiflix.Notify.failure(response.messages[0], {
                            timeout: 3000
                        }
                    );
                }
            },
            error: function (response) {
                Notiflix.Notify.failure('Ups, something went wrong! Please contact administrator!', {
                        timeout: 3000
                    }
                );
            }
        });
    });

    /*Destroy elements*/
    $(document).on('click', '.destroy-all-elements', function (e) {
        let data_goods_id = $(this).data('goods-id');
        let url = $(this).data('url');
        let data_current_url = $(this).data('current-url');
        let upload_path_optional = $(this).data('upload-path-optional');

        let conf = confirm("Do you want delete this element?");
        if (conf != true)
            e.preventDefault();
        else {
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    data_goods_id: data_goods_id,
                    data_current_url: data_current_url,
                    upload_path_optional: upload_path_optional,
                },
                success: function (response) {
                    if (response.status == true) {

                        Notiflix.Notify.info(response.messages, {
                                timeout: 3000
                            }
                        );

                        setTimeout(function () {
                            if (response.redirect != null) {
                                window.location.href = response.redirect;
                            }
                        }, 1000);

                    } else {
                        Notiflix.Notify.failure('Ups, something went wrong!', {
                                timeout: 3000
                            }
                        );
                    }
                },
            });
        }
    });

    /*Restore elements*/
    $(document).on('click', '.restore-all-elements', function (e) {

        e.preventDefault();

        let data_goods_id = $(this).data('goods-id');
        let url = $(this).data('url');
        let data_current_url = $(this).data('current-url');

        $.ajax({
            type: "POST",
            url: url,
            data: {
                data_goods_id: data_goods_id,
                data_current_url: data_current_url,
            },
            success: function (response) {
                if (response.status == true) {

                    Notiflix.Notify.info(response.messages, {
                            timeout: 3000
                        }
                    );

                    setTimeout(function () {
                        if (response.redirect != null) {
                            window.location.href = response.redirect;
                        }
                    }, 1000);
                } else {
                    Notiflix.Notify.failure('Ups, something went wrong!', {
                            timeout: 3000
                        }
                    );
                }
            },
        });
    });

    /*Delete uploaded image*/
    $(document).on('click', '.delete-uploaded-image', function (e) {
        e.preventDefault();

        let _this = $(this);
        let curr_img = _this.attr('data-file-name');
        let url = _this.data('destroy-url');
        let uploadPath = _this.parents('.image-element-li').data('path');
        let curr_id = _this.parents('.image-element-li').data('id');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                'curr_img': curr_img,
                'uploadPath': uploadPath,
                'curr_id': curr_id
            },
            success: function (data) {
                if (data.status === true) {
                    _this.parents('.image-element-li').fadeOut(function () {
                        _this.parents('.image-element-li').remove();
                        if ($('.image-element-li').length === 0)
                            $('.card-files .card-body').addClass('d-none');
                    });
                    if (data.messages !== undefined) {
                        let show_message;
                        if ($.isArray(data.messages))
                            show_message = data.messages[0];
                        else
                            show_message = data.messages;

                        Notiflix.Notify.info(show_message, {
                                timeout: 3000
                            }
                        );
                    }
                } else {
                    if (data.messages !== undefined)
                        Notiflix.Notify.failure(data.messages[0], {
                                timeout: 3000
                            }
                        );
                }
            }
        });
    });

    /*Create parameter*/
    if ($('#add-form').data('page-type') === 'create-parameter') {

        let measure_type_val = $('#measure_type').val();
        if (measure_type_val === "no_measure") {
            $(".hide-with-measure").hide();
        }
        if (measure_type_val === "with_measure") {
            $(".hide-with-measure").show();
        }

        let parametr_type_val = $('#parametr_type').val();
        if (parametr_type_val === "input" || parametr_type_val === "textarea") {
            $(".hide-parameter-values").hide();
            $('.inputs').children('input').val('');
            $('.new-inputs').remove();
        } else {
            $(".hide-parameter-values").show();
        }
        if (parametr_type_val === "input") {
            $(".hide-measure-type").show();
            $(".hide-with-measure").show();
        } else {
            $(".hide-measure-type").hide();
            $(".hide-with-measure").hide();
        }

        $("#parametr_type").on("change", function () {
            let val = $(this).val();
            if (val === "input" || val === "textarea") {
                $(".hide-parameter-values").hide();
                $('.inputs').children('input').val('');
                $('.new-inputs').remove();
                $(".hide-measure-type").hide();
                $(".hide-with-measure").hide();
            } else {
                $(".hide-parameter-values").show();
                $(".hide-measure-type").hide();
                $(".hide-with-measure").hide();
            }

            if (val === "input") {
                $(".hide-measure-type").show();
                $(".hide-with-measure").show();
            } else {
                $(".hide-measure-type").hide();
                $(".hide-with-measure").hide();
            }
        });

        $('#more_values').on('click', function () {
            $('.parametr-values-list').append('<div class="new-inputs"><input name="parametr_type_value[]" class="form-control form-control-sm mb-3"><span class="badge delete-parameter-value">x</span></div>');
        });

        $(document).on('click', '.delete-parameter-value', function () {
            $(this).parent('.new-inputs').remove();
        });

        $("#measure_type").on('change', function () {
            let val = $(this).val();
            if (val === "no_measure") {
                $(".hide-with-measure").hide();
            }
            if (val === "with_measure") {
                $(".hide-with-measure").show();
            }
        });
    }

    /*Edit parameter*/
    if ($('#edit-form').data('page-type') === 'edit-parameter') {

        let measure_type_val = $('#measure_type').val();
        if (measure_type_val === "no_measure") {
            $(".hide-with-measure").hide();
        }
        if (measure_type_val === "with_measure") {
            $(".hide-with-measure").show();
        }

        $("#measure_type").change(function () {
            let measure_type_val = $(this).val();
            if (measure_type_val === "no_measure") {
                $(".hide-with-measure").hide();
            }
            if (measure_type_val === "with_measure") {
                $(".hide-with-measure").show();
            }
        });

        let parametr_value_index = 0;
        $('#more_values').on('click', function () {
            parametr_value_index = parametr_value_index - 1;
            $('.table-parametr-values tbody').append('<tr>' +
                '<td class="position cursor-pointer text-center"><i class="lni lni-move"></i></td>' +
                '<td class="text-center">' +
                '<input name="parametr_type_value[' + parametr_value_index + ']" class="parameter-input form-control form-control-sm">' +
                '</td>' +
                '<td class="text-center">' +
                '<button type="button" class="btn delete-parametr-value-new"><i class="bx bxs-trash"></i></button>' +
                '</td>' +
                '</tr>')
        });

        $(document).on('click', '.delete-parametr-value-new', function () {
            $(this).parents('td').parents('tr').remove();
        });

        $(".delete-parametr-value").on('click', function () {
            if ($(".table-parametr-values tr").length > 2) {
                $(this).parents('td').parents('tr').remove();
                let goods_parametr_value_id = $(this).parents('td').parents('tr').data('id');
                let goods_parametr_id = $(this).parents('td').parents('tr').data('param-id');
                let url = $('.table-parametr-values tbody').data('url') + '/ajaxRequest/removeParameter';
                if (goods_parametr_value_id > 0) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            action: goods_parametr_value_id,
                            param_id: goods_parametr_id
                        },
                        success: function (response) {
                            if (response.status === true) {
                                if (response.messages !== '' && response.messages !== undefined)
                                    Notiflix.Notify.success(response.messages, {
                                            timeout: 3000
                                        }
                                    );
                            } else {
                                if (response.messages !== '' && response.messages !== undefined)
                                    Notiflix.Notify.failure(response.messages, {
                                            timeout: 3000
                                        }
                                    );
                            }
                        }
                    });
                }
            } else {
                Notiflix.Notify.failure('Select must have min one value!', {
                        timeout: 3000
                    }
                );
            }
        });
    }
    ;

    /*Preview youtube video for (GalleryItemId)*/
    $("#youtube_link").change(function () {
        let code = $(this).val();
        let url = $(this).data('url');

        $.ajax({
            type: "POST",
            url: url + '/ajaxRequest/youtubeId',
            data: {code: code},
            success: function (data) {
                if (data != '') {
                    $(".youtube_id").html('<div><input type="hidden" name="youtube_id" value="' + data + '">' + '<iframe width="100%" height="200" src="https://www.youtube.com/embed/' + data + '" frameborder="0" allowfullscreen=""></iframe></div>');
                } else {
                    $(".youtube_id").html('<input type="hidden" name="youtube_id" value="">');
                }
            }
        });
    });

    /*Edit(render new inputs) for gallery item photo*/
    $('.edit-gallery-photo a').on('click', function (e) {
        e.preventDefault();

        let _this = $(this);
        let curr_item_id = _this.data('item-id');
        let curr_lang_edit = _this.data('lang-id');
        let url = _this.data('url') + '/ajaxRequest/ajaxAudioContent';

        $.ajax({
            type: "POST",
            url: url,
            data: {
                id: curr_item_id,
                lang_id: curr_lang_edit
            },
            success: function (response) {
                if (response.status === true) {
                    _this.parents('.edit-gallery-photo').siblings('.photo-name').html('<input name="material-name" class="form-control material-name-input" value="' + response.name + '" data-lang-id="' + curr_lang_edit + '" data-url="' + _this.data('url') + '" data-element-id="' + curr_item_id + '" >');
                    _this.parents('.edit-gallery-photo').siblings('.photo-descr').html('<textarea name="material-body" class="form-control material-name-input" data-lang-id="' + curr_lang_edit + '" data-url="' + _this.data('url') + '" data-element-id="' + curr_item_id + '" >' + response.body + '</textarea>');
                } else {
                    Notiflix.Notify.failure(response.messages, {
                            timeout: 3000
                        }
                    );
                }
            }
        });
    });

    /*Update gallery item photo*/
    $(document).on('change', '.material-name-input', function () {

        let current_element = $(this);
        let url = $(this).data('url') + '/ajaxRequest/changeItemName';
        let element_id = $(this).data('element-id');
        let lang_id = $(this).data('lang-id');
        let element_name = $(this).val();
        let element_attr_name = $(this).attr('name');

        let edited_row = '';

        if (element_attr_name === 'material-name')
            edited_row = 'name';
        else
            edited_row = 'body';

        if (element_id === undefined)
            element_id = '';

        $.ajax({
            type: "POST",
            url: url,
            data: {
                id: element_id,
                name: element_name,
                lang_id: lang_id,
                edited_row: edited_row
            },
            success: function (response) {

                if (response.status === true) {

                    $(current_element.parents('.photo-name').siblings('.edit-gallery-photo').find('a[data-lang-id="' + lang_id + '"]')).removeClass('btn-danger').addClass('btn-success');
                    current_element.parents('.photo-name').html('<span>' + response.new_name + '</span>');
                    current_element.parents('.photo-descr').html('<span>' + response.new_body + '</span>');

                    Notiflix.Notify.success(response.messages, {
                            timeout: 3000
                        }
                    );
                } else {
                    Notiflix.Notify.failure(response.messages, {
                            timeout: 3000
                        }
                    );
                }
            }
        });
    });

    /*Access list*/
    $('.modules-id').on('change', function () {
        let _this = $(this);
        let module_id = _this.data('module-id');
        changeActionDisplay(module_id);
    });

    /*Edit gallery item video*/
    $('.edit-gallery-item a').on('click', function (e) {
        e.preventDefault();

        let _this = $(this);
        let video_form = $('#add-video-form');
        let curr_item_id = _this.data('item-id');
        let curr_lang_edit = _this.data('lang-id');
        let url = _this.data('url') + '/ajaxRequest/ajaxVideoContent';

        $('html, body').animate({
            scrollTop: (video_form.offset().top - 60)
        }, 300);

        $.ajax({
            type: "POST",
            url: url,
            data: {
                id: curr_item_id,
                lang_id: curr_lang_edit
            },
            success: function (response) {

                if (response.status === true) {
                    video_form.addClass('edit-video-form');

                    let _this_name = response.name;
                    let _this_body = response.body;
                    let _this_link = response.link;
                    let _this_youtube_id = response.youtube_id;

                    video_form.find('div').first().before('<input type="hidden" name="current_item" value="' + curr_item_id + '">');

                    $.each(video_form.find('#lang').children('option'), function (k, v) {
                        if ($(v).val() === curr_lang_edit)
                            $(v).prop('selected', true);
                    });

                    video_form.find('#name').val(_this_name);
                    video_form.find('#body').val(_this_body);
                    video_form.find('#youtube_link').val(_this_link);
                    $(".youtube_id").html('<div><input type="hidden" name="youtube_id" value="' + _this_youtube_id + '">' + '<iframe width="100%" height="200" src="https://www.youtube.com/embed/' + _this_youtube_id + '" frameborder="0" allowfullscreen=""></iframe></div>');

                } else {
                    Notiflix.Notify.failure(response.messages, {
                            timeout: 3000
                        }
                    );
                }
            }
        });
    });

    /*Change select input, textarea, ckeditor*/
    let set_type = $('#set_type').val();
    if (set_type === 'textarea') {
        $('.input').hide().find('input').val('');
        $('.textarea').show();
    }

    if (set_type === 'ckeditor') {
        $('.input').hide().find('input').val('');
        $('.textarea').hide().find('textarea').val('');
    }

    if (set_type === 'input') {
        $('.textarea').hide().find('textarea').val('');
        $('.input').show();
    }

    $('#set_type').on('change', function () {
        let set_type = $(this).val();

        if (set_type === 'textarea') {
            $('.input').hide().find('input').val('');
            $('.textarea').show();

            $('.input').addClass('hidden').val('');
            $('.textarea').show();
        }

        if (set_type === 'input') {
            $('.textarea').hide().find('textarea').val('');
            $('.input').show();
        }
    });

    /*Sitemap generator*/
    $(document).on('click', '#sitemap', function (e) {
        e.preventDefault();
        let lang = $('html').attr('lang');
        let url = window.location.origin + '/' + lang + '/back/sitemap';

        let wait_sitemap = $('#wait-sitemap').html();

        Notiflix.Notify.warning(wait_sitemap, {
                timeout: 3000
            }
        );

        let wait_msg = setInterval(function () {
            Notiflix.Notify.warning(wait_sitemap, {
                    timeout: 3000
                }
            );
        }, 10000);

        $.ajax({
            type: "POST",
            url: url,
            success: function (data) {
                clearInterval(wait_msg);

                if (data.status == true)
                    Notiflix.Notify.success(data.messages[0], {
                            timeout: 3000
                        }
                    );
            },
            error: function () {
                clearInterval(wait_msg);
            }
        });
    });
})(jQuery);

function translit(s) {
    let t = "îişsăaâaţtаaбbвvгgдdеeёjoжzhзzиiйjjкkлlмmнnоoпpрrсsтtуuфfхkhцcчchшshщshhъ''ыyь'эehюjuяjaĂAÂAÎIŞSŢTАAБBВVГGДDЕEЁJoЖZhЗZИIЙJjКKЛLМMНNОOПPРRСSТTУUФFХKhЦCЧChШShЩShhЪ''ЫYЬ'ЭEhЮJuЯJa";
    t = t.replace(/([а-яёЁţâăşî])([a-z']+)/gi, '.replace(/$1/g,"$2")');
    ret = eval("s" + t);
    ret = ret.replace(/[ș]/g, "s");
    ret = ret.replace(/[ț]/g, "t");
    ret = ret.replace(/[^a-z0-9]/gi, "-");
    ret = ret.replace(/-{2,1000}/gi, "-");
    ret = ret.replace(/-$/gi, "").toLowerCase();
    return ret;
}

/*Upload and delete preview images*/
let images = [];

function image_select(input) {
    $('.sort-img').sortable("disable");

    let image = input.files;
    for (let i = 0; i < image.length; i++) {
        images.push({
            "name": image[i].name,
            "url": URL.createObjectURL(image[i]),
            "file": image[i],
        })
    }
    $('.new_image').html(image_show()).parents('.card-body').removeClass('d-none');
}

function image_show() {
    let image = '';

    $.each(images, function (index, value) {
        if (index < 10) {
            if (value.file.type == 'application/pdf') {
                image += '<li class="image-element-li">\n' +
                    '<img class="rounded p-1 border img-fit-cover" width="100" height="100" src="/admin-assets/images/icons/pdf-icon.png">\n' +
                    '<span class="badge delete-image-button" onclick="delete_image(' + images.indexOf(value) + ')">x</span>\n' +
                    '<input type="hidden" name="uploaded_files[]" value="' + index + '">\n' +
                    '</li>';
            }else {
                image += '<li class="image-element-li">\n' +
                    '<img class="rounded p-1 border img-fit-cover" width="100" height="100" src="' + value.url + '">\n' +
                    '<span class="badge delete-image-button" onclick="delete_image(' + images.indexOf(value) + ')">x</span>\n' +
                    '<input type="hidden" name="uploaded_files[]" value="' + index + '">\n' +
                    '</li>';
            }
        }
    });

    return image;
}

/*function delete_image(index) {

    images.splice(index, 1);
    $('.upload-files-list').html(image_show());

    const dt = new DataTransfer();
    const upload_files = document.getElementById("upload_files").files;

    for (let i = 0; i < upload_files.length; i++) {
        if (index !== i)
            dt.items.add(upload_files[i]);
    }

    document.getElementById("upload_files").files = dt.files;

    if ($('.image-element-li').length === 0)
        $('.card-files').remove();

    Notiflix.Notify.success('Image deleted successfully', {
            timeout: 3000
        }
    );
}*/

function delete_image(index) {

    images.splice(index, 1);
    $('.new_image').html(image_show());
    const dt = new DataTransfer();
    const upload_files = document.getElementById("upload_files").files;
    for (let i = 0; i < upload_files.length; i++) {
        if (index !== i)
            dt.items.add(upload_files[i]);
    }
    document.getElementById("upload_files").files = dt.files;
    if ($('.image-element-li').length === 0)
        $('.card-files .card-body').addClass('d-none');

    Notiflix.Notify.success('Image deleted successfully', {
            timeout: 3000
        }
    );
}


/*Access list*/
function changeActionDisplay(modules_id) {
    let actions = ['new', 'save', 'active', 'del_to_rec', 'del_from_rec'];

    if ($('#modules_id-' + modules_id + '-').is(':checked')) {

        $('#taction-' + modules_id + '-').stop().slideDown();
        for (i = 0; i < actions.length; i++) {
            $('#' + actions[i] + '-' + modules_id + '-').prop('checked', true);
            $('#' + actions[i] + '-' + modules_id + '-').trigger('refresh');
        }
    } else {
        $('#taction-' + modules_id + '-').stop().slideUp();
        for (i = 0; i < actions.length; i++) {
            $('#' + actions[i] + '-' + modules_id + '-').prop('checked', false);
            $('#' + actions[i] + '-' + modules_id + '-').trigger('refresh');
        }
    }
}

/*Save items*/
function saveForm(e) {

    let form_id = $(e).data('form-id');
    $('#' + form_id).submit(function (event) {
        event.preventDefault();
    });


    /*$('[data-type="ckeditor"]').each(function (index, el) {
          if (CKEDITOR.instances['body' + index] !== undefined)
              $(this).val(CKEDITOR.instances['body' + index].getData());
          else
              $(this).val(CKEDITOR.instances.body.getData());
      });*/
    /*$('.editor').each(function (index, el) {
        const editorData = editor.getData();
        $("#body").val(editorData);
    });*/

    let editorData;
    $('.editor').each(function (index, el) {
        editorData = window.editors[index].getData();
        el.value = editorData;
    });

    let form = $('#' + form_id);

    let serializedForm = new FormData(form[0]);

    if (images.length) {
        serializedForm.delete('upload_files[]');
        for (let i = 0; i < images.length; i++) {
            serializedForm.append('upload_files[]', images[i].file)
        }
    }

    if (!$(form)) {
        return;
    }

    $.ajax({
        method: "POST",
        url: $(form).attr('action'),
        data: serializedForm,
        enctype: 'multipart/form-data',
        processData: false,  // Important!
        contentType: false,
        cache: false,
        async: true,
        success: function (response) {

            let timeout = 3000;
            if (form_id == 'add-form')
                timeout = 1000;
            if (form_id == 'edit-form')
                timeout = 3000;

            if (response.status == true) {

                Notiflix.Notify.success(response.messages[0], {
                        timeout: timeout
                    }
                );

                setTimeout(function () {
                    if (response.redirect != null) {
                        window.location.href = response.redirect;
                    }
                }, timeout);

                if (form_id == 'request-1c-form'){
                    let check_goods_action = $('.goods-action:checked').val();

                    if(check_goods_action == 'show_goods'){
                        $('.goods-request').removeClass('d-none')
                        $('.render-goods-request').html(response.render_goods_request);
                    }
                }

                //remove inputs values after send message
                //form.find('input[type=text],input[type=email], input[type=password], input[type=date], textarea').val('');

            } else {
                if (response.messages != null) {

                    $.each(response.messages, function (ObjNames, ObjValues) {
                        Notiflix.Notify.failure(ObjValues[0], {
                                timeout: 3000
                            }
                        );
                    });
                } else {
                    Notiflix.Notify.failure(response.message[0], {
                            timeout: 3000
                        }
                    );
                }
            }
        }
    })
}

/*Sortable table and images*/
function sendOrderToServer(_this, type) {

    let url = '';
    let action = _this.data('action');
    let position = [];

    console.log(action);

    switch (type) {
        case 'sort-table':
            url = _this.data('url') + '/ajaxRequest/changePosition';
            $('tr.row-id').each(function (index, element) {
                position.push({
                    id: $(this).attr('data-id'),
                    position: index + 1
                });
            });
            break;
        case 'sort-images':
            url = $('.form').data('parent-url') + '/ajaxRequest/changeImgPosition';
            $(".image-element-li").each(function (index, element) {
                position.push({
                    id: $(this).attr('data-id'),
                    position: index + 1
                });
            });
            break;
        default:
            break
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: {
            action: action,
            position: position,
        },
        success: function (response) {
            if (response.status === true) {
                Notiflix.Notify.success(response.message, {
                        timeout: 3000
                    }
                );
            }
        }
    });
}

$(document).on('click', '.save-config-value', function (e) {
    e.preventDefault();
    let lang = $('html').attr('lang');
    let url = window.location.pathname + '/ajaxRequest/ajaxUpdateConfig';

    let config_key = $(this).closest(".row-id").find(':input').attr("id");
    let config_value = $(this).closest(".row-id").find(':input').val();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        url: url,
        type: "POST",
        data: {
            config_key: config_key,
            config_value: config_value,
        },
        success: function (response) {
            if (response.status == true) {

                Notiflix.Notify.info(response.message[0], {
                        timeout: 3000
                    }
                );

                setTimeout(function () {
                    if (response.redirect != null) {
                        window.location.href = response.redirect;
                    }
                }, 3000);
            }
        }
    });
});


$('.save-slider-link').on('change', function () {

    let goods_subject_id = $(this).data('goods-subject-id');
    let goods_subject_image_id = $(this).data('goods-subject-image-id');
    let current_lang_id = $(this).data('current-lang-id');
    let link = $(this).val();

    let lang = $('html').attr('lang');
    let url = '/' + lang + '/back/goods/ajaxRequest/ajaxSaveSliderLink';
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        type: "POST",
        url: url,
        data: {
            'goods_subject_id': goods_subject_id,
            'goods_subject_image_id': goods_subject_image_id,
            'current_lang_id': current_lang_id,
            'link': link,
        },
        success: function (response) {
            if (response.status == true) {
                Notiflix.Notify.success(response.message[0], {
                        timeout: 3000
                    }
                );
            }
        }
    });
});

//For top scroll
$(function(){
    $(".table-responsive-scrollbar-top").scroll(function(){
        $(".scrollbar-top")
            .scrollLeft($(".table-responsive-scrollbar-top").scrollLeft());
    });
    $(".scrollbar-top").scroll(function(){
        $(".table-responsive-scrollbar-top")
            .scrollLeft($(".scrollbar-top").scrollLeft());
    });
});


$('.goods-action').on('change', function () {

    let check_goods_action = $(this).val();

    if(check_goods_action == 'update_goods'){
        $('.goods-request').addClass('d-none');
    }
});

$(document).on('change', '.products_count', function () {

    let url = $(this).data('url') + '/ajaxRequest/changeGoodItemCount';
    let goods_item_id = $(this).data('goods-id');
    let products_count = $(this).val();


    $.ajax({
        type: "POST",
        url: url,
        data: {
            goods_item_id: goods_item_id,
            products_count: products_count
        },
        success: function (response) {
            if (response.status === true) {
                $('.product_count_value_'+goods_item_id).val(response.products_count);

                Notiflix.Notify.success(response.messages, {
                        timeout: 3000
                    }
                );
            }
            else {
                Notiflix.Notify.success(response.messages[0], {
                        timeout: 3000
                    }
                );
            }
        }
    });

});

$(document).on('click', '.refund-order-ga4', function () {

    let lang = $('html').attr('lang');
    let url = '/' + lang + '/back/orders/ajaxRequest/ajaxRefundOrderGA4';
    let order_id = $(this).data('order-id');

    let conf = confirm("Anula comandă?");
    if (conf != true)
        e.preventDefault();
    else {
        $.ajax({
            type: "POST",
            url: url,
            data: {
                order_id: order_id,
            },
            success: function (response) {
                if (response.status === true) {

                    dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
                    dataLayer.push({
                        event: "refund",
                        ecommerce: {
                            transaction_id: response.order_id,
                            affiliation: "Efrumos Beauty Shop",
                            value: response.total_price,
                            tax: "4.90",
                            shipping: response.delivery_cost,
                            currency: "MDL",
                            coupon: "",
                            items: JSON.parse(response.goods_objects)
                        }
                    });

                    Notiflix.Notify.success(response.messages, {
                            timeout: 3000
                        });
                } else {
                    Notiflix.Notify.failure(response.messages, {
                            timeout: 3000
                        });
                }
            }
        });
    }
});
