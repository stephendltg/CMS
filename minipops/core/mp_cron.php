<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestionnaire de tâche : cron
 *
 *
 * @package cms mini POPS
 * @subpackage cron
 * @version 1
 */

/*

    add_action('my_task', 'do_task');

    function do_task(){
        ....code ...
    }

    do_event( time(), false, 'my_task');
*/

/**
 * Plannifie un évenement soit récurrent soit simple
 * @param (integer) $timestamp
 * @param (string)  $recurrence (plage-horaires)
 * @param (string)  $hook
 * @param (array)   $args    
 * @return
 */
function do_event( $timestamp, $recurrence = false , $hook, $args = null ) {

    // On vérifie que le timestamp est un entier positif
    if ( ! is_numeric( $timestamp ) || $timestamp <= 0 )
        return false;

    // Don't schedule a duplicate if there's already an identical event due within 10 minutes of it 
    // On bloque 
    $next = get_scheduled($hook, $args);
    if ( $next || abs( $next - $timestamp ) <= 10 * MINUTE_IN_SECONDS )
        return false;

    $crons = get_option('crons');
    $key   = md5(serialize($args));

    if( false == $recurrence ){

        $crons['_'. $timestamp][$hook][$key] = array( 'schedule' => false, 'args' => $args );

    } else {

        $schedules = get_schedules();

        if ( !isset( $schedules[$recurrence] ) )
            return false;
 
        $crons['_'.$timestamp][$hook][$key] = array( 'schedule' => $recurrence, 'args' => $args, 'interval' => $schedules[$recurrence]['interval'] );
    }

    uksort( $crons, "strnatcasecmp" );

    // Mise à jour des tâches
    update_option( 'crons', $crons );
}



/**
 * Récupère les plages horaires formatés
 * @return $schedules
 */
function get_schedules(){

    $schedules = array(
        // 'second'     => array( 'interval' => 10,      'display' => 'Once Hourly' ), // For my test
        'hourly'     => array( 'interval' => HOUR_IN_SECONDS,      'display' => 'Once Hourly' ),
        'twicedaily' => array( 'interval' => 12 * HOUR_IN_SECONDS, 'display' => 'Twice Daily' ),
        'daily'      => array( 'interval' => DAY_IN_SECONDS,       'display' => 'Once Daily'  ),
        'weekly'     => array( 'interval' => 604800,               'display' => 'Once weekly' )
    );
    return array_merge( apply_filters( 'cron_schedules', array() ), $schedules );
}


/**
 * Lecture d'une plannification
 * @param (string)  $hook
 * @param (array)   $args    
 * @param (array)   $mode  timestamp|schedule
 * @return timestamp|plage-horaire
 */
function get_scheduled( $hook, $args = null, $mode = 'timestamp' ) {

    $crons = get_option('crons');

    if ( empty($crons) )  return false;

    $key = md5(serialize($args));

    foreach ( $crons as $timestamp => $cron ) {

        if ( isset( $cron[$hook][$key] ) ){

            switch ($mode) {
                case 'schedule':
                    return $cron[$hook][$key]['schedule'];
                    break;
                default:
                    return ltrim($timestamp,'_');
                    break;
            }
        }
    }
    return false;
}


/**
 * Réplannifie un évenement
 * @param (integer) $timestamp
 * @param (string)  $recurrence (plage-horaires)
 * @param (string)  $hook
 * @param (array)   $args    
 * @return
 */
function reschedule_event( $timestamp, $recurrence, $hook, $args = null ) {

    // On vérifie que le timestamp est un entier positif
    if ( ! is_numeric( $timestamp ) || $timestamp <= 0 )
        return false;

    $crons     = get_option('crons');
    $schedules = get_schedules();
    $key       = md5( serialize( $args ) );
    $interval  = 0;

    // First we try to get it from the schedule
    if ( isset( $schedules[ $recurrence ] ) )
        $interval = $schedules[$recurrence]['interval'];

    // Now we try to get it from the saved interval in case the schedule disappears
    if ( 0 == $interval )
        $interval = $crons[$timestamp][$hook][$key]['interval'];

    // Now we assume something is wrong and fail to schedule
    if ( 0 == $interval )
        return false;

    $now = time();
    if ( $timestamp >= $now )
        $timestamp = $now + $interval;
    else
        $timestamp = $now + ( $interval - ( ( $now - $timestamp ) % $interval ) );

    do_event( $timestamp, $recurrence, $hook, $args );
}


