<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite;

use EphpicMan\TestSuite\UnitTesting\Runner;
use EphpicMan\TestSuite\UnitTesting\TestResult;

/**
 * WordPress entry point for EphpicMan Test Suite.
 *
 * The plugin owns WordPress integration and exposes the test directory filter
 * used by other plugins to register their tests.
 */
final class Plugin
{
    private static ?self $instance = null;

    private Autoloader $autoloader;

    /**
     * Creates the plugin service and registers its Composer-backed autoloader.
     */
    private function __construct()
    {
        $this->autoloader = new Autoloader(
            require dirname(__DIR__) . '/vendor/autoload.php'
        );

        $this->autoloader->addNamespace(
            'EphpicMan\\TestSuite',
            dirname(__DIR__) . '/src'
        );

        $this->autoloader->register();
    }

    /**
     * Returns the shared plugin instance.
     *
     * The singleton keeps WordPress hooks and process-wide configuration in one place.
     */
    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /** Returns the Composer-backed EphpicMan autoloader. */
    public function autoloader(): Autoloader
    {
        return $this->autoloader;
    }

    /** Registers the plugin's WordPress hooks. */
    public function boot(): void
    {
        add_action('admin_menu', [$this, 'registerAdminMenu']);
    }

    /**
     * Registers the Unit Testing admin page.
     *
     * Access is restricted to users who can manage WordPress options.
     */
    public function registerAdminMenu(): void
    {
        add_menu_page(
            'Unit Testing',
            'Unit Testing',
            'manage_options',
            'ephpicman-unit-testing',
            [$this, 'renderUnitTestingPage'],
            'dashicons-yes-alt'
        );
    }

    /**
     * Renders the Unit Testing admin page and handles test execution.
     *
     * Test execution requires the manage_options capability and a valid nonce.
     */
    public function renderUnitTestingPage(): void
    {
        $results = null;

        if (
            isset($_POST['ephpicman_run_tests'])
            && current_user_can('manage_options')
        ) {
            check_admin_referer('ephpicman_run_tests');

            $runner = new Runner($this->getTestsDirectories());
            $results = $runner->run();
        }

        $total = is_array($results) ? count($results) : 0;
        $passed = is_array($results)
            ? count(array_filter($results, static fn (TestResult $result): bool => $result->passed()))
            : 0;
        $failed = $total - $passed;
        $assertions = is_array($results)
            ? array_sum(array_map(static fn (TestResult $result): int => $result->assertions(), $results))
            : 0;
        ?>
        <div class="wrap">
            <h1>Unit Testing</h1>

            <form method="post">
                <?php wp_nonce_field('ephpicman_run_tests'); ?>
                <?php submit_button('Run Unit Tests', 'primary', 'ephpicman_run_tests'); ?>
            </form>

            <?php if ($results !== null) : ?>
                <h2>Results</h2>

                <p>
                    <strong><?php echo esc_html((string) $total); ?></strong> tests,
                    <strong><?php echo esc_html((string) $passed); ?></strong> passed,
                    <strong><?php echo esc_html((string) $failed); ?></strong> failed,
                    <strong><?php echo esc_html((string) $assertions); ?></strong> assertions.
                </p>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Test</th>
                            <th>Assertions</th>
                            <th>Duration</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($result->class()); ?></strong><br>
                                    <code><?php echo esc_html($result->method()); ?></code>
                                </td>
                                <td><?php echo esc_html((string) $result->assertions()); ?></td>
                                <td><?php echo esc_html(number_format_i18n($result->duration(), 4)); ?>s</td>
                                <td>
                                    <?php if ($result->passed()) : ?>
                                        <strong>PASS</strong>
                                    <?php elseif ($result->failure() !== null) : ?>
                                        <strong>FAIL</strong>
                                        <details>
                                            <summary>Failure details</summary>
                                            <p><?php echo esc_html($result->failure()->message()); ?></p>
                                            <p><strong>Expected</strong></p>
                                            <pre><?php echo esc_html(var_export($result->failure()->expected(), true)); ?></pre>
                                            <p><strong>Actual</strong></p>
                                            <pre><?php echo esc_html(var_export($result->failure()->actual(), true)); ?></pre>
                                        </details>
                                    <?php else : ?>
                                        <strong>ERROR</strong>
                                        <details>
                                            <summary>Error details</summary>
                                            <p><?php echo esc_html($result->error() ?? 'Unknown error'); ?></p>
                                        </details>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Returns all valid test directories registered for the current request.
     *
     * The Test Suite's own tests/ directory is always included first. Other
     * plugins can append directories with the ephpicman_test_directories filter.
     *
     * @return list<string> Existing, unique test directory paths.
     */
    public function getTestsDirectories(): array
    {
        $directories = [dirname(__DIR__) . '/tests'];

        /** @var list<string> $directories */
        $directories = apply_filters('ephpicman_test_directories', $directories);

        return array_values(array_unique(array_filter(
            $directories,
            static fn (mixed $directory): bool => is_string($directory) && is_dir($directory)
        )));
    }
}
