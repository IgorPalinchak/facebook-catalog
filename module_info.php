<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

$com_info = [
             'menu_name'   => lang('Интеграция с Facebook Marketing Catalog', 'facebook_feed'), // Menu name
             'description' => '', // Module Description
             'admin_type'  => 'window', // Open admin class in new window or not. Possible values window/inside
             'window_type' => 'xhr', // Load method. Possible values xhr/iframe
             'w'           => 600, // Window width
             'h'           => 550, // Window height
             'version'     => '0.1', // Module version
             'author'      => 'andry.corp.imagecms@gmail.com', // Author info
             'icon_class'  => 'fa fa-google',// Module menu icon
            ];

/* End of file module_info.php */

  /*https://developers.facebook.com/docs/reference/php/#sdk-----php----github  SDK!!!
   *
   * APP id 158252614868113
   * APP secret 2be58fbb5d402bf63f3d1e7e45892a2c
   * https://business.facebook.com/home/accounts?business_id=1247262862072814
   *https://developers.facebook.com/tools/accesstoken
   *User Token EAACP7g7k3JEBAAFv3hpdsNSv1sEBn3Brl7rj1ZBdh8qwErBxWRPENIvW8T7oLHzDEPIdcM5mNvY84wRNhsKYxBYNwEZBHZAx8CxPFpSv3ZCFDJGurpiUyNy3hZCmwZC1sEwsUGfnYZAQzbfwudBnRgM9RDZCmU0jEls15iSgpD2XogZDZD
   * App Token	158252614868113|w9MlkHpzF-8RbghfbpyJFCOtcRo
   *
   *
   * маркер доступа
   * EAACP7g7k3JEBACjbBnfydgBgmd9yc9MPz7YKX5Sau1d2PZAs4wlRmxeBoOxCgYoTgpsy7JmlnhDhwtpsVnRk2nOuFK3ypxYpTKvT5XlCyxbD71fAuGkhvL443V7ep9jRbswczvb2zCXyfZBx5R3aMnJ5w0nm2DHNBlWnOkDgZDZD
*/

  /*Модуль установил
http://vuhocom.imagecms.com.ua/admin/components/init_window/facebook_feed
нужно внести настройки . Сохранить. перегрузить страницу .
если в правом блоке будет ошибка http://storage5.static.itmages.com/i/18/0419/h_1524126852_9750679_f7ab7776d9.png  значит настройки внесені неверно. нужно сверить с Фейсбуком, отредактировать, СОХРАНИТЬ И ПЕРЕГРУЗИТЬ страницу.
когда ошибка пропала, переходим на раздел создания каталогов.

2.http://vuhocom.imagecms.com.ua/admin/components/cp/facebook_feed/facebook_catalogs
Создав каталог , он автоматически создастся в каталоге фейсбука, но к нему еще не будет создан фид товаров (Ресурс в каталоге в фейсбуке, с которого Фейсбук будет тащит товарі с сайта.
)http://storage1.static.itmages.com/i/18/0419/h_1524127055_5414639_30b3f640dc.png

нажав на переключатель в фейсбуке создается фид. далее фейсбук по расписанию читает сайт.

Если віключчить опцию, то Фиид удалиться и из Фейсбука вместе с ранее вігруженніми товарами.

Если удалить каталог в модуле, то каталог удалиться и из фейсбука, вместе с фидами и товарами.

Внимание!!!!
в каталоге на фейсбуке может біть несколько фидов, они могут біть созданні НЕ ТОЛЬКО С САЙТА. С Сайта создается ТОЛЬКО 1 фид для одного каталога.*/