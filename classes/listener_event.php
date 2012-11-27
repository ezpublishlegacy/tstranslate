<?php

class tsTranslateListenerEvent
{
    static public function clearSession( eZURI $uri )
    {
        // Remove data from previous page request
        if ( isset( $_SESSION['ts-translate-excluded'] ) )
        {
            $_SESSION['ts-translate-excluded'] = array();
        }

        // Check if we should clear cache
        $ini = eZINI::instance( 'tstranslate.ini' );
        if ( $ini->variable( 'TSTranslateSettings', 'TSTranslate' ) == 'enabled' )
        {
            $has_access = eZFunctionHandler::execute( 'user', 'has_access_to', array( 'module' => 'tstranslate',
                                                                                      'function' => 'write' ) );
            if ( $has_access )
            {
                if ( $ini->variable( 'TSTranslateSettings', 'TSCacheClean' ) == "enabled" )
                {
                    // Need to clear cache for each page request for the ts editor!
                    eZCache::clearByID( array( 'content', 'template', 'template-block', 'template-override' ) );
                }
            }
        }
    }
}

?>
