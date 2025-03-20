<?php

class Etichette_Dna_Ajax {
    
    private $materiali_raccolta = [
        'Vetro trasparente - GL 70' => 'Raccolta vetro',
        'Vetro verde - GL 71' => 'Raccolta vetro',
        'Vetro marrone - GL 72' => 'Raccolta vetro',
        'Alluminio - ALU 41' => 'Raccolta metalli',
        'Polilaminato-Alluminio - C/ALU 90' => 'Raccolta metalli',
        'Poliaccoppiato - C/PVC 90' => 'Raccolta plastica',
        'PET - PET 1' => 'Raccolta plastica',
        'Polietilene - PE-HD 2' => 'Raccolta plastica',
        'Polipropilene - PP 5' => 'Raccolta plastica',
        'Polistirolo - PS 6' => 'Raccolta plastica',
        'PVC - PVC 3' => 'Raccolta plastica',
        'Acciaio - FE 40' => 'Raccolta metalli',
        'Sughero - FOR 51' => 'Raccolta differenziata dedicata'
    ];

    private $materiali_traduzioni = [
        'Vetro trasparente - GL 70' => 'Clear glass - GL 70',
        'Vetro verde - GL 71' => 'Green glass - GL 71',
        'Vetro marrone - GL 72' => 'Brown glass - GL 72',
        'Alluminio - ALU 41' => 'Aluminium - ALU 41',
        'Polilaminato-Alluminio - C/ALU 90' => 'Paper and aluminium composite - C/ALU 90',
        'Poliaccoppiato - C/PVC 90' => 'Paper and PVC composite - C/PVC 90',
        'PET - PET 1' => 'PET (Polyethylene terephthalate) - PET 1',
        'Polietilene - PE-HD 2' => 'Polyethylene - PE-HD 2',
        'Polipropilene - PP 5' => 'Polypropylene - PP 5',
        'Polistirolo - PS 6' => 'Polystyrene - PS 6',
        'PVC - PVC 3' => 'PVC (Polyvinyl chloride) - PVC 3',
        'Acciaio - FE 40' => 'Steel - FE 40',
        'Sughero - FOR 51' => 'Cork - FOR 51'
    ];

    public function __construct() {
        // Aggiungi questi log per debug
        error_log('=== Costruttore Etichette_Dna_Ajax ===');
        
        add_action('wp_ajax_add_slots', array($this, 'handle_add_slots'));
        add_action('wp_ajax_get_etichette', array($this, 'handle_get_etichette'));
        add_action('wp_ajax_get_generated_etichette', array($this, 'handle_get_generated_etichette'));
        add_action('wp_ajax_save_etichetta', array($this, 'handle_save_etichetta'));
        add_action('wp_ajax_generate_etichetta', array($this, 'handle_generate_etichetta'));
        add_action('wp_ajax_get_etichetta_data', array($this, 'handle_get_etichetta_data'));
        add_action('wp_ajax_generate_etichetta_html', array($this, 'handle_generate_etichetta_html'));
        add_action('wp_ajax_update_etichetta_path', array($this, 'handle_update_etichetta_path'));
        add_action('wp_ajax_generate_qrcode', array($this, 'handle_generate_qrcode'));
        add_action('wp_ajax_delete_etichetta', array($this, 'delete_etichetta'));
        error_log('Azione AJAX registrata: generate_etichetta_html');
    }

    public function handle_add_slots() {
        check_ajax_referer('etichette_dna_nonce', 'nonce');

        $numero_slots = intval($_POST['numero_slots']);
        
        if ($numero_slots <= 0) {
            wp_send_json_error('Numero slots non valido');
        }

        $id_installazione = Etichette_DNA_DB::add_slots($numero_slots);
        
        if ($id_installazione) {
            wp_send_json_success(array(
                'message' => sprintf('Aggiunti %d slots', $numero_slots),
                'stats' => array(
                    'total_slots' => Etichette_DNA_DB::get_total_slots(),
                    'used_slots' => Etichette_DNA_DB::get_used_slots(),
                    'remaining_slots' => Etichette_DNA_DB::get_total_slots() - Etichette_DNA_DB::get_used_slots()
                )
            ));
        } else {
            wp_send_json_error('Errore durante l\'aggiunta degli slots');
        }
    }

