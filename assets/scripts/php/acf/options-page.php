<?php
// ACF Options Page для глобальных настроек
if ( function_exists('acf_add_options_page') ) {
    acf_add_options_page([
        'page_title' => 'Настройки сайта',
        'menu_title' => 'Настройки сайта',
        'menu_slug'  => 'site-settings',
        'capability' => 'edit_theme_options',
        'redirect'   => false,
    ]);
}
