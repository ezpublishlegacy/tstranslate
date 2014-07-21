{if ezini( 'TSTranslateSettings', 'TSTranslate', 'tstranslate.ini' )|eq( 'enabled' )}
    {def $has_access = fetch( 'user', 'has_access_to', hash( 'module', 'tstranslate',
                                                             'function', 'write' ) )}
    {if $has_access}
        {ezscript_require( 'tstranslate.js' )}

        {def $tsTranslateExcluded = ezhttp( 'ts-translate-excluded' , 'session' )}
        {if gt( $tsTranslateExcluded|count(), 0)}
            {ezcss_require( 'tstranslate.css' )}

            <div id="tstranslate_untranslatable_strings" style="display: none;">
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
