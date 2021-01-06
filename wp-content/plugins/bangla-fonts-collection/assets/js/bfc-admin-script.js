var janu;
$('textarea, input[type=text]').avro({ 'bangla': false });
function avph_bangla() {
    avroAtiveKey = jQuery.Event("keydown");
    avroAtiveKey.which = 77;
    avroAtiveKey.ctrlKey = true;
    $(janu).trigger(avroAtiveKey);
}

$("textarea, input[type=text]").on('focus', function () {
    if (this.bangla) {
        $('#wp-admin-bar-bfc-language-changer a').html('বাংলা চলছে');
    } else {
        $('#wp-admin-bar-bfc-language-changer a').html('English Enabled');
    }
})

$('#wp-admin-bar-bfc-language-changer a').on('click', function (e) {
    e.preventDefault();
    console.log(this.text);
    if ('English Enabled' == this.text) {
        $('#wp-admin-bar-bfc-language-changer a').html('বাংলা চলছে');
    } else {
        $('#wp-admin-bar-bfc-language-changer a').html('English Enabled');
    }
    avph_bangla();
})
$("textarea, input[type=text]").on('blur', function () {
    janu = this;
})