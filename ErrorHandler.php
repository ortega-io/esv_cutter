<?php

/**
 * 
 * Errors Handler
 * 
 * Registra errores y exceciones.
 *
 * @package     EleccionesSV
 * @subpackage  Cutter
 * @author      Otoniel Ortega <ortega_x2@hotmail.com>
 * @copyright   2015 Otoniel Ortega (c)
 * @version     1.0
 * @license     CC BY-NC 4.0 (https://creativecommons.org/licenses/by-nc/4.0/)
 * 
 */


register_shutdown_function("fatal_handler");

function fatal_handler()
{
	
	$error = error_get_last();

  	if( $error !== NULL)
  	{
  		if($error["type"]==1)
  		{
  			echo "[FATAL ERROR]\n";		
  		}
  		
  	}
	
}