/**
 * Déplannifie un évenement
 * @param (integer) $timestamp
 * @param (string)  $hook
 * @param (array)   $args    
 * @return
 */
function unschedule_event( $timestamp, $hook, $args = null ) {

    // On vérifie que le timestamp est un entier positif
    if ( ! is_numeric( $timestamp ) || $timestamp <= 0 )
        return false;

    $crons = get_option('crons');
    $key   = md5(serialize($args));

    unset( $crons[ '_'. $timestamp][$hook][$key] );

    if ( empty($crons[ '_'. $timestamp][$hook]) )
        unset( $crons[ '_'. $timestamp][$hook] );

    if ( empty($crons[ '_'. $timestamp]) )
        unset( $crons[ '_'. $timestamp] );
        
    update_option( 'crons', $crons );
}


/**
 * Efface un evenement plannifier
 * @param (string) $hook
 * @param (array)  $args    
 * @return
 */
function clear_scheduled_event( $hook, $args = null ) {

    $crons = get_option('crons');

    if ( empty( $crons ) )
        return;

    $key = md5( serialize( $args ) );

    foreach ( $crons as $timestamp => $cron ) {

        if ( isset( $cron[ $hook ][ $key ] ) )
            unschedule_event( ltrim($timestamp,'_'), $hook, $args );
    }
}



/*
 * Run cron
 */
function mp_cron() {

    if ( false === $crons = get_option('crons') )
        return; 

    $gmt_time = microtime( true );
    $keys     = array_keys( $crons );

    // On laisse 10 minutes entre chaque action
    if( $gmt_time < ( get_option('doing_cron', 0 ) + 10 * MINUTE_IN_SECONDS ) )
        return;

    // On supprime l'option
    delete_option('doing_cron');

    // Toogle
    $cron = false;

    if ( isset($keys[0]) && ltrim($keys[0], '_') > $gmt_time )
        return;

    foreach ( $crons as $timestamp => $cronhooks ) {

        $timestamp = ltrim($timestamp, '_');

        if ( $timestamp > $gmt_time ) break;

        $cron = true;

        foreach ( $cronhooks as $hook => $keys ) {

            foreach ( $keys as $k => $v ) {

                $schedule = $v['schedule'];

                // On supprime la tâche
                unschedule_event( $timestamp, $hook, $v['args'] );

                // On récrée une tâche si event récurrent
                if ( $schedule != false )
                    reschedule_event($timestamp, $schedule, $hook, $v['args']);

                // On lance l'action une fois minipops complètemement chargé ( le fichier de conf etant ecrit l'action ne se répètera pas indéfiniment)
                add_action('loaded', function() use($hook, $v) { do_action($hook, $v['args']); } );
            }
        }
    }

    if($cron){

        // On créer une option pour surveiller l'esapce temps entre chaque actions 
        add_option('doing_cron', sprintf( '%.22F', $gmt_time ) );

        // On prépare la redirection avant de lancer le script principal
        add_action('loaded', function(){ 

            // On force le time out du script
            ignore_user_abort(true);

            // On redirige vers la page demandé
            ob_start();
            $location = get_current_url('raw');
            header("Location: $location", true, 302);
            echo ' ';
            // flush any buffers and send the headers
            while ( @ob_end_flush() );
            flush();

            } , 1 );

        // On force la sortie du script principal
        add_action('loaded', function(){ exit() ; /* On force la sortie */ } , PHP_INT_MAX );
    }

}