<?php
/**
 * File containing the ezpI18n class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 4.7.0
 * @package kernel
 */

class ezpI18n
{
    /**
     * Indicates if text translation is enabled or not.
     * @see ezpI18n::isEnabled()
     *
     * @var null|bool
     */
    protected static $isEnabled = null;

    /**
     * Replaces keys found in \a $text with values in \a $arguments.
     * If \a $arguments is an associative array it will use the argument
     * keys as replacement keys. If not it will convert the index to
     * a key looking like %n, where n is a number between 1 and 9.
     *
     * @param string $string
     * @param array $arguments
     * @return string
    */
    protected static function insertArguments( $text, $arguments )
    {
        if ( is_array( $arguments ) )
        {
            $replaceList = array();
            foreach ( $arguments as $argumentKey => $argumentItem )
            {
                if ( is_int( $argumentKey ) )
                    $replaceList['%' . ( ($argumentKey%9) + 1 )] = $argumentItem;
                else
                    $replaceList[$argumentKey] = $argumentItem;
            }
            $text = strtr( $text, $replaceList );
        }
        return $text;
    }

    /**
     * Enabled if the site.ini settings RegionalSettings/TextTranslation is set to disabled
     *
     * @return bool
    */
    protected static function isEnabled()
    {
        if ( self::$isEnabled === null )
        {
            $ini = eZINI::instance();
            $useTextTranslation = $ini->variable( 'RegionalSettings', 'TextTranslation' ) != 'disabled';
            self::$isEnabled = $useTextTranslation || eZTranslatorManager::dynamicTranslationsEnabled();
        }
        return self::$isEnabled;
    }

    /**
     * Resets the state ezpI18n class.
     */
    public static function reset()
    {
        self::$isEnabled = null;
    }

    /**
     * Translates the source \a $source with context \a $context and optional comment \a $comment
     * and returns the translation if translations are enabled.
     * Uses {@link ezpI18n::translateText()}
     *
     * Example:
     * translate( 'content/view', 'There are %count nodes in this list out of %total total nodes.', 'Children view of nodes for whole site', array( '%count' => $c, '%total' => $t ) );
     *
     * @param string $context
     * @param string $source
     * @param string|null $comment
     * @param array|null $arguments
     * @return string
     */
    public static function tr( $context, $source, $comment = null, $arguments = null )
    {
        if ( self::isEnabled() )
        {
            return self::translateText( $context, $source, $comment, $arguments );
        }
        return self::insertArguments( $source, $arguments );
    }

    /**
     * Translates the source \a $source with context \a $context and optional comment \a $comment
     * and returns the translation if locale code is not eng-GB.
     * Uses {@link eZTranslatorMananger::translate()} to do the actual translation
     *
     * Example:
     * translateText( 'content/view', 'There are %count nodes in this list out of %total total nodes.', 'Children view of nodes for whole site', array( '%count' => $c, '%total' => $t ) );
     *
     * @param string $context
     * @param string $source
     * @param string|null $comment
     * @param array|null $arguments
     * @return string
     */
    protected static function translateText( $context, $source, $comment = null, $arguments = null )
    {
        $localeCode = eZLocale::instance()->localeFullCode();
        if ( $localeCode == 'eng-GB' )
        {
            // we don't have ts-file for 'eng-GB'.
            return self::insertArguments( $source, $arguments );
        }

        $ini = eZINI::instance();
        $useCache = $ini->variable( 'RegionalSettings', 'TranslationCache' ) != 'disabled';
        eZTSTranslator::initialize( $context, $localeCode, 'translation.ts', $useCache );

        // Bork translation: Makes it easy to see what is not translated.
        // If no translation is found in the eZTSTranslator, a Bork translation will be returned.
        // Bork is different than, but similar to, eng-GB, and is enclosed in square brackets [].
        $developmentMode = $ini->variable( 'RegionalSettings', 'DevelopmentMode' ) != 'disabled';
        if ( $developmentMode )
        {
            eZBorkTranslator::initialize();
        }

        $man = eZTranslatorManager::instance();
        $trans = $man->translate( $context, $source, $comment );

        // TSTRANSLATE HACK START
        $ini = eZINI::instance( 'tstranslate.ini' );
        $enabled = $ini->variable( 'TSTranslateSettings', 'TSTranslate' ) == 'enabled';
        if ( $trans !== null || $enabled )
        {
            if ( $trans === null )
            {
                $trans = $source;
            }
            $translation = self::insertArguments( $trans, $arguments );

            if ( $enabled )
            {
                $has_access = eZFunctionHandler::execute( 'user', 'has_access_to', array( 'module' => 'tstranslate',
                                                                                          'function' => 'write' ) );
                if ( $has_access )
                {
                    $exclude_list = $ini->variable( 'TSTranslateSettings', 'ExcludeList' );
                    $excluded = false;
                    foreach ( $exclude_list as $e )
                    {
                        list( $section, $string ) = (sizeof(explode( ";", $e )) > 1 ? explode( ";", $e ) : array($e,null));
                        if ( $section == $context )
                        {
                            if ( !isset( $string ) || $string == $source )
                            {
                                if ( !isset( $_SESSION["ts-translate-excluded"] ) )
                                {
                                    $_SESSION["ts-translate-excluded"] = array();
                                }
                                if ( !isset( $_SESSION["ts-translate-excluded"][hash( 'md5', $source )] ) )
                                {
                                    $_SESSION["ts-translate-excluded"][hash( 'md5', $source )] = array( "source" => htmlspecialchars( $source ),
                                                                "original" => htmlspecialchars( $trans ),
                                                                "translation" => htmlspecialchars( $translation ),
                                                                "context" => $context,
                                                                "comment" => htmlspecialchars( $comment ) );
                                }
                                $excluded = true;
                                break;
                            }
                        }
                    }

                    if ( !$excluded )
                    {
                        $translation = '<span class="ts-translated-text" alt="' . $context . '" title="' . htmlspecialchars( $source ) . '" original="' . htmlspecialchars( $trans ) . '" translation="' . htmlspecialchars( $translation ) . '">' . $translation . '</span>';
                    }
                }
            }

            return $translation;
        }
        // TSTRANSLATE HACK END

        if ( $comment != null and strlen( $comment ) > 0 )
            eZDebug::writeDebug( "Missing translation for message in context: '$context' with comment: '$comment'. The untranslated message is: '$source'", __METHOD__ );
        else
            eZDebug::writeDebug( "Missing translation for message in context: '$context'. The untranslated message is: '$source'", __METHOD__ );

        return self::insertArguments( $source, $arguments );
    }
}

?>
