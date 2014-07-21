$(document).ready( function() {
    $(document).data('translationSwitcherOn',false);

    $(document).keyup( function(e) {
        // 84 is the ascii code for the letter t
        if(e.keyCode == 84 && e.altKey && e.ctrlKey) {
            if (!$(document).data('translationSwitcherOn')){
                $(document).data('translationSwitcherOn',true);

                $(".ts-translated-text")[0].tstranslateOriginalColor = $(".ts-translated-text").css( 'background' );
                $(".ts-translated-text").css( 'background', 'red' );
                $("#tstranslate_untranslatable_strings").show();

                $(".ts-translated-text").click( function(evt) {
                    var _tstranslatedtext = $(this);

                    if (!_tstranslatedtext.data('editMode')){
                        _tstranslatedtext.data('editMode',true);
                        var translation = $(this).html();
                        var context = $(this).attr('alt');
                        var source = $(this).attr('title');
                        var original = $(this).attr('original');

                        this.innerHTML =
                        '<input type="hidden" name="Context" value="' + context + '" />' +
                        '<input type="hidden" name="Source" value="' + source + '" />' +
                        '<input type="text" name="Translation" value="' + original + '" />' +
                        '<button class="ts-translate-store-button" type="button" />' +
                        '<button class="ts-translate-cancel-button" type="cancel" />';

                        $('.ts-translate-store-button').click( function(evt){
                            var post_data = {
                                'Context' : context,
                                'Source' : source,
                                'Original' : original,
                                'Translation' : _tstranslatedtext.find('input[name=Translation]').val()
                            };

                            $.ez( 'TSTranslateSet::ajaxSetTranslation', post_data, function( data ) {
                                var trans_span = _tstranslatedtext;
                                if (data.error_text) {
                                    // Give the ajax error message on failure
                                    trans_span.html( trans_span.attr( 'translation' ) );
                                    alert( data.error_text );
                                } else {
                                    // Update text on successful result
                                    trans_span.html( data.content );
                                    trans_span[0].setAttribute( 'original', data.content );
                                    trans_span[0].setAttribute( 'translation', data.content );
                                }
                                trans_span.data('editMode',false);
                            });
                            return false;
                        });
                        $('.ts-translate-cancel-button').click( function(evt){
                            var trans_span = _tstranslatedtext;
                            trans_span.html( trans_span.attr( 'translation' ) );
                            trans_span.data('editMode',false);
                            return false;
                        });
                    }
                    // Avoid possible surrounding links default behaviour
                    return false;
                });
                return false;
            }
            else {
                $(document).data('translationSwitcherOn',false);
                $("#tstranslate_untranslatable_strings").hide();
                $(".ts-translated-text").each(function() {
                    var _tstranslatedtext = $(this);
                    if ($(this).find('.ts-translate-cancel-button').length > 0) {
                        var trans_span = _tstranslatedtext;
                        trans_span.html( trans_span.attr( 'translation' ) );
                        trans_span.data('editMode',false);
                    }
                });
                $(".ts-translated-text").off('click');
                $(".ts-translated-text").css( 'background', $(".ts-translated-text")[0].tstranslateOriginalColor );
                e.preventDefault();
            }
        }
    });
});
