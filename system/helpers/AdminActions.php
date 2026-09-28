<?php

/**
* AdminActionsHelper — единая система кнопок действий админ-панели.
* @package Helpers
*/
class AdminActionsHelper {

    /**
    * Реестр типов действий: тип => иконка по умолчанию.
    * Цвета, микро-анимации и прочая стилистика задаются в actions.css
    * по классу .act--{type}.
    *
    * @return array
    */
    public static function types(): array {
        return [
            'view'        => ['icon' => 'eye'],                  // просмотр на сайте
            'edit'        => ['icon' => 'pencil'],               // редактировать
            'delete'      => ['icon' => 'trash'],                // удалить
            'publish'     => ['icon' => 'check-lg'],             // опубликовать / одобрить
            'draft'       => ['icon' => 'archive'],              // в черновики
            'approve'     => ['icon' => 'check-lg'],             // подтвердить
            'ban'         => ['icon' => 'lock'],                 // заблокировать
            'unban'       => ['icon' => 'unlock'],               // разблокировать
            'add'         => ['icon' => 'plus-circle'],          // добавить (например, дочерний пункт)
            'info'        => ['icon' => 'info-circle'],          // информация
            'chat'        => ['icon' => 'chat-dots'],            // к комментариям
            'assign'      => ['icon' => 'trophy'],               // выдать достижение
            'settings'    => ['icon' => 'gear-fill'],            // настройки
            'permissions' => ['icon' => 'shield-lock'],          // права доступа
            'list'        => ['icon' => 'list-ul'],              // элементы списка
            'fields'      => ['icon' => 'input-cursor-text'],    // пользовательские поля
            'toggle'      => ['icon' => 'power'],                // включить/выключить
            'goto'        => ['icon' => 'arrow-right'],          // перейти
            'custom'      => ['icon' => 'dot'],                  // произвольная (иконка через 'icon')
        ];
    }

    /**
    * Рендер одной кнопки действия.
    *
    * @param array $action Параметры кнопки
    * @return string HTML
    */
    public static function button(array $action): string {
        $types = self::types();

        $type = (string)($action['type'] ?? 'custom');
        if (!isset($types[$type])) {
            $type = 'custom';
        }

        $icon    = (string)($action['icon'] ?? $types[$type]['icon']);
        $title   = trim((string)($action['title'] ?? ''));
        $titleHt = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $disabled = !empty($action['disabled']);

        $tag = strtolower((string)($action['tag'] ?? 'a'));
        if ($disabled || !in_array($tag, ['a', 'button'], true)) {
            $tag = $disabled ? 'button' : 'a';
        }
        $isButton = ($tag === 'button');

        $classes = 'act act--' . $type;
        if (!empty($action['class'])) {
            $classes .= ' ' . trim((string)$action['class']);
        }
        if ($disabled) {
            $classes .= ' is-off';
        }

        $attrs = '';

        if ($isButton) {
            $attrs .= ' type="' . htmlspecialchars((string)($action['button_type'] ?? 'button'), ENT_QUOTES, 'UTF-8') . '"';
        } else {
            $url = (string)($action['url'] ?? '#');
            $attrs .= ' href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"';
            if (!empty($action['target'])) {
                $attrs .= ' target="' . htmlspecialchars((string)$action['target'], ENT_QUOTES, 'UTF-8') . '"';
            }
            if (!empty($action['rel'])) {
                $attrs .= ' rel="' . htmlspecialchars((string)$action['rel'], ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        if ($titleHt !== '') {
            $attrs .= ' aria-label="' . $titleHt . '"';
            $attrs .= ' data-tip="' . $titleHt . '"';
        }

        if (!empty($action['confirm'])) {
            $js = "return confirm('" . addslashes((string)$action['confirm']) . "')";
            $attrs .= ' onclick="' . htmlspecialchars($js, ENT_QUOTES, 'UTF-8') . '"';
        } elseif (!empty($action['onclick'])) {
            $attrs .= ' onclick="' . htmlspecialchars((string)$action['onclick'], ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($disabled) {
            $attrs .= ' disabled';
        }

        if (!empty($action['attrs'])) {
            $attrs .= ' ' . trim((string)$action['attrs']);
        }

        $iconHtml = bloggy_icon('bs', $icon, '16 16', 'currentColor', 'act-ic');

        return '<' . $tag . ' class="' . $classes . '"' . $attrs . '>' . $iconHtml . '</' . $tag . '>';
    }

    /**
    * Рендер группы («рельса») с кнопками действий.
    *
    * @param array $actions Массив кнопок;
    * @param array $options
    * @return string HTML
    */
    public static function group(array $actions, array $options = []): string {
        $wrap = array_key_exists('wrap', $options) ? (bool)$options['wrap'] : true;
        $align = (string)($options['align'] ?? 'end');

        $groupClass = 'act-group';
        if (!empty($options['class'])) {
            $groupClass .= ' ' . trim((string)$options['class']);
        }

        $groupAttrs = '';
        if (!empty($options['attrs'])) {
            $groupAttrs = ' ' . trim((string)$options['attrs']);
        }

        $html = '<div class="' . $groupClass . '"' . $groupAttrs . ' role="group"';
        if ($title = trim((string)($options['title'] ?? ''))) {
            $html .= ' aria-label="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"';
        }
        $html .= '>';

        foreach ($actions as $action) {
            if (is_array($action)) {
                $html .= self::button($action);
            }
        }

        $html .= '</div>';

        if ($wrap) {
            $cellClass = 'act-cell' . ($align !== '' ? ' act-cell--' . $align : '');
            $html = '<div class="' . $cellClass . '">' . $html . '</div>';
        }

        return $html;
    }
}

if (!function_exists('admin_action_group')) {
    /**
    * Группа кнопок действий
    * @param array $actions Массив кнопок
    * @param array $options Опции группы
    * @return string HTML
    */
    function admin_action_group(array $actions, array $options = []): string {
        return AdminActionsHelper::group($actions, $options);
    }
}

if (!function_exists('admin_action')) {
    /**
    * Одна кнопка действия
    * @param array $action Параметры кнопки
    * @return string HTML
    */
    function admin_action(array $action): string {
        return AdminActionsHelper::button($action);
    }
}
