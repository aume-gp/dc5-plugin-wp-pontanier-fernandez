<?php

defined( 'ABSPATH' ) or die( 'Accès refusé !' );

function add_reservation_menu() {
    add_menu_page(
        'Gestion du Calendrier',
        'Calendrier Réservations',
        'manage_options',
        'gestion-calendrier',
        'display_gestion_page',
        'dashicons-calendar-alt',
        6
    );
}

add_action( 'admin_menu', 'add_reservation_menu' );

function obtenir_toutes_les_reservations() {
    global $wpdb;
    $table_reservations = $wpdb->prefix . 'reservations';
    $table_users = $wpdb->users;

    $query = "
        SELECT $table_reservations.id, $table_reservations.user_id, $table_users.user_login, $table_reservations.date_reservation, $table_reservations.heure_reservation 
        FROM $table_reservations 
        INNER JOIN $table_users ON $table_reservations.user_id = $table_users.ID
    ";

    return $wpdb->get_results($query, ARRAY_A);
}


function verifier_suppression_reservation() {
    if (isset($_GET['delete_reservation'])) {
        $reservation_id = intval($_GET['delete_reservation']);
        supprimer_reservation($reservation_id);
        wp_redirect(admin_url('admin.php?page=gestion-calendrier'));
        exit;
    }
}

add_action('admin_init', 'verifier_suppression_reservation');

function supprimer_reservation($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservations';
    $wpdb->delete($table_name, array('id' => $id));
}

function display_gestion_page() {
    echo '<h1>Gestion du Calendrier</h1>';

    $reservations = obtenir_toutes_les_reservations();

    echo '<table border="1" style="width:100%;">
        <tr>
            <th>ID Réservation</th>
            <th>Utilisateur</th>
            <th>Date Réservation</th>
            <th>Heure Réservation</th>
            <th>Actions</th>
        </tr>';

    foreach ($reservations as $reservation) {
        echo '<tr>
                <td>' . esc_html($reservation['id']) . '</td>
                <td>' . esc_html($reservation['user_id']) . ' (' . esc_html($reservation['user_login']) . ')</td>
                <td>' . esc_html($reservation['date_reservation']) . '</td>
                <td>' . esc_html($reservation['heure_reservation']) . '</td>
                <td><a style="color:red;" href="?page=gestion-calendrier&delete_reservation=' . esc_attr($reservation['id']) . '">Supprimer</a></td>
              </tr>';
    }

    echo '</table>';
}

?>