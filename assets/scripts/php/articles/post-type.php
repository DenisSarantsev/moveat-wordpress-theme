<?php
// ----------------------------------- Переименование стандартного типа записей в "Статьи"
add_filter('post_type_labels_post', function ($labels) {
	$labels->name = 'Статьи';
	$labels->singular_name = 'Статья';
	$labels->add_new = 'Добавить статью';
	$labels->add_new_item = 'Добавить статью';
	$labels->edit_item = 'Редактировать статью';
	$labels->new_item = 'Новая статья';
	$labels->view_item = 'Просмотреть статью';
	$labels->search_items = 'Искать статьи';
	$labels->not_found = 'Статей не найдено';
	$labels->not_found_in_trash = 'В корзине статей нет';
	$labels->all_items = 'Все статьи';
	$labels->menu_name = 'Статьи';
	$labels->name_admin_bar = 'Статья';
	return $labels;
});
?>