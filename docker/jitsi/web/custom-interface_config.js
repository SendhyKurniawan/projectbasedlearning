/*
 * PBL Workspace — Jitsi web branding overrides.
 *
 * The docker-jitsi-meet `web` container appends this file to the generated
 * interface_config.js at startup, so anything set here wins over the defaults.
 * It controls the parts the Laravel app CANNOT set via the room URL hash:
 * the browser-tab title, the welcome-page branding, and (paired with the
 * favicon bind-mount in docker-compose) the look of the standalone Jitsi tab.
 *
 * Deploy: copy to ~/.jitsi-meet-cfg/web/custom-interface_config.js on pjbl-vm,
 * then `docker compose restart web`. See docs/ops/jitsi-self-host.md Step 6.
 */
var APP_NAME = 'PBL Workspace';

interfaceConfig.APP_NAME = APP_NAME;
interfaceConfig.NATIVE_APP_NAME = APP_NAME;
interfaceConfig.PROVIDER_NAME = APP_NAME;

// Use our logo as the in-call watermark; hide Jitsi's own brand chrome.
interfaceConfig.SHOW_JITSI_WATERMARK = true;
interfaceConfig.JITSI_WATERMARK_LINK = 'https://polimedia.pblworkspace.com';
interfaceConfig.DEFAULT_LOGO_URL = 'images/pbl-logo.svg';
interfaceConfig.DEFAULT_WELCOME_PAGE_LOGO_URL = 'images/pbl-logo.svg';
interfaceConfig.SHOW_BRAND_WATERMARK = false;
interfaceConfig.SHOW_WATERMARK_FOR_GUESTS = false;
interfaceConfig.SHOW_POWERED_BY = false;
