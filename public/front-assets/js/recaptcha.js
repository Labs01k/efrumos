function getRecaptcha(action_name, where_name) {
    grecaptcha.ready(function() {
        grecaptcha.execute('6LcpcdohAAAAALgtUfVeLN80MsA10lI_jwL2o3xu', {action: action_name}).then(function(token) {
            $('#'+where_name).val(token);
        });
    });
}
