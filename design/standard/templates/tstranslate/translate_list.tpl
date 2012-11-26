{if ezini( 'TSTranslateSettings', 'TSTranslate', 'tstranslate.ini' )|eq( 'enabled' )}
    {def $has_access = fetch( 'user', 'has_access_to', hash( 'module', 'tstranslate',
                                                             'function', 'set' ) )}
    {if $has_access}
        <input type="hidden" id="tstranslate_ezurl" value={"/"|ezurl()} />
        {ezscript_require( 'tstranslate.js' )}

        {def $tsTranslateExcluded = ezhttp( 'ts-translate-excluded' , 'session' )}
        {if gt( $tsTranslateExcluded|count(), 0)}
            {literal}
            <style type="text/css">
                #tstranslate_untranslatable_strings {
                    display: none;
                    width: 100%;
                    background-color: #383838;
                    overflow: auto;
                    max-height: 500px;
                    padding: 5px;
                }
                #tstranslate_untranslatable_strings {
                    color: white;
                }
                .tstranslate_exception {
                    padding: 5px 0;
                }
                .tstranslate_exception .ts-translation-context {
                    font-size: small;
                }
            </style>
            {/literal}
            <div id="tstranslate_untranslatable_strings">
                <p>{"The following strings can not be translated inline because they may mangle the HTML code. They will have effect on the page you are looking at though."|i18n( "makingwaves/tstranslate" )}</p>
                {foreach $tsTranslateExcluded as $t}
                    <div class="tstranslate_exception">
                        <span class="ts-translated-text" alt="{$t.context}" title="{$t.source}" original="{$t.original}" translation="{$t.translation|wash()}">{$t.translation}</span>
                        <span class="ts-translation-context">[Context: {$t.context} {if $t.comment|ne( '' )}, comment: {$t.comment}{/if}]</span>
                    </div>
                {/foreach}
            </div>
        {/if}
    {/if}
{/if}
