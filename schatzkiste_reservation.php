<?php
/**
 * Plugin Name: Tamaras Schatzkiste Reservations Mechanismus
 * Description: Reserviert Produkte und blendet reservierte Produkte im Shop aus.
 * Version: 1.0
 * Author: Ramona Maurer
 */

class ReservierungsPlugin {

    public function __construct() {

        // Hinzufügen eines Hooks beim Hinzufügen von Produkten zum Warenkorb
        add_action('woocommerce_add_to_cart', array($this, 'produkt_reservieren'), 10, 6);

        // Hinzufügen eines Hooks zum Ausblenden reservierter Produkte im Shop
        add_action('woocommerce_product_query', array($this, 'ausgeblendete_produkte_filtern'));

        // Hinzufügen eines Hooks zum Hinzufügen von benutzerdefinierten Produkt-Metafeldern
        add_action('woocommerce_product_options_general_product_data', array($this, 'reservierungs_metafeld_hinzufuegen'));

        // Hinzufügen eines Hooks zum Speichern des benutzerdefinierten Metafelds
        add_action('woocommerce_process_product_meta', array($this, 'reservierungs_metafeld_speichern'));

        // Hinzufügen eines Hooks zum Löschen der Reservierung beim Entfernen des Produkts aus dem Warenkorb
        add_action('woocommerce_remove_cart_item', array($this, 'reservierung_entfernen'), 10, 2);
        
        add_action('wp_enqueue_scripts', array($this,'my_custom_pagination_enqueue_script'));
        add_filter('woocommerce_add_to_cart_validation', array($this, 'my_custom_add_to_cart_validation'), 10, 3);

    }

    public function my_custom_add_to_cart_validation($valid, $product_id, $quantity) {
        if (get_post_meta($product_id, '_reserved', true) !== 'yes') {
            // Bedingung ist erfüllt
            return true;
        }

        // Bedingung ist nicht erfüllt, zeige eine Fehlermeldung an
        wc_add_notice("Dieser Produkt ist leider bereits von einem anderen Kunden reserviert.", 'error');
        return false;
    
    }

    public function produkt_reservieren($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        // Überprüfen, ob das Produkt reserviert werden soll (füge hier deine Bedingungen hinzu)
        $reserviertes_produkt = true;
        $current_user = wp_get_current_user();
        if ($reserviertes_produkt && isset($current_user) && $current_user->ID > 0) {
            // Produkte als reserviert markieren und Reservierungszeitpunkt und Benutzer speichern
            update_post_meta($product_id, '_reserved', 'yes');
            update_post_meta($product_id, '_reservation_timestamp', current_time('timestamp'));

            // Benutzer-ID speichern
            
            update_post_meta($product_id, '_reservation_user_id', $current_user->ID);
        } 
    }

    public function ausgeblendete_produkte_filtern($q) {
        $q->set('meta_query', array(array('key' => '_reserved', 'value' => 'yes', 'compare' => 'NOT EXISTS')));
    }

    function my_custom_pagination_enqueue_script() {
        wp_enqueue_script('my-custom-pagination-js', plugin_dir_url(__FILE__) . 'my-custom-pagination.js', array('jquery'), '1.0', true);
    }
    

    public function reservierungs_metafeld_hinzufuegen() {
        woocommerce_wp_checkbox(array('id' => '_reserved', 'label' => 'Reserviertes Produkt'));

        echo '<div class="options_group">';
        $reservation_time = get_post_meta(get_the_ID(), '_reservation_timestamp', true);
        if(isset($reservation_time) && $reservation_time > 0){
            woocommerce_wp_text_input(array(
                'id'          => '_reservation_timestamp',
                'label'       => 'Reservierungszeitpunkt',
                'placeholder' => '',
                'description' => '',
                'custom_attributes' => array('readonly' => 'readonly'),
                'value'       => date('Y-m-d H:i:s', $reservation_time),
            ));
        }
        
        $reservation_user_id = get_post_meta(get_the_ID(), '_reservation_user_id', true);
        if(isset($reservation_user_id) && strlen($reservation_user_id)> 1){    
            // Neues Textfeld für Benutzer-ID hinzufügen
            woocommerce_wp_text_input(array(
                'id'          => '_reservation_user_id',
                'label'       => 'Benutzer-ID',
                'placeholder' => '',
                'description' => '',
                'custom_attributes' => array('readonly' => 'readonly'),
                'value'       => esc_html($reservation_user_id),
            ));
        }

        echo '</div>';
    }

    public function reservierungs_metafeld_speichern($product_id) {
        $reserviert = isset($_POST['_reserved']) ? 'yes' : 'no';
        update_post_meta($product_id, '_reserved', $reserviert);
    }

    public function reservierung_entfernen($cart_item_key, $cart) {
        $product_id = $cart->cart_contents[$cart_item_key]['product_id'];
        delete_post_meta($product_id, '_reserved');
        delete_post_meta($product_id, '_reservation_timestamp');
        delete_post_meta($product_id, '_reservation_user_id');
    }

}

// Instanziierung der Klasse
$reservierungs_plugin = new ReservierungsPlugin();
