<div class="wrap">
    <h1>AIAlbie Prompter Settings</h1>

    <div class="card">
        <h2>API Configuration</h2>
        <form method="post" action="options.php">
            <?php settings_fields('aialbie_prompter_options'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="abacus_api_key">Abacus.ai API Key</label>
                    </th>
                    <td>
                        <input type="password" 
                               id="abacus_api_key" 
                               name="aialbie_prompter_api_key" 
                               value="<?php echo esc_attr(get_option('aialbie_prompter_api_key')); ?>" 
                               class="regular-text">
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <div class="card">
        <h2>Usage Statistics</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Prompts Optimized</td>
                    <td><?php echo get_option('aialbie_prompter_total_prompts', 0); ?></td>
                </tr>
                <tr>
                    <td>Active Users</td>
                    <td><?php echo get_option('aialbie_prompter_active_users', 0); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
