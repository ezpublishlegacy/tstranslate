tstranslate
===========

Requirements:
* eZ Publish version 4.5 and up; tested on 4.7
* jQuery version <something>; tested on 1.7.2, 1.5.1

Installation:

1. Put tstranslate extension code into <ezRoot>/extension/tstranslate/
2. Add ActiveAccessExtensions[]=tstranslate in your frontend siteaccesses site.ini.append.php  
   Do not use ActiveExtensions[], since the settings will not be overriden correctly
3. Modify file <ezRoot>/config.php (or copy from config.php-RECOMMENDED) and add line:  
   define( 'EZP_AUTOLOAD_ALLOW_KERNEL_OVERRIDE', true );  
   This is because TS Translate requires a kernel hack to work.  
   Class ezpI18n will be overridden. The included class is from eZ Publish 4.7.  
   The changed section in this class is clearly defined in the source code, to facilitate easier upgrade.
4. Then run the autoload update scripts like this:
   php bin/php/ezpgenerateautoloads.php -o
   php bin/php/ezpgenerateautoloads.php -e
5. Include template translate_list.tpl at the bottom of your pagelayout.tpl template (after all translation strings have been displayed):
   {include uri='design:tstranslate/translate_list.tpl'}
6. Override tstranslate.ini.append.php in your frontend siteaccesses, and set the following parameters in [TSTranslateSettings]:  
   * TSTranslate=enabled  
   * TranslationsFolder, specify the folder of the translations files that should be editable, typically:  
     TranslationsFolder=extension/my_extension/translations/  
     Translation files you want to edit must be writeable by Apache  
   * ExcludeList, specify your custom translations strings that can not be edited inline,  
     for instance button texts etc.  
     You can specify a whole context by it's name, or a single string on the format "&lt;context name&gt;;&lt;string&gt;"  

BE AWARE:  
   Every page view by users with access to tstranslate will clear a bunch of caches (all template cache and content view cache),
   so it is not recommended to use this functionality on production server.
   
To enable edit mode hit CTRL + ALT + t.


TODO:
* Make shortcut keys configurable
* Make texts inside links be editable inline (need to disable the original link)
* Make it work for versions earlier than 4.5? (requires a content read workflow solution to replace event listener)
