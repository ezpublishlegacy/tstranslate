<?php

class tsTranslateListenerEvent
{
    static public function clearSession( eZURI $uri )
    {
        if ( isset( $_SESSION['ts-translate-excluded'] ) )
        {
            $_SESSION['ts-translate-excluded'] = array();
        }
    }
}

?>
