<?php
/**
 * Trafikverket example 
 *
 * @author    Erik Pettersson <mail@ptz0n.se>
 * @copyright 2011 Erik Pettersson <mail@ptz0n.se>,
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */

// Include service class
require_once('service.php');

// Create instance
$trafikverket = new Trafikverket();

// Setup attributes
$attr = array(
    'plugin' => 'WOW',
    'table'  => 'LpvTrafiklagen',
    'filter' => '',
    'order'  => '',
    'select' => '',
    'limit'  => 10,
);

// Explore returned data
echo '<pre>';
print_r($trafikverket->query($attr));