    public function handle_get_etichette() {
        check_ajax_referer('etichette_dna_nonce', 'nonce');

        global $wpdb;
        $table_etichette = $wpdb->prefix . 'dna_etichette';
        
        $etichette = $wpdb->get_results("
            SELECT id, nome_prodotto, descrizione_prodotto, anno, lotto, data_creazione, etichetta, qr_code 
            FROM $table_etichette 
            ORDER BY data_creazione DESC
        ");

        wp_send_json_success($etichette);
    }

    public function handle_get_generated_etichette() {
        check_ajax_referer('etichette_dna_nonce', 'nonce');

        global $wpdb;
        $table_etichette = $wpdb->prefix . 'dna_etichette';
        
        $etichette = $wpdb->get_results("
            SELECT id, nome_prodotto, descrizione_prodotto, anno, lotto, data_creazione, etichetta, qr_code 
            FROM $table_etichette 
            WHERE etichetta IS NOT NULL 
            ORDER BY data_creazione DESC
        ");

        wp_send_json_success($etichette);
    }

    public function handle_save_etichetta() {
        check_ajax_referer('etichette_dna_nonce', 'nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'dna_etichette';

        // L'ID è sempre richiesto perché stiamo modificando uno slot esistente
        $etichetta_id = isset($_POST['etichetta_id']) ? intval($_POST['etichetta_id']) : 0;

        // Prepara i dati per l'aggiornamento
        $data = [
            'nome_prodotto' => sanitize_text_field($_POST['nome_prodotto']),
            'anno' => intval($_POST['anno']),
            'descrizione_prodotto' => sanitize_text_field($_POST['descrizione_prodotto']),
            'denominazione_prodotto' => sanitize_text_field($_POST['denominazione_prodotto']),
            'lotto' => sanitize_text_field($_POST['lotto']),
            'ingredienti' => wp_kses_post($_POST['ingredienti']),
            'immagine' => esc_url_raw($_POST['immagine']),
            
            // Materiali con gestione booleana
            'bottiglia' => (!empty($_POST['bottiglia_materiale']) && $_POST['bottiglia_materiale'] !== 'Nessuno') ? 1 : 0,
            'bottiglia_materiale' => sanitize_text_field($_POST['bottiglia_materiale']),
            'bottiglia_raccolta' => isset($this->materiali_raccolta[$_POST['bottiglia_materiale']]) ? 
                                  $this->materiali_raccolta[$_POST['bottiglia_materiale']] : '',
            'bottiglia_raccolta_immagine' => $this->get_material_image_url($_POST['bottiglia_materiale']),
            
            'capsula' => (!empty($_POST['capsula_materiale']) && $_POST['capsula_materiale'] !== 'Nessuno') ? 1 : 0,
            'capsula_materiale' => sanitize_text_field($_POST['capsula_materiale']),
            'capsula_raccolta' => isset($this->materiali_raccolta[$_POST['capsula_materiale']]) ? 
                                  $this->materiali_raccolta[$_POST['capsula_materiale']] : '',
            'capsula_raccolta_immagine' => $this->get_material_image_url($_POST['capsula_materiale']),
            
            'gabbietta' => (!empty($_POST['gabbietta_materiale']) && $_POST['gabbietta_materiale'] !== 'Nessuno') ? 1 : 0,
            'gabbietta_materiale' => sanitize_text_field($_POST['gabbietta_materiale']),
            'gabbietta_raccolta' => isset($this->materiali_raccolta[$_POST['gabbietta_materiale']]) ? 
                                  $this->materiali_raccolta[$_POST['gabbietta_materiale']] : '',
            'gabbietta_raccolta_immagine' => $this->get_material_image_url($_POST['gabbietta_materiale']),

            'tappo' => (!empty($_POST['tappo_materiale']) && $_POST['tappo_materiale'] !== 'Nessuno') ? 1 : 0,            
            'tappo_materiale' => sanitize_text_field($_POST['tappo_materiale']),
            'tappo_raccolta' => isset($this->materiali_raccolta[$_POST['tappo_materiale']]) ? 
                                  $this->materiali_raccolta[$_POST['tappo_materiale']] : '',
            'tappo_raccolta_immagine' => $this->get_material_image_url($_POST['tappo_materiale']),
            
            // Valori nutrizionali
            'energia_kj' => floatval($_POST['energia_kj']),
            'energia_kcal' => floatval($_POST['energia_kcal']),
            'grassi' => floatval($_POST['grassi']),
            'acidi_grassi_saturi' => floatval($_POST['acidi_grassi_saturi']),
            'carboidrati' => floatval($_POST['carboidrati']),
            'zuccheri' => floatval($_POST['zuccheri']),
            'proteine' => floatval($_POST['proteine']),
            'sale' => floatval($_POST['sale'])
        ];

        // Aggiorna la riga esistente
        $result = $wpdb->update(
            $table_name,
            $data,
            ['id' => $etichetta_id]
        );

        if ($result === false) {
            wp_send_json_error('Errore durante l\'aggiornamento dello slot: ' . $wpdb->last_error);
        } else {
            wp_send_json_success([
                'message' => 'Slot aggiornato con successo',
                'id' => $etichetta_id
            ]);
        }
    }

    private function get_material_image_url($material_value) {
        if (empty($material_value)) {
            return '';
        }

        // Estrae il codice dopo il pattern ' - '
        $parts = explode(' - ', $material_value);
        if (count($parts) < 2) {
            return '';
        }

        $code = trim($parts[1]); // Prende la parte dopo ' - ' (es. 'FOR 51')
        
        // Formatta il codice per il nome del file
        $filename = str_replace(
            [' ', '/'], 
            ['-', ':'], 
            $code
        );

        // Costruisce l'URL completo dell'immagine
        return plugins_url('img/' . $filename . '.png', dirname(__FILE__));
    }

    public function handle_get_etichetta_data() {
        check_ajax_referer('etichette_dna_nonce', 'nonce');

        $etichetta_id = isset($_POST['etichetta_id']) ? intval($_POST['etichetta_id']) : 0;

        global $wpdb;
        $table_name = $wpdb->prefix . 'dna_etichette';

        // Recupera la riga corrispondente all'ID del box
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $etichetta_id
        );

        $etichetta = $wpdb->get_row($query, ARRAY_A);

        if (!$etichetta) {
            wp_send_json_error('Errore nel caricamento dei dati dell\'etichetta');
            return;
        }

        // Debug: verifica il contenuto di $etichetta
        error_log('Dati etichetta: ' . print_r($etichetta, true));

        // Sanitizza i dati prima di inviarli al client
        $sanitized_data = [
            'id' => $etichetta['id'],
            'nome_prodotto' => sanitize_text_field($etichetta['nome_prodotto']),
            'anno' => intval($etichetta['anno']),
            'descrizione_prodotto' => sanitize_text_field($etichetta['descrizione_prodotto']),
            'denominazione_prodotto' => sanitize_text_field($etichetta['denominazione_prodotto']),
            'lotto' => sanitize_text_field($etichetta['lotto']),
            'ingredienti' => wp_kses_post($etichetta['ingredienti']),
            'immagine' => esc_url_raw($etichetta['immagine']),
            
            // Materiali
            'bottiglia' => intval($etichetta['bottiglia']),
            'bottiglia_materiale' => sanitize_text_field($etichetta['bottiglia_materiale']),
            'bottiglia_raccolta' => sanitize_text_field($etichetta['bottiglia_raccolta']),
            'bottiglia_raccolta_immagine' => esc_url_raw($etichetta['bottiglia_raccolta_immagine']),
            
            'capsula' => intval($etichetta['capsula']),
            'capsula_materiale' => sanitize_text_field($etichetta['capsula_materiale']),
            'capsula_raccolta' => sanitize_text_field($etichetta['capsula_raccolta']),
            'capsula_raccolta_immagine' => esc_url_raw($etichetta['capsula_raccolta_immagine']),
            
            'gabbietta' => intval($etichetta['gabbietta']),
            'gabbietta_materiale' => sanitize_text_field($etichetta['gabbietta_materiale']),
            'gabbietta_raccolta' => sanitize_text_field($etichetta['gabbietta_raccolta']),
            'gabbietta_raccolta_immagine' => esc_url_raw($etichetta['gabbietta_raccolta_immagine']),
            
            'tappo' => intval($etichetta['tappo']),
            'tappo_materiale' => sanitize_text_field($etichetta['tappo_materiale']),
            'tappo_raccolta' => sanitize_text_field($etichetta['tappo_raccolta']),
            'tappo_raccolta_immagine' => esc_url_raw($etichetta['tappo_raccolta_immagine']),
            
            // Valori nutrizionali
            'energia_kj' => floatval($etichetta['energia_kj']),
            'energia_kcal' => floatval($etichetta['energia_kcal']),
            'grassi' => floatval($etichetta['grassi']),
            'acidi_grassi_saturi' => floatval($etichetta['acidi_grassi_saturi']),
            'carboidrati' => floatval($etichetta['carboidrati']),
            'zuccheri' => floatval($etichetta['zuccheri']),
            'proteine' => floatval($etichetta['proteine']),
            'sale' => floatval($etichetta['sale']),
            
            // Campi per i pulsanti
            'etichetta' => esc_url_raw($etichetta['etichetta']),
            'qr_code' => esc_url_raw($etichetta['qr_code'])
        ];

        // Debug: verifica il contenuto di $sanitized_data
        error_log('Dati sanitizzati: ' . print_r($sanitized_data, true));

        wp_send_json_success($sanitized_data);
    }

    public function handle_generate_etichetta_html() {
        error_log('=== INIZIO handle_generate_etichetta_html ===');
        error_log('POST data: ' . print_r($_POST, true));
        
        check_ajax_referer('etichette_dna_nonce', 'nonce');

        $etichetta_id = isset($_POST['etichetta_id']) ? intval($_POST['etichetta_id']) : 0;
        $nome_prodotto = sanitize_text_field($_POST['nome_prodotto']);
        $anno = sanitize_text_field($_POST['anno']);
        $descrizione_prodotto = sanitize_text_field($_POST['descrizione_prodotto']);

        // Crea lo slug del file mantenendo le maiuscole nel lotto
        $filename = str_replace(' ', '', $nome_prodotto) . str_replace(' ', '', $descrizione_prodotto) . str_replace(' ', '', $anno) . '.html';
        
        // Crea la cartella etichette nella root del sito
        $etichette_dir = ABSPATH . 'etichette';
        
        if (!file_exists($etichette_dir)) {
            if (!mkdir($etichette_dir, 0755, true)) {
                wp_send_json_error('Impossibile creare la cartella etichette');
                return;
            }
            file_put_contents($etichette_dir . '/.htaccess', "Options -Indexes\n");
        }

        $filepath = $etichette_dir . '/' . $filename;

        // Recupera i dati dell'etichetta dal database
        global $wpdb;
        $table_name = $wpdb->prefix . 'dna_etichette';
        $etichetta = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $etichetta_id
        ), ARRAY_A);

        if (!$etichetta) {
            wp_send_json_error('Etichetta non trovata nel database');
            return;
        }

        // Debug: verifica i dati dell'etichetta
        error_log('Dati etichetta per generazione HTML: ' . print_r($etichetta, true));

        // Genera il contenuto HTML usando il metodo della classe
        $html_content = $this->generate_etichetta_html($etichetta);

        // Debug: verifica il contenuto HTML generato
        error_log('HTML generato: ' . substr($html_content, 0, 500) . '...');

        // Salva o sovrascrivi il file
        if (file_put_contents($filepath, $html_content) === false) {
            wp_send_json_error('Errore durante il salvataggio del file');
            return;
        }

        // Calcola l'URL pubblico del file
        $site_url = get_site_url();
        $file_url = $site_url . '/etichette/' . $filename;

        // Aggiorna la data_creazione solo se non è già impostata
        if (empty($etichetta['data_creazione'])) {
            $wpdb->update(
                $table_name,
                ['data_creazione' => current_time('mysql')],
                ['id' => $etichetta_id]
            );
        }

        wp_send_json_success([
            'message' => 'Etichetta generata con successo',
            'file_path' => $file_url
        ]);
    }

