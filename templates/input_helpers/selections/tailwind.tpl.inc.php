<?php

use WebFramework\Core\WF;

WF::verify(isset($args['template_parameters']['colors']), 'No colors defined');
$colors = $args['template_parameters']['colors'];
$required_colors = ['focus:border', 'focus:ring'];
WF::verify(array_diff(array_keys($colors), $required_colors) == array_diff($required_colors, array_keys($colors)), 'Missing required colors');

WF::verify(isset($args['template_parameters']['default_width']), 'No default_width defined');
$default_width = $args['template_parameters']['default_width'];

$parameters = $args['parameters'];

$model_fmt = (strlen($parameters['model'])) ? "x-model=\"{$parameters['model']}\"" : '';
$show_fmt = (strlen($parameters['show'])) ? "x-cloak x-show=\"{$parameters['show']}\"" : '';
$width_fmt = (strlen($parameters['width'])) ? $parameters['width'] : $default_width;
$value_param = $parameters['value'] ?? '';

echo <<<HTML
<div {$show_fmt} class="{$width_fmt}">
  <label for="{$parameters['id']}" class="block text-sm font-medium text-gray-700">
    {$parameters['label']}
  </label>
HTML;

if (strlen($parameters['extra_label']))
{
    echo <<<HTML
  <p class="text-sm text-gray-500">{$parameters['extra_label']}</p>
HTML;
}

echo <<<HTML
  <select {$model_fmt}
          id="{$parameters['id']}"
          name="{$parameters['name']}"
          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none {$colors['focus:ring']} {$colors['focus:border']} sm:text-sm">
HTML;

foreach ($parameters['options'] as $value => $name)
{
    $selected_fmt = (strlen($value_param) == strlen($value) && $value_param == $value) ? 'selected' : '';

    echo <<<HTML
    <option value="{$value}" {$selected_fmt}>{$name}</option>
HTML;
}

echo <<<'HTML'
  </select>
</div>
HTML;
