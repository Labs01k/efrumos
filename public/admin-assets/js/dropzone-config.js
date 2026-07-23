//Gallery
Dropzone.options.imageUpload = {
    paramName: "file",
    maxFilesize: 5,
    parallelUploads: 2,
    uploadMultiple: true,
    acceptedFiles: 'image/*',
    init: function() {
        this.on('error', function(file, response){
            $(file.previewElement).find('.dz-error-message').text(response);
        });

        this.on('sending', function (file, xhr, formData) {
            formData.append('gallery-id', $('#image-upload').attr('element-id'));
            formData.append('_token', $('meta[name=_token]').attr('content'));
        });

        this.on("complete", function (file) {
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                window.location.reload()
            }
        });
    }
};

//1C File
Dropzone.options.oneCFileUpload = {
    paramName: "file",
    uploadMultiple: false,
    acceptedFiles: 'text/plain',
    init: function() {
        this.on('error', function(file, response){
            $(file.previewElement).find('.dz-error-message').text(response);
        });

        this.on("complete", function (file) {
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                window.location.reload()
            }
        });
    }
};




