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
     * @since   5.0.0
     *
     * @param   void
     * @return  void
     */
    public function initialize() {
        $this->name = 'eloquent_model';
        $this->label = 'Eloquent Model';
        $this->category = __('Custom');
        $this->object_type = 'post';
    }

    /**
     * Returns an array of possible values for this location.
     *
     * @date    9/4/20
     * @since   5.9.0
     *
     * @param   array $rule A location rule.
     * @return  array
     */
    public function get_values($rule) {
        $phpLoader = new Loader(Helper::getEloquentModelsDirs());
        $eloquentModelFiles = $phpLoader->findFiles();
        $choices = [];

        foreach($eloquentModelFiles as $eloquentModelFile) {
            $className = \pathinfo($eloquentModelFile, PATHINFO_FILENAME);

            $choices[$className] = $className;
        }

        return $choices;
    }

    /**
     * Matches the provided rule against the screen args returning a bool result.
     *
     * @date    9/4/20
     * @since   5.9.0
     *
     * @param   array $rule The location rule.
     * @param   array $screen The screen args.
     * @param   array $field_group The field group settings.
     * @return  bool
     */
    public function match($rule, $screen, $field_group)
    {
        global $currentAcfEditableModel, $currentAcfEditablePage;
        $currentModel = substr($currentAcfEditableModel, strrpos($currentAcfEditableModel, '\\') + 1);
        $ruleCurrentModel = $rule['value'];

        if (! array_key_exists('options_page', $screen) || $screen['options_page'] !== $currentAcfEditablePage) {
            return false;
        }

        if ($rule['operator'] === '==' && $ruleCurrentModel === $currentModel) {
            return true;
        }

        if ($rule['operator'] === '!=' && $ruleCurrentModel !== $currentModel) {
            return true;
        }

        return false;
    }

}