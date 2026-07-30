<?php
/**
 * Minimal Elementor stubs for static analysis only.
 *
 * Elementor is an optional integration: the widget files are required at
 * runtime by the service that registers them, and only after Elementor has
 * booted. PHPStan has no Elementor to look at, so without these declarations
 * every widget reads as extending an unknown class and the whole integration
 * falls out of analysis.
 *
 * This covers exactly the surface our widgets touch, nothing more. There is no
 * official php-stubs package for Elementor, and the community ones are not
 * worth a supply-chain dependency for thirteen symbols.
 *
 * Never loaded at runtime. Referenced from phpstan.neon.dist only.
 */

declare(strict_types=1);

namespace Elementor;

if (! class_exists(Controls_Manager::class)) {
    /**
     * Control type constants used by our widget control definitions.
     */
    class Controls_Manager
    {
        public const TEXT = 'text';
        public const NUMBER = 'number';
        public const SWITCHER = 'switcher';
        public const RAW_HTML = 'raw_html';
        public const SELECT = 'select';
    }
}

if (! class_exists(Widget_Base::class)) {
    /**
     * The base class our widgets extend.
     */
    abstract class Widget_Base
    {
        /**
         * @param array<string, mixed> $data
         * @param array<string, mixed>|null $args
         */
        public function __construct($data = [], $args = null)
        {
        }

        public function get_name(): string
        {
            return '';
        }

        public function get_title(): string
        {
            return '';
        }

        public function get_icon(): string
        {
            return '';
        }

        /** @return string[] */
        public function get_categories(): array
        {
            return [];
        }

        /** @return string[] */
        public function get_keywords(): array
        {
            return [];
        }

        /**
         * @param string $id
         * @param array<string, mixed> $args
         * @param array<string, mixed> $options
         */
        public function add_control($id, $args, $options = []): void
        {
        }

        /**
         * @param string $id
         * @param array<string, mixed> $args
         */
        public function start_controls_section($id, $args = []): void
        {
        }

        public function end_controls_section(): void
        {
        }

        /** @return array<string, mixed> */
        public function get_settings_for_display($setting_key = null)
        {
            return [];
        }

        protected function register_controls(): void
        {
        }

        protected function render(): void
        {
        }
    }
}

if (! class_exists(Widgets_Manager::class)) {
    /**
     * The registrar Elementor hands to our service.
     */
    class Widgets_Manager
    {
        /** @param Widget_Base $widget */
        public function register($widget): bool
        {
            return true;
        }
    }
}
