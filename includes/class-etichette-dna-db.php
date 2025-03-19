<?php

class Etichette_DNA_DB {
    private static $db_version = '1.1'; // Aggiungiamo versione del DB
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $current_version = get_option('etichette_dna_db_version', '1.0');

        // Se la versione è già aggiornata, non fare nulla
        if (version_compare($current_version, self::$db_version, '>=')) {
            return;
        }

        // Tabella slots
        $table_slots = $wpdb->prefix . 'dna_slots';
        $sql_slots = "CREATE TABLE IF NOT EXISTS $table_slots (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            numero_slots int NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Tabella etichette
        $table_etichette = $wpdb->prefix . 'dna_etichette';
        $sql_etichette = "CREATE TABLE IF NOT EXISTS $table_etichette (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            id_installazione mediumint(9) NOT NULL,
            nome_prodotto varchar(255),
            descrizione_prodotto varchar(255),
            denominazione_prodotto varchar(255),
            anno int,
            data_creazione TIMESTAMP NULL,
            lotto varchar(100),
            immagine varchar(255),
            ingredienti text,
            energia_kj decimal(10,2),
            energia_kcal decimal(10,2),
            grassi decimal(10,2),
            acidi_grassi_saturi decimal(10,2),
            carboidrati decimal(10,2),
            zuccheri decimal(10,2),
            proteine decimal(10,2),
            sale decimal(10,2),
            bottiglia boolean DEFAULT 0,
            bottiglia_materiale varchar(100),
            bottiglia_raccolta varchar(100),
            bottiglia_raccolta_immagine varchar(255),
            capsula boolean DEFAULT 0,
            capsula_materiale varchar(100),
            capsula_raccolta varchar(100),
            capsula_raccolta_immagine varchar(255),
            gabbietta boolean DEFAULT 0,
            gabbietta_materiale varchar(100),
            gabbietta_raccolta varchar(100),
            gabbietta_raccolta_immagine varchar(255),
            tappo boolean DEFAULT 0,
            tappo_materiale varchar(100),
            tappo_raccolta varchar(100),
            tappo_raccolta_immagine varchar(255),
            etichetta varchar(255),
            qr_code varchar(255),
            PRIMARY KEY  (id),
            FOREIGN KEY  (id_installazione) REFERENCES $table_slots(id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_slots);
        dbDelta($sql_etichette);

        // Aggiorna la versione del database
        update_option('etichette_dna_db_version', self::$db_version);
    }

    public static function add_slots($numero_slots) {
        global $wpdb;
        $table_slots = $wpdb->prefix . 'dna_slots';
        $table_etichette = $wpdb->prefix . 'dna_etichette';

        // Controlla se esistono già slots
        $existing_slots = $wpdb->get_var("SELECT id FROM $table_slots LIMIT 1");

        if ($existing_slots) {
            // Aggiorna il numero di slots esistente
            $wpdb->query($wpdb->prepare(
                "UPDATE $table_slots SET numero_slots = numero_slots + %d WHERE id = %d",
                $numero_slots,
                $existing_slots
            ));
            $id_installazione = $existing_slots;
        } else {
            // Inserisce il primo record
            $wpdb->insert(
                $table_slots,
                array('numero_slots' => $numero_slots),
                array('%d')
            );
            $id_installazione = $wpdb->insert_id;
        }

        // Crea le nuove righe nella tabella etichette
        for ($i = 0; $i < $numero_slots; $i++) {
            $wpdb->insert(
                $table_etichette,
                array(
                    'id_installazione' => $id_installazione,
                    'data_creazione' => null  // Esplicitamente settiamo NULL
                ),
                array(
                    '%d',  // per id_installazione
                    'NULL' // per data_creazione
                )
            );
        }

        return $id_installazione;
    }

    public static function get_total_slots() {
        global $wpdb;
        $table_etichette = $wpdb->prefix . 'dna_etichette';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_etichette");
    }

    public static function get_used_slots() {
        global $wpdb;
        $table_etichette = $wpdb->prefix . 'dna_etichette';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_etichette WHERE etichetta IS NOT NULL");
    }
} 