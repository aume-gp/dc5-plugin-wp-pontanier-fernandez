<?php

/**
 * Plugin Name: DC5C aume-hugo-calendar
 * Description: Calendrier de réservation
 * Version: 1.0
 * Author: PONTANIER Guillaume & FERNANDEZ Hugo
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Tested up to: 6.4.2
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Accès refusé !' );

require_once plugin_dir_path( __FILE__ ) . 'back_office_calendar.php';

function creer_table_reservation() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservations';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        date_reservation date NOT NULL,
        heure_reservation time NOT NULL,
        PRIMARY KEY  (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'creer_table_reservation');

function supprimer_table_reservation() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservations';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}

register_deactivation_hook(__FILE__, 'supprimer_table_reservation');

function afficher_formulaire_reservation() {
    if (is_user_logged_in()) {
        echo "<form id='formulaire_reservation' action='' method='post'>
            <label for='date_reservation'>Date:</label>
            <input type='date' id='date_reservation' name='date_reservation' required>
            
            <label for='heure_reservation'>Heure:</label>
            <select id='heure_reservation' name='heure_reservation' required>
                <option value=''>Sélectionnez une date d'abord</option>
            </select>
            
            <input type='submit' value='Réserver'>
        </form>";
    } else {
        echo '<p style="color:red";>Vous devez être connecté pour faire une réservation.</p>';
    }
}

function generer_options_heures($dateChoisie) {
    $heureDebut = 9; // Début à 9h00
    $heureFin = 18.5; // Fin à 18h30
    $interval = 30; // Intervalle de 30 minutes
    $options = '';

    $heureActuelle = date('H:i');
    $dateActuelle = date('Y-m-d');

    $heureActuelleArrondie = date('H:i', ceil(strtotime($heureActuelle) / 1800) * 1800);

    if ($dateChoisie < $dateActuelle) {
        return 'La date sélectionnée est passée. Veuillez choisir une autre date.';
    }

    $creneauxReserves = obtenir_creneaux_reserves($dateChoisie);

    for ($heure = $heureDebut; $heure <= $heureFin; $heure += $interval / 60) {
        $heureAffichage = sprintf('%02d:%02d', floor($heure), ($heure - floor($heure)) * 60);

        if ($dateChoisie == $dateActuelle && $heureAffichage <= $heureActuelleArrondie) {
            continue;
        }

        if (in_array($heureAffichage, $creneauxReserves)) {
            continue;
        }

        $options .= "<option value=\"$heureAffichage\">$heureAffichage</option>";
    }

    return $options;
}

function obtenir_creneaux_reserves($date) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservations';
    $reservations = $wpdb->get_results($wpdb->prepare("SELECT heure_reservation FROM $table_name WHERE date_reservation = %s", $date), ARRAY_A);

    $creneaux = array();
    foreach ($reservations as $reservation) {
        $heureFormattee = substr($reservation['heure_reservation'], 0, 5);
        $creneaux[] = $heureFormattee;
    }

    return $creneaux;
}

?>