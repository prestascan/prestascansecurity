<div id="ps_migration" class="error">
    <div class="ps_migration_overlay">
        <div class="ps_migration_card">
            <div class="ps_migration_header">
                <div class="ps_migration_title">
                    <span class="ps_migration_title-text">{l s='PrestaScan is evolving into' mod='prestascansecurity'}</span>
                    <img class="ps_migration_logo" src="{$urlZentriaLogo}" alt="Zentria">
                </div>
            </div>

            <div class="ps_migration_alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                     stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <p class="ps_migration_alert-title">
                        {l s='Error during Zentria module installation' mod='prestascansecurity'}
                    </p>
                    <p class="ps_migration_alert-text">
                        {$error|escape:'html':'UTF-8'}
                    </p>
                </div>
                <div class="ps_migration_actions">
                    <a href="{$migrationLink}"
                       class="ps_migration_btn">{l s='Retry' mod='prestascansecurity'}</a>
                </div>
            </div>

            <div class="ps_migration_note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <div>
                {l s='If the problem persists, please try installing Zentria' mod='prestascansecurity'}
                    <a href="https://lp.profileo.com/zentria-pour-prestashop"
                       target="_blank">{l s='manually' mod='prestascansecurity'}</a>
                    {l s='or contact the' mod='prestascansecurity'}
                    <a href="https://zentria.profileo.com/fr/contactez-nous" target="_blank">{l s='Zentria support' mod='prestascansecurity'}</a>
                </div>
            </div>
        </div>
    </div>
</div>