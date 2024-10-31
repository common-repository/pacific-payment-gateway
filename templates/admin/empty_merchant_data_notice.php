<div class="notice notice-error is-dismissible">
    <p>
        <?php echo wp_kses($message, [
            'a' => [
                'href'  => [],
                'title' => [],
            ]
        ]) ?>
    </p>
</div>