    // Funzione helper per formattare i valori nutrizionali
    private function format_nutritional_value($value) {
        return floatval($value) == 0 || floatval($value) == intval($value) 
            ? number_format($value, 0, ',', '') 
            : number_format($value, 1, ',', '');
    }

    // Funzione helper per determinare il colore del bordo
    private function get_material_border_color($material_code) {
        if (empty($material_code)) return '#CCCCCC';
        
        // Estrae il codice del materiale (es. "GL" da "Vetro trasparente - GL 70")
        preg_match('/- ([A-Z\/]+)/', $material_code, $matches);
        $base_material = isset($matches[1]) ? strtok($matches[1], ' ') : '';
        
        // Mappa dei colori per tipo di materiale
        $material_colors = [
            'GL' => '#0DB54A',  // Verde per vetro
            'ALU' => '#F6C412', // Giallo per alluminio
            'FE' => '#F6C412',  // Giallo per ferro/metalli
            'PET' => '#FF69B4', // Rosa per PET
            'PE-HD' => '#FF69B4', // Rosa per PE-HD
            'PP' => '#FF69B4',   // Rosa per PP
            'PS' => '#FF69B4',   // Rosa per PS
            'PVC' => '#FF69B4',  // Rosa per PVC
            'FOR' => '#9F6619',  // Marrone per sughero
            'C/ALU' => '#F6C412', // Giallo per compositi con alluminio
            'C/PVC' => '#F6C412' // Giallo per compositi con PVC
        ];
        
        return isset($material_colors[$base_material]) ? $material_colors[$base_material] : '#CCCCCC';
    }

