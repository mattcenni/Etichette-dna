<?php

class Etichette_DNA_Admin_Notices {
    
    public function __construct() {
        add_action('admin_notices', array($this, 'show_email_status'));
    }
    
    public function show_email_status() {
        $email_status = get_transient('etichette_dna_email_status');
        
        if ($email_status) {
            $class = $email_status['success'] ? 'notice-success' : 'notice-error';
            $message = $email_status['success'] ? 
                'Email inviata con successo a ' . $email_status['to'] : 
                'Errore nell\'invio dell\'email. Dettagli: To=' . $email_status['to'] . 
                ', Subject=' . $email_status['subject'];
            
            printf(
                '<div class="notice %s is-dismissible"><p>%s</p></div>',
                esc_attr($class),
                esc_html($message)
            );
            
            delete_transient('etichette_dna_email_status');
        }
    }
} 