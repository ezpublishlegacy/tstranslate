$(document).ready( function() {
    $(document).data('translationSwitcherOn',false);

    $(document).keyup( function(e) {
        //84 is a code for letter t
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
                        // Support for the ezformtoken extension
                        var _token = '', _tokenNode = document.getElementById( 'ezxform_token_js' );
                        if ( _tokenNode )
                        {
                            _token = _tokenNode.getAttribute( 'title' );
                        }

                        var action = $("#tstranslate_ezurl").val() == "/" ? "" : $("#tstranslate_ezurl").val();

                        this.innerHTML = "<form action=\"" + action + "/tstranslate/set\" method=\"POST\" style=\"display: inline\" >" + 
                        '<input type="hidden" name="ezxform_token" value="' + _token + '" />' +
                        '<input type="hidden" name="Context" value="' + context + '" />' +
                        '<input type="hidden" name="Source" value="' + source + '" />' +
                        '<input type="text" name="Translation" value="' + original + '" />' +
                        '<input class="ts-translate-store-button" type="submit" value="Store" />' +
                        '<input class="ts-translate-cancel-button" type="button" value="Cancel" />' +
                        '</form>'
                        // Avoid possible surrounding links default behaviour (need to submit form manually)
                        $('.ts-translate-store-button').click( function(evt){
                            $(this).parent().submit();
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
