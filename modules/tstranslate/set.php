<?php

function findContextNode( $root, $context_name )
{
    $children = $root->childNodes;
    for ( $i = 0; $i < $children->length; $i++ )
    {
        $doc_child = $children->item( $i );

        if ( $doc_child->nodeType == XML_ELEMENT_NODE )
        {
            if ( $doc_child->tagName == 'context' )
            {
                $context_children = $doc_child->childNodes;
                $context_match = false;
                for ( $j = 0; $j < $context_children->length; $j++ )
                {
                    $context_child = $context_children->item( $j );

                    if ( $context_child->nodeType == XML_ELEMENT_NODE )
                    {
                        if ( $context_child->tagName == 'name' AND $context_child->nodeValue == $context_name )
                        {
                            return $doc_child;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function findTranslationNode( $context_node, $source_text )
{
    $context_children = $context_node->childNodes;
    for ( $j = 0; $j < $context_children->length; $j++ )
    {
        $context_child = $context_children->item( $j );

        if ( $context_child->nodeType == XML_ELEMENT_NODE )
        {
            if ( $context_child->tagName == 'message' )
            {
                $message_children = $context_child->childNodes;
                $source_match = false;
                for ( $k = 0; $k < $message_children->length; $k++ )
                {
                    $message_child = $message_children->item( $k );

                    if ( $message_child->nodeType == XML_ELEMENT_NODE )
                    {
                        if ( $message_child->tagName == 'source' AND $message_child->nodeValue == $source_text )
                        {
                            $source_match = true;
                        }
                        elseif ( $message_child->tagName == 'translation' AND $source_match )
                        {
                            return $message_child;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function addStringToContext( $doc, $context_node, $source, $translation )
{
    $message_node = $doc->createElement( 'message', '' );
    $source_element = $doc->createElement( 'source', $source);
    $translation_element = $doc->createElement( 'translation', $translation );

    $context_node->appendChild( $message_node );
    $message_node->appendChild( $source_element );
    $message_node->appendChild( $translation_element );
}

function addNewContext( DOMDocument $doc, $context )
{
    $context_node = $doc->createElement( "context" );
    $name_node = $doc->createElement( "name", $context );

    $rootNode = $doc->documentElement;
    $rootNode->appendChild( $context_node );
    $context_node->appendChild( $name_node );
    
    return $context_node;
}

define( 'MODULE_NAME', 'TS Translate Set' );
$http = eZHTTPTool::instance();
$Module = $Params['Module'];

if ( $http->hasPostVariable( 'Translation' ) )
{
    $context = $http->postVariable( 'Context' );
    $source = $http->postVariable( 'Source' );
    $translation = $http->postVariable( 'Translation' );

    if ( !empty( $context ) AND !empty( $source ) AND !empty( $translation ) )
    {
        // Input is validated. Get translations filename, and read the XML
        $localeCode = eZLocale::instance()->localeFullCode();
        $ini = eZINI::instance( 'tstranslate.ini' );
        $translations_folder = $ini->variable( 'TSTranslateSettings', 'TranslationsFolder' );
        $folder_array = explode( '/', $translations_folder );
        $folder_array[] = $localeCode;
        $folder_array[] = 'translation.ts';
        $translation_filename = implode( '/', $folder_array );
        if ( is_writable( $translation_filename ) )
        {
            $doc = new DOMDocument( '1.0', 'utf-8' );
            $success = $doc->load( $translation_filename );
            if ( $success )
            {
                if ( eZTSTranslator::validateDOMTree( $doc ) )
                {
                    // Find the XML node to change, and update the translation
                    $context_node = findContextNode( $doc->documentElement, $context );
                    if ( !$context_node )
                    {
                        $context_node = addNewContext( $doc, $context );
                    }
                    
                    //check if adding new context node went fine
                    if ( !$context_node )
                    {
                        eZDebug::writeError( "There was an error while adding new '$context' context  to xml file", MODULE_NAME );
                        return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
                    }

                    $translator_node = findTranslationNode( $context_node, $source );
                    if ( $translator_node )
                    {
                        $translator_node->nodeValue = $translation;
                    }
                    else
                    {
                        // String not found. Add a new string to specified context
                        addStringToContext( $doc, $context_node, $source, $translation );
                    }

                    // Store changes in translations file
                    $xml = $doc->saveXML();
                    if ( !file_put_contents( $translation_filename, $xml ) )
                    {
                        eZDebug::writeError( "Could not store xml in file '$translation_filename'", MODULE_NAME );
                        return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
                    }

                    //cache clearing
                    if ( $ini->variable( 'TSTranslateSettings', 'TSCacheClean' ) == "enabled" )
                    {
                        eZCache::clearByID( array("content", "template") );
                    }

                    //clearing this array as it must be rebuilded
                    if ( isset( $_SESSION["ts-translated-excluded"] ) )
                    {
                        unset( $_SESSION["ts-translated-excluded"] );
                    }
                    $http->redirect( $http->sessionVariable( 'LastAccessesURI' ) );
                   
                }
                else
                {
                    eZDebug::writeError( "XML text for file '$translation_filename' did not validate", MODULE_NAME );
                    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
                }
            }
            else
            {
                eZDebug::writeError( "Unable to load XML from file '$translation_filename'", MODULE_NAME );
                return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
            }
        }
        else
        {
            eZDebug::writeError( "Translations file '$translation_filename' is not writable", MODULE_NAME );
            return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        }
    }
    else
    {
        eZDebug::writeError( "Missing context, source or translation string", MODULE_NAME );
        return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }
}
else
{
    eZDebug::writeError( "Missing input parameters", MODULE_NAME );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

?>
