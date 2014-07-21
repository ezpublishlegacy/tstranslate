<?php

class TSTranslateSet extends ezjscServerFunctions
{
    private function findContextNode( $root, $context_name )
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

    private function findTranslationNode( $context_node, $source_text )
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

    private function addStringToContext( $doc, $context_node, $source, $translation )
    {
        $message_node = $doc->createElement( 'message', '' );
        $source_element = $doc->createElement( 'source', $source);
        $translation_element = $doc->createElement( 'translation', $translation );

        $context_node->appendChild( $message_node );
        $message_node->appendChild( $source_element );
        $message_node->appendChild( $translation_element );
    }

    private function addNewContext( DOMDocument $doc, $context )
    {
        $context_node = $doc->createElement( "context" );
        $name_node = $doc->createElement( "name", $context );

        $rootNode = $doc->documentElement;
        $rootNode->appendChild( $context_node );
        $context_node->appendChild( $name_node );

        return $context_node;
    }

    private function updateXML( $doc, $context, $source, $translation )
    {
        // Find the XML node to change, and update the translation
        $context_node = $this->findContextNode( $doc->documentElement, $context );
        if ( !$context_node )
        {
            $context_node = $this->addNewContext( $doc, $context );
        }

        // Check if adding new context node went fine
        if ( !$context_node )
        {
            eZDebug::writeError( "There was an error while adding new '$context' context  to xml file", 'TS Translate Set' );
            throw new Exception( "There was an error while adding new '$context' context  to xml file" );
        }

        $translator_node = $this->findTranslationNode( $context_node, $source );
        if ( $translator_node )
        {
            $translator_node->nodeValue = $translation;
        }
        else
        {
            // String not found. Add a new string to specified context
            $this->addStringToContext( $doc, $context_node, $source, $translation );
        }
    }

    private function getTranslationsFilename()
    {
        $localeCode = eZLocale::instance()->localeFullCode();
        $ini = eZINI::instance( 'tstranslate.ini' );
        $translations_folder = $ini->variable( 'TSTranslateSettings', 'TranslationsFolder' );
        $folder_array = explode( '/', $translations_folder );
        $folder_array[] = $localeCode;
        $folder_array[] = 'translation.ts';
        $translation_filename = implode( '/', $folder_array );
        if ( !is_writable( $translation_filename ) )
        {
            eZDebug::writeError( "Translations file '$translation_filename' is not writable", 'TS Translate Set' );
            throw new Exception( "Translations file '$translation_filename' is not writable" );
        }
        return $translation_filename;
    }

    private function setTranslation( $context, $source, $translation )
    {
        $translation_filename = $this->getTranslationsFilename();
        $doc = new DOMDocument( '1.0', 'utf-8' );
        $success = $doc->load( $translation_filename );

        if ( $success )
        {
            if ( eZTSTranslator::validateDOMTree( $doc ) )
            {
                $this->updateXML( $doc, $context, $source, $translation );

                // Store changes in translations file
                $doc->encoding = 'utf-8';
                $doc->formatOutput = true;
                $result = $doc->save( $translation_filename, LIBXML_NOEMPTYTAG );
                if ( $result === false )
                {
                    eZDebug::writeError( "Could not store xml in file '$translation_filename'", 'TS Translate Set' );
                    throw new Exception( "Could not store xml in file '$translation_filename'" );
                }
            }
            else
            {
                eZDebug::writeError( "XML text for file '$translation_filename' did not validate", 'TS Translate Set' );
                throw new Exception( "XML text for file '$translation_filename' did not validate" );
            }
        }
        else
        {
            eZDebug::writeError( "Unable to load XML from file '$translation_filename'", 'TS Translate Set' );
            throw new Exception( "Unable to load XML from file '$translation_filename'" );
        }
    }

    public static function ajaxSetTranslation()
    {
        $http = eZHTTPTool::instance();
        $context = $http->postVariable( 'Context' );
        $source = $http->postVariable( 'Source' );
        $translation = $http->postVariable( 'Translation' );

        if ( !empty( $context ) AND !empty( $source ) AND !empty( $translation ) )
        {
            $ts = new TSTranslateSet;
            $ts->setTranslation( $context, $source, $translation );
        }
        else
        {
            eZDebug::writeError( 'Missing context, source or translation string', 'TS Translate Set' );
            throw new Exception( 'Missing context, source or translation string' );
        }

        return $translation;
    }
}

?>