    private function generate_etichetta_html($etichetta) {
        $plugin_dir = plugin_dir_url(dirname(dirname(__FILE__)));
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="it">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title><?php echo esc_html(str_replace(' ', '', $etichetta['nome_prodotto'] . $etichetta['anno'] . $etichetta['lotto'])); ?></title>
            <link rel="stylesheet" href="<?php echo esc_url($plugin_dir); ?>etichette-dna/admin/css/etichetta-styles.css">
            <link rel="stylesheet" href="<?php echo esc_url($plugin_dir); ?>etichette-dna/public/css/etichette-dna-public.css">
            <script src="https://unpkg.com/@material-tailwind/html@latest/scripts/tabs.js"></script>
            <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
        </head>
        <body>
            <div class="container mx-auto px-4 py-8">
                <!-- Layout superiore a 3 blocchi -->
                <div class="bg-white rounded-lg shadow-md p-6 space-y-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 md:gap-8 mb-8">
                    <!-- Blocco 1: Informazioni prodotto -->
                    <div class="card-titolo lg:p-6">
                        <h1 class="titolo-etichetta text-3xl font-medium"><?php echo esc_html($etichetta['nome_prodotto']); ?></h1>
                        <p class="text-gray-700 titolo-etichetta-paragrafo"><?php echo nl2br(esc_html($etichetta['descrizione_prodotto'])); ?></p>
                        <p class="font-medium titolo-etichetta-paragrafo"><?php echo esc_html($etichetta['denominazione_prodotto']); ?></p>
                        <p class="titolo-etichetta-paragrafo"><?php echo esc_html($etichetta['anno']); ?></p>
                    </div>

                    <!-- Blocco 2: Immagine -->
                    <div class="lg:p-6">
                        <img src="<?php echo esc_url($etichetta['immagine']); ?>" alt="Immagine prodotto" class="w-full h-auto object-cover rounded-lg">
                    </div>
                </div>

                <!-- Layout a schede -->
                <div class="gap-6 grid grid-cols-1 md:grid-cols-2 mb-8">
                    <!-- Scheda 1: Ingredienti -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold mb-4">Ingredienti/<i>Ingredients</i></h2>
                        <p class="text-gray-700"><?php echo nl2br(wp_kses_post($etichetta['ingredienti'])); ?></p>
                    </div>

                    <!-- Scheda 2: Valori nutrizionali -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold mb-4">Dichiarazione Nutrizionale/<i>Nutrition Declaration</i></h2>
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="text-left p-2 border">Valori medi/<i>Average values</i></th>
                                    <th class="text-right p-2 border">per 100 ml</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white">
                                    <td class="p-2 border">Valore energetico/<i>Energy</i></td>
                                    <td class="text-right p-2 border"><?php echo esc_html($this->format_nutritional_value($etichetta['energia_kj'])); ?> kJ / <?php echo esc_html($this->format_nutritional_value($etichetta['energia_kcal'])); ?> kcal</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="p-2 border">Grassi/<i>Fat</i></td>
                                    <td class="text-right p-2 border"><?php echo esc_html($this->format_nutritional_value($etichetta['grassi'])); ?> g</td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="p-2 border pl-6">di cui acidi grassi saturi/<i>of which saturates</i></td>
                                    <td class="text-right p-2 border"><?php echo esc_html($this->format_nutritional_value($etichetta['acidi_grassi_saturi'])); ?> g</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="p-2 border">Carboidrati/<i>Carbohydrate</i></td>
                                    <td class="text-right p-2 border"><?php echo esc_html($this->format_nutritional_value($etichetta['carboidrati'])); ?> g</td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="p-2 border pl-6">di cui zuccheri/<i>of which sugars</i></td>
                                    <td class="text-right p-2 border"><?php echo esc_html($this->format_nutritional_value($etichetta['zuccheri'])); ?> g</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="p-2 border">Proteine/<i>Protein</i></td>
                                    <td class="text-right p-2 border"><?php echo esc_html($this->format_nutritional_value($etichetta['proteine'])); ?> g</td>
                                </tr>
                                <tr class="bg-white">
                                    <td class="p-2 border">Sale/<i>Salt</i></td>
                                    <td class="text-right p-2 border"><?php echo esc_html($this->format_nutritional_value($etichetta['sale'])); ?> g</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <!-- Scheda 3: Etichettatura ambientale -->
                    <h2 class="text-xl font-bold mb-4">Etichettatura ambientale/<i>Environmental labelling</i></h2>
                        
                    <div class="gap-6 grid grid-cols-1 lg:grid-cols-3">
                        
                        <?php if ($etichetta['bottiglia']) : ?>
                            <div class="flex items-center justify-between p-4 mb-4 rounded-lg" 
                                 style="border: 2px solid <?php echo esc_attr($this->get_material_border_color($etichetta['bottiglia_materiale'])); ?>">
                                <div>
                                    <p class="font-bold">Bottiglia/<i>Bottle</i></p>
                                    <p><?php echo esc_html($etichetta['bottiglia_materiale']); ?></p>
                                    <p><i><?php echo isset($this->materiali_traduzioni[$etichetta['bottiglia_materiale']]) ? 
                                        esc_html($this->materiali_traduzioni[$etichetta['bottiglia_materiale']]) : ''; ?></i></p>
                                    <p><?php echo esc_html($etichetta['bottiglia_raccolta']); ?></p>
                                </div>
                                <img src="<?php echo esc_url($etichetta['bottiglia_raccolta_immagine']); ?>" 
                                     alt="Raccolta bottiglia" class="w-16 h-16 object-contain">
                            </div>
                        <?php endif; ?>

                        <?php if ($etichetta['capsula']) : ?>
                            <div class="flex items-center justify-between p-4 mb-4 rounded-lg" 
                                 style="border: 2px solid <?php echo esc_attr($this->get_material_border_color($etichetta['capsula_materiale'])); ?>">
                                <div>
                                    <p class="font-bold">Capsula/<i>Capsules</i></p>
                                    <p><?php echo esc_html($etichetta['capsula_materiale']); ?></p>
                                    <p><i><?php echo isset($this->materiali_traduzioni[$etichetta['capsula_materiale']]) ? 
                                        esc_html($this->materiali_traduzioni[$etichetta['capsula_materiale']]) : ''; ?></i></p>
                                    <p><?php echo esc_html($etichetta['capsula_raccolta']); ?></p>
                                </div>
                                <img src="<?php echo esc_url($etichetta['capsula_raccolta_immagine']); ?>" 
                                     alt="Raccolta capsula" class="w-16 h-16 object-contain">
                            </div>
                        <?php endif; ?>

                        <?php if ($etichetta['gabbietta']) : ?>
                            <div class="flex items-center justify-between p-4 mb-4 rounded-lg" 
                                 style="border: 2px solid <?php echo esc_attr($this->get_material_border_color($etichetta['gabbietta_materiale'])); ?>">
                                <div>
                                    <p class="font-bold">Gabbietta/<i>Muselet</i></p>
                                    <p><?php echo esc_html($etichetta['gabbietta_materiale']); ?></p>
                                    <p><i><?php echo isset($this->materiali_traduzioni[$etichetta['gabbietta_materiale']]) ? 
                                        esc_html($this->materiali_traduzioni[$etichetta['gabbietta_materiale']]) : ''; ?></i></p>
                                    <p><?php echo esc_html($etichetta['gabbietta_raccolta']); ?></p>
                                </div>
                                <img src="<?php echo esc_url($etichetta['gabbietta_raccolta_immagine']); ?>" 
                                     alt="Raccolta gabbietta" class="w-16 h-16 object-contain">
                            </div>
                        <?php endif; ?>

                        <?php if ($etichetta['tappo']) : ?>
                            <div class="flex items-center justify-between p-4 mb-4 rounded-lg" 
                                 style="border: 2px solid <?php echo esc_attr($this->get_material_border_color($etichetta['tappo_materiale'])); ?>">
                                <div>
                                    <p class="font-bold">Tappo/<i>Cap</i></p>
                                    <p><?php echo esc_html($etichetta['tappo_materiale']); ?></p>
                                    <p><i><?php echo isset($this->materiali_traduzioni[$etichetta['tappo_materiale']]) ? 
                                        esc_html($this->materiali_traduzioni[$etichetta['tappo_materiale']]) : ''; ?></i></p>
                                    <p><?php echo esc_html($etichetta['tappo_raccolta']); ?></p>
                                </div>
                                <img src="<?php echo esc_url($etichetta['tappo_raccolta_immagine']); ?>" 
                                     alt="Raccolta tappo" class="w-16 h-16 object-contain">
                            </div>
                        <?php endif; ?>
                        
                    </div>
                    <div class="col-span-3">
                        <p class="font-medium text-xl text-center">Verificare le disposizioni del proprio Comune/<i>Check your municipality rules</i></p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    public function handle_update_etichetta_path() {
        check_ajax_referer('etichette_dna_nonce', 'nonce');

        $etichetta_id = isset($_POST['etichetta_id']) ? intval($_POST['etichetta_id']) : 0;
        $etichetta_path = isset($_POST['etichetta_path']) ? esc_url_raw($_POST['etichetta_path']) : '';

        if (!$etichetta_id || !$etichetta_path) {
            wp_send_json_error('Dati mancanti');
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'dna_etichette';

        $result = $wpdb->update(
            $table_name,
            ['etichetta' => $etichetta_path],
            ['id' => $etichetta_id]
        );

        if ($result === false) {
            wp_send_json_error('Errore durante l\'aggiornamento del percorso');
        } else {
            wp_send_json_success('Percorso etichetta aggiornato con successo');
        }
    }

    public function handle_generate_qrcode() {
        check_ajax_referer('etichette_dna_nonce', 'nonce');

        $etichetta_id = isset($_POST['etichetta_id']) ? intval($_POST['etichetta_id']) : 0;

        // Recupera i dati dell'etichetta
        global $wpdb;
        $table_name = $wpdb->prefix . 'dna_etichette';
        $etichetta = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $etichetta_id
        ), ARRAY_A);

        if (!$etichetta || empty($etichetta['etichetta'])) {
            wp_send_json_error('Etichetta non trovata o URL non disponibile');
            return;
        }

        // Configura il QR code
        $qr_config = array(
            'data' => $etichetta['etichetta'],
            'size' => 1000,
            'config' => json_encode(array(
                'body' => 'square',
                'eye' => 'frame0',
                'eyeBall' => 'ball0',
                'erf1' => [],
                'erf2' => [],
                'erf3' => [],
                'brf1' => [],
                'brf2' => [],
                'brf3' => [],
                'bodyColor' => '#000000',
                'bgColor' => '#FFFFFF',
                'eye1Color' => '#000000',
                'eye2Color' => '#000000',
                'eye3Color' => '#000000',
                'eyeBall1Color' => '#000000',
                'eyeBall2Color' => '#000000',
                'eyeBall3Color' => '#000000',
                'gradientColor1' => '',
                'gradientColor2' => '',
                'gradientType' => 'linear',
                'gradientOnEyes' => false,
                'logo' => '',
                'logoMode' => 'default'
            )),
            'file' => 'pdf'
        );

        // Chiamata API per generare il QR code
        $response = wp_remote_post('https://api.qrcode-monkey.com/qr/custom', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($qr_config),
            'timeout' => 45,
            'sslverify' => false
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error('Errore nella chiamata API: ' . $response->get_error_message());
            return;
        }

        // Crea la cartella qrcodes se non esiste
        $qrcodes_dir = ABSPATH . 'qrcodes';
        if (!file_exists($qrcodes_dir)) {
            if (!mkdir($qrcodes_dir, 0755, true)) {
                wp_send_json_error('Impossibile creare la cartella qrcodes');
                return;
            }
            // Proteggi la cartella
            file_put_contents($qrcodes_dir . '/.htaccess', "Options -Indexes\n");
        }

        // Usa lo stesso nome del file HTML
        $html_filename = basename($etichetta['etichetta']);
        $qr_filename = str_replace('.html', '.pdf', $html_filename);
        $filepath = $qrcodes_dir . '/' . $qr_filename;

        // Salva il file QR
        if (file_put_contents($filepath, $response['body']) === false) {
            wp_send_json_error('Errore durante il salvataggio del QR Code');
            return;
        }

        // Aggiorna il campo qr_code nel database
        $site_url = get_site_url();
        $qr_url = $site_url . '/qrcodes/' . $qr_filename;
        
        $wpdb->update(
            $table_name,
            ['qr_code' => $qr_url],
            ['id' => $etichetta_id]
        );

        wp_send_json_success([
            'message' => 'QR Code generato con successo',
            'qr_url' => $qr_url
        ]);
    }

