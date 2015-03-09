<?php

/**
 * 
 * Cutter Tester
 * 
 * Script para testing de las clases encargadas de extraer los datos
 * de los diferentes formatos de actas de conteo de votos en imagenes individuales 
 *
 * @package     EleccionesSV
 * @subpackage  Cutter
 * @author      Otoniel Ortega <ortega_x2@hotmail.com>
 * @copyright   2015 Otoniel Ortega (c)
 * @version     1.0
 * @license 	CC BY-NC 4.0 (https://creativecommons.org/licenses/by-nc/4.0/)
 * 
 */

include('CutA.class.php');
include('CutADetails.class.php');
include('CutExtra.class.php');
include('EdgesMapper.class.php');


// Cortando papeletas [PARLACEN] =================================== */
// ================================================================= */ 

/* Cortando papeletas formato A ==================================== */

$cutA = new CutA();
$cutA->setActa('http://elecciones2015.tse.gob.sv/actas/actas/2/san_salvador/soyapango/centro_escolar_colon/jrv_01415_A.png');

$cutA->getConteo();
$cutA->getTotales();


/* Cortando papeletas formato A1-A8 ================================ */

$cutA1 = new CutADetails();
$cutA1->setActa('http://elecciones2015.tse.gob.sv/actas/actas/2/san_salvador/soyapango/centro_escolar_colon/jrv_01415_A1.png');

$cutA1->getConteo();


/* Cortando papeletas formato B-F ================================== */

$cutB = new CutExtra();
$cutB->setActa('http://elecciones2015.tse.gob.sv/actas/actas/2/san_salvador/soyapango/centro_escolar_colon/jrv_01415_B.png');

$cutB->getConteo();



// Cortando papeletas [DIPUTADOS] ================================== */
// ================================================================= */ 

/* Cortando papeletas formato A ==================================== */

$cutA = new CutA();
$cutA->setActa('http://elecciones2015.tse.gob.sv/actas/actas/3/san_salvador/soyapango/centro_escolar_colon/jrv_01415_A.png');

$cutA->getConteo();
$cutA->getTotales();


/* Cortando papeletas formato A1-A8 ================================ */

$cutA1 = new CutADetails();
$cutA1->setActa('http://elecciones2015.tse.gob.sv/actas/actas/3/san_salvador/soyapango/centro_escolar_colon/jrv_01415_A1.png');

$cutA1->getConteo();


/* Cortando papeletas formato B-F ================================== */

$cutB = new CutExtra();
$cutB->setActa('http://elecciones2015.tse.gob.sv/actas/actas/3/san_salvador/soyapango/centro_escolar_colon/jrv_01415_B.png');

$cutB->getConteo();



// Cortando papeletas [ALCALDES] =================================== */
// ================================================================= */ 

/* Cortando papeletas formato A ==================================== */

$cutA = new CutA();
$cutA->setActa('http://elecciones2015.tse.gob.sv/actas/actas/5/san_salvador/soyapango/centro_escolar_colon/jrv_01415_A.png');

$cutA->getConteo();
$cutA->getTotales();


?>