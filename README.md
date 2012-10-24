tstranslate
===========

Installation:

0. Put mwtranslate directory into <ezRoot>/extension 
1. Add ActiveExtensions[]=mwtranslate to your site.ini.append.php
2. Modify file <ezRoot>/config.php (or copy from config.php-RECOMMENDED) and add line: define( 'EZP_AUTOLOAD_ALLOW_KERNEL_OVERRIDE', true );
   This is because TS Translator requires a kernel hack to work. Class ezpI18n will be overwritten. The included class is from eZ Publish 4.7. 
   The changed section in this class is clearly defined in the source code.
3. Then run the autoload update script like this: php bin/php/ezpgenerateautoloads.php -o
4. Include template translate_list at the bottom of your template (after all translation entries have been displayed), most probably it will be pagelayout.tpl: {include uri='design:mwtranslate/translate_list.tpl'}
5. Edit extension/mwtranslate/settings/mwtranslate.ini.append.php and set values of ExcludeList[]
    This is what will be excluded from translation on frontpage in order not to mess with system messages. 
    You can set either all section:
    ExcludeList[]=design/ezwebin/link
    or a specific string within section:
    ExcludeList[]=kernel/navigationpart;Media library
6. Edit extension/mwtranslate/settings/mwtranslate.ini.append.php and set TranslationsFolder value to folder containing translations you want to edit. Translation files you want to edit must be writeable by Apache

BE AWARE:
   Every update of translation file will clear a bunch of caches so it is not recommended to use this functionality on production server.
   
To enable edit mode hit CTRL + ALT + t

List of remaining tasks to look at:

* Error page - error messages raised by set.php should be displayed to editor