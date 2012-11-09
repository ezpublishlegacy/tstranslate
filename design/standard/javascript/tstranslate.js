$(document).ready( function() {
    var isCtrl = false;
    var isAlt = false;
    $(document).data('translationSwitcherOn',false);

    $(document).keyup(function(e) {
        if(e.which == 17) {
            isCtrl = false;
        }
        if(e.which == 18) {
            isAlt = false;
        }
    });
    $(document).keydown(function(e) {
        if(e.which == 17) {
            isCtrl = true;
        }
        if(e.which == 18) {
            isAlt = true;
        }
        //84 is a code for letter t
        if(e.which == 84 && isCtrl && isAlt) {
            var background = $(".ts-translated-text").css( 'background' );            
            if (!$(document).data('translationSwitcherOn')){
                $(document).data('translationSwitcherOn',true);    
                $("#tstranslate_untranslatable_strings").show();

                $(".ts-translated-text").css( 'background', 'red' );

                $(".ts-translated-text").on( 'click', function() {
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
                        '<input type="submit" value="Store" />' +
                        '<input class="ts-translate-cancel-button" type="button" value="Cancel" />' +
                        '</form>'
                        $('.ts-translate-cancel-button').on( 'click', function(evt){
                            evt.stopPropagation();
                            var trans_span = _tstranslatedtext;
                            trans_span.html( trans_span.attr( 'translation' ) );
                            trans_span.data('editMode',false);
                        });
                    }
                });
                return false;
            }
            else {
                $(document).data('translationSwitcherOn',false);
                $(".tstranslate_untranslatable_strings").hide();
                $(".ts-translated-text").each(function() {
                    var _tstranslatedtext = $(this);
                    if ($(this).find('.ts-translate-cancel-button').length > 0) {
                        var trans_span = _tstranslatedtext;
                        trans_span.html( trans_span.attr( 'translation' ) );
                        trans_span.data('editMode',false);
                    }
                });
                $(".ts-translated-text").off('click');
                $(".ts-translated-text").css( 'background', background );
                e.preventDefault();
            }
        }
    });
});
