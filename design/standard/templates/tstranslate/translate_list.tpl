{def $tsTranslatedExcluded = ezhttp( 'ts-translated-excluded' , 'session' )}
<input type="hidden" id="mw_ezurl" value={"/"|ezurl()} />
{if gt( $tsTranslatedExcluded|count(), 0)}
    {literal}
    <style type="text/css">
        #untranslatedDiv{
            display: none;
            width: 100%;
            background-color: #383838;
            overflow: auto;
            max-height: 500px;
            padding: 5px;
        }
        #untranslatedDiv p{
            color: white;
        }
        .tstranslate_exception{
            padding: 5px 0;
            color: white;
        }
    </style>
    {/literal}
    <div id="untranslatedDiv">
        <p>{"The following strings can not be translated inline because they may mangle the HTML code. They will have effect on the page you are looking at though."|i18n( "makingwaves/tstranslate" )}</p>
        {foreach $tsTranslatedExcluded as $t}
            <div class="tstranslate_exception">
                <span class="ts-translated-text" alt="{$t.context}" title="{$t.source}" original="{$t.original}" translation="{$t.translation|wash()}">{$t.translation}</span>
                (Section: {$t.context} {if $t.comment|ne( '' )}, comment: {$t.comment}{/if})
            </div>
        {/foreach}
    </div>
{/if}
