tstranslate
===========

Installation:

1. Put tstranslate extension code into <ezRoot>/extension/tstranslate/
2. Add ActiveExtensions[]=tstranslate in your site.ini.append.php
3. Modify file <ezRoot>/config.php (or copy from config.php-RECOMMENDED) and add line:  
   define( 'EZP_AUTOLOAD_ALLOW_KERNEL_OVERRIDE', true );  
   This is because TS Translate requires a kernel hack to work.  
   Class ezpI18n will be overridden. The included class is from eZ Publish 4.7.  
   The changed section in this class is clearly defined in the source code, to facilitate easier upgrade.
4. Then run the autoload update script like this:
   php bin/php/ezpgenerateautoloads.php -o
5. Include template translate_list.tpl at the bottom of your pagelayout.tpl template (after all translation strings have been displayed):
   {include uri='design:tstranslate/translate_list.tpl'}
6. Override tstranslate.ini.append.php and set the following parameters in [TSTranslateSettings]:  
   * TSTranslate=enabled  
   * TranslationsFolder, specify the folder of the translations files that should be editable, typically:  
     TranslationsFolder=extension/my_extension/translations/  
     Translation files you want to edit must be writeable by Apache  
   * ExcludeList, specify your custom translations strings that can not be edited inline,  
     for instance button text.  
     You can specify a whole context by it's name, or a single string on the format "&lt;context name&gt;;&lt;string&gt;"  

BE AWARE:  
   Every update of translation file will clear a bunch of caches (all template cache),
   so it is not recommended to use this functionality on production server.
   
To enable edit mode hit CTRL + ALT + t.
