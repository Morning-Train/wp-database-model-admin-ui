<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

use ACF_Location;
use Morningtrain\PHPLoader\Loader;

class AcfEloquentModelLocation extends ACF_Location
{
    /**
     * Initializes props.
     *
     * @date    5/03/2014
     *
     * @since   5.0.0
     *
     * @param   void
     * @return  void
     */
    public function initialize()
    {
        $this->name = 'eloquent-model';
        $this->label = 'Eloquent Model';
        $this->category = __('Eloquent');
        $this->object_type = 'eloquent-model';
    }

    /**
     * Returns an array of possible values for this location.
     *
     * @date    9/4/20
     *
     * @since   5.9.0
     *
     * @param  array  $rule A location rule.
     * @return  array
     */
    public function get_values($rule)
    {
        $phpLoader = new Loader(Helper::getEloquentModelsDirs());
        $eloquentModelFiles = $phpLoader->findFiles();
        $choices = [];

        foreach ($eloquentModelFiles as $eloquentModelFile) {
            $className = \pathinfo($eloquentModelFile, PATHINFO_FILENAME);

            $choices[$className] = $className;
        }

        return $choices;
    }

    /**
     * Matches the provided rule against the screen args returning a bool result.
     *
     * @date    9/4/20
     *
     * @since   5.9.0
     *
     * @param  array  $rule The location rule.
     * @param  array  $screen The screen args.
     * @param  array  $field_group The field group settings.
     * @return  bool
     */
    public function match($rule, $screen, $field_group)
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null || ($currentModelPage->acfCreatePage === null && $currentModelPage->acfEditPage === null)) {
            return false;
        }

        $acfCreatePageSlug = $currentModelPage->acfCreatePage->pageSlug ?? null;
        $acfEditPageSlug = $currentModelPage->acfEditPage->pageSlug ?? null;

        if (
            empty($screen['options_page']) ||
            ! in_array($screen['options_page'], [$acfCreatePageSlug, $acfEditPageSlug])
        ) {
            return false;
        }

        $basename = basename(str_replace('\\', '/', $currentModelPage->model));

        if ($rule['operator'] === '==' && $rule['value'] === $basename) {
            return true;
        }

        if ($rule['operator'] === '!=' && $rule['value'] !== $basename) {
            return true;
        }

        return false;
    }
}