    public function delete_etichetta() {
        try {
            if (!wp_verify_nonce($_POST['nonce'], 'etichette_dna_nonce')) {
                wp_send_json_error('Nonce non valido');
                return;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'dna_etichette';
            $etichetta_id = intval($_POST['etichetta_id']);

            // Recupera i percorsi dei file prima dell'eliminazione
            $etichetta = $wpdb->get_row(
                $wpdb->prepare("SELECT etichetta, qr_code FROM $table_name WHERE id = %d", $etichetta_id)
            );

            if (!$etichetta) {
                wp_send_json_error('Etichetta non trovata');
                return;
            }

            // Elimina i file fisici se esistono
            if ($etichetta->etichetta) {
                $etichetta_path = str_replace(
                    array(site_url(), 'http://localhost:10043'),
                    ABSPATH,
                    $etichetta->etichetta
                );
                
                if (file_exists($etichetta_path)) {
                    unlink($etichetta_path);
                }
            }

            if ($etichetta->qr_code) {
                $qr_path = str_replace(
                    array(site_url(), 'http://localhost:10043'),
                    ABSPATH,
                    $etichetta->qr_code
                );
                
                if (file_exists($qr_path)) {
                    unlink($qr_path);
                }
            }

            // Aggiorna il record mantenendo solo id e id_installazione
            $result = $wpdb->update(
                $table_name,
                array(
                    'nome_prodotto' => null,
                    'anno' => null,
                    'descrizione_prodotto' => null,
                    'denominazione_prodotto' => null,
                    'lotto' => null,
                    'ingredienti' => null,
                    'immagine' => null,
                    'bottiglia' => 0,
                    'bottiglia_materiale' => null,
                    'bottiglia_raccolta' => null,
                    'bottiglia_raccolta_immagine' => null,
                    'capsula' => 0,
                    'capsula_materiale' => null,
                    'capsula_raccolta' => null,
                    'capsula_raccolta_immagine' => null,
                    'gabbietta' => 0,
                    'gabbietta_materiale' => null,
                    'gabbietta_raccolta' => null,
                    'gabbietta_raccolta_immagine' => null,
                    'tappo' => 0,
                    'tappo_materiale' => null,
                    'tappo_raccolta' => null,
                    'tappo_raccolta_immagine' => null,
                    'energia_kj' => null,
                    'energia_kcal' => null,
                    'grassi' => null,
                    'acidi_grassi_saturi' => null,
                    'carboidrati' => null,
                    'zuccheri' => null,
                    'proteine' => null,
                    'sale' => null,
                    'etichetta' => null,
                    'qr_code' => null,
                    'data_creazione' => null
                ),
                array('id' => $etichetta_id),
                array(
                    '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                    '%d', '%s', '%s', '%s',
                    '%d', '%s', '%s', '%s',
                    '%d', '%s', '%s', '%s',
                    '%d', '%s', '%s', '%s',
                    '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f',
                    '%s', '%s', '%s'
                ),
                array('%d')
            );

            if ($result !== false) {
                wp_send_json_success('Etichetta eliminata con successo');
            } else {
                wp_send_json_error('Errore nell\'aggiornamento del database: ' . $wpdb->last_error);
            }

        } catch (Exception $e) {
            wp_send_json_error('Errore durante l\'eliminazione: ' . $e->getMessage());
        }
    }
} 