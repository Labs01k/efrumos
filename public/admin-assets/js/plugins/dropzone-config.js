Dropzone.options.dropzoneImagesUpload = {
    paramName: "file",
    maxFilesize: 5,
    parallelUploads: 2,
    uploadMultiple: true,
    acceptedFiles: ".jpeg,.jpg,.png,.gif,.webp",
    init: function () {
        this.on('error', function (file, response) {
            $(file.previewElement).find('.dz-error-message').text(response);
        });

        this.on('sending', function (file, xhr, formData) {
            formData.append('_token', $('meta[name=_token]').attr('content'));
            formData.append('gallery-id', $('#dropzone-images-upload').data('gallery-id'));
            formData.append('current-lang-id', $('#dropzone-images-upload').data('current-lang-id'));
        });

        this.on("complete", function (file) {
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                window.location.reload()
            }
        });
    }
};
