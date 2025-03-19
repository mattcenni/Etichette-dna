<?php
if (!defined('ABSPATH')) exit;

// Assicurati che la funzione non sia già definita
if (!function_exists('etichette_dna_admin_page')) {
    function etichette_dna_admin_page() {
        // Verifica i permessi
        if (!current_user_can('manage_options')) {
            return;
        }

        // Salva le impostazioni
        if (isset($_POST['etichette_dna_save_settings'])) {
            check_admin_referer('etichette_dna_settings');
            update_option('etichette_dna_setting_1', sanitize_text_field($_POST['setting_1']));
            add_settings_error('etichette_dna_messages', 'etichette_dna_message', 'Impostazioni salvate con successo', 'success');
        }
        ?>

        <div class="wrap">
            <h1 class="text-2xl font-bold mb-6"><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php settings_errors('etichette_dna_messages'); ?>

            <!--
  This example requires updating your template:

  ```
  <html class="h-full bg-gray-100">
  <body class="h-full">
  ```
-->
<div class="min-h-full">
  <nav class="bg-fuchsia-700">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <div class="flex items-center">
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-4">
              <button href="#" data-page="dashboard" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 focus:bg-neutral-100 focus:text-gray-700 hover:bg-gray-300 hover:text-gray-700">Dashboard</button>
              <button href="#" data-page="etichette" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 focus:bg-neutral-100 focus:text-gray-700 hover:bg-gray-300 hover:text-gray-700">Etichette</button>
              <button href="#" data-page="assistenza" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 focus:bg-neutral-100 focus:text-gray-700 hover:bg-gray-300 hover:text-gray-700">Assistenza</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <header class="bg-white shadow-sm">
    <div class="mx-auto etichette-dna-admin-container px-4 py-6 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold tracking-tight text-gray-900" id="page-title">Dashboard</h1>
    </div>
  </header>
  <main>
    <!-- Dashboard Content -->
    <div id="dashboard-content" class="page-content mx-auto etichette-dna-admin-container px-4 py-6 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 gap-6">
        <!-- Card 1 -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Riepilogo slots etichette</h3>
            <dl class="grid grid-cols-1 gap-5 mb-6">
              <div class="px-4 py-2 bg-gray-50 rounded-lg total-slots">
                <dt class="text-sm font-medium text-gray-500">Totale slots creati</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                <?php echo intval(Etichette_DNA_DB::get_total_slots()); ?>
                </dd>
              </div>
              <div class="px-4 py-2 bg-gray-50 rounded-lg used-slots">
                <dt class="text-sm font-medium text-gray-500">Totale slots utilizzati</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                    <?php echo intval(Etichette_DNA_DB::get_used_slots()); ?>
                </dd>
              </div>
              <div class="px-4 py-2 bg-gray-50 rounded-lg remaining-slots">
                <dt class="text-sm font-medium text-gray-500">Totale slots rimanenti</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                    <?php echo intval(Etichette_DNA_DB::get_total_slots() - Etichette_DNA_DB::get_used_slots()); ?>
                </dd>
              </div>
            </dl>
            <div class="flex gap-4">
                <?php 
                $current_user = wp_get_current_user();
                $allowed_email = 'assistenza@tobugroup.com';
                
                if ($current_user->user_email === $allowed_email) : ?>
                    <input id="slots-number" type="number" class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-fuchsia-600 sm:text-sm sm:leading-6" placeholder="Numero slots">
                    <button id="add-slots-button" type="button" class="rounded-md bg-fuchsia-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-fuchsia-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-fuchsia-600">
                        Aggiungi slots
                    </button>
                <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Riepilogo etichette generate</h3>
            <!-- Contenitore per le etichette -->
            <div id="etichette-generated-summary" class="space-y-2">
                <!-- Qui verranno inseriti dinamicamente i box delle etichette -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Etichette Content -->
    <div id="etichette-content" class="page-content mx-auto etichette-dna-admin-container px-4 py-6 sm:px-6 lg:px-8 hidden">
      <div class="grid grid-cols-2 gap-6">
        <!-- Card 1 -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Riepilogo etichette</h3>
                <!-- Contenitore per le etichette -->
                <div id="etichette-summary" class="space-y-2">
                    <!-- Qui verranno inseriti dinamicamente i box delle etichette -->
                </div>
            </div>
        </div>
      
        <!-- Card 2 -->
        <div class="bg-white overflow-hidden shadow rounded-lg card-2">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Nuova etichetta</h3>
                <div id="etichetta-form-container">
                    <!-- Qui verrà inserito il form -->
                </div>
            </div>
        </div>

      </div>
    </div>

    <!-- Assistenza Content -->
    <div id="assistenza-content" class="page-content mx-auto etichette-dna-admin-container px-4 py-6 sm:px-6 lg:px-8 hidden">
      <div class="grid grid-cols-2 gap-6">
        <!-- Card 1 -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">FAQ</h3>
          </div>
        </div>
        <!-- Card 2 -->
        <div class="bg-white overflow-hidden shadow rounded-lg h-auto">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Segnala un problema</h3>
            <div class="relative" style="height: 600px;">
                
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    const pageContents = document.querySelectorAll('.page-content');
    const pageTitle = document.getElementById('page-title');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Rimuovi la classe attiva da tutti i link
            navLinks.forEach(l => {
                l.classList.remove('bg-neutral-100', 'text-gray-900');
                l.classList.add('text-gray-300');
            });
            
            // Aggiungi la classe attiva al link cliccato
            this.classList.add('bg-neutral-100', 'text-gray-900');
            this.classList.remove('text-gray-300');
            
            // Nascondi tutti i contenuti
            pageContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Mostra il contenuto selezionato
            const pageId = this.getAttribute('data-page');
            document.getElementById(pageId + '-content').classList.remove('hidden');
            
            // Aggiorna il titolo
            pageTitle.textContent = this.textContent;
        });
    });
});
</script>

        </div>
        <?php
    }
}

// Aggiungi questa nuova funzione
if (!function_exists('etichette_dna_etichette_page')) {
    function etichette_dna_etichette_page() {
        // Verifica i permessi
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="min-h-full">
          <nav class="bg-fuchsia-700">
            <div class="mx-auto etichette-dna-admin-container px-4 sm:px-6 lg:px-8">
              <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                  <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                      <a href="?page=etichette-dna" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Dashboard</a>
                      <a href="?page=etichette-dna-etichette" class="rounded-md bg-neutral-100 px-3 py-2 text-sm font-medium text-gray-900" aria-current="page">Etichette</a>
                      <a href="#" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Assistenza</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </nav>

          <header class="bg-white shadow-sm">
            <div class="mx-auto etichette-dna-admin-container px-4 py-6 sm:px-6 lg:px-8">
              <h1 class="text-3xl font-bold tracking-tight text-gray-900">Gestione Etichette</h1>
            </div>
          </header>
          <main>
            <div class="mx-auto etichette-dna-admin-container px-4 py-6 sm:px-6 lg:px-8">
              <div class="grid grid-cols-2 gap-6">
                <!-- Card 1 -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Nuova etichetta</h3>
                  </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Assistenza</h3>
                  </div>
                </div>
              </div>
            </div>
          </main>
        </div>
        <?php
    }
}

// Aggiungi questa funzione per caricare il JavaScript
function etichette_dna_admin_scripts($hook) {
    if (strpos($hook, 'etichette-dna') !== false) {
        wp_enqueue_script(
            'etichette-dna-navigation',
            plugins_url('/admin/js/navigation.js', dirname(__FILE__)),
            array(),
            '1.0.0',
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'etichette_dna_admin_scripts');

