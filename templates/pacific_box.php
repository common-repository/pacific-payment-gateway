<div id="pacific-plugin__main" data-plugin-version="<?php echo esc_attr($pacificPluginVersion) ?>">
  <div id="pacific-plugin__main-wrapper">
    <div class="pacific-plugin__header">
<!--      <div class="pacific-plugin__qr-wrapper">-->
<!--        <img-->
<!--          class="pacific-plugin__qr-img"-->
<!--          src="--><?//= App::get('PACIFIC_PLUGIN_URL') ?><!--/public/dist/images/content/example_QR.png"-->
<!--          alt="kod QR"-->
<!--        />-->
<!--        <p class="pacific-plugin__qr-text">Kod QR</p>-->
<!--      </div>-->
      <div class="pacific-plugin__header-text">SZYBKI ZAKUP</div>
        <div class="pacific-plugin__logos-wrapper">
            <div class="pacific-plugin__blik-logo-wrapper">
                <img
                    class="pacific-plugin__blik-logo"
                    src="<?php echo esc_url($pacificPluginUrl) ?>/public/dist/images/content/blik_logo.svg"
                    alt="logo Blik"
                />
            </div>
            <div class="pacific-plugin__logo-wrapper">
                <img
                    class="pacific-plugin__logo"
                    src="<?php echo esc_url($pacificPluginUrl) ?>/public/dist/images/content/pacific_logo.svg"
                    alt="logo Pacific"
                />
            </div>
      </div>
    </div>
    <div class="pacific-plugin__form-wrapper">
      <div class="pacific-plugin__form-content">
        <input
          id="pacific-plugin__main-email-input"
          class="pacific-plugin__input"
          placeholder="Podaj e-mail"
          type="email"
          required
        />
        <a id="pacific-plugin__main-button" type="button" tabindex="0">
            <span id="pacific-plugin__main-button-text">Kupuję i płacę</span>
<!--            <img-->
<!--               id="pacific-plugin__main-button-text"-->
<!--               src="--><?php //echo esc_url($pacificPluginUrl) ?><!--/public/dist/images/content/main_button.svg"-->
<!--               alt="check"-->
<!--               />-->
            <svg
                id="pacific-plugin__spinner-main"
                class="pacific-plugin__spinner-main"
                viewBox="0 0 50 50"
            >
                <circle
                    class="path"
                    cx="25"
                    cy="25"
                    r="20"
                    fill="none"
                    stroke-width="5"
                ></circle>
            </svg>
        </a>
      </div>
      <div id="pacific-plugin__error-main-email" class="pacific-plugin-error-input"></div>
      <div id="pacific-plugin__snackbar"></div>
    </div>
  </div>
</div>
