<?php

/**
 * Подставляет варианты чекбоксов «Темы отзыва» из поля reviews_topics_vocabulary.
 */

if (! function_exists('moveat_reviews_acf_resolve_edit_post_id')) {
	function moveat_reviews_acf_resolve_edit_post_id() {
		if (function_exists('acf_get_valid_post_id')) {
			$pid = acf_get_valid_post_id();
			if ($pid && is_numeric($pid)) {
				return (int) $pid;
			}
		}
		if (! empty($_GET['post'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return (int) $_GET['post'];
		}
		if (! empty($_POST['post_ID'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return (int) $_POST['post_ID'];
		}
		global $post;
		if ($post && ! empty($post->ID)) {
			return (int) $post->ID;
		}
		return 0;
	}
}

if (! function_exists('moveat_reviews_acf_load_field_topic_checkboxes')) {
	function moveat_reviews_acf_load_field_topic_checkboxes($field) {
		if (empty($field['name']) || empty($field['type']) || $field['type'] !== 'checkbox') {
			return $field;
		}
		if (! preg_match('/^review_\d+_topics$/', $field['name'])) {
			return $field;
		}
		if (! function_exists('moveat_reviews_topic_choices_from_vocabulary')) {
			return $field;
		}
		$post_id = moveat_reviews_acf_resolve_edit_post_id();
		if (! $post_id) {
			$field['choices'] = [];

			return $field;
		}
		$field['choices'] = moveat_reviews_topic_choices_from_vocabulary($post_id);

		return $field;
	}
}

if (function_exists('acf_add_local_field_group')) {
	add_action(
		'acf/init',
		static function () {
			add_filter('acf/load_field', 'moveat_reviews_acf_load_field_topic_checkboxes', 20, 1);
		}
	);
}
